<?php

namespace App\Console\Commands;

use App\Filament\Sales\Resources\ProspectResource;
use App\Models\Prospect;
use App\Support\MensajesDeVenta;
use App\Support\SiguientePaso;
use Illuminate\Console\Command;

/**
 * La ficha de un prospecto en texto, para el skill de ventas: quién es, qué
 * ha dicho, qué mensajes recibió y qué sigue. Solo lee.
 */
class VentasProspecto extends Command
{
    protected $signature = 'docfacil:ventas-prospecto {buscar : nombre, consultorio o teléfono}';

    protected $description = 'Ficha de un prospecto: lo que dijo, los mensajes que recibió y el siguiente paso (solo lee)';

    public function handle(): int
    {
        $buscar = trim((string) $this->argument('buscar'));
        $digitos = preg_replace('/\D/', '', $buscar);

        $encontrados = Prospect::query()
            ->where(fn ($q) => $q->where('name', 'like', "%{$buscar}%")
                ->orWhere('clinic_name', 'like', "%{$buscar}%")
                ->when(strlen($digitos) >= 7, fn ($q) => $q->orWhere('phone', 'like', '%' . substr($digitos, -10) . '%')))
            ->limit(10)->get();

        if ($encontrados->isEmpty()) {
            $this->line("No encontré a nadie con \"{$buscar}\".");

            return self::FAILURE;
        }

        if ($encontrados->count() > 1) {
            $this->line("Hay {$encontrados->count()} con \"{$buscar}\"; diga cuál:");
            foreach ($encontrados as $p) {
                $this->line('- ' . VentasHoy::quien($p) . " · estado {$p->status} · paso {$p->contact_day}");
            }

            return self::SUCCESS;
        }

        $p = $encontrados->first();
        $notas = VentasHoy::notas($p);
        $paso = SiguientePaso::para($p);

        $this->line('# ' . VentasHoy::quien($p));
        $this->line("Estado: {$p->status} · paso de la cadencia: {$p->contact_day}"
            . ($p->replied_at ? ' · contestó ' . $p->replied_at->locale('es')->diffForHumans() : ' · no ha contestado')
            . ($p->demo_scheduled_at ? ' · demo: ' . $p->demo_scheduled_at->locale('es')->isoFormat('dddd D [a las] H:mm') : '')
            . ($p->demo_completed_at ? ' (ya se hizo)' : ''));
        if ($notas['dijo']) {
            $this->line('Lo que dijo: "' . $notas['dijo'] . '"');
        }
        if ($notas['dolor']) {
            $this->line('Cómo le hace hoy: ' . $notas['dolor']);
        }

        $this->line('');
        $this->line('## Mensajes que recibió');
        foreach ($p->mensajes()->orderBy('enviado_at')->get() as $m) {
            $this->line('- ' . $m->enviado_at->format('d/m H:i') . ' · ' . (MensajesDeVenta::ETIQUETAS[$m->version] ?? $m->version) . ($m->estimado ? ' (fecha estimada)' : ''));
        }

        $this->line('');
        $this->line("## Siguiente paso: {$paso['que']}");
        $this->line($paso['porque']);
        if ($paso['mensaje']) {
            $texto = str_contains($paso['mensaje'], 'text=') ? urldecode(substr($paso['mensaje'], strpos($paso['mensaje'], 'text=') + 5)) : $paso['mensaje'];
            $this->line('');
            $this->line('Mensaje que propone el CRM:');
            $this->line($texto);
        }
        if ($video = ProspectResource::videoDelSeguimiento($p)) {
            $this->line("Video para el siguiente mensaje: {$video['titulo']} ({$video['url']})");
        }

        return self::SUCCESS;
    }
}
