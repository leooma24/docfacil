<?php

namespace App\Filament\Sales\Pages;

use App\Filament\Sales\Resources\ProspectResource;
use App\Models\Prospect;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * A quién le escribo hoy.
 *
 * El CRM tiene más de mil quinientos prospectos y eso, en la práctica, es lo
 * mismo que no tener ninguno: nadie abre una lista de mil quinientos y decide.
 * Esta pantalla contesta la única pregunta que importa en la mañana, y se
 * limita a tres bloques para que quepa en una decisión de treinta segundos.
 *
 * Las dos reglas que la hacen confiable:
 *
 * 1. **Al primer contacto solo entran números verificados.** De los lotes de
 *    directorio, 9 de cada 10 no existían en WhatsApp. Cada mensaje al vacío
 *    no solo es tiempo perdido: acerca el número de quien vende a un bloqueo.
 * 2. **Un seguimiento es un seguimiento solo si alguien escribió antes.** El
 *    día de cadencia en cero delata al contacto que puso el correo automático
 *    y que nunca ocurrió.
 */
class ColaDelDia extends Page
{
    /** Cuántos primeros contactos al día. Decisión de Omar, 24-sep. */
    public const TOPE_DIARIO = 12;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationLabel = 'Cola del día';

    protected static ?string $title = 'Cola del día';

    protected static ?string $slug = 'cola-del-dia';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.sales.pages.cola-del-dia';

    public function getViewData(): array
    {
        return [
            'contestaron' => $this->contestaron(),
            'seguimientos' => $this->seguimientos(),
            'primerContacto' => $this->primerContacto(),
            'numeros' => $this->numeros(),
        ];
    }

    /** Los míos y nada más: cada vendedor trabaja su propia cartera. */
    private function mios(): Builder
    {
        return Prospect::query()
            ->where('assigned_to_sales_rep_id', auth()->id())
            ->whereNotIn('status', ['lost', 'converted']);
    }

    /**
     * Contestaron y siguen esperando. Va primero a propósito: un prospecto que
     * levantó la mano y se enfría es lo más caro que hay en todo el embudo.
     */
    private function contestaron(): Collection
    {
        return $this->mios()
            ->whereNotNull('replied_at')
            ->orderByDesc('replied_at')
            ->limit(20)
            ->get();
    }

    /** Les toca el siguiente mensaje de la cadencia, y sí se les escribió antes. */
    private function seguimientos(): Collection
    {
        return $this->mios()
            ->whereNull('replied_at')
            ->where('contact_day', '>', 0)
            ->where('last_contact_method', 'whatsapp')
            ->whereNotNull('next_contact_at')
            ->where('next_contact_at', '<=', now())
            ->orderBy('next_contact_at')
            ->limit(20)
            ->get();
    }

    /**
     * Nunca se les ha escrito y su número está verificado.
     *
     * Los de casa primero: el "soy de aquí de Los Mochis" solo se puede decir
     * una vez, y es lo único que ninguna empresa de software puede copiar.
     */
    private function primerContacto(): Collection
    {
        return $this->mios()
            ->where('status', 'new')
            ->where('contact_day', 0)
            ->where('has_whatsapp', true)
            ->whereNotNull('phone')
            ->orderByRaw("CASE WHEN city LIKE '%Mochis%' THEN 0 ELSE 1 END")
            ->orderByDesc('lead_score')
            ->orderBy('id')
            ->limit(self::TOPE_DIARIO)
            ->get();
    }

    private function numeros(): array
    {
        $base = fn () => Prospect::query()
            ->where('assigned_to_sales_rep_id', auth()->id())
            ->where('last_contact_method', 'whatsapp');

        return [
            'enviadosHoy' => $base()->whereDate('last_followup_at', today())->count(),
            'enviadosSemana' => $base()->where('last_followup_at', '>=', now()->startOfWeek())->count(),
            'respuestasHoy' => Prospect::where('assigned_to_sales_rep_id', auth()->id())
                ->whereDate('replied_at', today())->count(),
            'respuestasSemana' => Prospect::where('assigned_to_sales_rep_id', auth()->id())
                ->where('replied_at', '>=', now()->startOfWeek())->count(),
            'demosAgendadas' => Prospect::where('assigned_to_sales_rep_id', auth()->id())
                ->whereNotNull('demo_scheduled_at')
                ->whereNull('demo_completed_at')->count(),
            'tope' => self::TOPE_DIARIO,
            // El embudo por etapas. Lo que enseña no es el total, es en qué
            // escalón se cae: se puede tener buena tasa de respuesta y cero
            // demos, que es exactamente donde estamos.
            'embudo' => $this->embudo(),
        ];
    }

    /**
     * En qué escalón se cae.
     *
     * Contestar no es agendar y agendar no es cerrar. Medir solo los cierres
     * esconde dónde está la fuga: doce contestaron y ninguno llegó a demo, y
     * eso no se ve en un contador de ventas.
     */
    private function embudo(): array
    {
        $mios = fn () => Prospect::where('assigned_to_sales_rep_id', auth()->id());

        $contactados = (clone $mios())->where('contact_day', '>', 0)->count();
        $contestaron = (clone $mios())->whereNotNull('replied_at')->count();
        $agendaron = (clone $mios())->whereNotNull('demo_scheduled_at')->count();
        $hicieron = (clone $mios())->whereNotNull('demo_completed_at')->count();
        $cerraron = (clone $mios())->where('status', 'converted')->count();

        $tasa = fn (int $de, int $sobre) => $sobre > 0 ? round($de * 100 / $sobre) : null;

        return [
            ['etapa' => 'Contactados', 'valor' => $contactados, 'tasa' => null],
            ['etapa' => 'Contestaron', 'valor' => $contestaron, 'tasa' => $tasa($contestaron, $contactados)],
            ['etapa' => 'Demo agendada', 'valor' => $agendaron, 'tasa' => $tasa($agendaron, $contestaron)],
            ['etapa' => 'Demo hecha', 'valor' => $hicieron, 'tasa' => $tasa($hicieron, $agendaron)],
            ['etapa' => 'Cerraron', 'valor' => $cerraron, 'tasa' => $tasa($cerraron, $hicieron)],
        ];
    }

    /** El mensaje que le toca a este prospecto, desde la única fuente que hay. */
    public function ligaWhatsApp(Prospect $prospecto): string
    {
        return ProspectResource::buildContextualWhatsappUrl($prospecto);
    }

    /** El mensaje que pide la cita con hora concreta. */
    public function ligaParaPedirLaCita(Prospect $prospecto): string
    {
        return ProspectResource::buildDemoWhatsappUrl($prospecto);
    }

    /** De dónde salió su número, para poder mirar el perfil antes de escribir. */
    public function fuente(Prospect $prospecto): ?array
    {
        $notas = json_decode((string) $prospecto->notes, true);

        if (! is_array($notas) || empty($notas['source_url'])) {
            return null;
        }

        return [
            'url' => $notas['source_url'],
            'donde' => $notas['source_channel'] ?? 'su publicación',
        ];
    }
}
