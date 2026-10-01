<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\TreatmentPlanResource\Pages;
use App\Models\Patient;
use App\Models\Service;
use App\Models\TreatmentPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\URL;
use App\Filament\Doctor\Concerns\SearchesPatientName;

class TreatmentPlanResource extends Resource
{
    use SearchesPatientName;

    protected static ?string $slug = 'presupuestos';

    protected static ?string $model = TreatmentPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Dinero';

    protected static ?string $navigationLabel = 'Presupuestos';

    protected static ?string $modelLabel = 'Presupuesto';

    protected static ?string $pluralModelLabel = 'Presupuestos';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->clinic?->hasFeature('treatment_plans');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información general')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('patient_id')
                        ->label('Paciente')
                        ->relationship('patient')
                        ->getOptionLabelFromRecordUsing(fn (Patient $record) => "{$record->first_name} {$record->last_name}")
                        ->searchable(['first_name', 'last_name'])
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('doctor_id')
                        ->label('Doctor a cargo')
                        ->options(fn () => \App\Models\Doctor::where('clinic_id', auth()->user()->clinic_id)
                            ->with('user')->get()
                            ->mapWithKeys(fn ($d) => [$d->id => $d->user?->name ?? 'Doctor ' . $d->id]))
                        ->default(fn () => auth()->user()->doctor?->id)
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('title')
                        ->label('Título del presupuesto')
                        ->placeholder('Ej: Tratamiento de ortodoncia 18 meses')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->label('Descripción / introducción')
                        ->placeholder('Qué incluye el plan, objetivo clínico, consideraciones previas...')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\DatePicker::make('valid_until')
                        ->label('Válido hasta')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now()->addDays(30)),
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options([
                            'draft' => 'Borrador',
                            'sent' => 'Enviado',
                            'accepted' => 'Aceptado',
                            'rejected' => 'Rechazado',
                            'completed' => 'Completado',
                            'cancelled' => 'Cancelado',
                        ])
                        ->default('draft')
                        ->required(),
                ]),

            Forms\Components\Section::make('Servicios y costos')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->label('Tratamientos')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('service_id')
                                ->label('Servicio')
                                ->columnSpan(['md' => 5])
                                ->options(fn () => Service::where('clinic_id', auth()->user()->clinic_id)
                                    ->where('is_active', true)
                                    ->pluck('name', 'id'))
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $service = Service::find($state);
                                        if ($service) {
                                            $set('description', $service->name);
                                            $set('unit_price', $service->price);
                                        }
                                    }
                                }),
                            Forms\Components\TextInput::make('tooth_number')
                                ->label('Diente')
                                ->placeholder('16')
                                ->columnSpan(['md' => 2])
                                ->maxLength(10),
                            Forms\Components\TextInput::make('quantity')
                                ->label('Cant.')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->minValue(1)
                                ->columnSpan(['md' => 2])
                                ->live(onBlur: true),
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Precio')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->minValue(0)
                                ->columnSpan(['md' => 3])
                                ->live(onBlur: true),
                            Forms\Components\TextInput::make('description')
                                ->label('Descripción')
                                ->columnSpan(['md' => 12])
                                ->required()
                                ->maxLength(255),
                        ])
                        // Servicio, diente, cantidad y precio en un renglón y la
                        // descripción debajo: antes cada tratamiento ocupaba media
                        // pantalla con cinco campos encimados.
                        ->columns(['default' => 1, 'md' => 12])
                        ->defaultItems(1)
                        ->orderColumn('sort_order')
                        ->reorderable()
                        ->addActionLabel('Agregar servicio'),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('discount')
                            ->label('Descuento (monto)')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->live(onBlur: true),
                        // En vivo, con lo que el doctor va escribiendo; antes era el
                        // total guardado y quedaba viejo al cambiar un precio.
                        Forms\Components\Placeholder::make('total_preview')
                            ->label('Total')
                            ->content(fn (Forms\Get $get) => new \Illuminate\Support\HtmlString(
                                '<span style="font-size:1.6rem;font-weight:800;color:#0f766e;">$'
                                . number_format(self::totalEstimado($get('items') ?? [], $get('discount')), 2) . '</span>'
                            )),
                    ]),
                ]),

            Forms\Components\Section::make('Notas internas')
                ->collapsed()
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Notas solo para ti (no aparecen en el PDF)')
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient?->first_name} {$record->patient?->last_name}")
                    ->searchable(query: self::buscarPorNombreDePaciente()),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('MXN', locale: 'es_MX')
                    ->weight('bold'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Borrador',
                        'sent' => 'Enviado',
                        'accepted' => 'Aceptado',
                        'rejected' => 'Rechazado',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'sent',
                        'success' => fn ($state) => in_array($state, ['accepted', 'completed']),
                        'danger' => fn ($state) => in_array($state, ['rejected', 'cancelled']),
                    ]),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Enviado')
                    ->since()
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'sent' => 'Enviado',
                        'accepted' => 'Aceptado',
                        'rejected' => 'Rechazado',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (TreatmentPlan $record) => route('treatment-plan.pdf', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('plan_de_pagos')
                    ->label('Hacer plan de pagos')
                    ->icon('heroicon-o-calendar-days')
                    ->color('info')
                    ->visible(fn (TreatmentPlan $record) => $record->status === 'accepted')
                    ->url(fn (TreatmentPlan $record) => PaymentPlanResource::getUrl('create', ['presupuesto' => $record->id], panel: 'doctor')),
                Tables\Actions\Action::make('send_whatsapp')
                    ->label('Enviar por WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->visible(fn (TreatmentPlan $record) => !empty($record->patient?->phone) && in_array($record->status, ['draft', 'sent']))
                    ->requiresConfirmation()
                    ->modalHeading('Enviar presupuesto por WhatsApp')
                    ->modalDescription('Se generará un link único para que el paciente acepte en línea y se abrirá WhatsApp con el mensaje listo.')
                    ->action(fn (TreatmentPlan $record) => self::enviarPorWhatsapp($record)),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTreatmentPlans::route('/'),
            'create' => Pages\CreateTreatmentPlan::route('/create'),
            'edit' => Pages\EditTreatmentPlan::route('/{record}/edit'),
        ];
    }

    /**
     * Marca el presupuesto como enviado, le da su liga pública y abre
     * WhatsApp con el mensaje listo. Lo usan la lista y la pantalla del
     * presupuesto.
     */
    /** Suma de cantidad × precio de las líneas, menos el descuento. Nunca negativo. */
    public static function totalEstimado(array $items, mixed $descuento): float
    {
        $subtotal = collect($items)->sum(fn ($i) => (float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0));

        return round(max(0, $subtotal - (float) $descuento), 2);
    }

    public static function enviarPorWhatsapp(TreatmentPlan $record)
    {
        if (empty($record->public_token)) {
            $record->generatePublicToken();
        }
        $record->update([
            'status' => $record->status === 'draft' ? 'sent' : $record->status,
            'sent_at' => $record->sent_at ?? now(),
        ]);

        $phone = preg_replace('/\D/', '', $record->patient->phone);
        if (strlen($phone) === 10) $phone = '52' . $phone;

        $acceptUrl = URL::signedRoute('treatment-plan.accept', ['token' => $record->public_token]);
        $pdfUrl = route('treatment-plan.public', ['token' => $record->public_token]);

        $clinicName = $record->clinic->name ?? 'tu consultorio';
        $firstName = $record->patient->first_name ?: 'hola';
        $total = number_format((float) $record->total, 2);

        $msg = "Hola {$firstName}, te comparto el plan de tratamiento que armamos en *{$clinicName}*:\n\n"
            . "*{$record->title}*\n"
            . "Total: *\${$total} MXN*\n\n"
            . "Ver el presupuesto: {$pdfUrl}\n\n"
            . "Si te parece bien, puedes aceptarlo aquí: {$acceptUrl}\n\n"
            . "Cualquier duda me la platicas por aquí.";

        Notification::make()
            ->title('Presupuesto listo para enviar')
            ->body('Se abrirá WhatsApp con el mensaje pre-armado.')
            ->success()
            ->send();

        return redirect()->away("https://wa.me/{$phone}?text=" . urlencode($msg));
    }
}
