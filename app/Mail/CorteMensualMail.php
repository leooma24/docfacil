<?php

namespace App\Mail;

use App\Models\Clinic;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * El corte del mes, en el correo del doctor el día 1.
 *
 * Casi nadie le vende a un dentista "cuánto te quedó". Este correo se lo dice
 * sin que tenga que entrar, y de paso le da una razón para entrar cada mes.
 * Va por correo y no por WhatsApp porque por correo no cuesta.
 */
class CorteMensualMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array  $numeros  Lo que regresa NumerosDelCorte::calcular()
     */
    public function __construct(
        public Clinic $clinic,
        public User $doctor,
        public CarbonImmutable $mes,
        public array $numeros,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->asunto());
    }

    /**
     * El asunto ya dice el número. Mucha gente no abre el correo, y aun así
     * se queda con cuánto le quedó.
     */
    public function asunto(): string
    {
        $mes = $this->nombreDelMes();
        $n = $this->numeros;

        // Sin gastos anotados, "te quedó" sería todo lo que entró: mentira.
        if ($n['gastos'] <= 0) {
            return "Tu corte de {$mes}: entraron " . self::pesos($n['ingresos']);
        }

        if ($n['utilidad'] < 0) {
            return "Tu corte de {$mes}: salió más de lo que entró";
        }

        return "Tu corte de {$mes}: te quedaron " . self::pesos($n['utilidad']);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.corte-mensual',
            with: [
                'nombreMes' => $this->nombreDelMes(),
                'mesAnterior' => $this->mes->subMonthNoOverflow()->locale('es')->translatedFormat('F'),
                // Abre el corte justo en este mes, no en el que va corriendo.
                'urlCorte' => url('/doctor/corte') . '?' . http_build_query([
                    'desde' => $this->numeros['desde']->toDateString(),
                    'hasta' => $this->numeros['hasta']->toDateString(),
                ]),
                'urlSinCorreo' => URL::signedRoute('corte.sin-correo', ['clinic' => $this->clinic->id]),
            ],
        );
    }

    public function nombreDelMes(): string
    {
        return $this->mes->locale('es')->translatedFormat('F');
    }

    private static function pesos(float $monto): string
    {
        return '$' . number_format($monto, 0);
    }
}
