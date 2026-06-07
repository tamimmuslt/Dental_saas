<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    use ApiResponse;

    /**
     * جلب قائمة المرضى التابعين لفرع المستخدم الحالي (مع إمكانية البحث بالاسم أو الهاتف)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // بناء الاستعلام وعزل البيانات بالفرع (إذا لم يكن سوبر أدمن)
        $query = Patient::query();
        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // إمكانية البحث من الفلاتر
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });

                
        }

        $patients = $query->orderBy('name', 'asc')->paginate(15);

        return $this->successResponse($patients, 'تم جلب قائمة المرضى بنجاح');
    }

    /**
     * إضافة مريض جديد
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:male,female',
            'dob' => 'nullable|date',
            'chronic_conditions' => 'nullable|array' // يستقبل مصفوفة مثل ["السكري", "ضغط"]
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('خطأ في التحقق من البيانات', 422, $validator->errors());
        }

        // إنشاء المريض وربطه تلقائياً بفرع الموظف الحالي
        $patient = Patient::create([
            'branch_id' => $user->branch_id ?? $request->branch_id, // السوبر أدمن يمرر الفرع يدوياً
            'name' => $request->name,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'chronic_conditions' => $request->chronic_conditions,
        ]);

        return $this->successResponse($patient, 'تم تسجيل المريض بنجاح', 211);
    }

    /**
     * جلب ملف مريض محدد بكافة تفاصيله
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $patient = Patient::find($id);

        if (!$patient) {
            return $this->errorResponse('المريض غير موجود', 404);
        }

        // حماية البيانات: التأكد أن المريض يتبع لفرع المستخدم
        if ($user->branch_id && $patient->branch_id !== $user->branch_id) {
            return $this->errorResponse('غير مصرح لك بالوصول لبيانات هذا المريض', 403);
        }

        return $this->successResponse($patient, 'تم جلب بيانات المريض بنجاح');
    }
}