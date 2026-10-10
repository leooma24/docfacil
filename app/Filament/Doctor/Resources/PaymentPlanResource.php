<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\PaymentPlanResource\Pages;
use App\Filament\Doctor\Resources\PaymentPlanResource\RelationManagers;
use App\Models\Patient;
use App\Models\PaymentPlan;
use App\Models\Service;
use App\Models\TreatmentPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

/**
 * Planes de pago: la ortodoncia (y cualquier tratamiento largo) con
 * enganche y mensualidades. Cada parte es un cobro, así que se abona, se
 * recuerda y entra al corte como cualquier otro.
 */
class PaymentPlanResource extends Resource
{
    protected static ?string $model = PaymentPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Dinero';

    protected static ?string $navigationLabel = 'Planes de pago';

    protected static ?string $modelLabel = 'Plan de pagos';

    protected static ?string $pluralModelLabel = 'Planes de pago';

    protected static ?string $slug = 'planes-de-pago';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    /** Planes con mensualidades vencidas. */
    public static function getNavigationBadge(): ?string
    {
        $clinicId = auth()->user()?->clinic_id;
        $n = $clinicId ? PaymentPlan::where('clinic_id', $clinicId)->where('status', 'active')
            ->whereHas('payments', fn ($q) => $q->withBalance()->whereDate('due_date', '<', today()))->count() : 0;

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pacientes con mensualidades vencidas';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('treatment_plan_id'),
            Forms\Components\Section::make('El tratamiento')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('patient_id')
                        ->label('Paciente')
                        ->options(fn () => Patient::where('clinic_id', auth()->user()->clinic_id)
                            ->orderBy('first_name')->get()->mapWithKeys(fn (Patient $p) => [$p->id => $p->full_name]))
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('description')
                        ->label('Tratamiento')
                        ->default('Ortodoncia')
                        ->required()
                        ->maxLength(150),
                    Forms\Components\Select::make('service_id')
                        ->label('Servicio (opcional)')
                        ->options(fn () => Service::where('clinic_id', auth()->user()->clinic_id)->where('is_active', true)->pluck('name', 'id'))
                        ->searchable(),
                ]),
            Forms\Components\Section::make('Cómo se paga')
                ->columns(['default' => 1, 'md' => 4])
                ->schema([
                    Forms\Components\TextInput::make('total')->label('Total')->numeric()->prefix('$')->required()->minValue(1)->live(onBlur: true),
                    Forms\Components\TextInput::make('down_payment')->label('Enganche')->numeric()->prefix('$')->default(0)->minValue(0)->live(onBlur: true),
                    Forms\Components\TextInput::make('installments_count')->label('Mensualidades')->numeric()->default(20)->required()->minValue(1)->maxValue(120)->live(onBlur: true),
                    Forms\Components\DatePicker::make('first_due_date')->label('Primera mensualidad')
                        ->default(fn () => now()->addMonthNoOverflow()->startOfDay())->required()->native(false)->displayFormat('d/m/Y')->live(),
                    Forms\Components\Toggle::make('enganche_pagado')->label('Paga el enganche hoy')->default(true)->live()->columnSpan(['md' => 2]),
                    Forms\Components\Select::make('payment_method')->label('Forma de pago del enganche')
                        ->options(PaymentResource::FORMAS_DE_PAGO)->default('cash')
                        ->visible(fn (Forms\Get $get) => (bool) $get('enganche_pagado'))->columnSpan(['md' => 2]),
                    Forms\Components\Placeholder::make('como_queda')
                        ->label('Así queda')
                        ->columnSpanFull()
                        ->content(fn (Forms\Get $get) => self::comoQueda($get('total'), $get('down_payment'), $get('installments_count'), $get('first_due_date'))),
                ]),
            Forms\Components\Textarea::make('notes')->label('Notas')->rows(2)->columnSpanFull(),
        ]);
    }

    /** "Enganche de $5,000.00 hoy y 20 mensualidades de $800.00, del 01/11/2026 al 01/06/2028." */
    public static function comoQueda(mixed $total, mixed $enganche, mixed $n, mixed $primera): HtmlString|string
    {
        $total = (float) $total; $enganche = (float) $enganche; $n = (int) $n;
        if ($total <= 0 || $n < 1 || ! $primera) {
            return 'Escriba el total, el enganche y cuántas mensualidades.';
        }
        if ($enganche > $total) {
            return 'El enganche no puede ser mayor al total.';
        }
        $mensualidad = floor(($total - $enganche) / $n * 100) / 100;
        $desde = Carbon::parse($primera);
        $hasta = $desde->copy()->addMonthsNoOverflow($n - 1);

        return new HtmlString('<span style="font-size:1.05rem">'
            . ($enganche > 0 ? 'Enganche de <b>$' . number_format($enganche, 2) . '</b> hoy y ' : '')
            . "<b>{$n} mensualidades de $" . number_format($mensualidad, 2) . '</b>'
            . ', del ' . $desde->format('d/m/Y') . ' al ' . $hasta->format('d/m/Y') . '.</span>');
    }

    /** Lo que trae un presupuesto aceptado para volverlo plan. */
    public static function datosDesdePresupuesto(?int $id): array
    {
        $presupuesto = $id ? TreatmentPlan::where('clinic_id', auth()->user()->clinic_id)->find($id) : null;

        return $presupuesto ? [
            'treatment_plan_id' => $presupuesto->id,
            'patient_id' => $presupuesto->patient_id,
            'description' => $presupuesto->title,
            'total' => (float) $presupuesto->total,
        ] : [];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()
                ->columns(['default' => 2, 'md' => 4])
                ->schema([
                    Infolists\Components\TextEntry::make('patient.full_name')->label('Paciente')->weight('bold'),
                    Infolists\Components\TextEntry::make('description')->label('Tratamiento'),
                    Infolists\Components\TextEntry::make('total')->label('Total')->formatStateUsing(fn ($state) => '$' . number_format((float) $state, 2)),
                    Infolists\Components\TextEntry::make('estado')->label('Estado')->badge()
                        ->state(fn (PaymentPlan $r) => self::estado($r))
                        ->color(fn (PaymentPlan $r) => match (true) {
                            $r->status === 'completed' => 'success',
                            $r->vencidas()->exists() => 'danger',
                            default => 'info',
                        }),
                    Infolists\Components\TextEntry::make('pagado')->label('Pagado')->state(fn (PaymentPlan $r) => $r->pagado())->formatStateUsing(fn ($state) => '$' . number_format((float) $state, 2))->color('success')->weight('bold'),
                    Infolists\Components\TextEntry::make('saldo')->label('Le falta')->state(fn (PaymentPlan $r) => $r->saldo())->formatStateUsing(fn ($state) => '$' . number_format((float) $state, 2))->weight('bold'),
                    Infolists\Components\TextEntry::make('siguiente')->label('Siguiente pago')
                        ->state(fn (PaymentPlan $r) => ($s = $r->siguiente()) ? $s->due_date->format('d/m/Y') . ' · $' . number_format($s->remaining, 2) : '—'),
                    Infolists\Components\TextEntry::make('avance')->label('Mensualidades pagadas')
                        ->state(fn (PaymentPlan $r) => $r->payments()->where('installment_number', '>', 0)->where('status', 'paid')->count() . ' de ' . $r->installments_count),
                ]),
        ]);
    }

    public static function estado(PaymentPlan $plan): string
    {
        if ($plan->status !== 'active') {
            return PaymentPlan::ESTADOS[$plan->status] ?? $plan->status;
        }
        $vencidas = $plan->vencidas()->count();

        return $vencidas ? $vencidas . ($vencidas === 1 ? ' vencida' : ' vencidas') : 'Al corriente';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.first_name')->label('Paciente')
                    // El nombre lleva al perfil del paciente: de cualquier lista se llega a todo lo suyo.
                    ->url(fn ($record) => \App\Filament\Doctor\Pages\PatientProfile::getUrl(['patient' => $record->patient_id], panel: 'doctor'))
                    ->color('primary')
                    ->formatStateUsing(fn (PaymentPlan $r) => $r->patient?->full_name)->searchable(['first_name', 'last_name']),
                // En pantallas medianas no cabe todo; lo que manda es quién,
                // cuánto lleva, qué sigue y si debe.
                Tables\Columns\TextColumn::make('description')->label('Tratamiento')->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('total')->label('Total')->visibleFrom('lg')->formatStateUsing(fn ($state) => '$' . number_format((float) $state, 2)),
                Tables\Columns\TextColumn::make('pagado')->label('Pagado')->state(fn (PaymentPlan $r) => $r->pagado())->formatStateUsing(fn ($state) => '$' . number_format((float) $state, 2)),
                Tables\Columns\TextColumn::make('siguiente')->label('Siguiente')
                    ->state(fn (PaymentPlan $r) => ($s = $r->siguiente()) ? $s->due_date->format('d/m/Y') . ' · $' . number_format($s->remaining, 2) : '—'),
                Tables\Columns\TextColumn::make('estado')->label('Estado')->badge()
                    ->state(fn (PaymentPlan $r) => self::estado($r))
                    ->color(fn (PaymentPlan $r) => match (true) {
                        $r->status === 'completed' => 'success',
                        $r->vencidas()->exists() => 'danger',
                        default => 'info',
                    }),
            ])
            ->actions([Tables\Actions\ViewAction::make()->label('Ver')])
            ->defaultSort('created_at', 'desc');
    }

    public static function getBreadcrumb(): string
    {
        return 'Planes de pago';
    }

    public static function getRelations(): array
    {
        return [RelationManagers\MensualidadesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentPlans::route('/'),
            'create' => Pages\CreatePaymentPlan::route('/create'),
            'view' => Pages\ViewPaymentPlan::route('/{record}'),
        ];
    }
}
