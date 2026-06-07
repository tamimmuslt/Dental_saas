<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('clinical_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
    $table->text('complaint'); // شكوى المريض
    $table->text('diagnosis'); // تشخيص الطبيب
    $table->text('notes')->nullable(); // ملاحظات عامة
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_sessions');
    }
};
