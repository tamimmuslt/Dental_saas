<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade'); // عزل بيانات كل عيادة
            $table->string('name');
            $table->string('phone');
            $table->enum('gender', ['male', 'female']);
            $table->date('dob')->nullable(); // تاريخ الميلاد لحساب العمر بدقة في الفلاتر
            $table->json('chronic_conditions')->nullable(); // الأمراض المزمنة (مثل السكري، الضغط) كـ JSON لتسهيل التعامل معها
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};