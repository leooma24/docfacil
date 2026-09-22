<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La receta de insumos: qué gasta cada servicio.
 *
 * Es lo que hace posible el descuento automático — sin receta no hay nada que
 * descontar. Pero la receta sola no basta: hay que decir CÓMO escala cada
 * línea, y ahí está lo que la hace correcta.
 *
 * `scope` es el alcance de esa línea, y es NULL por defecto a propósito:
 * significa "como se cobre el servicio". Un curetaje que se cobra por cuadrante
 * gasta sus insumos de curetaje por cuadrante. Pero tiene que poder
 * sobrescribirse, y la anestesia es la prueba: el curetaje se cobra por
 * cuadrante y su anestesia se gasta por zona contigua. Si se forzara el default,
 * la anestesia se descontaría mal.
 *
 * `waste_factor` es la merma normal de ese insumo (1.15 = se pierde 15%). Sin
 * él, el inventario queda corto de forma crónica.
 *
 * `is_optional` es para lo que no siempre se usa: no toda resina lleva banda de
 * matriz.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supply_id')->constrained()->cascadeOnDelete();

            // Cuánto se gasta por cada unidad del alcance.
            $table->decimal('quantity', 12, 3)->default(1);

            // NULL = el alcance del servicio.
            $table->string('scope', 20)->nullable();

            $table->boolean('is_optional')->default(false);
            $table->decimal('waste_factor', 6, 3)->default(1);
            $table->string('notes')->nullable();
            $table->timestamps();

            // Un insumo no se repite en la receta del mismo servicio: si
            // hiciera falta más, se sube la cantidad.
            $table->unique(['service_id', 'supply_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_supplies');
    }
};
