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
        $firstName = $this->prospect->firstName();
        $greeting = $firstName !== '' ? "Dr. {$firstName}, " : '';

        return new Envelope(
            subject: "{$greeting}cierro este hilo — gracias por su tiempo",
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
                'prospectName' => $this->prospect->cleanName(),
                'ctaUrl' => $token ? route('track.click', ['token' => $token]) : $registerUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
            ],
        );
    }
}
