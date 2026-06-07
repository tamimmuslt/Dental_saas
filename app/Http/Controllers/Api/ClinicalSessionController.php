<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClinicalSession;
use App\Models\SessionProcedure;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Appointment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClinicalSessionController extends Controller
{
    use ApiResponse;

    /**
     * جلب التاريخ المرضي للجلسات الخاصة بمريض معين
     */
    public function getPatientSessions(Request $request, $patientId)
    {
        // جلب الجلسات مع الإجراءات المنفذة والروشتات الطبية التابعة لها
        $sessions = ClinicalSession::whereHas('appointment', function($q) use ($patientId) {
            $q->where('patient_id', $patientId);
        })
        ->with(['appointment.doctor', 'procedures.service', 'prescription.items'])
        ->orderBy('created_at', 'desc')
        ->get();

        return $this->successResponse($sessions, 'تم جلب السجل العلاجي للمريض بنجاح');
    }

    /**
     * تسجيل جلسة علاجية جديدة + الإجراءات المنفذة + الروشتة الطبية (كلها في طلب واحد)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,id',
            'complaint' => 'required|string', // شكوى المريض
            'diagnosis' => 'required|string', // تشخيص الطبيب
            'notes' => 'nullable|string',
            
            // مصفوفة الخدمات الطبية المنفذة في هذه الجلسة
            'procedures' => 'required|array|min:1',
            'procedures.*.service_id' => 'required|exists:service_price_list,id',
            'procedures.*.price_charged' => 'required|numeric', // السعر المحتسب للخدمة
            
            // بيانات الروشتة الطبية (اختيارية)
            'prescription_notes' => 'nullable|string',
            'prescription_items' => 'nullable|array',
            'prescription_items.*.drug_name' => 'required_with:prescription_items|string',
            'prescription_items.*.dosage' => 'required_with:prescription_items|string',
            'prescription_items.*.duration' => 'required_with:prescription_items|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('خطأ في التحقق من البيانات المطلوبة للجلسة', 422, $validator->errors());
        }

        $appointment = Appointment::find($request->appointment_id);

        // استخدام الـ DB Transaction لضمان سلامة تخزين الجداول الثلاثة معاً
        DB::beginTransaction();

        try {
            // 1. حفظ الجلسة العلاجية الأساسية
            $session = ClinicalSession::create([
                'appointment_id' => $request->appointment_id,
                'complaint' => $request->complaint,
                'diagnosis' => $request->diagnosis,
                'notes' => $request->notes,
            ]);

            // 2. تحديث حالة الموعد المرتبط بها إلى "مكتمل - completed" تلقائياً
            $appointment->update(['status' => 'completed']);

            // 3. حفظ الإجراءات والخدمات الطبية التي نُفذت في الجلسة
            foreach ($request->procedures as $procedure) {
                SessionProcedure::create([
                    'session_id' => $session->id,
                    'service_id' => $procedure['service_id'],
                    'price_charged' => $procedure['price_charged'],
                ]);
            }

            // 4. حفظ الروشتة الطبية ومواد الأدوية إن وجدت في طلب الفلاتر
            if ($request->has('prescription_items') && count($request->prescription_items) > 0) {
                $prescription = Prescription::create([
                    'session_id' => $session->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'notes' => $request->prescription_notes,
                ]);

                foreach ($request->prescription_items as $item) {
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'drug_name' => $item['drug_name'],
                        'dosage' => $item['dosage'],
                        'duration' => $item['duration'],
                    ]);
                }
            }

            DB::commit();

            // إعادة جلب الجلسة بكامل علاقاتها لإرجاعها للفلاتر
            $fullSessionData = $session->load(['procedures.service', 'prescription.items']);
            
            return $this->successResponse($fullSessionData, 'تم تسجيل الجلسة العلاجية والروشتة بنجاح', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('حدث خطأ أثناء حفظ بيانات الجلسة: ' . $e->getMessage(), 500);
        }
    }
}