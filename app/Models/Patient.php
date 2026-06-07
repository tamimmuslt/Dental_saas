<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
  protected $fillable = ['branch_id', 'name', 'phone', 'gender', 'dob', 'chronic_conditions'];

// لتحويل الـ JSON تلقائياً إلى Array عند التعامل معه في لارافيل
protected $casts = [
    'chronic_conditions' => 'array',
    'dob' => 'date'
];

public function odontograms() {
    return $this->hasMany(Odontogram::class);
}

public function invoices()
{
    return $this->hasMany(Invoice::class);
}
}

