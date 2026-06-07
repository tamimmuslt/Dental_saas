<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    use ApiResponse;

    /**
     * T9: جلب قائمة الموظفين
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = User::query();

        if ($user->role === 'admin' && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->role !== 'super_admin') {
            return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
        }

        $staff = $query->orderBy('name', 'asc')->paginate(15);

        return $this->successResponse($staff, 'تم جلب قائمة الموظفين بنجاح');
    }

    /**
     * T9: إضافة موظف جديد
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return $this->errorResponse('غير مصرح لك بإضافة موظفين', 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:doctor,secretary,accountant',
            'commission_rate' => 'required_if:role,doctor|nullable|numeric|min:0|max:100',
            'branch_id' => $user->role === 'super_admin' ? 'required|exists:branches,id' : 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('خطأ في التحقق من البيانات', 422, $validator->errors());
        }

        $branchId = $user->role === 'admin' ? $user->branch_id : $request->branch_id;

        if (!Branch::find($branchId)) {
            return $this->errorResponse('الفرع المحدد غير موجود', 404);
        }

        $staff = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role,
            'branch_id' => $branchId,
            'commission_rate' => $request->role === 'doctor' ? $request->commission_rate : 0,
            'is_active' => true,
        ]);

        return $this->successResponse([
            'id' => $staff->id,
            'name' => $staff->name,
            'email' => $staff->email,
            'role' => $staff->role,
            'branch_id' => $staff->branch_id,
            'commission_rate' => $staff->commission_rate,
        ], 'تم إضافة الموظف بنجاح', 201);
    }

    /**
     * T9: جلب تفاصيل موظف
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $staff = User::find($id);

        if (!$staff) {
            return $this->errorResponse('الموظف غير موجود', 404);
        }

        if ($user->role === 'admin' && $staff->branch_id !== $user->branch_id) {
            return $this->errorResponse('غير مصرح لك بمشاهدة هذا الموظف', 403);
        } elseif (!in_array($user->role, ['admin', 'super_admin'])) {
            return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
        }

        return $this->successResponse($staff, 'تم جلب بيانات الموظف بنجاح');
    }

    /**
     * T9: تعديل موظف
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $staff = User::find($id);

        if (!$staff) {
            return $this->errorResponse('الموظف غير موجود', 404);
        }

        if ($user->role === 'admin' && $staff->branch_id !== $user->branch_id) {
            return $this->errorResponse('غير مصرح لك بتعديل هذا الموظف', 403);
        } elseif (!in_array($user->role, ['admin', 'super_admin'])) {
            return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('خطأ في التحقق من البيانات', 422, $validator->errors());
        }

        $data = $request->only(['name', 'phone', 'commission_rate']);
        
        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $staff->update($data);

        return $this->successResponse($staff, 'تم تحديث بيانات الموظف بنجاح');
    }

    /**
     * T8: تجميد موظف
     */
    public function suspend(Request $request, $id)
    {
        $user = $request->user();
        $staff = User::find($id);

        if (!$staff) {
            return $this->errorResponse('الموظف غير موجود', 404);
        }

        if ($user->role === 'admin' && $staff->branch_id !== $user->branch_id) {
            return $this->errorResponse('غير مصرح لك بتعطيل هذا الموظف', 403);
        } elseif (!in_array($user->role, ['admin', 'super_admin'])) {
            return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
        }

        $staff->update(['is_active' => false]);

        return $this->successResponse(null, 'تم تعطيل حساب الموظف بنجاح');
    }

    /**
     * T8: تفعيل موظف
     */
    public function activate(Request $request, $id)
    {
        $user = $request->user();
        $staff = User::find($id);

        if (!$staff) {
            return $this->errorResponse('الموظف غير موجود', 404);
        }

        if ($user->role === 'admin' && $staff->branch_id !== $user->branch_id) {
            return $this->errorResponse('غير مصرح لك بتفعيل هذا الموظف', 403);
        } elseif (!in_array($user->role, ['admin', 'super_admin'])) {
            return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
        }

        $staff->update(['is_active' => true]);

        return $this->successResponse(null, 'تم تفعيل حساب الموظف بنجاح');
    }

    /**
     * T9: حذف موظف
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $staff = User::find($id);

        if (!$staff) {
            return $this->errorResponse('الموظف غير موجود', 404);
        }

        if ($user->role === 'admin' && $staff->branch_id !== $user->branch_id) {
            return $this->errorResponse('غير مصرح لك بحذف هذا الموظف', 403);
        } elseif (!in_array($user->role, ['admin', 'super_admin'])) {
            return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
        }

        if ($staff->role === 'super_admin') {
            return $this->errorResponse('لا يمكن حذف حساب الـ super_admin', 422);
        }

        $staff->delete();

        return $this->successResponse(null, 'تم حذف الموظف بنجاح');
    }
}
