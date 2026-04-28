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
        $firstName = $this->prospect->firstName();
        $greeting = $firstName !== '' ? "Dr. {$firstName}, " : '';

        return new Envelope(
            subject: "{$greeting}¿cuántos le dejaron plantado esta semana?",
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
                'prospectName' => $this->prospect->cleanName(),
                'clinicName' => $this->prospect->clinic_name,
                'ctaUrl' => $token ? route('track.click', ['token' => $token]) : $registerUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
            ],
        );
    }
}
