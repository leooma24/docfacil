<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Las órdenes de laboratorio (12-oct-2026): qué se mandó, a qué laboratorio,
 * cuándo prometieron regresarlo, si ya llegó, si se entregó y si se pagó.
 * Es la libreta del laboratorio del dentista, no una conexión con el
 * laboratorio: nada se le manda a nadie.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            // La cita donde se coloca el trabajo: de ahí sale el aviso.
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('laboratorio');
            $table->string('trabajo');
            $table->string('diente', 20)->nullable();
            $table->string('color', 20)->nullable();
            $table->decimal('costo', 10, 2)->default(0);
            $table->date('enviada_at');
            $table->date('prometida_para')->nullable();
            $table->dateTime('llego_at')->nullable();
            $table->dateTime('entregada_at')->nullable();
            $table->dateTime('pagada_at')->nullable();
            $table->foreignId('expense_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notas')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['clinic_id', 'llego_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
    }
};
