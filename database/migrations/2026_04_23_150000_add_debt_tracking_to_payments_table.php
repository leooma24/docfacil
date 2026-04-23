<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adeudos (Fase 3.1): extiende payments para rastrear deuda vigente.
 *
 * - amount ya existe (se reinterpreta como "monto del cobro total")
 * - amount_paid: cuanto ha pagado el paciente hasta ahora
 * - due_date: fecha limite de pago (para calcular adeudos vencidos)
 *
 * Pagos historicos (status=paid): amount_paid = amount. Pagos pending/
 * partial: amount_paid = 0 por default (el usuario lo actualiza con
 * la accion "Pagar abono").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount_paid', 10, 2)->default(0)->after('amount');
            $table->date('due_date')->nullable()->after('payment_date');

            $table->index(['clinic_id', 'status', 'due_date']);
        });

        // Backfill: cobros existentes con status=paid ya tienen amount_paid=amount.
        // Los partial/pending se dejan en 0 — el usuario ira actualizando.
        DB::table('payments')
            ->where('status', 'paid')
            ->update(['amount_paid' => DB::raw('amount')]);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['clinic_id', 'status', 'due_date']);
            $table->dropColumn(['amount_paid', 'due_date']);
        });
    }
};
