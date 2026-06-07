<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odontograms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->integer('tooth_number'); // رقم السن من 1 إلى 32
            
            // الحالات الشائعة للسن
            $table->enum('status', ['healthy', 'decay', 'missing', 'filled', 'crowned', 'bridge_pointing', 'implant'])->default('healthy');
            $table->text('notes')->nullable(); // ملاحظات الطبيب على هذا السن تحديداً
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontograms');
    }
};