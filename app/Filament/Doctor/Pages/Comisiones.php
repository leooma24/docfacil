<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Doctor;
use App\Models\Expense;
use App\Models\Liquidacion;
use App\Models\Service;
use App\Support\LiquidacionDelDoctor;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * La comisión de cada doctor (día 8 del plan, 10-oct-2026): lo que le toca
 * de lo ya cobrado en el mes, con semáforo y «Explicar». El dueño la
 * configura, ve la de todos y la marca pagada (queda como gasto de sueldos y
 * honorarios); cada doctor ve solo la suya. Sale en el menú cuando el
 * consultorio tiene más de un doctor.
 */
class Comisiones extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Comisiones';

    protected static ?string $title = 'Comisiones de los doctores';

    protected static ?string $slug = 'comisiones';

    protected static string $view = 'filament.doctor.pages.comisiones';

    protected static ?string $navigationGroup = 'Dinero';

    protected static ?int $navigationSort = 7;

    /** El mes, como 2026-10. */
    public string $mes = '';

    public static function canAccess(): bool
    {
        $u = auth()->user();

        return $u && $u->role === 'doctor' && (bool) $u->clinic?->hasFeature('multi_doctor');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess() && Doctor::where('clinic_id', auth()->user()->clinic_id)->count() > 1;
    }

    public function mount(): void
    {
        $this->mes = now()->format('Y-m');
    }

    private function periodo(): array
    {
        $inicio = preg_match('/^\d{4}-\d{2}$/', $this->mes) ? Carbon::createFromFormat('!Y-m', $this->mes) : now()->startOfMonth();

        return [$inicio->copy()->startOfMonth(), $inicio->copy()->endOfMonth()];
    }

    private function doctor(array $arguments): ?Doctor
    {
        return Doctor::with('user')->where('clinic_id', auth()->user()->clinic_id)->find($arguments['doctor'] ?? null);
    }

    public function configurarAction(): Action
    {
        return Action::make('configurar')
            ->label('Configurar')
            ->icon('heroicon-o-cog-6-tooth')
            ->color('gray')
            ->size('sm')
            ->visible(fn () => auth()->user()->esDuenoDelConsultorio())
            ->modalHeading(fn (array $arguments) => 'Comisión de ' . ($this->doctor($arguments)?->user?->name ?? 'doctor'))
            ->fillForm(function (array $arguments) {
                $doctor = $this->doctor($arguments);

                return [
                    'comision_porcentaje' => $doctor?->comision_porcentaje,
                    'comision_montos' => collect((array) ($doctor?->comision_montos ?? []))
                        ->map(fn ($monto, $servicio) => ['service_id' => $servicio, 'monto' => $monto])->values()->all(),
                ];
            })
            ->form([
                Forms\Components\TextInput::make('comision_porcentaje')
                    ->label('Porcentaje de lo cobrado')
                    ->numeric()->minValue(0)->maxValue(100)->suffix('%')
                    ->helperText('Se le paga sobre lo que el paciente ya pagó.'),
                Forms\Components\Repeater::make('comision_montos')
                    ->label('Montos fijos por tratamiento (opcional)')
                    ->helperText('Para estos tratamientos gana esta cantidad, sin importar el porcentaje.')
                    ->schema([
                        Forms\Components\Select::make('service_id')->label('Tratamiento')
                            ->options(fn () => Service::where('clinic_id', auth()->user()->clinic_id)->orderBy('name')->pluck('name', 'id'))
                            ->required()->searchable(),
                        Forms\Components\TextInput::make('monto')->label('Monto')->numeric()->prefix('$')->minValue(0)->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Agregar tratamiento'),
            ])
            ->action(function (array $data, array $arguments) {
                $doctor = $this->doctor($arguments);
                if (! $doctor || ! auth()->user()->esDuenoDelConsultorio()) {
                    return;
                }
                $doctor->update([
                    'comision_porcentaje' => $data['comision_porcentaje'] !== null && $data['comision_porcentaje'] !== '' ? $data['comision_porcentaje'] : null,
                    'comision_montos' => collect($data['comision_montos'] ?? [])
                        ->filter(fn ($f) => ! empty($f['service_id']))
                        ->mapWithKeys(fn ($f) => [(int) $f['service_id'] => round((float) $f['monto'], 2)])->all(),
                ]);
                Notification::make()->title('Comisión guardada')->success()->send();
            });
    }

    public function pagarAction(): Action
    {
        return Action::make('pagar')
            ->label('Marcar como pagada')
            ->icon('heroicon-o-check')
            ->color('success')
            ->size('sm')
            ->visible(fn () => auth()->user()->esDuenoDelConsultorio())
            ->requiresConfirmation()
            ->modalDescription('Queda como gasto de «Sueldos y honorarios» en el corte del mes.')
            ->action(function (array $arguments) {
                $doctor = $this->doctor($arguments);
                if (! $doctor || ! auth()->user()->esDuenoDelConsultorio()) {
                    return;
                }
                [$desde, $hasta] = $this->periodo();
                if (Liquidacion::where('doctor_id', $doctor->id)->whereDate('desde', $desde)->whereDate('hasta', $hasta)->exists()) {
                    Notification::make()->title('Ese mes ya se le pagó')->warning()->send();

                    return;
                }
                $monto = LiquidacionDelDoctor::de($doctor, $desde, $hasta)['total'];
                if ($monto <= 0) {
                    Notification::make()->title('No hay nada que pagarle ese mes')->warning()->send();

                    return;
                }
                $gasto = Expense::create([
                    'clinic_id' => $doctor->clinic_id,
                    'created_by' => auth()->id(),
                    'category' => 'nomina',
                    'concept' => 'Comisión de ' . ($doctor->user?->name ?? 'doctor') . ' · ' . $desde->locale('es')->isoFormat('MMMM YYYY'),
                    'amount' => $monto,
                    'expense_date' => today(),
                    'payment_method' => 'transfer',
                ]);
                Liquidacion::create([
                    'clinic_id' => $doctor->clinic_id, 'doctor_id' => $doctor->id, 'user_id' => auth()->id(),
                    'expense_id' => $gasto->id, 'desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString(), 'monto' => $monto,
                ]);
                Notification::make()->title('Pagada: $' . number_format($monto, 2))->success()->send();
            });
    }

    public function getViewData(): array
    {
        [$desde, $hasta] = $this->periodo();
        $usuario = auth()->user();
        $doctores = Doctor::with('user')->where('clinic_id', $usuario->clinic_id)
            ->when(! $usuario->esDuenoDelConsultorio(), fn ($q) => $q->where('user_id', $usuario->id))
            ->orderBy('id')->get();

        return [
            'tarjetas' => $doctores->map(fn (Doctor $d) => [
                'doctor' => $d,
                'liq' => LiquidacionDelDoctor::de($d, $desde, $hasta),
                'pagada' => Liquidacion::where('doctor_id', $d->id)->whereDate('desde', $desde)->whereDate('hasta', $hasta)->first(),
            ]),
            'mesTexto' => ucfirst($desde->locale('es')->isoFormat('MMMM YYYY')),
            'esDueno' => $usuario->esDuenoDelConsultorio(),
        ];
    }
}
