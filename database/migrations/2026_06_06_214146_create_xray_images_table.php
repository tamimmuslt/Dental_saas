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
        Schema::create('xray_images', function (Blueprint $table) {
    $table->id();
    $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
    $table->string('file_path'); // مسار تخزين الصورة في السيرفر
    $table->enum('type', ['xray', 'panorama', 'cbct'])->default('xray');
    $table->text('notes')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xray_images');
    }
};
