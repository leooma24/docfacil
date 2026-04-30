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

class ProspectLastChanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Prospect $prospect) {}

    public function envelope(): Envelope
    {
        // Subject A/B 50/50, tono cordial de cierre — no "cierro este hilo"
        // que sonaba transaccional.
        $first = $this->prospect->firstName();
        $personalize = (! $this->prospect->isBusinessName() && ! empty($first))
            ? "Dr. {$first}, "
            : '';

        $subject = $this->prospect->id % 2 === 0
            ? "{$personalize}último mensaje y le dejo en paz"
            : "{$personalize}antes de cerrar este hilo";

        return new Envelope(
            subject: $subject,
            replyTo: ['leooma24@gmail.com' => 'Omar Lerma'],
        );
    }

    public function headers(): Headers
    {
        if (!$this->prospect->id) return new Headers();
        $url = route('prospect.unsubscribe', [
            'token' => ProspectUnsubscribeToken::make($this->prospect->id),
        ]);
        return new Headers(text: [
            'List-Unsubscribe' => "<{$url}>",
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            'X-DocFacil-Skip-Bcc' => '1',
        ]);
    }

    public function content(): Content
    {
        $registerUrl = url('/doctor/register?utm_source=prospect_email&utm_medium=email&utm_campaign=last_chance');
        $token = $this->prospect->id
            ? ProspectTrackingToken::make($this->prospect->id, 'last_chance', $registerUrl)
            : null;

        $unsubscribeUrl = $this->prospect->id
            ? route('prospect.unsubscribe', ['token' => ProspectUnsubscribeToken::make($this->prospect->id)])
            : null;

        return new Content(
            view: 'emails.prospect-last-chance',
            with: [
                'salutation' => $this->prospect->salutationGreeting(),
                'followCall' => $this->prospect->salutationFollowCall(),
                'isBusiness' => $this->prospect->isBusinessName(),
                'sector' => $this->prospect->sectorLabel(),
                'ctaUrl' => $token ? route('track.click', ['token' => $token]) : $registerUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
            ],
        );
    }
}
