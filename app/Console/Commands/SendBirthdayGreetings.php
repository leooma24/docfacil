<?php

namespace App\Console\Commands;

use App\Models\Patient;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

/**
 * Felicitacion de cumpleanos por WhatsApp a pacientes cuyo birth_date
 * coincide con hoy (mes + dia). Idempotente: last_birthday_greeting_year
 * evita doble envio si el cron corre dos veces el mismo dia. Feature-gated
 * por whatsapp_reminders (Basico+).
 *
 * Programado diario a las 10:00 AM (hora del server). Si un paciente no
 * tiene telefono o su clinica no tiene el feature activo, se omite.
 */
class SendBirthdayGreetings extends Command
{
    protected $signature = 'docfacil:send-birthday-greetings';

    protected $description = 'Send WhatsApp birthday greetings to patients whose birthday is today';

    public function handle(WhatsAppService $whatsapp): int
    {
        $today = now();
        $year = (int) $today->year;

        $patients = Patient::with(['clinic'])
            ->whereNotNull('birth_date')
            ->whereNotNull('phone')
            ->whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->where(function ($q) use ($year) {
                $q->whereNull('last_birthday_greeting_year')
                  ->orWhere('last_birthday_greeting_year', '<', $year);
            })
            ->whereHas('clinic', fn ($q) => $q->withActiveFeature('whatsapp_reminders'))
            ->get();

        $count = 0;
        foreach ($patients as $patient) {
            $name = $patient->first_name ?: 'hola';
            $clinicName = $patient->clinic->name ?? 'tu consultorio';

            $message = "🎉 *¡Feliz cumpleaños, {$name}!* 🎂\n\n"
                . "Todo el equipo de *{$clinicName}* te desea un día increíble. "
                . "Gracias por confiar en nosotros para cuidar tu salud.\n\n"
                . "Que cumplas muchos más. 🌟";

            if ($whatsapp->sendMessage($patient->phone, $message)) {
                $patient->forceFill(['last_birthday_greeting_year' => $year])->save();
                $count++;
                $this->line("Birthday greeting sent: {$name} ({$patient->clinic->name})");
            }
        }

        $this->info("Birthday greetings sent: {$count}");
        return Command::SUCCESS;
    }
}
