<?php

namespace App\Mail;

use App\Models\Prospect;
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
        return new Content(
            view: 'emails.prospect-last-chance',
            with: [
                'prospectName' => $this->prospect->name,
            ],
        );
    }
}
