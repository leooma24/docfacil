<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Prospect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

trait NotifiesNewLead
{
    protected function notifyAdminNewLead(Prospect $prospect, ?string $subjectPrefix = null, ?string $extraBody = null): void
    {
        $recipients = array_filter(array_map(
            'trim',
            explode(',', (string) config('services.notifications.emails', 'leooma24@gmail.com'))
        ));
        if (empty($recipients)) {
            return;
        }

        $sourceLabel = match ($prospect->source) {
            'landing' => 'Formulario landing',
            'chatbot_landing' => 'Chatbot IA',
            default => ucfirst($prospect->source ?? 'desconocido'),
        };

        $body = sprintf(
            "Nuevo prospecto registrado en DocFácil (%s).\n\n" .
                "Nombre: %s\nEmail: %s\nTeléfono: %s\nConsultorio: %s\nCiudad: %s\nEspecialidad: %s\n" .
                "Lead score: %s\n\nMensaje/Notas:\n%s\n\n" .
                "Ver en admin: %s/admin/prospects/%d/edit",
            $sourceLabel,
            $prospect->name,
            $prospect->email,
            $prospect->phone ?: '—',
            $prospect->clinic_name ?: '—',
            $prospect->city ?: '—',
            $prospect->specialty ?: '—',
            $prospect->lead_score !== null ? $prospect->lead_score . '/100' : '—',
            $prospect->notes ?: '(sin mensaje)',
            rtrim(config('app.url'), '/'),
            $prospect->id
        );

        if ($extraBody) {
            $body .= "\n\n" . $extraBody;
        }

        $subject = ($subjectPrefix ?: 'Nuevo lead') . ': ' . $prospect->name;

        try {
            Mail::raw($body, function ($mail) use ($recipients, $subject) {
                $mail->to($recipients)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Error al notificar prospect por email', [
                'prospect_id' => $prospect->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
