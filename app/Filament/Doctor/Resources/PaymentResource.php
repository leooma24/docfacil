<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\PaymentResource\Pages;
use App\Models\Patient;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    public const FORMAS_DE_PAGO = [
        'cash' => 'Efectivo',
        'card' => 'Tarjeta',
        'transfer' => 'Transferencia',
        'other' => 'Otro',
    ];

    protected static ?string $slug = 'cobros';

    public static function getEloquentQuery(): Builder
    {
        // Las mensualidades futuras de un plan viven en el plan; aquí aparecen
        // cuando ya tocan.
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id)->yaToca();
    }

    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Dinero';

    protected static ?string $navigationLabel = 'Cobros';

    protected static ?string $modelLabel = 'Cobro';

    protected static ?string $pluralModelLabel = 'Cobros';

    protected static ?int $navigationSort = 1;

    /** Cobros vencidos: lo que ya debió entrar y no ha entrado. */
    public static function getNavigationBadge(): ?string
    {
        $clinicId = auth()->user()?->clinic_id;
        $n = $clinicId ? Payment::where('clinic_id', $clinicId)->overdue()->count() : 0;

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Cobros vencidos';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Cobro')
                    ->columns(2)
                    ->schema([
                        // Las mensualidades ya existen en Planes de pago, pero el
                        // ortodoncista de la prueba del 12-oct las buscó aquí.
                        Forms\Components\Placeholder::make('a_meses')
                            ->hiddenLabel()
                            ->content(fn () => new \Illuminate\Support\HtmlString(
                                '<div style="font-size:.85rem;padding:8px 12px;border-radius:8px;background:#f0fdfa;color:#115e59;">'
                                . '¿Es un tratamiento a meses, como ortodoncia? Hágalo en '
                                . '<a href="' . e(PaymentPlanResource::getUrl('create', panel: 'doctor')) . '" style="font-weight:700;text-decoration:underline;">Planes de pago</a>: '
                                . 'pone el enganche y las mensualidades, y cada una queda como su cobro.</div>'
                            ))
                            ->visible(fn (string $operation) => $operation === 'create')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('patient_id')
                            ->label('Paciente')
                            ->relationship('patient')
                            ->getOptionLabelFromRecordUsing(fn (Patient $record) => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('service_id')
                            ->label('Servicio')
                            ->relationship('service', 'name')
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $service = \App\Models\Service::find($state);
                                    if ($service) {
                                        $set('amount', $service->price);
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('amount')
                            ->label('Total')
                            ->helperText('Lo que cuesta el tratamiento completo')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0.01)
                            ->reactive()
                            ->required(),
                        // "¿Cómo quedó?" en vez de "Estado" + "Pagado hasta
                        // ahora": 6 de los 20 doctores de la prueba del
                        // 12-oct no sabían qué poner en cada uno.
                        Forms\Components\Radio::make('status')
                            ->label('¿Cómo quedó?')
                            ->options(fn (?Payment $record) => array_filter([
                                'paid' => 'Pagó todo',
                                'partial' => 'Dejó un abono',
                                'pending' => 'No ha pagado',
                                // Solo al editar: un cobro nuevo no nace reembolsado.
                                'refunded' => $record ? 'Se le regresó el dinero' : null,
                            ]))
                            ->default('paid')
                            ->inline()
                            ->live()
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('amount_paid')
                            ->label('¿Cuánto dejó?')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0.01)
                            ->required(fn (Forms\Get $get) => $get('status') === 'partial')
                            ->visible(fn (Forms\Get $get) => $get('status') === 'partial'),
                        Forms\Components\Select::make('payment_method')
                            ->label('¿Cómo pagó?')
                            ->options(self::FORMAS_DE_PAGO)
                            ->default('cash')
                            ->required(fn (Forms\Get $get) => in_array($get('status'), ['paid', 'partial']))
                            ->visible(fn (Forms\Get $get) => $get('status') !== 'pending'),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label(fn (Forms\Get $get) => $get('status') === 'pending' ? 'Fecha del tratamiento' : 'Fecha en que pagó')
                            ->helperText(fn (Forms\Get $get) => $get('status') === 'pending' ? 'Desde ese día cuenta lo que le debe.' : null)
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('¿Para cuándo queda de pagar?')
                            ->helperText('Opcional. Si pasa esa fecha, sale como vencido.')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['pending', 'partial'])),
                        // Solo las citas del paciente elegido: antes salían las de
                        // todos y era fácil ligar el cobro a la cita de otro.
                        Forms\Components\Select::make('appointment_id')
                            ->label('Cita asociada')
                            ->relationship('appointment', modifyQueryUsing: fn ($query, Forms\Get $get) => $query->where('patient_id', $get('patient_id') ?: 0)->latest('starts_at'))
                            ->disabled(fn (Forms\Get $get) => blank($get('patient_id')))
                            ->helperText(fn (Forms\Get $get) => blank($get('patient_id')) ? 'Primero elija al paciente.' : null)
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->starts_at->format('d/m/Y H:i') . ' - ' . $record->patient->full_name)
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Lo que dice "¿Cómo quedó?" se vuelve los números del cobro: pagó todo
     * es pagado = total; no ha pagado es cero; un abono por todo es pagado.
     */
    public static function cuadrarLoQueQuedo(array $data): array
    {
        $total = (float) ($data['amount'] ?? 0);

        // Se le regresó el dinero: lo pagado vuelve a cero y la caja de hoy
        // lo registra como devolución. Antes el estado cambiaba y el dinero
        // seguía contando como entrado (auditoría del 12-oct-2026).
        if (($data['status'] ?? null) === 'refunded') {
            $data['amount_paid'] = 0;
        } elseif (($data['status'] ?? null) === 'pending') {
            $data['amount_paid'] = 0;
        } elseif (($data['status'] ?? null) === 'paid') {
            $data['amount_paid'] = $total;
        } elseif (($data['status'] ?? null) === 'partial' && (float) ($data['amount_paid'] ?? 0) >= $total) {
            $data['amount_paid'] = $total;
            $data['status'] = 'paid';
        }

        if (empty($data['payment_method'])) {
            $data['payment_method'] = 'cash';
        }

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->visibleFrom('md')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.first_name')
                    // El nombre lleva al perfil del paciente: de cualquier lista se llega a todo lo suyo.
                    ->url(fn ($record) => \App\Filament\Doctor\Pages\PatientProfile::getUrl(['patient' => $record->patient_id], panel: 'doctor'))
                    ->color('primary')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient->first_name} {$record->patient->last_name}")
                    ->description(fn ($record) => $record->patient?->phone ?: null)
                    ->searchable(['patient.first_name', 'patient.last_name', 'patient.phone']),
                Tables\Columns\TextColumn::make('service.name')
                    ->visibleFrom('2xl')
                    ->label('Servicio')
                    ->placeholder('Sin servicio'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('MXN', locale: 'es_MX')
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining')
                    ->label('Restante')
                    ->money('MXN', locale: 'es_MX')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success')
                    ->placeholder('—')
                    // Cuando esta pagado, mostrar guion en lugar de "$0.00"
                    ->formatStateUsing(function ($state, $record) {
                        if (in_array($record->status, ['paid', 'refunded']) || $state <= 0) {
                            return '—';
                        }
                        return '$' . number_format((float) $state, 2) . ' MXN';
                    }),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Límite')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->color(fn ($record) => $record?->is_overdue ? 'danger' : 'gray')
                    // Ocultada por default — la mayoria de dentistas no usa
                    // fechas limite de pago. Se activa con toggle si la necesitan.
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('payment_method')
                    ->visibleFrom('2xl')
                    ->label('Método')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'cash' => 'Efectivo',
                        'card' => 'Tarjeta',
                        'transfer' => 'Transferencia',
                        'other' => 'Otro',
                    })
                    ->colors([
                        'success' => 'cash',
                        'primary' => 'card',
                        'info' => 'transfer',
                        'warning' => 'other',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'paid' => 'Pagado',
                        'pending' => 'Pendiente',
                        'partial' => 'Parcial',
                        'refunded' => 'Reembolsado',
                    })
                    ->colors([
                        'success' => 'paid',
                        'warning' => fn ($state) => in_array($state, ['pending', 'partial']),
                        'danger' => 'refunded',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'paid' => 'Pagado',
                        'pending' => 'Pendiente',
                        'partial' => 'Parcial',
                        'refunded' => 'Reembolsado',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Método')
                    ->options([
                        'cash' => 'Efectivo',
                        'card' => 'Tarjeta',
                        'transfer' => 'Transferencia',
                    ]),
                Tables\Filters\Filter::make('today')
                    ->label('Hoy')
                    ->query(fn ($query) => $query->whereDate('payment_date', today())),
                Tables\Filters\Filter::make('overdue')
                    ->label('Vencidos')
                    ->query(fn ($query) => $query->overdue())
                    ->toggle(),
                Tables\Filters\Filter::make('with_balance')
                    ->label('Con saldo')
                    ->query(fn ($query) => $query->withBalance())
                    ->toggle(),
                // Cobros que llevan >30 dias pendientes/parciales -> persigue cobranza vieja
                Tables\Filters\Filter::make('over_30_days')
                    ->label('Sin pago hace +30 días')
                    ->query(fn ($query) => $query
                        ->whereIn('status', ['pending', 'partial'])
                        ->where('created_at', '<=', now()->subDays(30)))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('recibo')
                    ->label('Recibo (PDF)')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(fn (Payment $record) => route('cobro.recibo', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('pay_installment')
                    ->label('Pagar abono')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Payment $record) => in_array($record->status, ['pending', 'partial']) && $record->remaining > 0)
                    ->form(fn (Payment $record) => [
                        Forms\Components\Placeholder::make('info')
                            ->label('Saldo actual')
                            ->content(fn () => '$' . number_format($record->remaining, 2) . ' MXN'),
                        Forms\Components\TextInput::make('installment')
                            ->label('Monto del abono')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->minValue(0.01)
                            ->maxValue((float) $record->remaining),
                        Forms\Components\Select::make('payment_method')
                            ->label('Forma de pago')
                            ->options(self::FORMAS_DE_PAGO)
                            ->default($record->payment_method ?: 'cash')
                            ->required(),
                    ])
                    ->action(function (Payment $record, array $data) {
                        $record->registrarAbono((float) $data['installment'], $data['payment_method'] ?? null);

                        \Filament\Notifications\Notification::make()
                            ->title('Abono registrado')
                            ->body('Saldo restante: $' . number_format($record->fresh()->remaining, 2))
                            ->success()
                            ->send();
                    }),
                // Recordatorio de cobro por WhatsApp a 1 clic.
                // Promesa desde plan Básico; Free ve la accion con tooltip de upgrade.
                Tables\Actions\Action::make('whatsapp_reminder')
                    ->label('Recordar por WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color(fn (Payment $record) => auth()->user()?->clinic?->hasFeature('whatsapp_payment')
                        && !empty($record->patient?->telefonoDeContacto())
                        && in_array($record->status, ['pending', 'partial'])
                            ? 'success'
                            : 'gray')
                    ->visible(fn (Payment $record) => in_array($record->status, ['pending', 'partial']) && !empty($record->patient?->telefonoDeContacto()))
                    ->tooltip(fn () => auth()->user()?->clinic?->hasFeature('whatsapp_payment')
                        ? 'Abra WhatsApp con el mensaje de recordatorio listo'
                        : 'Disponible desde el plan Básico — actualice su plan para enviar cobros por WhatsApp')
                    ->url(function (Payment $record) {
                        if (!auth()->user()?->clinic?->hasFeature('whatsapp_payment')) {
                            return null;
                        }
                        $phone = preg_replace('/\D/', '', (string) $record->patient->telefonoDeContacto());
                        if (strlen($phone) === 10) $phone = '52' . $phone;
                        if (strlen($phone) < 12) return null;

                        $clinic = $record->clinic;
                        $clinicName = $clinic->name ?? 'DocFácil';
                        // A la mamá se le saluda a ella y se dice de quién es el tratamiento.
                        $firstName = $record->patient->nombreDeContacto() ?: 'hola';
                        $servicePart = ($record->service?->name ? " por *{$record->service->name}*" : '')
                            . ($record->patient->responsable ? ' de ' . $record->patient->first_name : '');

                        // Status: partial (con abonos) vs pending (sin abonos)
                        $remaining = number_format((float) $record->remaining, 2);
                        if ($record->status === 'partial') {
                            $total = number_format((float) $record->amount, 2);
                            $paid = number_format((float) $record->amount_paid, 2);
                            $context = "Le recuerdo que de su tratamiento{$servicePart}:\n"
                                . "  • Total: *\${$total} MXN*\n"
                                . "  • Abonado: \${$paid} MXN\n"
                                . "  • *Pendiente: \${$remaining} MXN*";
                        } else {
                            $context = "Le recuerdo que tiene un cobro pendiente de *\${$remaining} MXN*{$servicePart}.";
                        }

                        // Fecha limite (si existe)
                        $dueDatePart = '';
                        if ($record->due_date) {
                            $dueDate = $record->due_date->translatedFormat('d \d\e F');
                            $dueDatePart = $record->is_overdue
                                ? "\n\n⚠ La fecha de pago era el {$dueDate}."
                                : "\n\nFecha de pago: {$dueDate}.";
                        }

                        // Como pagar (3 opciones)
                        $clinicPhone = $clinic->phone ? "\n  • Llamar al consultorio: {$clinic->phone}" : '';
                        $clinicAddress = $clinic->address ? "\n  • Pasar al consultorio: {$clinic->address}" : '';
                        $howToPay = "\n\nCuando le acomode:{$clinicPhone}{$clinicAddress}\n  • O respondame por aqui y le paso los datos de transferencia";

                        $msg = urlencode(
                            "Hola {$firstName}, le escribo de *{$clinicName}*.\n\n"
                            . $context
                            . $dueDatePart
                            . $howToPay
                            . "\n\nSi ya lo pagó, avíseme y lo marco como saldado. ¡Gracias!"
                        );
                        return "https://wa.me/{$phone}?text={$msg}";
                    })
                    ->openUrlInNewTab()
                    ->action(function (Payment $record) {
                        if (!auth()->user()?->clinic?->hasFeature('whatsapp_payment')) {
                            \Filament\Notifications\Notification::make()
                                ->title('Función disponible desde el plan Básico')
                                ->body('El recordatorio de cobro por WhatsApp es parte del plan Básico en adelante.')
                                ->warning()
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('upgrade')
                                        ->label('Mejorar plan')
                                        ->url(route('filament.doctor.pages.actualizar-plan')),
                                ])
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make()->label('Editar'),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ])
            // Sin esto Filament dice "No se encontraron registros", que no
            // le dice al doctor que hacer ni con que llenarlo.
            ->emptyStateHeading('Aún no registras cobros')
            ->emptyStateDescription('Cada consulta que cobres queda aquí, con lo pagado y lo que falta por cobrar.')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('payment_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
