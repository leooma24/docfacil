<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\URL;

/**
 * El recordatorio que le llega al paciente por WhatsApp.
 *
 * Vive aquí porque lo arman tres pantallas distintas —el comando de las 24
 * horas, la lista de citas y el widget del día— y cada una lo tenía escrito
 * por su cuenta. Con el texto copiado tres veces, corregir una frase deja las
 * otras dos como estaban.
 *
 * Lo que cambió al juntarlo:
 *
 * - **Una liga, no dos.** Antes iban dos direcciones firmadas de unos 250
 *   caracteres cada una, confirmar y cancelar. En el celular eso se ve como un
 *   muro de letras y números: da desconfianza y no se sabe cuál tocar. Ahora
 *   va una sola liga corta que abre una página con los dos botones.
 * - **Abrir no es confirmar.** La página ya no decide por el paciente: él ve
 *   su cita y elige. Antes, entrar a curiosear la dejaba confirmada.
 */
class RecordatorioDeCita
{
    /** Cuánto dura la liga antes de caducar. */
    public const HORAS_DE_VIGENCIA = 72;

    /**
     * El mensaje listo para mandar por WhatsApp.
     *
     * Sin emojis y de usted, como el resto de lo que sale a pacientes.
     */
    public static function mensaje(Appointment $cita, string $momento = '24h'): string
    {
        $cita->loadMissing(['patient', 'clinic']);

        $nombre = trim((string) $cita->patient?->first_name) ?: 'Hola';
        $consultorio = $cita->clinic?->name ?? 'su consultorio';
        $hora = $cita->starts_at->format('H:i');

        $cuando = match ($momento) {
            '2h' => 'hoy a las ' . $hora,
            default => $cita->starts_at->locale('es')->isoFormat('dddd [a las] ') . $hora,
        };

        return "Hola {$nombre}, le recordamos su cita en {$consultorio} {$cuando}.\n\n"
            . "Confirme o cancele aquí:\n"
            . self::liga($cita) . "\n\n"
            . '¡Le esperamos!';
    }

    /**
     * Las citas que el paciente tiene ese mismo día y siguen vigentes, en
     * orden. Con varias, se le manda un solo recordatorio con todas.
     */
    public static function delMismoDia(Appointment $cita): \Illuminate\Support\Collection
    {
        return Appointment::withoutGlobalScopes()
            ->where('clinic_id', $cita->clinic_id)
            ->where('patient_id', $cita->patient_id)
            ->whereDate('starts_at', $cita->starts_at->toDateString())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * El recordatorio de todo el día del paciente: si tiene una cita es el de
     * siempre; si tiene varias, un solo mensaje con todas las horas.
     */
    public static function mensajeDelDia(Appointment $cita): string
    {
        $citas = self::delMismoDia($cita);

        if ($citas->count() <= 1) {
            return self::mensaje($cita);
        }

        $cita->loadMissing(['patient', 'clinic']);
        $nombre = trim((string) $cita->patient?->first_name) ?: 'Hola';
        $consultorio = $cita->clinic?->name ?? 'su consultorio';
        $dia = $cita->starts_at->isToday() ? 'hoy' : $cita->starts_at->locale('es')->isoFormat('dddd');
        $horas = $citas->map(fn ($c) => $c->starts_at->format('H:i'))->values();
        $lista = $horas->count() === 2
            ? $horas[0] . ' y a las ' . $horas[1]
            : $horas->slice(0, -1)->implode(', ') . ' y a las ' . $horas->last();

        return "Hola {$nombre}, le recordamos sus citas en {$consultorio} {$dia} a las {$lista}.\n\n"
            . "Confirme o cancele aquí:\n"
            . self::liga($citas->first()) . "\n\n"
            . '¡Le esperamos!';
    }

    /** La dirección de WhatsApp con el recordatorio del día listo. */
    public static function ligaDeWhatsapp(Appointment $cita): ?string
    {
        $telefono = preg_replace('/\D/', '', (string) $cita->patient?->phone);
        if ($telefono === '') {
            return null;
        }
        if (strlen($telefono) === 10) {
            $telefono = '52' . $telefono;
        }

        return 'https://wa.me/' . $telefono . '?text=' . urlencode(self::mensajeDelDia($cita));
    }

    /**
     * La liga corta que abre la página de la cita.
     *
     * La dirección real va firmada y con caducidad; la corta es la que se
     * manda, y es la que cabe en un mensaje sin taparlo.
     */
    public static function liga(Appointment $cita): string
    {
        return ShortUrl::make(
            self::ligaDirecta($cita),
            now()->addHours(self::HORAS_DE_VIGENCIA),
        );
    }

    /**
     * La dirección firmada, sin acortar.
     *
     * Sin `$accion` abre la página para que el paciente decida; con ella, es
     * el botón que confirma o cancela.
     */
    public static function ligaDirecta(Appointment $cita, ?string $accion = null): string
    {
        return URL::temporarySignedRoute(
            'appointment.confirm',
            now()->addHours(self::HORAS_DE_VIGENCIA),
            array_filter([
                'appointment' => $cita->id,
                'action' => $accion,
            ]),
        );
    }
}
