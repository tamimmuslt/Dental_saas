<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    use ApiResponse;

    /**
     * جلب المواعيد الخاصة بفرع المستخدم الحالي مع فلاتر اختيارية
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // عزل المواعيد بناءً على الفرع (Multi-tenancy)
        $query = Appointment::with(['patient', 'doctor']);
        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // 1. فلترة حسب تاريخ معين (مثلاً لليوم فقط أو ليوم محدد في الكاليندر)
        if ($request->has('date')) {
            $query->whereDate('appointment_date', $request->date);
        } else {
            // افتراضياً إذا لم يرسل تاريخاً، نجلب مواعيد اليوم فما بعد
            $query->whereDate('appointment_date', '>=', Carbon::today());
        }

        // 2. فلترة حسب طبيب معين (تخص السكرتارية أو المطبقة تلقائياً عند حساب الطبيب)
        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        } elseif ($user->role === 'doctor') {
            // إذا كان المستخدم الحالي طبيب، يرى مواعيده هو فقط
            $query->where('doctor_id', $user->id);
        }

        // 3. فلترة حسب حالة الموعد (pending, confirmed, completed, canceled)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date', 'asc')->get();

        return $this->successResponse($appointments, 'تم جلب المواعيد بنجاح');
    }

    /**
     * حجز موعد جديد مع التحقق من عدم التعارض
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date_format:Y-m-d H:i:s|after:now', // يجب أن يكون الموعد مستقبلياً
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('خطأ في التحقق من البيانات', 422, $validator->errors());
        }

        // حماية منطقية: التأكد أن الطبيب المختار يتبع لنفس الفرع
        $doctor = \App\Models\User::find($request->doctor_id);
        if ($user->branch_id && $doctor->branch_id !== $user->branch_id) {
            return $this->errorResponse('هذا الطبيب لا يتبع لفرعك الحالي', 403);
        }

        // فحص التعارض (Conflict Check): منع حجز موعد لنفس الطبيب في نفس الساعة والدقيقة
        $requestedTime = Carbon::parse($request->appointment_date);
        
        // سنفترض أن متوسط الجلسة هو 30 دقيقة، ونمنع التداخل في هذا النطاق
        $startTime = $requestedTime->copy()->subMinutes(29);
        $endTime = $requestedTime->copy()->addMinutes(29);

        $hasConflict = Appointment::where('doctor_id', $request->doctor_id)
            ->where('status', '!=', 'canceled') // المواعيد المُلغاة لا تسبب تعارضاً
            ->whereBetween('appointment_date', [$startTime, $endTime])
            ->exists();

        if ($hasConflict) {
            return $this->errorResponse('عذراً، هذا الوقت محجوز مسبقاً لدى الطبيب المختار',409);
        }

        // إنشاء الموعد
        $appointment = Appointment::create([
            'branch_id' => $user->branch_id ?? $doctor->branch_id,
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->appointment_date,
            'status' => 'pending', // افتراضياً معلق حتى تؤكده السكرتارية
        ]);

        return $this->successResponse($appointment, 'تم حجز الموعد بنجاح', 201);
    }

    /**
     * تحديث حالة الموعد (تأكيد، إلغاء، إلخ)
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,completed,canceled'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('حالة الموعد غير صالحة', 422, $validator->errors());
        }

        $user = $request->user();
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return $this->errorResponse('الموعد غير موجود', 404);
        }

        // حماية البيانات بالفرع
        if ($user->branch_id && $appointment->branch_id !== $user->branch_id) {
            return $this->errorResponse('غير مسموح لك بتعديل مواعيد الفروع الأخرى', 403);
        }

        $appointment->update([
            'status' => $request->status
        ]);

        return $this->successResponse($appointment, 'تم تحديث حالة الموعد بنجاح');
    }
}