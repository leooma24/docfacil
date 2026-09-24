<?php

namespace App\Support;

use App\Models\Prospect;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Lo que le toca hacer hoy a quien vende.
 *
 * Vive aquí y no dentro de una pantalla porque lo usan dos: el escritorio,
 * que lo enseña al entrar, y la cola del día, que es donde se trabaja. Con la
 * consulta copiada en cada una, tarde o temprano una dice tres pendientes y la
 * otra cuatro, y entonces ninguna sirve. Ya pasó con las plantillas de
 * WhatsApp, que llegaron a estar en tres lugares distintos.
 */
class CargaDeTrabajo
{
    /** Cuántos primeros contactos al día. Decisión de Omar, 24-sep. */
    public const TOPE_DIARIO = 12;

    /** Los de este vendedor, sin los que ya se cerraron o se perdieron. */
    private static function suyos(int $repId): Builder
    {
        return Prospect::query()
            ->where('assigned_to_sales_rep_id', $repId)
            ->whereNotIn('status', ['lost', 'converted']);
    }

    /**
     * Contestaron y siguen esperando respuesta.
     *
     * Va primero en las dos pantallas: un prospecto que levantó la mano y se
     * enfría es lo más caro que hay en todo el embudo.
     */
    public static function contestaron(int $repId): Collection
    {
        return self::suyos($repId)
            ->whereNotNull('replied_at')
            ->orderByDesc('replied_at')
            ->limit(20)
            ->get();
    }

    /**
     * Cuántos días tarde puede estar un seguimiento y seguir siendo de hoy.
     *
     * Había 88 prospectos de abril con el seguimiento vencido desde el 1 de
     * mayo. Eso no es la tarea de hoy: es una cadencia abandonada hace cinco
     * meses, y retomarla como si nada —"le escribo una vez más por si el
     * mensaje se perdió entre los del día"— se lee falso.
     */
    public const DIAS_DE_GRACIA = 30;

    /**
     * Les toca el siguiente mensaje, y sí se les escribió antes.
     *
     * Con número verificado, igual que el primer contacto: seguir una cadencia
     * contra un número que no existe es gastar el día en mensajes al vacío.
     */
    public static function seguimientos(int $repId): Collection
    {
        return self::suyos($repId)
            ->whereNull('replied_at')
            ->where('contact_day', '>', 0)
            ->where('last_contact_method', 'whatsapp')
            ->where('has_whatsapp', true)
            ->whereNotNull('next_contact_at')
            ->where('next_contact_at', '<=', now())
            ->where('next_contact_at', '>=', now()->subDays(self::DIAS_DE_GRACIA))
            ->orderBy('next_contact_at')
            ->limit(20)
            ->get();
    }

    /**
     * Nunca se les ha escrito y su número está verificado.
     *
     * Verificado quiere decir que alguien abrió el chat y vio que existe: de
     * los números de directorio, 9 de cada 10 no estaban en WhatsApp, y cada
     * mensaje al vacío acerca el número de quien vende a un bloqueo.
     *
     * Los de casa primero: el "soy de aquí de Los Mochis" es lo único que
     * ninguna empresa de software puede copiar.
     */
    public static function primerContacto(int $repId): Collection
    {
        return self::suyos($repId)
            ->where('status', 'new')
            ->where('contact_day', 0)
            ->where('has_whatsapp', true)
            ->whereNotNull('phone')
            ->orderByRaw("CASE WHEN city LIKE '%Mochis%' THEN 0 ELSE 1 END")
            ->orderByDesc('lead_score')
            ->orderBy('id')
            ->limit(self::TOPE_DIARIO)
            ->get();
    }

    /** Cuántos números verificar por día para que nunca falte cola mañana. */
    public const TOPE_VERIFICAR = 10;

