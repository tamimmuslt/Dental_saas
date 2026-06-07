<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    use ApiResponse;

    /**
     * T8: جلب قائمة جميع الفروع (صلاحية: super_admin فقط)
     */
    public function index(Request $request)
    {
        if ($request->user()->role !== 'super_admin') {
            return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
        }

        $branches = Branch::orderBy('name', 'asc')->paginate(15);
        return $this->successResponse($branches, 'تم جلب الفروع بنجاح');
    }

    /**
     * T8: إنشاء فرع جديد (صلاحية: super_admin فقط)
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== 'super_admin') {
            return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:branches,name',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20|unique:branches,phone',
            'email' => 'required|email|unique:branches,email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('خطأ في التحقق من البيانات', 422, $validator->errors());
        }

        $branch = Branch::create([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_active' => true,
        ]);

        return $this->successResponse($branch, 'تم إنشاء الفرع بنجاح', 201);
    }

    /**
     * T8: جلب تفاصيل فرع محدد
     */
    public function show(Request $request, $id)
    {
        if ($request->user()->role !== 'super_admin') {
            return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
        }

        $branch = Branch::with('users')->find($id);

        if (!$branch) {
            return $this->errorResponse('الفرع غير موجود', 404);
        }

        return $this->successResponse($branch, 'تم جلب بيانات الفرع بنجاح');
    }

    /**
     * T8: تعديل بيانات فرع
     */
    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'super_admin') {
            return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
        }

        $branch = Branch::find($id);

        if (!$branch) {
            return $this->errorResponse('الفرع غير موجود', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255|unique:branches,name,' . $id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20|unique:branches,phone,' . $id,
            'email' => 'nullable|email|unique:branches,email,' . $id,
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('خطأ في التحقق من البيا��ات', 422, $validator->errors());
        }

        $branch->update($request->only(['name', 'address', 'phone', 'email', 'is_active']));

        return $this->successResponse($branch, 'تم تحديث بيانات الفرع بنجاح');
    }

    /**
     * T8: حذف (soft delete) فرع
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'super_admin') {
            return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
        }

        $branch = Branch::find($id);

        if (!$branch) {
            return $this->errorResponse('الفرع غير موجود', 404);
        }

        if ($branch->users()->where('is_active', true)->count() > 0) {
            return $this->errorResponse('لا يمكن حذف فرع يحتوي على موظفين نشطين', 422);
        }

        $branch->update(['is_active' => false]);

        return $this->successResponse(null, 'تم تعطيل الفرع بنجاح');
    }

    /**
     * T8: تفعيل فرع معطل
     */
    public function activate(Request $request, $id)
    {
        if ($request->user()->role !== 'super_admin') {
            return $this->errorResponse('غير مصرح لك بهذه العملية', 403);
        }

        $branch = Branch::find($id);

        if (!$branch) {
            return $this->errorResponse('الفرع غير موجود', 404);
        }

        $branch->update(['is_active' => true]);

        return $this->successResponse($branch, 'تم تفعيل الفرع بنجاح');
    }
}
