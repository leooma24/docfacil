<?php

namespace App\Mail;

use App\Models\Prospect;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Alerta a Omar cuando un prospect cruza umbral 80 (caliente). Push del
 * Gmail app le llega al celular en segundos.
 */
class LeadHeatedUpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Prospect $prospect, public int $score)
    {
    }

    public function envelope(): Envelope
    {
        $name = $this->prospect->cleanName() ?: 'Sin nombre';
        return new Envelope(
            subject: "🔥 Lead caliente: {$name} · Score {$this->score}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lead-heated-up',
            with: [
                'prospect' => $this->prospect,
                'score' => $this->score,
                'panelUrl' => url("/ventas/prospectos/{$this->prospect->id}/edit"),
            ],
        );
    }
}
