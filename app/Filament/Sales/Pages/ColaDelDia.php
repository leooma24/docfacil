<?php

namespace App\Filament\Sales\Pages;

use App\Filament\Sales\Resources\ProspectResource;
use App\Models\Prospect;
use App\Models\TipDeVenta;
use App\Support\CargaDeTrabajo;
use App\Support\SiguientePaso;
use Filament\Notifications\Notification;
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

    /** El prospecto cuyo cuadro de reporte está abierto, si hay alguno. */
    public ?int $reportando = null;

    public string $reporte = '';

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

        $this->avisarSiSeCumplioLaMeta();
    }

    /**
     * El aviso al llegar al numero del dia.
     *
     * Suena a adorno y no lo es: el tope existe para que el numero de quien
     * vende no termine bloqueado, y un limite que solo se siente como freno se
     * salta. Como meta, se respeta. Sale una sola vez, justo al llegar.
     */
    private function avisarSiSeCumplioLaMeta(): void
    {
        $enviadosHoy = CargaDeTrabajo::numeros((int) auth()->id())['enviadosHoy'];

        if ($enviadosHoy !== CargaDeTrabajo::TOPE_DIARIO) {
            return;
        }

        Notification::make()
            ->title('Meta del dia cumplida')
            ->body(CargaDeTrabajo::TOPE_DIARIO . ' mensajes. De aqui en adelante mejor manana: el ritmo es lo que mantiene tu numero sano.')
            ->success()
            ->persistent()
            ->send();
    }

    /**
     * El chat con el mensaje ya cargado, también al verificar.
     *
     * Decisión de Omar: prefiere ver el mensaje que le toca a cada quien antes
     * de mandarlo, tanto para revisar cómo quedó el texto como para decidir él
     * si lo manda o no. WhatsApp no envía nada solo: el mensaje se queda en el
     * cuadro hasta que alguien le da enviar, y si el número no existe lo dice
     * antes de abrir el chat.
     */
    public function ligaParaVerificar(Prospect $prospecto): string
    {
        return ProspectResource::buildContextualWhatsappUrl($prospecto);
    }

    /** Lo único que toca hacer con este prospecto ahora. */
    public function siguientePaso(Prospect $prospecto): array
    {
        return SiguientePaso::para($prospecto);
    }

    /**
     * La demo se hizo.
     *
     * Este botón no existía: `demo_completed_at` solo se podía llenar desde una
     * sección colapsada del formulario de edición, así que nadie lo llenaba y
     * el embudo mostraba cero demos hechas para siempre. Sin ese dato, la tasa
     * de cierre —la única que falta por conocer— no se puede medir.
     */
    public function marcarDemoHecha(int $id): void
    {
        $prospecto = Prospect::where('assigned_to_sales_rep_id', auth()->id())->find($id);

        if (! $prospecto || $prospecto->demo_completed_at) {
            return;
        }

        $prospecto->update([
            'demo_completed_at' => now(),
            'status' => $prospecto->status === 'converted' ? 'converted' : 'interested',
        ]);

        Notification::make()
            ->title('Demo registrada')
            ->body('Ahora lo que sigue es pedir el cierre, con dos opciones. El botón ya lo trae listo.')
            ->success()
            ->send();
    }

    /**
     * El consejo que toca, pegado a la acción que está por hacer.
     *
     * Aparece arriba del botón y no en una pantalla de consejos: una lista de
     * veinticinco se lee una vez y no se vuelve a abrir. Rota entre los de esa
     * etapa, para que no salga siempre el mismo, y se deja de ver cuando la
     * persona dice que ya le sale solo.
     */
    public function tipDe(string $etapa): ?array
    {
        $tip = TipDeVenta::paraMostrar((int) auth()->id(), $etapa);

        if ($tip) {
            TipDeVenta::anotarQueSeVio((int) auth()->id(), $tip['clave']);
        }

        return $tip;
    }

    /** "Ya me sale solo": deja de aparecer, y vuelve a repaso en 15 días. */
    public function yaMeSaleSolo(string $clave): void
    {
        TipDeVenta::marcarDominado((int) auth()->id(), $clave);

        Notification::make()
            ->title('Listo, ya no te lo repito')
            ->body('Vuelve una vez en 15 días, nada más para comprobar que sigue saliendo solo.')
            ->success()
            ->send();
    }

    /** Se abre el cuadro para reportar algo de este prospecto. */
    public function abrirReporte(int $id): void
    {
        $this->reportando = $id;
        $this->reporte = '';
    }

    public function cancelarReporte(): void
    {
        $this->reportando = null;
        $this->reporte = '';
    }

    /**
     * Lo que salió mal, anotado en el prospecto.
     *
     * Un mensaje con una frase rara, un nombre mal escrito, una especialidad
     * que no era: eso se ve al abrir el chat y se olvida en dos minutos si no
     * hay dónde escribirlo. Queda en las notas con su fecha, y el prospecto
     * queda marcado para revisar.
     */
    public function guardarReporte(): void
    {
        $prospecto = Prospect::where('assigned_to_sales_rep_id', auth()->id())->find($this->reportando);

        if (! $prospecto || trim($this->reporte) === '') {
            $this->cancelarReporte();

            return;
        }

        $notas = json_decode((string) $prospecto->notes, true);
        $notas = is_array($notas) ? $notas : [];
        $notas['reportes'][] = [
            'que' => trim($this->reporte),
            'cuando' => now()->toDateTimeString(),
        ];
        $notas['revisar'] = true;

        $prospecto->update(['notes' => json_encode($notas, JSON_UNESCAPED_UNICODE)]);

        $this->cancelarReporte();
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

        if (! $existe) {
            // No se cierra: un consultorio con teléfono fijo sigue siendo un
            // consultorio. Lo que no tiene es WhatsApp, así que se guarda para
            // llamarle. Cerrarlo sería tirar un prospecto bueno por el canal.
            $notas['canal'] = 'telefono';
        }

        $prospecto->update([
            'has_whatsapp' => $existe,
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
