<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\LifecycleEmail;
use App\Models\Patient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEngagementEmails extends Command
{
    protected $signature = 'docfacil:send-engagement';

    protected $description = 'Send engagement emails to inactive beta testers and clinics';

    public function handle(): int
    {
        $this->sendInactiveBetaEmails();
        $this->sendNoPatientEmails();
        $this->sendNoAppointmentEmails();

        $this->info('Engagement emails processed.');

        return Command::SUCCESS;
    }

    private function sendInactiveBetaEmails(): void
    {
        // Beta testers with 0 appointments in the last 7 days
        $clinics = Clinic::where('is_beta', true)
            ->where('is_active', true)
            ->whereNotNull('beta_starts_at')
            ->where('beta_starts_at', '<=', now()->subDays(3)) // Give them 3 days to start
            ->get();

        foreach ($clinics as $clinic) {
            $recentActivity = Appointment::where('clinic_id', $clinic->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();

            if ($recentActivity) continue;

            $emailType = 'engagement_inactive_7d';
            $alreadySent = LifecycleEmail::where('emailable_type', Clinic::class)
                ->where('emailable_id', $clinic->id)
                ->where('type', $emailType)
                ->where('sent_at', '>=', now()->subDays(7)) // Don't spam, max 1 per week
                ->exists();

            if ($alreadySent) continue;

            $owner = $clinic->users()->where('role', 'doctor')->first();
            if (!$owner) continue;

            $subject = 'Te extrañamos en DocFácil — ¿necesitas ayuda?';
            $this->sendEmail($owner->email, $subject, 'emails.engagement-inactive', [
                'clinic' => $clinic,
                'doctorName' => $owner->name,
            ], $clinic, $emailType);

            $this->line("Sent inactive reminder to {$owner->email}");
        }
    }

    private function sendNoPatientEmails(): void
    {
        // Clinics with beta active but 0 patients after 3 days
        $clinics = Clinic::where('is_beta', true)
            ->where('is_active', true)
            ->where('beta_starts_at', '<=', now()->subDays(3))
            ->get();

        foreach ($clinics as $clinic) {
            $hasPatients = Patient::where('clinic_id', $clinic->id)->exists();
            if ($hasPatients) continue;

            $emailType = 'engagement_no_patients';
            $alreadySent = LifecycleEmail::where('emailable_type', Clinic::class)
                ->where('emailable_id', $clinic->id)
                ->where('type', $emailType)
                ->exists();

            if ($alreadySent) continue;

            $owner = $clinic->users()->where('role', 'doctor')->first();
            if (!$owner) continue;

            $subject = 'Tip: Registra tu primer paciente en DocFácil (toma 1 minuto)';
            $this->sendEmail($owner->email, $subject, 'emails.engagement-no-patients', [
                'clinic' => $clinic,
                'doctorName' => $owner->name,
            ], $clinic, $emailType);

            $this->line("Sent no-patients tip to {$owner->email}");
        }
    }

    private function sendNoAppointmentEmails(): void
    {
        // Clinics with patients but 0 appointments after 5 days
        $clinics = Clinic::where('is_beta', true)
            ->where('is_active', true)
            ->where('beta_starts_at', '<=', now()->subDays(5))
            ->get();

        foreach ($clinics as $clinic) {
            $hasPatients = Patient::where('clinic_id', $clinic->id)->exists();
            $hasAppointments = Appointment::where('clinic_id', $clinic->id)->exists();

            if (!$hasPatients || $hasAppointments) continue;

            $emailType = 'engagement_no_appointments';
            $alreadySent = LifecycleEmail::where('emailable_type', Clinic::class)
                ->where('emailable_id', $clinic->id)
                ->where('type', $emailType)
                ->exists();

            if ($alreadySent) continue;

            $owner = $clinic->users()->where('role', 'doctor')->first();
            if (!$owner) continue;

            $subject = 'Tu siguiente paso: agenda tu primera cita en DocFácil';
            $this->sendEmail($owner->email, $subject, 'emails.engagement-no-appointments', [
                'clinic' => $clinic,
                'doctorName' => $owner->name,
            ], $clinic, $emailType);

            $this->line("Sent no-appointments tip to {$owner->email}");
        }
    }

    private function sendEmail(string $to, string $subject, string $view, array $data, Clinic $clinic, string $type): void
    {
        try {
            Mail::send($view, $data, function ($msg) use ($to, $subject) {
                $msg->to($to)->subject($subject);
            });

            LifecycleEmail::create([
                'emailable_type' => Clinic::class,
                'emailable_id' => $clinic->id,
                'type' => $type,
                'subject' => $subject,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->error("Failed to send to {$to}: {$e->getMessage()}");
        }
    }
}
