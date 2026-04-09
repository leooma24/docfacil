<?php

namespace App\Mail;

use App\Models\Commission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommissionEarnedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Commission $commission)
    {
    }

    public function envelope(): Envelope
    {
        $tier = $this->commission->tier === 'first' ? 'primera mitad' : 'segunda mitad';
        return new Envelope(
            subject: "🎉 Nueva comisión ganada — {$tier} de \${$this->commission->amount}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.commission-earned',
            with: [
                'commission' => $this->commission,
                'clinic' => $this->commission->clinic,
                'user' => $this->commission->user,
            ],
        );
    }
}
