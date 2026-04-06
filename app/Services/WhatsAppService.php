<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

            Log::error("WhatsApp: Failed to send to {$to}", [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error("WhatsApp: Exception sending to {$to}: {$e->getMessage()}");
            return false;
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
        // Remove spaces, dashes, parentheses
        $phone = preg_replace('/[\s\-\(\)\+]/', '', $phone);

        // Add Mexico country code if not present
        if (strlen($phone) === 10) {
            $phone = '52' . $phone;
        }

        return $phone;
    }
}
