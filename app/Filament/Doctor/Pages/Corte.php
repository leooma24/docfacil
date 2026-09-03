<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

/**
 * El corte: cuánto entró, cuánto salió, cuánto quedó.
 *
 * A propósito NO es un balance general. Un balance general lleva activos,
 * pasivos y capital, lo arma el contador y sirve para el SAT — y aquí no
 * tenemos con qué armarlo sin inventar números. Esto es lo otro: el estado
 * de resultados del consultorio, que es lo que el doctor sí se pregunta cada
 * mes y lo que hoy lleva a mano en una hoja de cálculo.
 */
class Corte extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationLabel = 'Corte';

    protected static ?string $title = 'Corte del mes';

    protected static ?string $slug = 'corte';

    protected static ?string $navigationGroup = 'Consultorio';

    protected static ?int $navigationSort = 46;

    protected static string $view = 'filament.doctor.pages.corte';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->clinic?->hasFeature('expenses');
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->clinic?->hasFeature('expenses'), 403);

        $this->form->fill([
            'periodo' => 'este_mes',
            'desde' => now()->startOfMonth()->toDateString(),
            'hasta' => now()->endOfMonth()->toDateString(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Select::make('periodo')
                    ->label('Periodo')
                    ->options([
                        'este_mes' => 'Este mes',
                        'mes_pasado' => 'Mes pasado',
                        'este_ano' => 'Este año',
                        'personalizado' => 'Elegir fechas',
                    ])
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        [$desde, $hasta] = self::rangoDe($state);

                        if ($desde) {
                            $set('desde', $desde->toDateString());
                            $set('hasta', $hasta->toDateString());
                        }
                    }),

                DatePicker::make('desde')
                    ->label('Desde')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->visible(fn (callable $get) => $get('periodo') === 'personalizado'),

                DatePicker::make('hasta')
                    ->label('Hasta')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->visible(fn (callable $get) => $get('periodo') === 'personalizado'),
            ])
            ->columns(3);
    }

    /** @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable} */
    private static function rangoDe(string $periodo): array
    {
        $hoy = CarbonImmutable::today();

        return match ($periodo) {
            'este_mes' => [$hoy->startOfMonth(), $hoy->endOfMonth()],
            'mes_pasado' => [
                $hoy->subMonthNoOverflow()->startOfMonth(),
                $hoy->subMonthNoOverflow()->endOfMonth(),
            ],
            'este_ano' => [$hoy->startOfYear(), $hoy->endOfYear()],
            default => [null, null],
        };
    }

    /**
     * Los números del corte.
     *
     * El "por cobrar" va aparte a propósito: es dinero que todavía no entra,
     * y meterlo en el ingreso le pintaría al doctor un mes que no tuvo.
     */
    public function getNumeros(): array
    {
        $clinicId = auth()->user()->clinic_id;

        $desde = CarbonImmutable::parse($this->data['desde'] ?? now()->startOfMonth());
        $hasta = CarbonImmutable::parse($this->data['hasta'] ?? now()->endOfMonth());

        // El periodo anterior, del mismo largo, para comparar contra algo.
        $dias = $desde->diffInDays($hasta) + 1;
        $antesHasta = $desde->subDay();
        $antesDesde = $antesHasta->subDays($dias - 1);

        $ingresos = Payment::cobradoEntre($clinicId, $desde, $hasta);
        $gastos = Expense::totalEntre($clinicId, $desde, $hasta);
        $utilidad = $ingresos - $gastos;

        $ingresosAntes = Payment::cobradoEntre($clinicId, $antesDesde, $antesHasta);
        $gastosAntes = Expense::totalEntre($clinicId, $antesDesde, $antesHasta);

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'ingresos' => $ingresos,
            'gastos' => $gastos,
            'utilidad' => $utilidad,
            // Margen: de cada $100 que entraron, cuánto se quedó el doctor.
            'margen' => $ingresos > 0 ? ($utilidad / $ingresos) * 100 : null,
            'por_cobrar' => Payment::porCobrarEntre($clinicId, $desde, $hasta),
            'categorias' => Expense::porCategoria($clinicId, $desde, $hasta),
            'ingresos_antes' => $ingresosAntes,
            'gastos_antes' => $gastosAntes,
            'utilidad_antes' => $ingresosAntes - $gastosAntes,
            'cambio_ingresos' => self::cambio($ingresos, $ingresosAntes),
            'cambio_gastos' => self::cambio($gastos, $gastosAntes),
            'cambio_utilidad' => self::cambio($utilidad, $ingresosAntes - $gastosAntes),
            'hay_datos' => $ingresos > 0 || $gastos > 0,
        ];
    }

    /** Cuánto cambió contra el periodo anterior, en porcentaje. */
    private static function cambio(float $ahora, float $antes): ?float
    {
        // Sin base contra qué comparar, el porcentaje no dice nada: un mes
        // que arranca de cero siempre saldría "+100%".
        if (abs($antes) < 0.01) {
            return null;
        }

        return (($ahora - $antes) / abs($antes)) * 100;
    }
}
