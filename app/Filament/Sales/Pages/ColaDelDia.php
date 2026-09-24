<?php

namespace App\Filament\Sales\Pages;

use App\Filament\Sales\Resources\ProspectResource;
use App\Models\Prospect;
use App\Support\CargaDeTrabajo;
use Filament\Pages\Page;

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
    /** El tope vive en CargaDeTrabajo, que es de donde sale la cola. */
    public const TOPE_DIARIO = CargaDeTrabajo::TOPE_DIARIO;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationLabel = 'Cola del día';

    protected static ?string $title = 'Cola del día';

    protected static ?string $slug = 'cola-del-dia';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.sales.pages.cola-del-dia';

    public function getViewData(): array
    {
        $repId = (int) auth()->id();

        return [
            'contestaron' => CargaDeTrabajo::contestaron($repId),
            'seguimientos' => CargaDeTrabajo::seguimientos($repId),
            'primerContacto' => CargaDeTrabajo::primerContacto($repId),
            'numeros' => CargaDeTrabajo::numeros($repId),
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
