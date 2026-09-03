<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los gastos del consultorio.
 *
 * DocFácil solo sabía lo que entra. El doctor que quería saber cómo le fue
 * de verdad tenía que sacar sus cobros de aquí y restarles a mano lo que
 * llevaba en otra hoja — y por eso la hoja nunca se muere.
 *
 * No es contabilidad para el SAT: eso lo hace su contador. Es "cuánto me
 * quedó este mes", que es la pregunta que sí se hace todos los días.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            // Quién lo capturó, para cuando hay más de un doctor.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('category', 40);
            $table->string('concept');
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->string('payment_method', 20)->nullable();
            $table->string('supplier')->nullable();
            $table->text('notes')->nullable();

            // El comprobante va en disco privado: es información del negocio.
            $table->string('receipt_path')->nullable();

            // La renta y la nómina se pagan cada mes con el mismo monto. Sin
            // esto el doctor los captura doce veces al año y deja de hacerlo
            // al tercero.
            $table->boolean('is_recurring')->default(false);
            $table->date('last_generated_on')->nullable();

            $table->timestamps();

            $table->index(['clinic_id', 'expense_date']);
            $table->index(['clinic_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
