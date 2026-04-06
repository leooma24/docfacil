<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->boolean('is_beta')->default(false)->after('is_active');
            $table->boolean('is_founder')->default(false)->after('is_beta');
            $table->decimal('founder_price', 10, 2)->nullable()->after('is_founder');
            $table->timestamp('beta_starts_at')->nullable()->after('founder_price');
            $table->timestamp('beta_ends_at')->nullable()->after('beta_starts_at');
            $table->text('beta_notes')->nullable()->after('beta_ends_at');
            $table->boolean('show_as_case_study')->default(false)->after('beta_notes');
            $table->string('case_study_logo')->nullable()->after('show_as_case_study');
            $table->text('case_study_testimonial')->nullable()->after('case_study_logo');
            $table->enum('onboarding_status', ['pending', 'scheduled', 'completed'])->default('pending')->after('case_study_testimonial');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn([
                'is_beta', 'is_founder', 'founder_price',
                'beta_starts_at', 'beta_ends_at', 'beta_notes',
                'show_as_case_study', 'case_study_logo', 'case_study_testimonial',
                'onboarding_status',
            ]);
        });
    }
};
