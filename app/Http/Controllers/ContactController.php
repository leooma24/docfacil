<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // --- Capa 1: Honeypot clásico ---
        if ($request->filled('website_url')) {
            return $this->silentSuccess($request, 'honeypot');
        }

        // --- Capa 2: Time-to-submit (<3s = bot) ---
        $renderedAt = (int) $request->input('form_rendered_at', 0);
        if ($renderedAt > 0 && (time() - $renderedAt) < 3) {
            return $this->silentSuccess($request, 'too_fast');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'clinic_name' => 'nullable|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        // --- Capa 3: Validación heurística de contenido ---
        if ($this->looksLikeSpam($validated)) {
            return $this->silentSuccess($request, 'heuristic');
        }

        $prospect = Prospect::updateOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'clinic_name' => $validated['clinic_name'] ?? null,
                'specialty' => $validated['specialty'] ?? null,
                'city' => $validated['city'] ?? null,
                'notes' => $validated['message'] ?? null,
                'source' => 'landing',
                'status' => 'new',
            ]
        );

        $this->notifyAdmin($prospect);

        return back()->with('contact_success', true);
    }

    private function silentSuccess(Request $request, string $reason)
    {
        Log::info('Contact form blocked', [
            'reason' => $reason,
            'ip' => $request->ip(),
            'email' => $request->input('email'),
        ]);
        return back()->with('contact_success', true);
    }

    private function looksLikeSpam(array $data): bool
    {
        // Teléfono con formato no-MX (si se proveyó)
        if (!empty($data['phone'])) {
            $digits = preg_replace('/\D/', '', $data['phone']);
            $len = strlen($digits);
            // MX: 10 dígitos, o 12 con +52, o 11 con 52
            if ($len < 10 || $len > 13) {
                return true;
            }
        }

        // Mensaje 100% en inglés (contiene palabras clave típicas y cero acentos)
        $msg = strtolower($data['message'] ?? '');
        if ($msg !== '') {
            $englishHits = preg_match_all(
                '/\b(hi|hello|price|pricing|website|seo|link|cheap|offer|services|guest post|backlink)\b/',
                $msg
            );
            $spanishHits = preg_match_all('/[áéíóúñ¿¡]|\b(hola|precio|consultorio|doctor|dentista|cita)\b/u', $msg);
            if ($englishHits >= 1 && $spanishHits === 0) {
                return true;
            }
        }

        // Nombre + clinic iguales (bots a veces rellenan todo igual)
        if (!empty($data['clinic_name']) && $data['name'] === $data['clinic_name']) {
            return true;
        }

        return false;
    }

    private function notifyAdmin(Prospect $prospect): void
    {
        $recipients = array_filter(array_map(
            'trim',
            explode(',', (string) config('services.notifications.emails', 'leooma24@gmail.com'))
        ));
        if (empty($recipients)) {
            return;
        }

        $body = sprintf(
            "Nuevo prospecto registrado desde el landing de DocFácil.\n\n" .
                "Nombre: %s\nEmail: %s\nTeléfono: %s\nConsultorio: %s\nCiudad: %s\nEspecialidad: %s\n\nMensaje:\n%s\n\n" .
                "Ver en admin: %s/admin/prospects/%d/edit",
            $prospect->name,
            $prospect->email,
            $prospect->phone ?: '—',
            $prospect->clinic_name ?: '—',
            $prospect->city ?: '—',
            $prospect->specialty ?: '—',
            $prospect->notes ?: '(sin mensaje)',
            rtrim(config('app.url'), '/'),
            $prospect->id
        );

        try {
            Mail::raw($body, function ($mail) use ($recipients, $prospect) {
                $mail->to($recipients)
                    ->subject("Nuevo lead landing: {$prospect->name}");
            });
        } catch (\Throwable $e) {
            Log::warning('Error al notificar prospect por email', [
                'prospect_id' => $prospect->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
