<?php

namespace App\Mail;

use App\Models\Clinic;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BetaActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Clinic $clinic, public string $doctorName) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido al programa beta de DocFácil — 6 meses gratis',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.beta-activated',
        );
    }
}
