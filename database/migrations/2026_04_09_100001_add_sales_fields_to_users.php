<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('commission_rate_percent', 5, 2)->nullable()->after('role');
            $table->boolean('is_active_sales_rep')->default(true)->after('commission_rate_percent');
            $table->string('sales_rep_code', 20)->nullable()->unique()->after('is_active_sales_rep');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['commission_rate_percent', 'is_active_sales_rep', 'sales_rep_code']);
        });
    }
};
