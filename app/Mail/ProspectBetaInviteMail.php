<?php

namespace App\Mail;

use App\Models\Prospect;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProspectBetaInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Prospect $prospect) {}

    public function envelope(): Envelope
    {
        $firstName = trim(explode(' ', (string) $this->prospect->name)[0] ?? '');
        $greeting = $firstName !== '' ? "Dr. {$firstName}, " : '';

        return new Envelope(
            subject: "{$greeting}¿cuántos le dejaron plantado esta semana?",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.prospect-beta-invite',
            with: [
                'prospectName' => $this->prospect->name,
                'clinicName' => $this->prospect->clinic_name,
            ],
        );
    }
}
