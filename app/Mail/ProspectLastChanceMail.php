<?php

namespace App\Mail;

use App\Models\Prospect;
use App\Support\ProspectTrackingToken;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProspectLastChanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Prospect $prospect) {}

    public function envelope(): Envelope
    {
        $firstName = trim(explode(' ', (string) $this->prospect->name)[0] ?? '');
        $greeting = $firstName !== '' ? "Dr. {$firstName}, " : '';

        return new Envelope(
            subject: "{$greeting}cierro este hilo — gracias por su tiempo",
        );
    }

    public function content(): Content
    {
        $registerUrl = url('/doctor/register?utm_source=prospect_email&utm_medium=email&utm_campaign=last_chance');
        $token = $this->prospect->id
            ? ProspectTrackingToken::make($this->prospect->id, 'last_chance', $registerUrl)
            : null;

        return new Content(
            view: 'emails.prospect-last-chance',
            with: [
                'prospectName' => $this->prospect->name,
                'ctaUrl' => $token ? route('track.click', ['token' => $token]) : $registerUrl,
            ],
        );
    }
}
