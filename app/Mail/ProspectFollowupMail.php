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

class ProspectFollowupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Prospect $prospect) {}

    public function envelope(): Envelope
    {
        // A/B subject 50/50: el ID par recibe la cifra cruda, el impar la
        // pregunta. Deterministico por prospect_id para analytics estables.
        $subject = $this->prospect->id % 2 === 0
            ? '$8,000 al mes'
            : 'haga la cuenta';

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
        $registerUrl = url('/doctor/register?utm_source=prospect_email&utm_medium=email&utm_campaign=followup');
        $token = $this->prospect->id
            ? ProspectTrackingToken::make($this->prospect->id, 'followup', $registerUrl)
            : null;

        $unsubscribeUrl = $this->prospect->id
            ? route('prospect.unsubscribe', ['token' => ProspectUnsubscribeToken::make($this->prospect->id)])
            : null;

        return new Content(
            view: 'emails.prospect-followup',
            with: [
                'firstName' => $this->prospect->firstName(),
                'ctaUrl' => $token ? route('track.click', ['token' => $token]) : $registerUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
            ],
        );
    }
}
