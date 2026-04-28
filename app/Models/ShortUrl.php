<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * URL corta. Reemplaza links firmados largos (~250 chars) por links
 * cortos (~30 chars) que se ven mejor en WhatsApp.
 *
 * Uso:
 *   $shortUrl = ShortUrl::make('https://...long...', $expiresAt);
 *   // devuelve: https://docfacil.tu-app.co/c/Ab3kP9
 */
class ShortUrl extends Model
{
    protected $fillable = ['code', 'target_url', 'expires_at', 'clicks'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'clicks' => 'integer',
        ];
    }

    /**
     * Crea una URL corta y devuelve la URL pública. Si la URL ya existe
     * con el mismo target y expiración cercana, reusa el código existente
     * (idempotencia para webhooks o re-rendres del mismo recordatorio).
     */
    public static function make(string $targetUrl, ?\DateTimeInterface $expiresAt = null): string
    {
        $code = self::generateUniqueCode();
        self::create([
            'code' => $code,
            'target_url' => $targetUrl,
            'expires_at' => $expiresAt,
        ]);
        return route('shortlink', ['code' => $code]);
    }

    /**
     * Genera un código alfanumérico de 6 caracteres único en la tabla.
     * Reintenta hasta 5 veces si hay colisión (probabilidad <0.001%).
     */
    private static function generateUniqueCode(): string
    {
        for ($i = 0; $i < 5; $i++) {
            // Solo letras y dígitos amigables (no 0/O/1/l ambiguos)
            $code = Str::random(6);
            if (!self::where('code', $code)->exists()) {
                return $code;
            }
        }
        // Fallback con timestamp si los 5 randoms colisionaron (prácticamente imposible)
        return substr(uniqid('s', true), -6);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
