<?php

namespace App\Console\Commands;

use App\Mail\ProspectBetaInviteMail;
use App\Mail\ProspectFollowupMail;
use App\Mail\ProspectLastChanceMail;
use App\Models\LifecycleEmail;
use App\Models\Prospect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendProspectEmails extends Command
{
    protected $signature = 'docfacil:send-prospect-emails';

    protected $description = 'Send pipeline messages (email + WhatsApp) to prospects (max 10 per run)';

    private int $sent = 0;

    private const MAX_PER_RUN = 10;

    /**
     * Cadencia (dias desde el envio anterior). Cumulativo: 0 / 4 / 9.
     * Espaciar mas alla de los 3 dias originales reduce queja-spam y
     * sube reply rate sin perder mas que 1 punto de cobertura.
     */
    private const DAYS_AFTER_PREVIOUS = [
        'prospect_beta_invite' => 0,    // primer toque, sin espera
        'prospect_followup' => 4,       // 4 dias despues del beta_invite
        'prospect_last_chance' => 5,    // 5 dias despues del followup (acumulado: 9)
    ];


    // WhatsApp message templates per pipeline step
    public function handle(): int
    {

        // La secuencia avanza por lo que ya se le mandó, no por el status: el
        // status es de la persona que vende. Ver ElCorreoNoMueveElEstadoTest.
        $this->processStep('prospect_beta_invite', ProspectBetaInviteMail::class);
        $this->processStep('prospect_followup', ProspectFollowupMail::class, 'prospect_beta_invite');
        $this->processStep('prospect_last_chance', ProspectLastChanceMail::class, 'prospect_followup');

        $this->info("Prospect messages sent: {$this->sent}/" . self::MAX_PER_RUN);

        return Command::SUCCESS;
    }

    private function processStep(string $messageType, string $mailableClass, ?string $previousType = null): void
    {
        if ($this->sent >= self::MAX_PER_RUN) return;

        // Pipeline operativo: solo procesamos prospects con email valido. Los
        // que solo tienen phone se quedan inactivos hasta que (a) consigamos
        // su email, o (b) la WhatsApp Business API este aprobada en produccion
        // (hoy esta en modo test y rechaza envios a numeros no pre-aprobados).
        // Fuera de la secuencia fría: quien ya dijo que no, quien ya es
        // cliente, y quien ya está en manos de una persona —contestó, o va
        // avanzado en la cadencia—. A ese se le escribe a mano, no en serie.
        $query = Prospect::whereNotIn('status', ['lost', 'converted', 'trial', 'interested'])
            ->whereNull('replied_at')
            ->where('contact_day', 0)
            ->where('source', 'prospecting')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereNull('unsubscribed_at')
            ->whereDoesntHave('lifecycleEmails', function ($q) use ($messageType) {
                $q->where('type', $messageType);
            });

        // Esperar el numero de dias configurado en DAYS_AFTER_PREVIOUS
        // desde el envio anterior (0 para el primer correo, 4-5 para follow-ups).
        if ($previousType) {
            $waitDays = self::DAYS_AFTER_PREVIOUS[$messageType] ?? 4;
            $query->whereHas('lifecycleEmails', function ($q) use ($previousType, $waitDays) {
                $q->where('type', $previousType)
                    ->where('sent_at', '<=', now()->subDays($waitDays));
            });
        }

        $prospects = $query->orderBy('created_at', 'asc')
            ->limit(self::MAX_PER_RUN - $this->sent)
            ->get();

        foreach ($prospects as $prospect) {
            if ($this->sent >= self::MAX_PER_RUN) return;
            if ($this->alreadySent($prospect, $messageType)) continue;

            $this->sendEmail($prospect, new $mailableClass($prospect), $messageType);
        }
    }

    private function alreadySent(Prospect $prospect, string $type): bool
    {
        return LifecycleEmail::where('emailable_type', Prospect::class)
            ->where('emailable_id', $prospect->id)
            ->where('type', $type)
            ->exists();
    }

    private function sendEmail(Prospect $prospect, $mailable, string $messageType): void
    {
        try {
            Mail::to($prospect->email)->send($mailable);

            $this->registrarEnvio($prospect, $messageType, $mailable->envelope()->subject);
            $this->line("✉ [{$messageType}] email → {$prospect->email}");
        } catch (\Exception $e) {
            $this->error("✗ Email error {$prospect->email}: {$e->getMessage()}");
        }
    }


    /**
     * Queda constancia del envío y nada más.
     *
     * Antes aquí se movía el `status`, y eso convirtió a 1,313 prospectos en
     * "contactados" que nadie contactó. El correo no es un contacto: es un
     * correo.
     */
    private function registrarEnvio(Prospect $prospect, string $messageType, string $subject): void
    {
        LifecycleEmail::create([
            'emailable_type' => Prospect::class,
            'emailable_id' => $prospect->id,
            'type' => $messageType,
            'subject' => "[email] {$subject}",
            'sent_at' => now(),
        ]);

        $this->sent++;
    }
}
