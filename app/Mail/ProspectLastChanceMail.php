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
        // A/B subject 50/50 entre dos variantes calmadas
        $subject = $this->prospect->id % 2 === 0
            ? 'cierro este hilo'
            : 'última nota';

        return new Envelope(subject: $subject);
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
                'firstName' => $this->prospect->firstName(),
                'ctaUrl' => $token ? route('track.click', ['token' => $token]) : $registerUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
            ],
        );
    }
}
