<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'patient_id',
        'total_amount',
        'paid_amount',
        'discount',
        'tax',
        'status'
    ];

    /**
     * علاقة الفاتورة بالمريض: الفاتورة تنتمي لمريض واحد
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * يمكنك أيضاً إضافة علاقة الفرع هنا إن لم تكن مضافة
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}