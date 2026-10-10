<?php

namespace App\Filament\Doctor\Widgets;

use App\Filament\Doctor\Actions\CobrarAbono;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

/**
 * "Le deben": una tarjeta por paciente con cuánto debe, desde cuándo y si ya
 * venció, con Cobrar (pregunta cuánto trae) y Recordarle por WhatsApp.
 *
 * Reemplaza a "Cobros pendientes" y "Adeudos vencidos", dos cuadros a media
 * pantalla que se veían mochos (Omar, 12-oct-2026); el "Cobrar" del primero
 * daba todo por pagado con un clic.
 */
class LeDebenWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static string $view = 'filament.doctor.widgets.le-deben';

    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    /** Cuántas tarjetas se enseñan; el resto está en Cobros. */
    private const CUANTOS = 6;

    public static function canView(): bool
    {
        return self::adeudos()->exists();
    }

    /** Lo que ya se debe en este consultorio (sin mensualidades que no vencen). */
    private static function adeudos()
    {
        return Payment::where('clinic_id', auth()->user()->clinic_id)->withBalance()->yaToca();
    }

    /** Cobrar lo de un paciente: el pago se reparte del adeudo más viejo al más nuevo. */
    public function cobrarAction(): Action
    {
        return CobrarAbono::make('cobrar', fn (array $arguments) => self::adeudos()
            ->where('patient_id', $arguments['patient'] ?? null)
            ->orderBy('payment_date')
            ->get());
    }

    public function getViewData(): array
    {
        $porPaciente = self::adeudos()
            ->with(['patient.responsable', 'service'])
            ->get()
            ->filter(fn (Payment $p) => $p->patient)
            ->groupBy('patient_id')
            ->map(fn (Collection $cobros) => $this->tarjeta($cobros));

        $tarjetas = $porPaciente
            ->sortBy([['diasVencido', 'desc'], ['debe', 'desc']])
            ->values();

        return [
            'tarjetas' => $tarjetas->take(self::CUANTOS),
            'mas' => max(0, $tarjetas->count() - self::CUANTOS),
            'total' => $tarjetas->sum('debe'),
            'vencido' => $tarjetas->sum('vencido'),
        ];
    }

    private function tarjeta(Collection $cobros): array
    {
        $paciente = $cobros->first()->patient;
        $debe = round($cobros->sum(fn (Payment $p) => $p->remaining), 2);
        $vencidos = $cobros->filter(fn (Payment $p) => $p->due_date && $p->due_date->lt(today()));
        $masViejo = $vencidos->min('due_date');

        return [
            'patient_id' => $paciente->id,
            'nombre' => trim($paciente->first_name . ' ' . $paciente->last_name),
            'debe' => $debe,
            'vencido' => round($vencidos->sum(fn (Payment $p) => $p->remaining), 2),
            'diasVencido' => $masViejo ? (int) $masViejo->diffInDays(today()) : 0,
            'desde' => $cobros->min('payment_date'),
            'cuantos' => $cobros->count(),
            'que' => $cobros->count() === 1 ? $cobros->first()->service?->name : null,
            'whatsapp' => $this->recordatorio($paciente, $debe),
        ];
    }

    /** WhatsApp a quien paga (la mamá si es un niño), con el mensaje escrito. */
    private function recordatorio(\App\Models\Patient $paciente, float $debe): ?string
    {
        if (! auth()->user()->clinic?->hasFeature('whatsapp_payment')) {
            return null;
        }

        $telefono = preg_replace('/\D/', '', (string) $paciente->telefonoDeContacto());
        if (strlen($telefono) === 10) {
            $telefono = '52' . $telefono;
        }
        if (strlen($telefono) < 12) {
            return null;
        }

        $consultorio = auth()->user()->clinic->name ?? 'su dentista';
        $de = $paciente->responsable ? " del tratamiento de {$paciente->first_name}" : '';
        $mensaje = "Hola {$paciente->nombreDeContacto()}, le escribo de *{$consultorio}*. Le recuerdo que queda un saldo{$de} de *\$"
            . number_format($debe, 2) . "*.\n\nSi ya lo pagó, avíseme y lo descuento. Si no, cuando le acomode lo vemos. ¡Gracias!";

        return "https://wa.me/{$telefono}?text=" . urlencode($mensaje);
    }
}
