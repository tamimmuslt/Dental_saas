<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    use ApiResponse;

    /**
     * جلب كافة عناصر المخزون مع حقل إضافي يوضح إن كانت المادة تحت حد الأمان (Low Stock Alert)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = InventoryItem::query();

        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        $items = $query->orderBy('name', 'asc')->get();

        // إضافة ميزة منطقية ذكية للفلاتر لتحديد حالة المادة (تنبيه نقص المخزون)
        $mappedItems = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'safety_threshold' => $item->safety_threshold,
                'is_low_stock' => $item->quantity <= $item->safety_threshold, // إذا كانت True سيعرض تطبيق الفلاتر تنبيهاً أحمر
            ];
        });

        return $this->successResponse($mappedItems, 'تم جلب بيانات المخزون بنجاح');
    }

    /**
     * إضافة مادة جديدة للمخزون
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'quantity' => 'required|integer|min:0',
            'safety_threshold' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('خطأ في التحقق من البيانات', 422, $validator->errors());
        }

        $item = InventoryItem::create([
            'branch_id' => $user->branch_id ?? 1,
            'name' => $request->name,
            'quantity' => $request->quantity,
            'safety_threshold' => $request->safety_threshold
        ]);

        return $this->successResponse($item, 'تم إضافة المادة للمخزون بنجاح', 201);
    }
}