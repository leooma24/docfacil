<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Roadmap comunitario: los dentistas proponen features, votan, y
 * cada mes el admin elige dos ganadoras (una paga + una gratis).
 *
 * feature_requests: las propuestas. Status controla el flujo.
 * feature_votes: los votos por clinica. Cada voto incluye willingness-
 *   to-pay ('free', '49', '99', '199', '299plus') para obtener senal
 *   de pricing real. Unique (request, clinica) = 1 voto por clinica.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submitted_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('submitted_by_clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('description');
            $table->string('status', 20)->default('proposed');
            // proposed | in_review | in_progress | shipped | rejected
            $table->string('proposed_price_tier', 20)->nullable();
            // free | 49 | 99 | 199 | 299plus — lo que el proponente sugiere
            $table->unsignedInteger('votes_count')->default(0); // denormalized cache
            $table->timestamp('shipped_at')->nullable();
            $table->text('shipped_notes')->nullable(); // lo que Omar pone al cerrarla
            $table->string('release_type', 10)->nullable(); // 'paid' | 'free' (cuando gana y se construye)
            $table->string('winner_month', 7)->nullable(); // '2026-05' — que mes gano
            $table->timestamps();

            $table->index('status');
            $table->index(['status', 'votes_count']);
        });

        Schema::create('feature_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('willingness_to_pay', 20)->default('free');
            // free | 49 | 99 | 199 | 299plus
            $table->timestamps();

            $table->unique(['feature_request_id', 'clinic_id']); // 1 voto por clinica por feature
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_votes');
        Schema::dropIfExists('feature_requests');
    }
};
