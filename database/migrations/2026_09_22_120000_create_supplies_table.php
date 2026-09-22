<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los insumos del consultorio.
 *
 * Fase 1: el catálogo y su kardex. Todavía NO hay receta por servicio ni
 * descuento automático — eso es la fase 2, y necesita que el doctor confirme
 * qué gasta cada procedimiento. Lo que sí resuelve esta fase el primer día es
 * lo que nadie sabe hoy: cuánto se está gastando en material y qué está por
 * acabarse.
 *
 * `unit` es la unidad en la que se CONSUME (una pieza, un mililitro) y
 * `purchase_unit` la unidad en la que se COMPRA (una caja). Sin esa distinción
 * el inventario miente desde el primer día: se compran cajas de 50 guantes y
 * se gastan guantes. `units_per_purchase` es el puente entre las dos.
 *
 * `min_stock` es el punto de reorden, en unidad de consumo. Cuando el kardex
 * baja de ahí, el insumo se marca — el aviso no espera a que ya no haya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('category')->nullable();

            // Unidad de consumo, y cómo se compra.
            $table->string('unit', 20)->default('pieza');
            $table->string('purchase_unit', 20)->nullable();
            $table->decimal('units_per_purchase', 12, 3)->default(1);

            // Punto de reorden y costo, ambos en unidad de consumo.
            $table->decimal('min_stock', 12, 3)->default(0);
            // 4 decimales: el costo es por unidad de consumo (un ml, un guante),
            // y a 2 decimales un insumo de $0.008 el ml se guarda como $0.01,
            // 25% de error multiplicado por cada consulta.
            $table->decimal('cost_per_unit', 12, 4)->default(0);

            $table->string('preferred_supplier')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['clinic_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
