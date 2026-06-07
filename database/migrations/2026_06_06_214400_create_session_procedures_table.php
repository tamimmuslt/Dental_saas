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
        Schema::create('session_procedures', function (Blueprint $table) {
    $table->id();
    $table->foreignId('session_id')->constrained('clinical_sessions')->onDelete('cascade');
    $table->foreignId('service_id')->constrained('service_price_list')->onDelete('cascade');
    $table->decimal('price_charged', 10, 2); // السعر الفعلي المحتسب (قد يختلف عن اللائحة بسبب خصم طبيب مثلاً)
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_procedures');
    }
};
