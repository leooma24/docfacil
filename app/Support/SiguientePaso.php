<?php

namespace App\Support;

use App\Filament\Sales\Resources\ProspectResource;
use App\Models\Clinic;
use App\Models\Commission;
use App\Models\Prospect;

/**
 * Qué toca hacer con este prospecto, una sola cosa.
 *
 * El CRM ya decía a quién escribirle. Lo que faltaba es esto, y ahí estaba la
 * fuga: de los tres que contestaron en septiembre, uno agendó demo —porque se
 * le pidió la cita— y a los otros dos la conversación se les quedó a medias.
 *
 * Contestar no es diagnosticar, diagnosticar no es agendar, y agendar no es
 * cerrar. Cada etapa tiene una acción y nada más una: quien intenta vender,
 * agendar y pedir referido en el mismo mensaje no consigue ninguna.
 *
 * Nada de esto adivina. Todo sale de columnas que ya existen: si contestó, si
 * ya sabemos cómo le hace hoy, si tiene demo, si la demo ya pasó.
 */
class SiguientePaso
{
    /**
     * @return array{etapa: string, que: string, porque: string, mensaje: ?string, boton: ?string}
     */
    public static function para(Prospect $prospecto): array
    {
        if (in_array($prospecto->status, ['lost'], true)) {
            return self::paso('cerrado', 'Dijo que no', 'Se le agradeció y se dejó la puerta abierta. No se insiste.');
        }

        if ($prospecto->status === 'converted') {
            return self::paso(
                'referido',
                'Pídele un referido',
                'El referido es el prospecto más caliente que hay, y a este ya le funcionó.',
                self::mensajeDeReferido($prospecto),
            );
        }

        if ($prospecto->demo_completed_at) {
            return self::paso(
                'cerrar',
                'Pide el cierre',
                'Ya vio el sistema. Lo que sigue es preguntar, con dos opciones donde las dos son un sí.',
                self::mensajeDeCierre($prospecto),
            );
        }

        if ($prospecto->demo_scheduled_at) {
            return $prospecto->demo_scheduled_at->isPast()
                ? self::paso(
                    'marcar_demo',
                    'Marca si la demo se hizo',
                    'Sin esto no se puede saber cuántas demos cierran, que es el único dato que falta.',
                    null,
                    'demo_hecha',
                )
                : self::paso(
                    'preparar_demo',
                    'Prepara la demo del ' . $prospecto->demo_scheduled_at->locale('es')->isoFormat('dddd [a las] H:mm'),
                    'Repasa lo que te dijo y enséñale eso primero, no todo el sistema.',
                );
        }

        if (! $prospecto->replied_at) {
            return self::paso(
                'esperando',
                'Todavía no contesta',
                $prospecto->next_contact_at
                    ? 'Le toca el siguiente mensaje de la cadencia.'
                    : 'Ya se le escribió. Ahora es de él.',
            );
        }

        return self::conoceSuDolor($prospecto)
            ? self::paso(
                'pedir_cita',
                'Pídele la cita',
                'Ya sabes qué le duele. Aquí es donde se cae el embudo: contestan y nadie pide la cita.',
                ProspectResource::buildDemoWhatsappUrl($prospecto),
            )
            : self::paso(
                'diagnosticar',
                'Hazle la segunda pregunta',
                'Contestó, pero todavía no sabes cómo le hace hoy. De ahí sale todo lo demás.',
                self::mensajeDeDiagnostico($prospecto),
            );
    }

    /** ¿Ya nos dijo cómo le hace hoy? */
    private static function conoceSuDolor(Prospect $prospecto): bool
    {
        $notas = json_decode((string) $prospecto->notes, true);

        return is_array($notas) && ! empty($notas['dolor']);
    }

