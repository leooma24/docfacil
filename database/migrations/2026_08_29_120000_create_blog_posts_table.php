<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los posts del blog vivían en un arreglo de PHP dentro de BlogController.
 * Publicar uno nuevo obligaba a editar código y desplegar, que es justo lo
 * que impide sostener un ritmo de publicación.
 *
 * No lleva clinic_id: es contenido de marketing del sitio público, no de un
 * consultorio, así que no entra en el scope multi-tenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description');            // meta description y bajada del listado
            $table->string('category')->default('Gestión');
            $table->string('cover_image')->nullable();
            $table->string('read_time')->nullable(); // "9 min"; se calcula solo si se deja vacío

            // Bloques tipados (p, h2, h3, ul, table, cta) — los mismos que ya
            // renderiza la vista, para no perder el diseño al pasar a un editor.
            $table->json('content');

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // El listado público filtra por publicados y ordena por fecha.
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
