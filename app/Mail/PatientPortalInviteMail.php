<?php

namespace App\Mail;

use App\Models\Patient;
use App\Services\PatientPortalInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PatientPortalInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Patient $patient) {}

    public function envelope(): Envelope
    {
        $clinica = $this->patient->clinic->name ?? 'tu consultorio';

        return new Envelope(
            subject: "Tu acceso en línea de {$clinica}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.patient-portal-invite',
            with: [
                'nombre' => $this->patient->first_name,
                'clinica' => $this->patient->clinic->name ?? 'tu consultorio',
                'link' => PatientPortalInvite::link($this->patient),
                'dias' => PatientPortalInvite::DIAS_VIGENCIA,
            ],
        );
    }
}
