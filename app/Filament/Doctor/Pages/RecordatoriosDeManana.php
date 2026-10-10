<?php

namespace App\Filament\Doctor\Pages;

use App\Filament\Doctor\Concerns\GatedByPlanFeature;
use App\Support\RecordatorioDeCita;
use Filament\Pages\Page;

/**
 * Los recordatorios de mañana, en fila.
 *
 * Lo primero que pidió la asistente en la entrevista del 10-oct-2026 fue no
 * mandar los recordatorios uno por uno: son de 20 a 30 minutos cada tarde,
 * buscando cita por cita. Aquí está la fila: un paciente por renglón, en
 * orden de hora, y un solo botón grande para el siguiente.
 *
 * El botón es la ruta que ya existía (cita.recordar): abre WhatsApp con el
 * mensaje escrito y deja marcadas las citas del paciente ese día. Nada sale
 * solo: el doctor o la asistente dan enviar desde su propio WhatsApp.
 */
class RecordatoriosDeManana extends Page
{
    use GatedByPlanFeature;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationLabel = 'Recordatorios de mañana';

    protected static ?string $title = 'Recordatorios de mañana';

    protected static ?string $slug = 'recordatorios';

    protected static string $view = 'filament.doctor.pages.recordatorios-de-manana';

    protected static ?int $navigationSort = 4;

    protected static function planFeature(): string
    {
        return 'whatsapp_reminders';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::clinicHasPlanFeature();
    }

    public static function canAccess(): bool
    {
        return static::clinicHasPlanFeature();
    }

    /** Cuántos faltan, para el globito del menú. */
    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user || ! static::clinicHasPlanFeature()) {
            return null;
        }

        $faltan = RecordatorioDeCita::pendientesDeManana($user->clinic_id)
            ->filter(fn ($c) => self::telefono($c))->count();

        return $faltan > 0 ? (string) $faltan : null;
    }

    private static function telefono($cita): string
    {
        return preg_replace('/\D/', '', (string) $cita->patient?->telefonoDeContacto());
    }

    public function getViewData(): array
    {
        $todas = RecordatorioDeCita::deManana(auth()->user()->clinic_id);
        $conTelefono = $todas->filter(fn ($c) => self::telefono($c) !== '')->values();

        return [
            'manana' => today()->addDay()->locale('es')->isoFormat('dddd D [de] MMMM'),
            'total' => $conTelefono->count(),
            'enviados' => $conTelefono->filter(fn ($c) => $c->recordado)->count(),
            'pendientes' => $conTelefono->reject(fn ($c) => $c->recordado)->values(),
            'recordados' => $conTelefono->filter(fn ($c) => $c->recordado)->values(),
            'sinTelefono' => $todas->filter(fn ($c) => self::telefono($c) === '')->values(),
        ];
    }
}
