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
        Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
    $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
    $table->decimal('total_amount', 10, 2); // الإجمالي قبل الخصم والضريبة
    $table->decimal('paid_amount', 10, 2)->default(0.00); // المدفوع فعلياً
    $table->decimal('tax', 10, 2)->default(0.00);
    $table->decimal('discount', 10, 2)->default(0.00);
    $table->enum('status', ['paid', 'partially_paid', 'unpaid'])->default('unpaid');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
