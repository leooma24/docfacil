<?php

namespace App\Console\Commands;

use App\Filament\Sales\Resources\ProspectResource;
use App\Models\Prospect;
use App\Models\User;
use App\Support\CargaDeTrabajo;
use App\Support\MensajesDeVenta;
use App\Support\SiguientePaso;
use Illuminate\Console\Command;

/**
 * El día de ventas en texto, para el skill de ventas (.claude/skills/ventas-docfacil).
 *
 * Solo lee: lo que se marca en el CRM lo marca Omar desde /ventas.
 */
class VentasHoy extends Command
{
    protected $signature = 'docfacil:ventas-hoy {--rep= : id del vendedor (si hay uno solo, se toma ese)}';

    protected $description = 'Resumen de ventas de hoy: quién contestó, a quién le toca y cómo van los mensajes (solo lee)';

    public function handle(): int
    {
        $rep = self::vendedor($this->option('rep'));
        if (! $rep) {
            $this->error('No sé de qué vendedor. Use --rep=<id>.');

            return self::FAILURE;
        }

        $n = CargaDeTrabajo::numeros($rep->id);
        $this->line("# Ventas de hoy · {$rep->name} · " . now()->locale('es')->isoFormat('dddd D [de] MMMM'));
        $this->line("Enviados hoy: {$n['enviadosHoy']} de {$n['tope']} · esta semana: {$n['enviadosSemana']} · respuestas esta semana: {$n['respuestasSemana']} · demos agendadas: {$n['demosAgendadas']}");

        $this->line('');
        $this->line('## Contestaron (lo primero: el que levantó la mano y se enfría ya no vuelve)');
        $contestaron = CargaDeTrabajo::contestaron($rep->id)->reject(fn (Prospect $p) => in_array($p->status, ['lost', 'converted'], true));
        foreach ($contestaron as $p) {
            $paso = SiguientePaso::para($p);
            $notas = self::notas($p);
            $this->line('- ' . self::quien($p) . ' · contestó ' . $p->replied_at->locale('es')->diffForHumans()
                . ($notas['dijo'] ? ' · dijo: "' . $notas['dijo'] . '"' : '')
                . ($notas['dolor'] ? ' · cómo le hace hoy: ' . $notas['dolor'] : '')
                . " · siguiente: {$paso['que']}");
        }
        if ($contestaron->isEmpty()) {
            $this->line('- Nadie esperando respuesta.');
        }

        $this->line('');
        $seguimientos = CargaDeTrabajo::seguimientos($rep->id);
        $this->line("## Seguimiento de hoy ({$seguimientos->count()})");
        foreach ($seguimientos as $p) {
            $video = ProspectResource::videoDelSeguimiento($p);
            $this->line('- ' . self::quien($p) . " · paso {$p->contact_day}" . ($video ? " · adjuntar video: {$video['titulo']} ({$video['url']})" : ''));
        }

        $primeros = CargaDeTrabajo::primerContacto($rep->id);
        $this->line('');
        $this->line("## Primer contacto disponibles: {$primeros->count()} · por verificar: " . CargaDeTrabajo::porVerificar($rep->id)->count());

        $demos = Prospect::where('assigned_to_sales_rep_id', $rep->id)->whereNotNull('demo_scheduled_at')->whereNull('demo_completed_at')->orderBy('demo_scheduled_at')->get();
        $this->line('');
        $this->line('## Demos por hacer');
        foreach ($demos as $p) {
            $this->line('- ' . self::quien($p) . ' · ' . $p->demo_scheduled_at->locale('es')->isoFormat('dddd D [a las] H:mm'));
        }
        if ($demos->isEmpty()) {
            $this->line('- Ninguna agendada.');
        }

        $this->line('');
        $this->line('## Cómo van los mensajes');
        foreach (MensajesDeVenta::resultados($rep->id) as $f) {
            $this->line("- {$f['etiqueta']}: {$f['enviados']} lo recibieron, {$f['contestaron']} contestaron ({$f['porcentaje']}%)" . ($f['enviados'] < 30 ? ' · todavía son pocos' : ''));
        }

        return self::SUCCESS;
    }

    public static function vendedor(?string $id): ?User
    {
        if ($id) {
            return User::find($id);
        }

        $conProspectos = Prospect::query()->whereNotNull('assigned_to_sales_rep_id')->distinct()->pluck('assigned_to_sales_rep_id');

        return $conProspectos->count() === 1 ? User::find($conProspectos->first()) : null;
    }

    public static function quien(Prospect $p): string
    {
        return trim($p->name . ($p->clinic_name && $p->clinic_name !== $p->name ? " ({$p->clinic_name})" : '')
            . ($p->specialty ? " · {$p->specialty}" : '') . ($p->city ? " · {$p->city}" : ''));
    }

    /** @return array{dolor: ?string, dijo: ?string} */
    public static function notas(Prospect $p): array
    {
        $notas = json_decode((string) $p->notes, true);

        return ['dolor' => is_array($notas) ? ($notas['dolor'] ?? null) : null, 'dijo' => is_array($notas) ? ($notas['dijo'] ?? null) : null];
    }
}
