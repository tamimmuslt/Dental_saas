<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Odontogram;
use App\Models\Patient;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OdontogramController extends Controller
{
    use ApiResponse;

    /**
     * جلب خريطة الأسنان كاملة لمريض محدد
     */
    public function getPatientMap(Request $request, $patientId)
    {
        $patient = Patient::find($patientId);
        if (!$patient) {
            return $this->errorResponse('المريض غير موجود', 404);
        }

        // جلب السجلات المخزنة للأسنان المعدلة سابقاً
        $odontogramRecords = Odontogram::where('patient_id', $patientId)->get();

        return $this->successResponse($odontogramRecords, 'تم جلب خريطة الأسنان بنجاح');
    }

    /**
     * تحديث أو إضافة حالة لسن معين (مثلاً السن رقم 14 يعاني من تسوس)
     */
    public function updateTooth(Request $request, $patientId)
    {
        $validator = Validator::make($request->all(), [
            'tooth_number' => 'required|integer|between:1,32',
            'status' => 'required|in:healthy,decay,missing,filled,crowned,bridge_pointing,implant',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('خطأ في التحقق من البيانات', 422, $validator->errors());
        }

        $patient = Patient::find($patientId);
        if (!$patient) {
            return $this->errorResponse('المريض غير موجود', 404);
        }

        // تحديث حالة السن إن كان مسجلاً سابقاً أو إنشاء سجل جديد له (UpdateOrCreate)
        $tooth = Odontogram::updateOrCreate(
            [
                'patient_id' => $patientId,
                'tooth_number' => $request->tooth_number
            ],
            [
                'status' => $request->status,
                'notes' => $request->notes
            ]
        );

        return $this->successResponse($tooth, 'تم تحديث حالة السن بنجاح');
    }
}