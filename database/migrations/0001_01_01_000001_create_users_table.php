<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            
            // الأدوار الخمسة الأساسية في النظام
            $table->enum('role', ['super_admin', 'admin', 'doctor', 'secretary', 'accountant']);
            
            // ربط المستخدم بالفرع (يمكن أن يكون نال في حالة الـ Super Admin فقط)
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            
            $table->decimal('commission_rate', 5, 2)->default(0.00); // نسبة الطبيب من العمليات (مثلاً 25.50%)
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};