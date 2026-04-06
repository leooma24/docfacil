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
        Schema::create('odontogram_teeth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odontogram_id')->constrained()->cascadeOnDelete();
            $table->integer('tooth_number');
            $table->enum('condition', [
                'healthy', 'decay', 'filling', 'crown', 'extraction',
                'missing', 'implant', 'bridge', 'root_canal', 'fracture',
                'sealant', 'veneer', 'pending'
            ])->default('healthy');
            $table->string('top_surface')->nullable();
            $table->string('bottom_surface')->nullable();
            $table->string('left_surface')->nullable();
            $table->string('right_surface')->nullable();
            $table->string('center_surface')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['odontogram_id', 'tooth_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odontogram_teeth');
    }
};
