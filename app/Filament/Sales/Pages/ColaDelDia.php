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
            'porVerificar' => CargaDeTrabajo::porVerificar($repId),
            'numeros' => CargaDeTrabajo::numeros($repId),
        ];
    }

    /**
     * Abrir el chat cuenta como mandarlo.
     *
     * El botón es una liga normal —así el navegador abre WhatsApp con el
     * gesto del clic y no lo bloquea— y de paso avisa por aquí para dejar
     * constancia. Sin esto, el prospecto amanecía otra vez en la cola y se le
     * escribía dos veces: el error más caro de esta pantalla.
     *
     * La guarda de cinco minutos es por los clics repetidos: abrir el chat
     * tres veces seguidas no es mandar tres mensajes.
     */
    public function registrarEnvio(int $id): void
    {
        $prospecto = Prospect::where('assigned_to_sales_rep_id', auth()->id())->find($id);

        if (! $prospecto) {
            return;
        }

        $recienClicado = $prospecto->last_followup_at
            && $prospecto->last_followup_at->isAfter(now()->subMinutes(5));

        if ($recienClicado) {
            return;
        }

        if ($prospecto->status === 'new') {
            $prospecto->update([
                'status' => 'contacted',
                'contacted_at' => $prospecto->contacted_at ?? now(),
            ]);
        }

        $prospecto->advanceContactDay('whatsapp');
    }

    /**
     * El chat pelón, sin mensaje escrito.
     *
     * A propósito no lleva texto: esto es para ver si el número existe, no
     * para escribirle. Con el mensaje cargado, la tentación de mandarlo a un
     * número sin verificar está a un clic.
     */
    public function ligaParaVerificar(Prospect $prospecto): string
    {
        $telefono = preg_replace('/\D/', '', (string) $prospecto->phone);

        if (strlen($telefono) === 10) {
            $telefono = '52' . $telefono;
        }

        return "https://wa.me/{$telefono}";
    }

    /**
     * Queda anotado si el número existe o no.
     *
     * El que no existe se cierra —no se le va a poder escribir nunca— y el que
     * sí, pasa al primer contacto de mañana. Lo importante es que quede
     * escrito quién lo verificó y cuándo: la palomita que viene de un archivo
     * y no de abrir el chat ya nos costó una mañana de mensajes al vacío.
     */
    public function marcarVerificado(int $id, bool $existe): void
    {
        $prospecto = Prospect::where('assigned_to_sales_rep_id', auth()->id())->find($id);

        if (! $prospecto) {
            return;
        }

        $notas = json_decode((string) $prospecto->notes, true);
        $notas = is_array($notas) ? $notas : [];
        $notas[$existe ? 'verificado_wa' : 'sin_whatsapp'] = true;
        $notas['verificado_at'] = now()->toDateTimeString();

        $prospecto->update([
            'has_whatsapp' => $existe,
            'status' => $existe ? 'new' : 'lost',
            'next_contact_at' => null,
            'notes' => json_encode($notas, JSON_UNESCAPED_UNICODE),
        ]);
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
