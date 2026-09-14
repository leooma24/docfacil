<?php

namespace App\Filament\Doctor\Pages;

use App\Services\NumerosDelCorte;
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

        // El correo del corte mensual trae ?desde=&hasta= para abrir justo en
        // el mes del que habla, y no en el mes que va corriendo.
        [$desde, $hasta] = self::rangoDeLaLiga(request()->query('desde'), request()->query('hasta'));

        if ($desde) {
            $this->form->fill([
                'periodo' => 'personalizado',
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
            ]);

            return;
        }

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
     * Las fechas que vienen en la liga, solo si son fechas de verdad y van en
     * orden. Cualquier otra cosa abre el mes en curso, sin error.
     *
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    private static function rangoDeLaLiga(mixed $desde, mixed $hasta): array
    {
        $fecha = function (mixed $valor): ?CarbonImmutable {
            if (! is_string($valor) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                return null;
            }

            try {
                $f = CarbonImmutable::createFromFormat('!Y-m-d', $valor);
            } catch (\Throwable) {
                return null;
            }

            // createFromFormat acepta 2026-02-31 y lo vuelve 3 de marzo.
            return $f && $f->format('Y-m-d') === $valor ? $f : null;
        };

        $d = $fecha($desde);
        $h = $fecha($hasta);

        if (! $d || ! $h || $d->greaterThan($h)) {
            return [null, null];
        }

        return [$d, $h];
    }

    /**
     * Los números del corte. La cuenta vive en NumerosDelCorte, que es la
     * misma que usa el correo de cada mes.
     */
    public function getNumeros(): array
    {
        $desde = CarbonImmutable::parse($this->data['desde'] ?? now()->startOfMonth());
        $hasta = CarbonImmutable::parse($this->data['hasta'] ?? now()->endOfMonth());

        // El periodo anterior, del mismo largo, para comparar contra algo.
        $dias = $desde->diffInDays($hasta) + 1;
        $antesHasta = $desde->subDay();
        $antesDesde = $antesHasta->subDays($dias - 1);

        return NumerosDelCorte::calcular(auth()->user()->clinic_id, $desde, $hasta, $antesDesde, $antesHasta);
    }
}
