<?php

namespace App\Console\Commands;

use App\Mail\CorteMensualMail;
use App\Models\Clinic;
use App\Models\LifecycleEmail;
use App\Services\NumerosDelCorte;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Manda a cada consultorio el corte del mes que acaba de terminar.
 *
 * Corre el día 1. Solo le llega a quien tiene gastos y corte en su plan y
 * registró algo ese mes: un correo que dice "entró $0, salió $0" no le sirve
 * a nadie y le enseña a ignorar los que siguen.
 */
class EnviarCorteMensual extends Command
{
    protected $signature = 'docfacil:corte-mensual
        {--mes= : El mes a mandar, como 2026-08. Si no se pone, el mes pasado}
        {--clinica= : Solo a este consultorio (id)}
        {--a= : Mandarlo a este correo en vez de al doctor, para verlo antes. No cuenta como enviado}';

    protected $description = 'Manda por correo el corte del mes (entró, salió, quedó) a cada consultorio';

    /** El consultorio del demo público: su correo no es un buzón real. */
    private const SLUG_DEL_DEMO = 'clinica-dental-sonrisas-cdmx';

    public function handle(): int
    {
        $mes = $this->mes();

        if (! $mes) {
            $this->error('El mes va como AAAA-MM, por ejemplo 2026-08.');

            return self::FAILURE;
        }

        $desde = $mes->startOfMonth();
        $hasta = $mes->endOfMonth();
        $antesDesde = $mes->subMonthNoOverflow()->startOfMonth();
        $antesHasta = $antesDesde->endOfMonth();

        // Un tipo por mes: así, si el cron corre dos veces, nadie recibe dos.
        $tipo = 'corte_mensual_' . $mes->format('Y-m');
        $vistaPrevia = $this->option('a');
        $enviados = 0;

        $clinicas = Clinic::query()
            ->where('is_active', true)
            ->where('slug', '!=', self::SLUG_DEL_DEMO)
            ->where(fn ($q) => $q->where('is_demo', false)->orWhereNull('is_demo'))
            // La vista previa sí sale aunque el doctor lo haya apagado: la
            // pide Omar para ver cómo se ve, no se le manda al doctor.
            ->when(! $vistaPrevia, fn ($q) => $q->where('corte_por_correo', true))
            ->when($this->option('clinica'), fn ($q, $id) => $q->whereKey($id))
            ->orderBy('id')
            ->get();

        foreach ($clinicas as $clinica) {
            if (! $clinica->hasFeature('expenses')) {
                continue;
            }

            $yaSeMando = LifecycleEmail::where('emailable_type', Clinic::class)
                ->where('emailable_id', $clinica->id)
                ->where('type', $tipo)
                ->exists();

            if ($yaSeMando && ! $vistaPrevia) {
                continue;
            }

            $numeros = NumerosDelCorte::calcular($clinica->id, $desde, $hasta, $antesDesde, $antesHasta);

            if (! $numeros['hay_datos']) {
                continue;
            }

            $doctor = $clinica->users()->where('role', 'doctor')->orderBy('id')->first();

            if (! $doctor) {
                continue;
            }

            $correo = new CorteMensualMail($clinica, $doctor, $mes, $numeros);
            $destino = $vistaPrevia ?: $doctor->email;

            try {
                Mail::to($destino)->send($correo);
            } catch (\Throwable $e) {
                $this->error("No se pudo mandar a {$destino}: {$e->getMessage()}");
                report($e);

                continue;
            }

            if (! $vistaPrevia) {
                LifecycleEmail::create([
                    'emailable_type' => Clinic::class,
                    'emailable_id' => $clinica->id,
                    'type' => $tipo,
                    'subject' => $correo->asunto(),
                    'sent_at' => now(),
                ]);
            }

            $enviados++;
            $this->line("Corte de {$mes->format('Y-m')} → {$destino} ({$clinica->name})");
        }

        $this->info("Cortes enviados: {$enviados}");

        return self::SUCCESS;
    }

    private function mes(): ?CarbonImmutable
    {
        $valor = $this->option('mes');

        if ($valor === null) {
            return CarbonImmutable::today()->subMonthNoOverflow()->startOfMonth();
        }

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $valor)) {
            return null;
        }

        return CarbonImmutable::createFromFormat('!Y-m', $valor)->startOfMonth();
    }
}
