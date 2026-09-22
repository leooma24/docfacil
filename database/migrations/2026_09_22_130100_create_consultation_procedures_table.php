<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los procedimientos que se hicieron en la consulta.
 *
 * Es el prerrequisito del motor de insumos, y por tres razones distintas:
 *
 *  1. La anestesia necesita saber EN QUÉ DIENTES se trabajó para contar zonas.
 *  2. El curetaje necesita saber cuántos cuadrantes para cobrarse bien.
 *  3. El descuento por diente necesita los dientes.
 *
 * Antes la consulta guardaba un solo servicio y un monto tecleado a mano.
 *
 * `unit_price` y `unit` se congelan al momento: un procedimiento es un hecho
 * histórico, y si mañana sube el precio del curetaje, lo que se cobró ayer no
 * se mueve.
 *
 * `tooth_number` es texto y admite rangos ("16", "46-47"), igual que en
 * TreatmentPlanItem.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();

            $table->string('tooth_number', 10)->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);

            // Congelados al momento del procedimiento.
            $table->string('unit', 20)->default('visit');
            $table->decimal('unit_price', 12, 2)->default(0);

            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'appointment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_procedures');
    }
};
