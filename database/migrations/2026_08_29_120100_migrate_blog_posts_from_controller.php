<?php

use App\Models\BlogPost;
use Illuminate\Database\Migrations\Migration;

/**
 * Pasa a la base los artículos que vivían escritos a mano en
 * BlogController::articles(). Sin esto, al cambiar el controlador para que
 * lea de la tabla el blog se quedaría vacío.
 *
 * Es idempotente: si el slug ya está, no lo duplica.
 */
return new class extends Migration
{
    public function up(): void
    {
        // El controlador conserva el arreglo original justamente para poder
        // correr esta migración; después de esto ya lee de la tabla.
        if (! method_exists(\App\Http\Controllers\BlogController::class, 'articulosHeredados')) {
            return;
        }

        foreach (\App\Http\Controllers\BlogController::articulosHeredados() as $slug => $datos) {
            BlogPost::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $datos['title'],
                    'description' => $datos['description'],
                    'category' => $datos['category'] ?? 'Gestión',
                    'cover_image' => $datos['image'] ?? null,
                    'read_time' => $datos['read_time'] ?? null,
                    'content' => $datos['content'] ?? [],
                    'published_at' => $datos['date'] ?? now(),
                ]
            );
        }
    }

    public function down(): void
    {
        if (! method_exists(\App\Http\Controllers\BlogController::class, 'articulosHeredados')) {
            return;
        }

        BlogPost::whereIn(
            'slug',
            array_keys(\App\Http\Controllers\BlogController::articulosHeredados())
        )->delete();
    }
};
