<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Scaffold para anunciar una subida de precios a clínicas pagantes.
 *
 * NO se corre automáticamente. Se ejecuta manualmente cuando se necesite.
 * Siempre usar primero `--dry-run` para ver la lista antes de mandar.
 *
 * Uso:
 *   php artisan docfacil:announce-pricing-increase --dry-run
 *   php artisan docfacil:announce-pricing-increase --date=2026-05-15
 */
class AnnouncePricingIncrease extends Command
{
    protected $signature = 'docfacil:announce-pricing-increase
        {--dry-run : Solo lista destinatarios sin enviar}
        {--date= : Fecha en que aplica el nuevo precio (YYYY-MM-DD)}';

    protected $description = 'Avisa a clínicas pagantes activas que su precio aumenta en el próximo ciclo';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $effectiveDate = $this->option('date') ?: now()->addDays(30)->toDateString();

        // Clínicas pagantes con plan activo. Se excluyen Free, demo, canceladas.
        $clinics = Clinic::query()
            ->whereIn('plan', ['basico', 'profesional', 'clinica'])
            ->where('is_active', true)
            ->whereNull('cancelled_at')
            ->where(function ($q) {
                $q->where('is_demo', false)->orWhereNull('is_demo');
            })
            ->with('users')
            ->get();

        if ($clinics->isEmpty()) {
            $this->info('No hay clínicas pagantes activas. Nada que enviar.');
            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY-RUN] ' : '') . "Encontradas {$clinics->count()} clínicas pagantes. Fecha efectiva: {$effectiveDate}");
        $this->newLine();

        $sent = 0;
        $failed = 0;

        foreach ($clinics as $clinic) {
            $owner = $clinic->users->first();
            $email = $owner?->email ?? $clinic->email;

            if (!$email) {
                $this->warn("  · {$clinic->name} (#{$clinic->id}): sin email, saltado");
                continue;
            }

            $this->line("  · {$clinic->name} (#{$clinic->id}) → {$email} [{$clinic->plan}]");

            if ($dryRun) {
                continue;
            }

            try {
                Mail::send('emails.pricing-increase', [
                    'clinic' => $clinic,
                    'doctorName' => $owner?->name ?? $clinic->name,
                    'effectiveDate' => $effectiveDate,
                ], function ($m) use ($email) {
                    $m->to($email)->subject('Actualización de precios DocFácil — información importante');
                });
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('pricing-increase email failed', ['clinic_id' => $clinic->id, 'err' => $e->getMessage()]);
                $this->error("    ✗ Falló: {$e->getMessage()}");
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->info("Dry-run completo. Re-corre sin --dry-run para enviar.");
        } else {
            $this->info("Enviados: {$sent}. Fallidos: {$failed}.");
        }

        return self::SUCCESS;
    }
}
