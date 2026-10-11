<?php

namespace App\Filament\Doctor\Pages;

use App\Models\CorteDeCaja;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Support\CajaDelDia as Caja;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * La caja del día, para cuadrar contra lo que hay en el cajón.
 *
 * Es de recepción: la asistente la ve aunque no tenga permiso del corte del
 * mes, porque es ella la que cobra y cuadra. Aquí también se anota quién pidió
 * factura (la hace el contador; no hay CFDI) y si ya se le mandó.
 */
class CajaDelDia extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Caja del día';

    protected static ?string $title = 'Caja del día';

    protected static ?string $slug = 'caja';

    protected static string $view = 'filament.doctor.pages.caja-del-dia';

    protected static ?string $navigationGroup = 'Dinero';

    protected static ?int $navigationSort = 1;

    public string $dia = '';

    /** Lo que se contó en el cajón al cerrar. */
    public $contado = null;

    public string $notasDelCierre = '';

    public function mount(): void
    {
        $this->dia = today()->toDateString();
    }

    private function fecha(): Carbon
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->dia) ? Carbon::parse($this->dia) : today();
    }

    private function cobro(int $id): ?Payment
    {
        return Payment::where('clinic_id', auth()->user()->clinic_id)->find($id);
    }

    public function pidioFactura(int $id): void
    {
        $this->cobro($id)?->update(['factura_solicitada' => true]);
    }

    public function facturaEnviada(int $id): void
    {
        $cobro = $this->cobro($id);
        if (! $cobro) {
            return;
        }

        $cobro->update(['factura_solicitada' => true, 'factura_enviada_at' => now()]);
        Notification::make()->title('Anotado: factura enviada')->success()->send();
    }

    /**
     * Cerrar la caja: lo que debía haber en efectivo, lo que se contó y la
     * diferencia, con quién cerró. Lo hace quien cobra, aunque no vea el
     * corte del mes (10-oct-2026).
     */
    public function cerrarCaja(): void
    {
        $this->validate([
            'contado' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'notasDelCierre' => ['nullable', 'string', 'max:500'],
        ], [], ['contado' => 'lo que contó', 'notasDelCierre' => 'notas']);

        $esperado = (float) Caja::de(auth()->user()->clinic_id, $this->fecha())['porMetodo']['cash'];
        $contado = round((float) $this->contado, 2);

        CorteDeCaja::create([
            'clinic_id' => auth()->user()->clinic_id,
            'user_id' => auth()->id(),
            'fecha' => $this->fecha()->toDateString(),
            'esperado' => $esperado,
            'contado' => $contado,
            'diferencia' => round($contado - $esperado, 2),
            'notas' => trim($this->notasDelCierre) ?: null,
        ]);

        $this->reset(['contado', 'notasDelCierre']);
        Notification::make()->title('Caja cerrada')->success()->send();
    }

    public function getViewData(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $cierre = CorteDeCaja::with('user')->where('clinic_id', $clinicId)
            ->whereDate('fecha', $this->fecha()->toDateString())->latest('id')->first();

        return [
            'cierre' => $cierre,
            // Un cobro que entró después de cerrar ya no está en ese conteo.
            'entroDespues' => $cierre && PaymentReceipt::withoutGlobalScopes()->where('clinic_id', $clinicId)->queEntraron()
                ->whereBetween('paid_at', [$this->fecha()->format('Y-m-d') . ' 00:00:00', $this->fecha()->format('Y-m-d') . ' 23:59:59'])
                ->where('created_at', '>', $cierre->created_at)->exists(),
            'caja' => Caja::de($clinicId, $this->fecha()),
            'formas' => Caja::FORMAS,
            'facturas' => Caja::facturasPorMandar($clinicId),
            'esHoy' => $this->fecha()->isToday(),
            'fechaTexto' => $this->fecha()->locale('es')->isoFormat('dddd D [de] MMMM'),
        ];
    }
}
