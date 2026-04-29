<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->timestamp('hot_alerted_at')->nullable()->after('lead_score')
                ->comment('Cuándo se mandó la alerta "lead caliente" a Omar. Null = aún no se ha alertado o resetada por decay.');
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn('hot_alerted_at');
        });
    }
};
