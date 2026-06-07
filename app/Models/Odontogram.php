<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Odontogram extends Model
{
protected $fillable = ['patient_id', 'tooth_number', 'status', 'notes'];

public function patient() {
    return $this->belongsTo(Patient::class);
}}
