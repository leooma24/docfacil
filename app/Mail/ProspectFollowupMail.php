<?php

namespace App\Mail;

use App\Models\Prospect;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProspectFollowupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Prospect $prospect) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¿Sigues usando agenda de papel? Hay una mejor forma',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.prospect-followup',
            with: [
                'prospectName' => $this->prospect->name,
                'specialty' => $this->prospect->specialty,
            ],
        );
    }
}
