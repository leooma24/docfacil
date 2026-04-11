<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppBotService
{
    /**
     * Handle an incoming WhatsApp message: identify patient, build context,
     * ask AI to respond naturally, and send the reply.
     */
    public function handleIncoming(string $fromPhone, string $messageText): ?string
    {
        $phone = preg_replace('/\D/', '', $fromPhone);
        $last10 = substr($phone, -10);

        $patient = Patient::where('phone', 'like', "%{$last10}")->first();

        if (!$patient) {
            return $this->sendReply($fromPhone, "Hola, no pudimos identificarte en nuestro sistema. Por favor contacta directamente a la clínica para ayudarte. 🙂");
        }

        $clinic = $patient->clinic;
        if (!$clinic) return null;

        // Short-term memory: last 6 messages per patient
        $historyKey = "wa_bot_history:{$patient->id}";
        $history = Cache::get($historyKey, []);
        $history[] = ['role' => 'user', 'content' => $messageText];
        $history = array_slice($history, -6);

        $context = $this->buildPatientContext($patient, $clinic);

        $system = "Eres el asistente virtual de la clínica *{$clinic->name}*. "
            . "Respondes mensajes de pacientes por WhatsApp de forma BREVE, amable y profesional. "
            . "Usas emojis ocasionalmente (1-2 por mensaje). "
            . "Si te preguntan sobre:\n"
            . "- Su próxima cita: dales fecha y hora exacta\n"
            . "- Su receta/medicamento: explica las indicaciones que tienen\n"
            . "- Reagendar: diles que le avisarás a la clínica\n"
            . "- Precio de un servicio: dales el dato si lo tienes\n"
            . "- Emergencia o dolor intenso: diles que llamen a la clínica urgentemente\n"
            . "- Cualquier pregunta médica compleja: diles que el doctor los verá en la cita\n\n"
            . "NO inventes información que no tengas. Si no sabes algo, diles que le avisarás a la clínica.\n"
            . "Máximo 3 oraciones por respuesta.\n\n"
            . "DATOS DEL PACIENTE:\n{$context}";

        $messages = [['role' => 'system', 'content' => $system]];
        foreach ($history as $h) {
            $messages[] = $h;
        }

        try {
            $response = $this->callAi($messages);
            if (!$response) {
                return $this->sendReply($fromPhone, "Hola {$patient->first_name}, recibimos tu mensaje. En breve te contactamos. 🙂");
            }

            $history[] = ['role' => 'assistant', 'content' => $response];
            Cache::put($historyKey, $history, now()->addHours(2));

            return $this->sendReply($fromPhone, $response);
        } catch (\Throwable $e) {
            Log::error('WhatsApp bot exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function buildPatientContext(Patient $patient, Clinic $clinic): string
    {
        $lines = [];
        $lines[] = "Nombre: {$patient->first_name} {$patient->last_name}";
        if ($patient->allergies) $lines[] = "⚠ Alergias: {$patient->allergies}";

        $nextAppt = Appointment::where('patient_id', $patient->id)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->with('service', 'doctor.user')
            ->first();

        if ($nextAppt) {
            $lines[] = "Próxima cita: " . $nextAppt->starts_at->translatedFormat('l d \d\e F, H:i') . " hrs";
            if ($nextAppt->service) $lines[] = "Servicio: {$nextAppt->service->name}";
            if ($nextAppt->doctor?->user) $lines[] = "Doctor: Dr. {$nextAppt->doctor->user->name}";
        } else {
            $lines[] = "No tiene citas próximas agendadas";
        }

        $lastRx = $patient->prescriptions()->with('items')->latest('prescription_date')->first();
        if ($lastRx) {
            $meds = $lastRx->items->map(fn ($i) => "{$i->medication} {$i->dosage}, {$i->frequency} x {$i->duration}")->implode('; ');
            $lines[] = "Última receta ({$lastRx->prescription_date->format('d/m/Y')}): {$meds}";
        }

        $lines[] = "Teléfono clínica: " . ($clinic->phone ?? 'no disponible');

        return implode("\n", $lines);
    }

    protected function callAi(array $messages): ?string
    {
        $provider = config('services.ai.provider', 'deepseek');
        $config = config("services.ai.{$provider}");
        $apiKey = $config['key'] ?? null;
        if (!$apiKey) return null;

        if ($provider === 'anthropic') {
            $system = '';
            $userMessages = [];
            foreach ($messages as $m) {
                if ($m['role'] === 'system') $system = $m['content'];
                else $userMessages[] = $m;
            }
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $config['model'],
                    'max_tokens' => 300,
                    'system' => $system,
                    'messages' => $userMessages,
                ]);
            return $response->successful() ? trim($response->json('content.0.text') ?? '') : null;
        }

        $response = Http::timeout(30)
            ->withToken($apiKey)
            ->post($config['base_url'] . '/chat/completions', [
                'model' => $config['model'],
                'max_tokens' => 300,
                'temperature' => 0.5,
                'messages' => $messages,
            ]);

        return $response->successful() ? trim($response->json('choices.0.message.content') ?? '') : null;
    }

    protected function sendReply(string $to, string $message): ?string
    {
        app(WhatsAppService::class)->sendMessage($to, $message);
        return $message;
    }
}
