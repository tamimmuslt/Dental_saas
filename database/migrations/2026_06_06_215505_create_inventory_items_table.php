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
       Schema::create('inventory_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
    $table->string('name'); // اسم المادة (إبر مخدر، قفازات، حشوة ضوئية)
    $table->integer('quantity')->default(0); // الكمية الحالية
    $table->integer('safety_threshold')->default(10); // حد الأمان للتنبيه إذا نقص المخزون
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
