<?php

namespace App\Mail;

use App\Models\Prospect;
use App\Support\ProspectTrackingToken;
use App\Support\ProspectUnsubscribeToken;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class ProspectBetaInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Prospect $prospect) {}

    public function envelope(): Envelope
    {
        // Subject A/B 50/50 deterministico por prospect_id para tracking estable.
        // Más claros que los anteriores ("su agenda" / "consultorio en X" eran
        // crípticos y bajaban open rate). Ahora tono pregunta-personal.
        $city = trim((string) ($this->prospect->city ?? ''));
        $first = $this->prospect->firstName();
        $personalize = (! $this->prospect->isBusinessName() && ! empty($first))
            ? "Dr. {$first}, "
            : '';

        $subject = $this->prospect->id % 2 === 0
            ? "{$personalize}una pregunta sobre su consultorio"
            : ($city !== ''
                ? "Para consultorios dentales en {$city}"
                : "{$personalize}le escribo desde Sinaloa");

        return new Envelope(
            subject: $subject,
            replyTo: ['leooma24@gmail.com' => 'Omar Lerma'],
        );
    }

    /**
     * Headers RFC 8058 / Gmail 2024: permite a Gmail/iCloud mostrar el
     * boton "Unsubscribe" nativo en la UI del cliente, sin abrir el correo.
     */
    public function headers(): Headers
    {
        if (!$this->prospect->id) return new Headers();

        $url = route('prospect.unsubscribe', [
            'token' => ProspectUnsubscribeToken::make($this->prospect->id),
        ]);

        return new Headers(
            text: [
                'List-Unsubscribe' => "<{$url}>",
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                'X-DocFacil-Skip-Bcc' => '1',
            ],
        );
    }

    public function content(): Content
    {
        $registerUrl = url('/doctor/register?utm_source=prospect_email&utm_medium=email&utm_campaign=beta_invite');
        $token = $this->prospect->id
            ? ProspectTrackingToken::make($this->prospect->id, 'beta_invite', $registerUrl)
            : null;

        $unsubscribeUrl = $this->prospect->id
            ? route('prospect.unsubscribe', ['token' => ProspectUnsubscribeToken::make($this->prospect->id)])
            : null;

        return new Content(
            view: 'emails.prospect-beta-invite',
            with: [
                'salutation' => $this->prospect->salutationGreeting(),
                'followCall' => $this->prospect->salutationFollowCall(),
                'isBusiness' => $this->prospect->isBusinessName(),
                'cityPart' => $this->prospect->city ? " en {$this->prospect->city}" : '',
                'sector' => $this->prospect->sectorLabel(),
                'ctaUrl' => $token ? route('track.click', ['token' => $token]) : $registerUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
            ],
        );
    }
}
