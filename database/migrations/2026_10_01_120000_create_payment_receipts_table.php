<?php

use App\Models\PaymentReceipt;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cada abono es un pago con su fecha y su forma de pago. Los cobros que ya
 * existen se pasan tal cual: un liquidado deja un recibo por el total y uno
 * a plazos uno por lo abonado, los dos con la fecha del cobro, que es como
 * se contaban hasta hoy. Así ningún número del pasado cambia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 30)->nullable();
            $table->dateTime('paid_at');
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'paid_at']);
        });

        PaymentReceipt::respaldarCobrosSinRecibos();
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};
