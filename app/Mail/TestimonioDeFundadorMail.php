<?php

namespace App\Mail;

use App\Models\Clinic;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * A Omar: un fundador dejó su frase. El asunto dice si dio permiso de
 * publicarla, para que no se use por error una que no lo tiene.
 */
class TestimonioDeFundadorMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Clinic $clinic) {}

    public function envelope(): Envelope
    {
        $permiso = $this->clinic->testimonio_permiso_at
            ? '⭐ Frase de fundador, CON permiso de publicarla'
            : 'Frase de fundador, SIN permiso de publicarla';

        return new Envelope(subject: "{$permiso}: {$this->clinic->name}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.testimonio-de-fundador');
    }
}
