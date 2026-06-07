<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 1. تأكد من وجود هذا السطر بالـ imports

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // 2. تأكد من إضافة HasApiTokens هنا

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'role', 'branch_id', 'commission_rate', 'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function branch() {
        return $this->belongsTo(Branch::class);
    }
}