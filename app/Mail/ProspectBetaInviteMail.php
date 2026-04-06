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
        return new Envelope(
            subject: 'Invitación exclusiva: Prueba DocFácil gratis para tu consultorio',
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
