<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
protected $fillable = ['session_id', 'patient_id', 'doctor_id', 'notes'];

public function items() { return $this->hasMany(PrescriptionItem::class); }
}
