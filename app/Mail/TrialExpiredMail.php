<?php

namespace App\Mail;

use App\Models\Clinic;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Clinic $clinic) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu prueba gratuita de DocFácil ha terminado',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trial-expired',
        );
    }
}
