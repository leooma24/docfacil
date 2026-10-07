<?php

namespace App\Support;

use App\Models\Prospect;
use App\Models\ProspectMensaje;
use Illuminate\Support\Carbon;

/**
 * Las versiones de los mensajes de venta y cuántos contestan a cada una.
 *
 * Cuando cambie el texto de un mensaje en ProspectResource, se le da versión
 * nueva aquí (ACTUAL y ETIQUETAS): así la tarjeta "Cómo van los mensajes"
 * compara la de antes con la nueva en vez de revolverlas.
 */
class MensajesDeVenta
{
    /** Desde cuándo salen el primer mensaje corto y el segundo con video (hora del centro). */
    public const CAMBIO_2_OCT = '2026-10-02 13:16:00';

    /** La versión que sale hoy en cada paso de la cadencia. */
    public const ACTUAL = [
        0 => 'p0-corta',
        1 => 'p1-video',
        3 => 'p3-demo',
        7 => 'p7-ultimo',
        14 => 'p14-recontacto',
        30 => 'p30-recontacto',
    ];

    /** La promoción de fundador: va fuera de la cadencia, una vez por persona. */
    public const PROMO_FUNDADOR = 'promo-fundador';

    /** El paso con que se guarda la promoción (fuera de la cadencia, al final de la tarjeta). */
    public const PASO_PROMO = 99;

    public const ETIQUETAS = [
        'p0-larga' => 'Primer mensaje largo (fundador + pregunta)',
        'p0-corta' => 'Primer mensaje corto (una sola pregunta)',
        'p1-repite' => 'Segundo: repetía la pregunta',
        'p1-video' => 'Segundo: con video',
        'p3-demo' => 'Tercero: ofrece enseñarlo (demo)',
        'p7-ultimo' => 'Cuarto: último, con la liga del demo',
        'p14-recontacto' => 'Recontacto a las 2 semanas',
        'p30-recontacto' => 'Recontacto al mes',
        'promo-fundador' => 'Promoción de fundador (ganar-ganar)',
    ];

    /** Qué versión salió en ese paso en esa fecha (por omisión, hoy). */
    public static function version(int $paso, ?\DateTimeInterface $cuando = null): string
    {
        $antes = $cuando && Carbon::instance($cuando)->lt(Carbon::parse(self::CAMBIO_2_OCT));

        return match (true) {
            $paso === 0 && $antes => 'p0-larga',
            $paso === 1 && $antes => 'p1-repite',
            default => self::ACTUAL[$paso] ?? 'p' . $paso,
        };
    }

    /**
     * Por cada versión: cuántos la recibieron y cuántos contestaron a ella.
     * La respuesta cuenta para el último mensaje que la persona recibió antes
     * de contestar.
     *
     * @return array<int, array{paso:int, version:string, etiqueta:string, enviados:int, contestaron:int, porcentaje:float, estimado:bool}>
     */
    public static function resultados(int $userId): array
    {
        $mensajes = ProspectMensaje::where('user_id', $userId)->orderBy('enviado_at')->get();
        if ($mensajes->isEmpty()) {
            return [];
        }

        $respuestas = Prospect::withoutGlobalScopes()->whereIn('id', $mensajes->pluck('prospect_id')->unique())
            ->whereNotNull('replied_at')->pluck('replied_at', 'id');

        $acreditadas = [];
        foreach ($respuestas as $prospectoId => $contesto) {
            $ultimo = $mensajes->where('prospect_id', $prospectoId)->filter(fn ($m) => $m->enviado_at->lte($contesto))->last();
            if ($ultimo) {
                $acreditadas[$ultimo->id] = true;
            }
        }

        $orden = array_keys(self::ETIQUETAS);

        return $mensajes->groupBy('version')
            ->map(function ($grupo, $version) use ($acreditadas) {
                $enviados = $grupo->count();
                $contestaron = $grupo->filter(fn ($m) => isset($acreditadas[$m->id]))->count();

                return [
                    'paso' => $grupo->first()->paso,
                    'version' => $version,
                    'etiqueta' => self::ETIQUETAS[$version] ?? $version,
                    'enviados' => $enviados,
                    'contestaron' => $contestaron,
                    'porcentaje' => round(100 * $contestaron / max(1, $enviados), 1),
                    'estimado' => $grupo->contains('estimado', true),
                ];
            })
            ->sortBy(fn ($fila) => [$fila['paso'], array_search($fila['version'], $orden, true)])
            ->values()
            ->all();
    }
}
