<?php

namespace App\Mail;

use App\Models\Prospect;
use App\Support\ProspectTrackingToken;
use App\Support\ProspectUnsubscribeToken;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class ProspectFollowupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Prospect $prospect) {}

    public function envelope(): Envelope
    {
        $firstName = $this->prospect->firstName();
        $greeting = $firstName !== '' ? "Dr. {$firstName}, " : '';

        return new Envelope(
            subject: "{$greeting}le dejo un caso que quizá le suene familiar",
        );
    }

    public function headers(): Headers
    {
        if (!$this->prospect->id) return new Headers();
        $url = route('prospect.unsubscribe', [
            'token' => ProspectUnsubscribeToken::make($this->prospect->id),
        ]);
        return new Headers(text: [
            'List-Unsubscribe' => "<{$url}>",
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        $registerUrl = url('/doctor/register?utm_source=prospect_email&utm_medium=email&utm_campaign=followup');
        $token = $this->prospect->id
            ? ProspectTrackingToken::make($this->prospect->id, 'followup', $registerUrl)
            : null;

        $unsubscribeUrl = $this->prospect->id
            ? route('prospect.unsubscribe', ['token' => ProspectUnsubscribeToken::make($this->prospect->id)])
            : null;

        return new Content(
            view: 'emails.prospect-followup',
            with: [
                'prospectName' => $this->prospect->cleanName(),
                'specialty' => $this->prospect->specialty,
                'ctaUrl' => $token ? route('track.click', ['token' => $token]) : $registerUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
            ],
        );
    }
}
