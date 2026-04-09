<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NOM-004-SSA3-2012 y Reglamento de Insumos para la Salud art. 29:
 * toda nota clínica y receta debe llevar la cédula profesional del médico.
 * Dejamos license_number nullable por compatibilidad con cuentas existentes,
 * pero la Register page y el form de edición lo vuelven obligatorio.
 *
 * Además backfilleamos un placeholder para cuentas legacy sin cédula, para
 * que el sistema siga funcionando sin bloqueos. El doctor debe capturarla
 * en el onboarding.
 */
return new class extends Migration {
    public function up(): void
    {
        // Backfill placeholder para registros existentes sin cédula
        \DB::table('doctors')
            ->whereNull('license_number')
            ->orWhere('license_number', '')
            ->update(['license_number' => 'PENDIENTE']);
    }

    public function down(): void
    {
        // no-op
    }
};
