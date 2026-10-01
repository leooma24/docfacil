<?php

namespace App\Models;

use App\Support\TipsDeVenta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * El avance de una persona con un tip.
 *
 * La idea no es enseñar el consejo una vez, es repetirlo hasta que salga sin
 * pensarlo. Por eso se lleva la cuenta de cuántas veces se ha visto y se puede
 * marcar como dominado; y por eso el dominado vuelve a los quince días, una
 * sola vez, para comprobar que sigue saliendo solo.
 */
class TipDeVenta extends Model
{
    protected $table = 'tips_de_venta';

    protected $fillable = ['user_id', 'clave', 'veces_visto', 'visto_at', 'dominado_at'];

    protected function casts(): array
    {
        return [
            'veces_visto' => 'integer',
            'visto_at' => 'datetime',
            'dominado_at' => 'datetime',
        ];
    }

    /** Cada cuántos días vuelve un tip que ya se dio por dominado. */
    public const DIAS_PARA_REPASO = 15;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * El tip que toca mostrar de esta etapa.
     *
     * Primero el que menos veces se ha visto: así rotan todos en vez de salir
     * siempre el mismo. Los dominados quedan fuera hasta que toque repasarlos.
     */
    public static function paraMostrar(int $userId, string $etapa): ?array
    {
        $candidatos = TipsDeVenta::paraLaEtapa($etapa);

        if ($candidatos === []) {
            return null;
        }

        $avance = static::where('user_id', $userId)
            ->whereIn('clave', array_column($candidatos, 'clave'))
            ->get()
            ->keyBy('clave');

        $elegido = null;
        $menosVisto = PHP_INT_MAX;

        foreach ($candidatos as $tip) {
            $registro = $avance->get($tip['clave']);

            if ($registro?->dominado_at && $registro->dominado_at->diffInDays(now()) < self::DIAS_PARA_REPASO) {
                continue;
            }

            $veces = (int) ($registro->veces_visto ?? 0);

            if ($veces < $menosVisto) {
                $menosVisto = $veces;
                $elegido = $tip + ['veces_visto' => $veces, 'es_repaso' => (bool) $registro?->dominado_at];
            }
        }

        return $elegido;
    }

    /** Queda anotado que se vio, para que la próxima toque otro. */
    public static function anotarQueSeVio(int $userId, string $clave): void
    {
        $registro = static::firstOrNew(['user_id' => $userId, 'clave' => $clave]);

        // Una vez al día como mucho: recargar la pantalla no es practicar.
        if ($registro->visto_at?->isToday()) {
            return;
        }

        $registro->veces_visto = (int) $registro->veces_visto + 1;
        $registro->visto_at = now();
        $registro->save();
    }

    /** "Ya me sale solo": deja de aparecer, pero vuelve a repaso en 15 días. */
    public static function marcarDominado(int $userId, string $clave): void
    {
        static::updateOrCreate(
            ['user_id' => $userId, 'clave' => $clave],
            ['dominado_at' => now()],
        );
    }
}
