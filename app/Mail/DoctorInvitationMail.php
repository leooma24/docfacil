<?php

namespace App\Mail;

use App\Models\DoctorInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DoctorInvitation $invitation) {}

    public function envelope(): Envelope
    {
        $clinicName = $this->invitation->clinic?->name ?? 'un consultorio';
        return new Envelope(
            subject: "Invitación a unirse a {$clinicName} en DocFácil",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.doctor-invitation',
            with: [
                'name' => $this->invitation->name,
                'clinicName' => $this->invitation->clinic?->name ?? 'un consultorio',
                'inviterName' => $this->invitation->invitedBy?->name ?? 'el equipo',
                'specialty' => $this->invitation->specialty,
                'acceptUrl' => route('invitation.accept', ['token' => $this->invitation->token]),
                'expiresAt' => $this->invitation->expires_at?->translatedFormat('d \d\e F \a \l\a\s H:i'),
            ],
        );
    }
}
