<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Payment;
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

    public function getViewData(): array
    {
        $clinicId = auth()->user()->clinic_id;

        return [
            'caja' => Caja::de($clinicId, $this->fecha()),
            'formas' => Caja::FORMAS,
            'facturas' => Caja::facturasPorMandar($clinicId),
            'esHoy' => $this->fecha()->isToday(),
            'fechaTexto' => $this->fecha()->locale('es')->isoFormat('dddd D [de] MMMM'),
        ];
    }
}
