<?php

namespace App\Mail;

use App\Models\Clinic;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BetaExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Clinic $clinic, public int $daysLeft) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu periodo beta en DocFácil termina en {$this->daysLeft} días",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.beta-expiring',
        );
    }
}
