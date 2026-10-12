<?php

namespace App\Filament\Doctor\Resources\PaymentResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    use HasListHero;

    protected static string $resource = PaymentResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuevo Cobro'),
            // El CFDI va en la fase 2 (11-oct-2026): mientras, la lista para
            // su contador con quién pidió factura y sus datos fiscales.
            Actions\Action::make('listaParaSuContador')
                ->label('Lista para su contador')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->visible(fn () => auth()->user()->veElDinero())
                ->modalHeading('Lista para su contador')
                ->modalDescription('Los cobros del mes que pidieron factura, con los datos fiscales de cada paciente, en Excel. Los datos fiscales se ponen en la ficha del paciente.')
                ->modalSubmitActionLabel('Bajar en Excel')
                ->modalWidth('md')
                ->form([
                    \Filament\Forms\Components\Select::make('mes')
                        ->label('Mes')
                        ->options(fn () => collect(range(0, 11))->mapWithKeys(fn ($i) => [
                            now()->startOfMonth()->subMonths($i)->format('Y-m') => ucfirst(now()->startOfMonth()->subMonths($i)->translatedFormat('F Y')),
                        ])->all())
                        ->default(now()->format('Y-m'))
                        ->required(),
                ])
                ->action(fn (array $data) => redirect()->route('cobros.lista-para-su-contador', ['mes' => $data['mes']])),
        ];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;

        // Los mismos cálculos que el escritorio y el corte: aquí contaban
        // solo los liquidados, y un abono de hoy no aparecía en "Cobrado hoy".
        $today = Payment::cobradoEntre($clinicId, today(), today());
        $month = Payment::cobradoEntre($clinicId, now()->startOfMonth(), now()->endOfMonth());
        $pending = Payment::saldoPorCobrar($clinicId);
        $countMonth = Payment::where('clinic_id', $clinicId)
            ->whereBetween('payment_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->where(fn ($q) => $q->where('status', 'paid')->orWhere('amount_paid', '>', 0))
            ->count();

        return [
            'title'    => 'Cobros',
            'subtitle' => 'Todos los cobros, pagados y pendientes. Desde aquí registra pagos y le manda el recordatorio por WhatsApp; usted lo envía desde su teléfono.',
            'gradient' => '#10b981 0%, #059669 40%, #047857 100%',
            'accent'   => '#059669',
            // Lo que entró es del doctor; la asistente lo ve si él se lo dio.
            // Lo que falta cobrar sí lo ve: ella es la que cobra.
            'stats' => auth()->user()->veElDinero() ? [
                ['label' => 'Cobrado hoy',     'value' => '$' . number_format($today)],
                ['label' => 'Este mes',         'value' => '$' . number_format($month)],
                ['label' => 'Pendiente',        'value' => '$' . number_format($pending)],
                ['label' => 'Pagos del mes',    'value' => number_format($countMonth)],
            ] : [
                ['label' => 'Pendiente',        'value' => '$' . number_format($pending)],
            ],
        ];
    }
}