    /**
     * La segunda pregunta: la que destraba.
     *
     * Concreta y con opciones, para que contestarla cueste cinco segundos.
     * Nadie contesta "no aviso": todos dicen cómo le hacen, y ahí sale solo el
     * tiempo que se les va haciéndolo a mano.
     */
    private static function mensajeDeDiagnostico(Prospect $prospecto): string
    {
        $trato = self::trato($prospecto);

        return self::liga($prospecto,
            "{$trato}, gracias por contestar. Una más y ya no lo distraigo:\n\n"
            . "¿esos mensajes los escribe usted uno por uno, o alguien de su equipo? Y más o menos, ¿cuánto tiempo al día se le va en eso?"
        );
    }

    /**
     * El cierre: la oferta que ya promete la landing, pidiendo algo a cambio.
     *
     * Los meses y el precio salen de config/founders.php, que es lo mismo que
     * lee la página: si algún día cambia la oferta, cambia en un solo lugar y
     * nadie se entera por un mensaje que dice otra cosa.
     *
     * Y se pide retroalimentación a cambio. Esa es la parte que faltaba: el que
     * no da nada a cambio tampoco se compromete, y un "está bien" de alguien
     * que no usa el sistema no sirve para construir nada.
     */
    private static function mensajeDeCierre(Prospect $prospecto): string
    {
        $trato = self::trato($prospecto);
        $meses = (int) config('founders.free_months', 6);
        $precio = number_format((float) config('founders.monthly_price', 499));
        $lugares = Clinic::lugaresDeFundador();

        $texto = "{$trato}, le explico cómo funciona el programa de fundador.\n\n"
            . "Son {$meses} meses sin costo, y de ahí \${$precio} al mes congelado de por vida: nunca se lo subo. "
            . "A cambio le pido quince minutos de retroalimentación al mes, para irlo mejorando con lo que usted vaya viendo.\n\n";

        // Los lugares solo cuando ya hay fundadores: decir "quedan 10 de 10" no
        // crea urgencia, suena a guion.
        if ($lugares['tomados'] > 0 && $lugares['hay']) {
            $texto .= "Quedan {$lugares['quedan']} de los {$lugares['total']} lugares.\n\n";
        }

        $texto .= self::preguntaDeCierre($prospecto);

        return self::liga($prospecto, $texto);
    }

    /** Dos opciones donde las dos son un sí, según a quién se le pregunta. */
    private static function preguntaDeCierre(Prospect $prospecto): string
    {
        $notas = json_decode((string) $prospecto->notes, true);
        $doctores = is_array($notas) ? (int) ($notas['doctores'] ?? 0) : 0;

        if ($doctores > 1) {
            $basico = number_format(Commission::monthlyPriceForPlan('basico'));
            $pro = number_format(Commission::monthlyPriceForPlan('profesional'));

            return "¿Lo dejamos en el suyo, de \${$basico}, o lo abrimos para los {$doctores} doctores, que son \${$pro}?";
        }

        return '¿Se lo dejo listo hoy, o prefiere que lo veamos el lunes?';
    }

    /** Al cliente se le pide el referido, que es el prospecto más caliente que hay. */
    private static function mensajeDeReferido(Prospect $prospecto): string
    {
        $trato = self::trato($prospecto);

        return self::liga($prospecto,
            "{$trato}, una última cosa y ya.\n\n"
            . "¿Conoce a otro dentista al que le sirva? No tanto para venderle: entre más me digan cómo trabajan, mejor lo armo. "
            . 'Con que me diga el nombre, yo le escribo.'
        );
    }

    private static function trato(Prospect $prospecto): string
    {
        return $prospecto->salutationFollowCall() ?: 'Doctor';
    }

    private static function liga(Prospect $prospecto, string $texto): string
    {
        $telefono = preg_replace('/\D/', '', (string) $prospecto->phone);

        if (strlen($telefono) === 10) {
            $telefono = '52' . $telefono;
        }

        return "https://wa.me/{$telefono}?text=" . urlencode($texto);
    }

    /** @return array{etapa: string, que: string, porque: string, mensaje: ?string, boton: ?string} */
    private static function paso(string $etapa, string $que, string $porque, ?string $mensaje = null, ?string $boton = null): array
    {
        return compact('etapa', 'que', 'porque', 'mensaje', 'boton');
    }
}
