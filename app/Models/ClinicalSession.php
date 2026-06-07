<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicalSession extends Model
{
    protected $table = 'clinical_sessions';
    protected $fillable = ['appointment_id', 'complaint', 'diagnosis', 'notes'];

    public function appointment() 
    { 
        return $this->belongsTo(Appointment::class); 
    }

    public function procedures() 
    { 
        return $this->hasMany(SessionProcedure::class, 'session_id'); 
    }

    // 💡 أضف هذه العلاقة لحل مشكلة الـ Postman
    public function prescription() 
    { 
        return $this->hasOne(Prescription::class, 'session_id'); 
    }
}