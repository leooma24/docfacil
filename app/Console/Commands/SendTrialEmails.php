<?php

namespace App\Console\Commands;

use App\Mail\TrialExpiredMail;
use App\Mail\TrialExpiringMail;
use App\Models\Clinic;
use App\Models\LifecycleEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTrialEmails extends Command
{
    protected $signature = 'docfacil:send-trial-emails';

    protected $description = 'Send trial expiring and expired emails to clinics';

    public function handle(): int
    {
        $this->sendExpiringEmails(3, 'trial_expiring_3d');
        $this->sendExpiringEmails(1, 'trial_expiring_1d');
        $this->sendExpiredEmails();

        $this->info('Trial emails processed.');

        return Command::SUCCESS;
    }

    private function sendExpiringEmails(int $daysLeft, string $emailType): void
    {
        $targetDate = now()->addDays($daysLeft)->toDateString();

        $clinics = Clinic::where('plan', 'free')
            ->whereDate('trial_ends_at', $targetDate)
            ->where('is_active', true)
            ->get();

        foreach ($clinics as $clinic) {
            $alreadySent = LifecycleEmail::where('emailable_type', Clinic::class)
                ->where('emailable_id', $clinic->id)
                ->where('type', $emailType)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $owner = $clinic->users()->where('role', 'doctor')->first();
            if (!$owner) {
                continue;
            }

            Mail::to($owner->email)->send(new TrialExpiringMail($clinic, $daysLeft));

            LifecycleEmail::create([
                'emailable_type' => Clinic::class,
                'emailable_id' => $clinic->id,
                'type' => $emailType,
                'subject' => "Tu prueba gratuita termina en {$daysLeft} días",
                'sent_at' => now(),
            ]);

            $this->line("Sent {$emailType} to {$owner->email}");
        }
    }

    private function sendExpiredEmails(): void
    {
        $clinics = Clinic::where('plan', 'free')
            ->whereDate('trial_ends_at', now()->subDay()->toDateString())
            ->where('is_active', true)
            ->get();

        foreach ($clinics as $clinic) {
            $alreadySent = LifecycleEmail::where('emailable_type', Clinic::class)
                ->where('emailable_id', $clinic->id)
                ->where('type', 'trial_expired')
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $owner = $clinic->users()->where('role', 'doctor')->first();
            if (!$owner) {
                continue;
            }

            Mail::to($owner->email)->send(new TrialExpiredMail($clinic));

            LifecycleEmail::create([
                'emailable_type' => Clinic::class,
                'emailable_id' => $clinic->id,
                'type' => 'trial_expired',
                'subject' => 'Tu prueba gratuita ha terminado',
                'sent_at' => now(),
            ]);

            $this->line("Sent trial_expired to {$owner->email}");
        }
    }
}
