<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse; // استخدام الـ Trait الموحد

    /**
     * تسجيل الدخول وإصدار التوكن
     */
    public function login(Request $request)
    {
        // 1. التحقق من المدخلات
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('خطأ في التحقق من البيانات', 422, $validator->errors());
        }

        // 2. البحث عن المستخدم
        $user = User::with('branch')->where('email', $request->email)->first();

        // 3. التحقق من كلمة المرور وحالة الحساب
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('بيانات الاعتماد غير صحيحة', 401);
        }

        if (!$user->is_active) {
            return $this->errorResponse('هذا الحساب معطل حالياً، يرجى مراجعة المسؤول', 403);
        }

        // 4. توليد توكن جديد بواسطة Sanctum
        $token = $user->createToken('dental_saas_flutter_token')->plainTextToken;

        // 5. تجهيز البيانات المرسلة للفلاتر
        $responseData = [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role, // الدور أساسي لبناء الـ UI في فلاتر
                'branch' => $user->branch ? [
                    'id' => $user->branch->id,
                    'name' => $user->branch->name,
                ] : null,
            ]
        ];

        return $this->successResponse($responseData, 'تم تسجيل الدخول بنجاح');
    }

    /**
     * تسجيل الخروج وإبطال التوكن الحالي
     */
    public function logout(Request $request)
    {
        // حذف التوكن الذي تم استخدامه في الطلب الحالي
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'تم تسجيل الخروج بنجاح');
    }

    /**
     * جلب بيانات الملف الشخصي الحالي (للتأكد من أن التوكن فعال عند تشغيل تطبيق الفلاتر)
     */
    public function profile(Request $request)
    {
        $user = $request->user()->load('branch');
        return $this->successResponse($user, 'تم جلب بيانات المستخدم الحالي');
    }
}