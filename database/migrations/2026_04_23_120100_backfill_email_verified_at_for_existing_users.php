<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Marca email_verified_at=created_at para usuarios existentes al momento de la
 * migracion, para que no se bloqueen cuando prendamos MustVerifyEmail en User.
 * Solo aplica a cuentas preexistentes. Nuevas cuentas pasan por el flujo normal
 * de verificacion.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        // No revertir — no queremos dejar cuentas con email_verified_at=null
    }
};
