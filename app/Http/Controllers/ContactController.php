<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NotifiesNewLead;
use App\Models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    use NotifiesNewLead;

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

        $this->notifyAdminNewLead($prospect);

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

}
