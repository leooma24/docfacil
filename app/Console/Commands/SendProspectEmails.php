<?php

namespace App\Console\Commands;

use App\Mail\ProspectBetaInviteMail;
use App\Mail\ProspectFollowupMail;
use App\Mail\ProspectLastChanceMail;
use App\Models\LifecycleEmail;
use App\Models\Prospect;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendProspectEmails extends Command
{
    protected $signature = 'docfacil:send-prospect-emails';

    protected $description = 'Send pipeline messages (email + WhatsApp) to prospects (max 10 per run)';

    private int $sent = 0;

    private const MAX_PER_RUN = 10;

    private const DAYS_BETWEEN_MESSAGES = 3;

    private WhatsAppService $whatsapp;

    // WhatsApp message templates per pipeline step
    private const WA_MESSAGES = [
        'prospect_beta_invite' => "Hola *%s* 👋\n\nSoy Omar de *DocFácil*, un software para consultorios médicos y dentales.\n\nEstamos invitando consultorios de Sinaloa a nuestro *beta gratuito*:\n\n✅ Agenda de citas con calendario\n✅ Expedientes clínicos digitales\n✅ Recetas PDF profesionales\n✅ Recordatorios WhatsApp a pacientes\n✅ Control de cobros\n\nTodo *sin costo* durante el beta. Solo aceptamos 100 consultorios.\n\n¿Te interesa probarlo? Regístrate en: %s\n\nO responde este mensaje y te ayudo. 🙌",

        'prospect_followup' => "Hola *%s*, te escribí hace unos días sobre DocFácil 😊\n\nOtros consultorios ya están usando el sistema:\n📉 50%% menos citas perdidas\n⏱️ 20 min/día que se ahorran en papelería\n💰 Mejor control de pagos\n\nEn menos de 5 minutos tienes tu consultorio digital funcionando.\n\n¿Quieres que te haga una demo rápida? Regístrate aquí: %s\n\nO márcame al 668 249 3398 👍",

        'prospect_last_chance' => "Hola *%s*, último mensaje sobre DocFácil 🙏\n\nQuedan pocos lugares en el beta gratuito. Los que se registren ahora obtienen precio preferencial de por vida.\n\nResumen:\n• Agenda + recordatorios WhatsApp\n• Expedientes + recetas PDF\n• Control de cobros\n• Soporte directo por WhatsApp\n\nRegístrate gratis: %s\n\nSi no es para ti, no hay problema. ¡Éxito con tu consultorio! 🙌",
    ];

    public function handle(WhatsAppService $whatsapp): int
    {
        $this->whatsapp = $whatsapp;

        // Pipeline: new → contacted → interested → lost
        $this->processStep('new', 'prospect_beta_invite', 'contacted', ProspectBetaInviteMail::class);
        $this->processStep('contacted', 'prospect_followup', 'interested', ProspectFollowupMail::class, 'prospect_beta_invite');
        $this->processStep('interested', 'prospect_last_chance', 'lost', ProspectLastChanceMail::class, 'prospect_followup');

        $this->info("Prospect messages sent: {$this->sent}/" . self::MAX_PER_RUN);

        return Command::SUCCESS;
    }

    private function processStep(string $currentStatus, string $messageType, string $nextStatus, string $mailableClass, ?string $previousType = null): void
    {
        if ($this->sent >= self::MAX_PER_RUN) return;

        $query = Prospect::where('status', $currentStatus)
            ->where('source', 'prospecting')
            ->where(function ($q) {
                $q->whereNotNull('email')->orWhereNotNull('phone');
            });

        // For follow-ups, wait DAYS_BETWEEN_MESSAGES after previous message
        if ($previousType) {
            $query->whereHas('lifecycleEmails', function ($q) use ($previousType) {
                $q->where('type', $previousType)
                    ->where('sent_at', '<=', now()->subDays(self::DAYS_BETWEEN_MESSAGES));
            });
        }

        $prospects = $query->orderBy('created_at', 'asc')
            ->limit(self::MAX_PER_RUN - $this->sent)
            ->get();

        foreach ($prospects as $prospect) {
            if ($this->sent >= self::MAX_PER_RUN) return;

            if ($this->alreadySent($prospect, $messageType)) continue;

            // Try email first, fallback to WhatsApp
            if ($prospect->email) {
                $this->sendEmail($prospect, new $mailableClass($prospect), $messageType, $nextStatus);
            } elseif ($prospect->phone) {
                $this->sendWhatsApp($prospect, $messageType, $nextStatus);
            }
        }
    }

    private function alreadySent(Prospect $prospect, string $type): bool
    {
        return LifecycleEmail::where('emailable_type', Prospect::class)
            ->where('emailable_id', $prospect->id)
            ->where('type', $type)
            ->exists();
    }

    private function sendEmail(Prospect $prospect, $mailable, string $messageType, string $nextStatus): void
    {
        try {
            Mail::to($prospect->email)->send($mailable);

            $this->recordAndAdvance($prospect, $messageType, $mailable->envelope()->subject, $nextStatus, 'email');
            $this->line("✉ [{$messageType}] email → {$prospect->email} (ahora: {$nextStatus})");
        } catch (\Exception $e) {
            $this->error("✗ Email error {$prospect->email}: {$e->getMessage()}");
        }
    }

    private function sendWhatsApp(Prospect $prospect, string $messageType, string $nextStatus): void
    {
        $template = self::WA_MESSAGES[$messageType] ?? null;
        if (!$template) return;

        $message = sprintf($template, $prospect->name, url('/register'));

        try {
            $success = $this->whatsapp->sendMessage($prospect->phone, $message);

            if ($success) {
                $this->recordAndAdvance($prospect, $messageType, "WhatsApp: {$messageType}", $nextStatus, 'whatsapp');
                $this->line("📱 [{$messageType}] whatsapp → {$prospect->phone} (ahora: {$nextStatus})");
            } else {
                $this->error("✗ WhatsApp falló para {$prospect->phone}");
            }
        } catch (\Exception $e) {
            $this->error("✗ WhatsApp error {$prospect->phone}: {$e->getMessage()}");
        }
    }

    private function recordAndAdvance(Prospect $prospect, string $messageType, string $subject, string $nextStatus, string $channel): void
    {
        LifecycleEmail::create([
            'emailable_type' => Prospect::class,
            'emailable_id' => $prospect->id,
            'type' => $messageType,
            'subject' => "[{$channel}] {$subject}",
            'sent_at' => now(),
        ]);

        $updateData = ['status' => $nextStatus];
        if ($nextStatus === 'contacted') {
            $updateData['contacted_at'] = now();
        }
        $prospect->update($updateData);

        $this->sent++;
    }
}
