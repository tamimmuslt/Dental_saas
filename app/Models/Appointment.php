<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
protected $fillable = ['branch_id', 'patient_id', 'doctor_id', 'appointment_date', 'status'];

protected $casts = ['appointment_date' => 'datetime'];

public function patient() { return $this->belongsTo(Patient::class); }
public function doctor() { return $this->belongsTo(User::class, 'doctor_id'); }}
