<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los residuos que la ley obliga a manejar aparte.
 *
 * El sobrante de amalgama y la amalgama que se extrae del paciente no pueden ir
 * al drenaje (Convenio de Minamata y la normativa sanitaria mexicana). El
 * consultorio tiene que poder demostrar qué hizo con ellos y cuándo.
 *
 * Esta tabla NO toca el inventario, y es lo importante: ese material ya salió
 * del kardex cuando se mezcló, así que registrarlo como salida descontaría dos
 * veces lo mismo. Es un registro de cumplimiento, no un movimiento de stock.
 *
 * Por eso vive aparte en vez de ser un tipo de merma: mezclarlos obligaría a
 * que la suma del stock supiera excluir un caso, y una resta que se olvida una
 * vez ya desvió el inventario para siempre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hazardous_wastes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supply_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // amalgama_sobrante | amalgama_extraida | mercurio | biologico | otro
            $table->string('material', 40);

            $table->decimal('quantity', 12, 3);
            $table->string('unit', 20)->default('gramo');
            $table->date('disposed_on');

            // Trazabilidad: en qué contenedor se depositó y con qué número de
            // registro/manifiesto salió del consultorio.
            $table->string('container')->nullable();
            $table->string('manifest_number')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'disposed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazardous_wastes');
    }
};
