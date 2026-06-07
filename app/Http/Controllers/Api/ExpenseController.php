<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Expense::query();

        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        return $this->successResponse($query->orderBy('created_at', 'desc')->get(), 'تم جلب المصاريف بنجاح');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'category' => 'required|string',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('بيانات المصروف غير مكتملة', 422, $validator->errors());
        }

        $expense = Expense::create([
            'branch_id' => $user->branch_id ?? 1,
            'amount' => $request->amount,
            'category' => $request->category,
            'description' => $request->description,
        ]);

        return $this->successResponse($expense, 'تم تسجيل المصروف بنجاح', 201);
    }
}