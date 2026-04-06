<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'docfacil:send-reminders';

    protected $description = 'Send WhatsApp reminders for appointments in the next 24 hours';

    public function handle(WhatsAppService $whatsapp): int
    {
        $appointments = Appointment::with(['patient', 'doctor.user', 'clinic'])
            ->where('reminder_sent', false)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereBetween('starts_at', [now(), now()->addHours(24)])
            ->get();

        $sent = 0;

        foreach ($appointments as $appointment) {
            $phone = $appointment->patient->phone;

            if (empty($phone)) {
                $this->line("Skip: {$appointment->patient->full_name} - no phone");
                continue;
            }

            $success = $whatsapp->sendAppointmentReminder(
                to: $phone,
                patientName: $appointment->patient->full_name,
                doctorName: $appointment->doctor->user->name ?? '',
                dateTime: $appointment->starts_at->translatedFormat('l d \d\e F, H:i') . ' hrs',
                clinicName: $appointment->clinic->name ?? 'DocFácil',
            );

            if ($success) {
                $appointment->update(['reminder_sent' => true]);
                $sent++;
                $this->line("Sent: {$appointment->patient->full_name} ({$phone})");
            } else {
                $this->error("Failed: {$appointment->patient->full_name} ({$phone})");
            }
        }

        $this->info("Reminders sent: {$sent}/{$appointments->count()}");

        return Command::SUCCESS;
    }
}
