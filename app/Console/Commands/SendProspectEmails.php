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
        'prospect_beta_invite' => "Hola Dr. *%s*,\n\nLe escribo porque me imaginé su semana: un par de pacientes que no llegaron, otro que canceló a última hora, y el hueco en la agenda ahí. Cada uno, $500 a $1,500 que se evaporaron.\n\nSoy Omar — hice *DocFácil* para que eso deje de pasar tanto. A 1 clic desde su agenda manda el recordatorio por WhatsApp, el paciente confirma con un link, y su expediente abre en 5 segundos.\n\nPruébelo 15 días con todo desbloqueado, sin tarjeta. Después su cuenta se queda viva en plan gratis — nunca pierde acceso.\n\nSe registra en 2 min aquí: %s\n\nO respóndame \"sí\" y le paso un video de 3 min mostrando cómo se ve.",

        'prospect_followup' => "Dr. *%s*, le escribo solo una cosa y me quito.\n\nHay un dentista aquí en Culiacán que antes perdía 6-8 pacientes a la semana. En 2 meses con DocFácil bajaron a 1-2 — manda recordatorios por WhatsApp a 1 clic, los pacientes confirman con un link, y tiene lista de espera que cubre cancelaciones automáticamente. Calcula que recupera ~$8,000 al mes. Paga $499.\n\nSi a su consultorio le suena familiar, el link sigue aquí — 15 días con todo, sin tarjeta: %s\n\nY si no le suena, ignóreme sin pena.",

        'prospect_last_chance' => "Dr. *%s*, último mensaje — no le quiero robar más tiempo.\n\nEl link para probar DocFácil 15 días gratis sigue aquí por si algún día le sirve: %s\n\nY si conoce a un colega al que le pueda servir, pásemelo y le regalo un mes cuando se suscriba.\n\nGracias por leerme hasta aquí.\n\n— Omar, 668 249 3398",
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

        // Pipeline operativo: solo procesamos prospects con email valido. Los
        // que solo tienen phone se quedan inactivos hasta que (a) consigamos
        // su email, o (b) la WhatsApp Business API este aprobada en produccion
        // (hoy esta en modo test y rechaza envios a numeros no pre-aprobados).
        $query = Prospect::where('status', $currentStatus)
            ->where('source', 'prospecting')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereNull('unsubscribed_at');

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

            $this->sendEmail($prospect, new $mailableClass($prospect), $messageType, $nextStatus);
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

        $message = sprintf($template, $prospect->firstName() ?: $prospect->cleanName(), url('/register'));

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
