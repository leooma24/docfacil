<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El kardex: cada movimiento de insumos, para siempre.
 *
 * El stock NO es una columna que se edita. Se deriva de sumar estos
 * movimientos (ver Supply::currentStock()). Un campo `current_stock` mutable
 * se desincroniza al primer doble clic o al primer reintento, y a partir de
 * ahí el inventario es ficción y nadie vuelve a confiar en él.
 *
 * `quantity` siempre va en positivo: el signo lo pone `type`. Guardar
 * cantidades con signo invita a que una salida negativa sume, y ese error es
 * imposible de ver en un reporte.
 *
 * `reference_type` + `reference_id` ligan el movimiento con lo que lo originó
 * (un gasto, y en la fase 2 una consulta). El índice único sobre esa tupla es
 * la guardia de idempotencia: el mismo gasto no puede entrar dos veces, y en
 * la fase 2 una consulta reabierta y vuelta a cerrar no puede descontar doble.
 * Las filas sin referencia (captura manual) no chocan entre sí porque los NULL
 * son distintos en un índice único.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supply_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // in | out | waste
            $table->string('type', 20);

            // Siempre positivo, en unidad de consumo del insumo.
            $table->decimal('quantity', 12, 3);

            // Solo para entradas: lo que costó esa compra por unidad.
            $table->decimal('unit_cost', 12, 4)->nullable();

            $table->string('reason')->nullable();
            // 64 y no 255: aqui solo caben nombres de clase (App\Models\Appointment,
            // App\Models\Expense) y la columna entra en el indice unico de
            // abajo con holgura en cualquier motor.
            $table->string('reference_type', 64)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            // dateTime y no timestamp: TIMESTAMP se convierte con la zona horaria
            // de la sesion de MySQL, y desde que cada consultorio tiene su propia
            // hora eso es una fuente de ruido innecesaria. Ademas no topa en 2038.
            $table->dateTime('occurred_at');
            $table->timestamps();

            $table->index(['clinic_id', 'supply_id', 'occurred_at']);
            $table->unique(
                ['reference_type', 'reference_id', 'supply_id', 'type'],
                'supply_movements_reference_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_movements');
    }
};
