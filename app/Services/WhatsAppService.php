<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WhatsAppService
{
    private string $apiUrl;
    private string $token;
    private string $phoneNumberId;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token') ?? '';
        $this->phoneNumberId = config('services.whatsapp.phone_number_id') ?? '';
        $this->apiUrl = "https://graph.facebook.com/v21.0/{$this->phoneNumberId}/messages";
    }

    public function sendMessage(string $to, string $message): bool
    {
        if (empty($this->token) || empty($this->phoneNumberId)) {
            Log::warning('WhatsApp: Token or Phone Number ID not configured');
            return false;
        }

        // Format phone number (remove spaces, add country code if needed)
        $to = $this->formatPhoneNumber($to);

        try {
            $response = Http::withToken($this->token)
                ->post($this->apiUrl, [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'text',
                    'text' => [
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                Log::info("WhatsApp: Message sent to {$to}");
                return true;
            }

            $this->handleHttpFailure($to, $response->status(), $response->json());
            return false;
        } catch (\Exception $e) {
            Log::error("WhatsApp: Exception sending to {$to}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Registra el fallo y — si es token expirado (401) — dispara alerta al admin.
     * Throttled: máximo 1 alerta cada 6 horas para no spammear cuando fallan cientos seguidos.
     */
    private function handleHttpFailure(string $to, int $status, $body): void
    {
        Log::error("WhatsApp: Failed to send to {$to}", ['status' => $status, 'body' => $body]);

        if ($status !== 401) {
            return;
        }

        $throttleKey = 'whatsapp:token_expired_alert_sent';
        if (Cache::has($throttleKey)) {
            return; // ya avisamos hace menos de 6 horas
        }
        Cache::put($throttleKey, now()->toIso8601String(), now()->addHours(6));

        $adminEmails = collect(explode(',', (string) config('services.notifications.emails', 'leooma24@gmail.com')))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->values()
            ->all();

        $subject = '[DocFacil] Token de WhatsApp expirado — renueva en Meta Business';
        $bodyMsg = "El token de la WhatsApp Business API expiró o fue revocado.\n\n"
            . "Todas las comunicaciones automáticas por WhatsApp (recordatorios de citas, "
            . "aprobaciones de pago SPEI, emails al bot) están fallando con error 401.\n\n"
            . "Para renovarlo:\n"
            . "  1. Entra a https://business.facebook.com\n"
            . "  2. Settings → WhatsApp Accounts → System Users\n"
            . "  3. Genera un nuevo Permanent Token\n"
            . "  4. Actualiza WHATSAPP_TOKEN en el .env de prod\n"
            . "  5. Ejecuta: php artisan config:clear\n\n"
            . "Primer fallo detectado: " . now()->toDateTimeString() . " hora servidor.";

        foreach ($adminEmails as $email) {
            try {
                Mail::raw($bodyMsg, fn ($m) => $m->to($email)->subject($subject));
            } catch (\Throwable $e) {
                Log::warning('WhatsApp: no se pudo enviar alerta de token expirado', ['err' => $e->getMessage()]);
            }
        }
    }

    public function sendTemplate(string $to, string $templateName, array $parameters = [], string $language = 'es_MX'): bool
    {
        if (empty($this->token) || empty($this->phoneNumberId)) {
            return false;
        }

        $to = $this->formatPhoneNumber($to);

        $components = [];
        if (!empty($parameters)) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(fn ($value) => [
                    'type' => 'text',
                    'text' => $value,
                ], $parameters),
            ];
        }

        try {
            $response = Http::withToken($this->token)
                ->post($this->apiUrl, [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'template',
                    'template' => [
                        'name' => $templateName,
                        'language' => ['code' => $language],
                        'components' => $components,
                    ],
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("WhatsApp: Template exception: {$e->getMessage()}");
            return false;
        }
    }

    public function sendAppointmentReminder(string $to, string $patientName, string $doctorName, string $dateTime, string $clinicName): bool
    {
        $message = "🏥 *Recordatorio de cita — {$clinicName}*\n\n"
            . "Hola *{$patientName}*, te recordamos tu cita:\n\n"
            . "👨‍⚕️ Doctor: {$doctorName}\n"
            . "📅 Fecha y hora: {$dateTime}\n\n"
            . "Si necesitas cancelar o reagendar, por favor contáctanos.\n\n"
            . "— DocFácil";

        return $this->sendMessage($to, $message);
    }

    private function formatPhoneNumber(string $phone): string
    {
        // Quitar espacios, guiones, paréntesis, signo +
        $phone = preg_replace('/[\s\-\(\)\+]/', '', (string) $phone);

        // 10 dígitos sin lada → agregar 52
        if (strlen($phone) === 10) {
            $phone = '52' . $phone;
        }
        // 11 dígitos empezando con 1 (formato legacy mexicano sin lada) → meter 52 antes
        elseif (strlen($phone) === 11 && str_starts_with($phone, '1')) {
            $phone = '52' . $phone;
        }

        // Normaliza el "1" móvil mexicano legacy: Meta WhatsApp espera 12 dígitos
        // (52 + 10), no 13 (52 + 1 + 10). Strip del "1" extra después del 52.
        if (strlen($phone) === 13 && str_starts_with($phone, '521')) {
            $phone = '52' . substr($phone, 3);
        }

        return $phone;
    }
}
