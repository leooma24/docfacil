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
    protected static ?string $slug = 'cobros';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Cobros';

    protected static ?string $modelLabel = 'Cobro';

    protected static ?string $pluralModelLabel = 'Cobros';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Cobro')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->label('Paciente')
                            ->relationship('patient')
                            ->getOptionLabelFromRecordUsing(fn (Patient $record) => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
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
                            ->label('Monto')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\Select::make('payment_method')
                            ->label('Método de pago')
                            ->options([
                                'cash' => 'Efectivo',
                                'card' => 'Tarjeta',
                                'transfer' => 'Transferencia',
                                'other' => 'Otro',
                            ])
                            ->default('cash')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'paid' => 'Pagado',
                                'pending' => 'Pendiente',
                                'partial' => 'Parcial',
                                'refunded' => 'Reembolsado',
                            ])
                            ->default('paid')
                            ->required(),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Fecha de pago')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Select::make('appointment_id')
                            ->label('Cita asociada')
                            ->relationship('appointment')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient->first_name} {$record->patient->last_name}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Servicio')
                    ->placeholder('Sin servicio'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('payment_method')
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