    /**
     * Los que hay que verificar antes de poder escribirles.
     *
     * Abrir el chat para ver si el número existe no manda nada y no arriesga
     * la cuenta de quien vende; mandarle a un número muerto sí, y de los que
     * salen de directorios 9 de cada 10 no están en WhatsApp.
     *
     * Por eso esto es una tarea aparte y no se mezcla con el primer contacto:
     * verificar es trabajo de bodega, escribir es trabajo de venta, y meterlos
     * en el mismo botón termina en mensajes al vacío.
     */
    public static function porVerificar(int $repId): Collection
    {
        return self::suyos($repId)
            ->where('status', 'new')
            ->where('contact_day', 0)
            ->whereNull('has_whatsapp')
            ->whereNotNull('phone')
            ->orderByRaw("CASE WHEN city LIKE '%Mochis%' THEN 0 WHEN city LIKE '%Guasave%' OR city LIKE '%Fuerte%' OR city LIKE '%Ahome%' THEN 1 ELSE 2 END")
            ->orderByDesc('lead_score')
            ->orderBy('id')
            ->limit(self::TOPE_VERIFICAR)
            ->get();
    }

    /**
     * Los que no tienen WhatsApp y quedan para llamar.
     *
     * Verificar que un número no está en WhatsApp no lo convierte en basura:
     * casi siempre es el fijo de la recepción de un consultorio que sí existe.
     * Se guardan aparte, para el día que haya tiempo de marcar.
     */
    public static function paraLlamar(int $repId): Collection
    {
        return self::suyos($repId)
            ->where('has_whatsapp', false)
            ->where('contact_day', 0)
            ->whereNotNull('phone')
            ->orderByRaw("CASE WHEN city LIKE '%Mochis%' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->limit(20)
            ->get();
    }

    /** Los números de arriba: hoy, la semana y las demos por hacer. */
    public static function numeros(int $repId): array
    {
        $porWhatsApp = fn () => Prospect::where('assigned_to_sales_rep_id', $repId)
            ->where('last_contact_method', 'whatsapp');

        $mios = fn () => Prospect::where('assigned_to_sales_rep_id', $repId);

        return [
            'enviadosHoy' => $porWhatsApp()->whereDate('last_followup_at', today())->count(),
            'enviadosSemana' => $porWhatsApp()->where('last_followup_at', '>=', now()->startOfWeek())->count(),
            'respuestasHoy' => $mios()->whereDate('replied_at', today())->count(),
            'respuestasSemana' => $mios()->where('replied_at', '>=', now()->startOfWeek())->count(),
            'demosAgendadas' => $mios()->whereNotNull('demo_scheduled_at')->whereNull('demo_completed_at')->count(),
            'tope' => self::TOPE_DIARIO,
            'embudo' => self::embudo($repId),
        ];
    }

    /**
     * En qué escalón se cae.
     *
     * Contestar no es agendar y agendar no es cerrar. Un contador de ventas
     * dice cero y no explica nada; esto dice dónde: doce contestaron y ninguno
     * llegó a demo, o sea que el problema no es que no contesten, es que nadie
     * pide la cita.
     */
    public static function embudo(int $repId): array
    {
        $mios = fn () => Prospect::where('assigned_to_sales_rep_id', $repId);

        $contactados = (clone $mios())->where('contact_day', '>', 0)->count();
        $contestaron = (clone $mios())->whereNotNull('replied_at')->count();
        $agendaron = (clone $mios())->whereNotNull('demo_scheduled_at')->count();
        $hicieron = (clone $mios())->whereNotNull('demo_completed_at')->count();
        $cerraron = (clone $mios())->where('status', 'converted')->count();

        $tasa = fn (int $de, int $sobre) => $sobre > 0 ? (int) round($de * 100 / $sobre) : null;

        return [
            ['etapa' => 'Contactados', 'valor' => $contactados, 'tasa' => null],
            ['etapa' => 'Contestaron', 'valor' => $contestaron, 'tasa' => $tasa($contestaron, $contactados)],
            ['etapa' => 'Demo agendada', 'valor' => $agendaron, 'tasa' => $tasa($agendaron, $contestaron)],
            ['etapa' => 'Demo hecha', 'valor' => $hicieron, 'tasa' => $tasa($hicieron, $agendaron)],
            ['etapa' => 'Cerraron', 'valor' => $cerraron, 'tasa' => $tasa($cerraron, $hicieron)],
        ];
    }

    /**
     * Lo de hoy, en frases.
     *
     * Cada renglón es una tarea con su número, en el orden en que conviene
     * hacerlas. Cuando no quedan números verificados eso también es una tarea
     * —conseguir y verificar la siguiente tanda—, y es la que más se olvida
     * porque no se siente como vender.
     */
    public static function tareasDeHoy(int $repId): array
    {
        $contestaron = self::suyos($repId)->whereNotNull('replied_at')->count();
        $seguimientos = self::seguimientos($repId)->count();
        $porContactar = self::primerContacto($repId)->count();
        $numeros = self::numeros($repId);

        $tareas = [];

        if ($contestaron > 0) {
            $tareas[] = [
                'que' => $contestaron === 1
                    ? 'Contestarle a 1 doctor que te escribió'
                    : "Contestarles a {$contestaron} que te escribieron",
                'porque' => 'Es lo primero: el que levantó la mano y se enfría ya no vuelve.',
                'urgente' => true,
            ];
        }

        if ($seguimientos > 0) {
            $tareas[] = [
                'que' => "Mandar {$seguimientos} " . ($seguimientos === 1 ? 'seguimiento' : 'seguimientos'),
                'porque' => 'Ya les escribiste antes y hoy les toca el siguiente mensaje.',
                'urgente' => false,
            ];
        }

        $faltan = max(0, $numeros['tope'] - $numeros['enviadosHoy']);

        if ($faltan === 0 && $numeros['enviadosHoy'] > 0) {
            // El tope existe para que no le bloqueen el número a quien vende.
            // Como límite se salta; como meta cumplida, se respeta.
            $tareas[] = [
                'que' => 'Meta del día cumplida · ' . $numeros['enviadosHoy'] . ' mensajes',
                'porque' => 'Lo que sigue, mejor mañana: el ritmo es lo que mantiene tu número sano.',
                'urgente' => false,
                'cumplida' => true,
            ];
        } elseif ($porContactar > 0) {
            $cuantos = min($porContactar, $faltan);

            $tareas[] = [
                'que' => "Mandar {$cuantos} primeros contactos",
                'porque' => 'Todos verificados. Después del mediodía, uno cada 8 o 10 minutos.',
                'urgente' => false,
            ];
        } else {
            $porVerificar = self::porVerificar($repId)->count();

            $tareas[] = $porVerificar > 0
                ? [
                    'que' => "Verificar {$porVerificar} números",
                    'porque' => 'No quedan verificados para escribir. Abrir el chat no manda nada: es para ver cuáles existen y dejar la cola lista.',
                    'urgente' => true,
                ]
                : [
                    'que' => 'Conseguir la siguiente tanda de números',
                    'porque' => 'Ya no queda ninguno por verificar. Sin números nuevos, mañana no hay a quién escribirle.',
                    'urgente' => true,
                ];
        }

        $paraLlamar = self::paraLlamar($repId)->count();

        if ($paraLlamar > 0) {
            $tareas[] = [
                'que' => "Tienes {$paraLlamar} consultorios sin WhatsApp, para llamarles",
                'porque' => 'El número existe, nada más no es de WhatsApp. Casi siempre es el fijo de recepción.',
                'urgente' => false,
            ];
        }

        if ($numeros['demosAgendadas'] > 0) {
            $tareas[] = [
                'que' => "Preparar {$numeros['demosAgendadas']} " . ($numeros['demosAgendadas'] === 1 ? 'demo agendada' : 'demos agendadas'),
                'porque' => 'Revisa antes qué te contestó cada uno, para enseñarle lo suyo y no todo.',
                'urgente' => false,
            ];
        }

        return $tareas;
    }
}
