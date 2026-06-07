<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    use ApiResponse;

    /**
     * جلب الفواتير التابعة لفرع المستخدم الحالي
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Invoice::with('patient');

        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);
        return $this->successResponse($invoices, 'تم جلب الفواتير بنجاح');
    }

    /**
     * إنشاء فاتورة جديدة لمريض
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('خطأ في بيانات الفاتورة', 422, $validator->errors());
        }

        // تحديد حالة الفاتورة تلقائياً بناءً على المدفوع والإجمالي بعد الخصم
        $total = $request->total_amount - ($request->discount ?? 0) + ($request->tax ?? 0);
        $paid = $request->paid_amount;

        if ($paid >= $total) {
            $status = 'paid';
        } elseif ($paid > 0 && $paid < $total) {
            $status = 'partially_paid';
        } else {
            $status = 'unpaid';
        }

        $invoice = Invoice::create([
            'branch_id' => $user->branch_id ?? 1, // إسنادها لفرع الموظف الحالي تلقائياً
            'patient_id' => $request->patient_id,
            'total_amount' => $request->total_amount,
            'paid_amount' => $request->paid_amount,
            'discount' => $request->discount ?? 0.00,
            'tax' => $request->tax ?? 0.00,
            'status' => $status,
        ]);

        return $this->successResponse($invoice, 'تم إصدار الفاتورة بنجاح', 201);
    }
}