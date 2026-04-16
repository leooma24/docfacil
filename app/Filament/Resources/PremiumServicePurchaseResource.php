<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PremiumServicePurchaseResource\Pages;
use App\Models\PremiumServicePurchase;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PremiumServicePurchaseResource extends Resource
{
    protected static ?string $model = PremiumServicePurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Compras de servicios';

    protected static ?string $modelLabel = 'Compra';

    protected static ?string $pluralModelLabel = 'Compras de servicios';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->whereIn('status', [PremiumServicePurchase::STATUS_PAID, PremiumServicePurchase::STATUS_IN_PROGRESS])
            ->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clinic.name')
                    ->label('Clínica')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service_name_snapshot')
                    ->label('Servicio')
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount_mxn')
                    ->label('Monto')
                    ->money('MXN')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => PremiumServicePurchase::STATUSES[$state] ?? $state)
                    ->color(fn ($record) => $record->statusColor()),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Asignado a')
                    ->placeholder('Sin asignar')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Pagado')
                    ->dateTime('d/m/Y')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Entregado')
                    ->dateTime('d/m/Y')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(PremiumServicePurchase::STATUSES),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Método de pago')
                    ->options([
                        'stripe' => 'Stripe',
                        'spei' => 'SPEI',
                        'manual' => 'Manual',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('start')
                    ->label('Marcar en ejecución')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === PremiumServicePurchase::STATUS_PAID)
                    ->form([
                        Forms\Components\Select::make('assigned_to')
                            ->label('Asignar a')
                            ->options(User::whereIn('role', ['admin', 'super_admin'])->pluck('name', 'id'))
                            ->searchable()
                            ->default(fn () => auth()->id()),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => PremiumServicePurchase::STATUS_IN_PROGRESS,
                            'assigned_to' => $data['assigned_to'] ?? auth()->id(),
                            'started_at' => now(),
                        ]);
                        Notification::make()->title('Marcado en ejecución')->success()->send();
                    }),
                Tables\Actions\Action::make('deliver')
                    ->label('Marcar entregado')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === PremiumServicePurchase::STATUS_IN_PROGRESS)
                    ->form([
                        Forms\Components\Textarea::make('delivery_notes')
                            ->label('Notas de entrega (se envían al cliente)')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => PremiumServicePurchase::STATUS_DELIVERED,
                            'delivered_at' => now(),
                            'delivery_notes' => $data['delivery_notes'] ?? null,
                        ]);
                        Notification::make()->title('Entrega registrada')->success()->send();
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, [
                        PremiumServicePurchase::STATUS_PENDING_PAYMENT,
                        PremiumServicePurchase::STATUS_PAID,
                        PremiumServicePurchase::STATUS_IN_PROGRESS,
                    ], true))
                    ->form([
                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Motivo (interno)')
                            ->required()
                            ->rows(2),
                    ])
                    ->requiresConfirmation()
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => PremiumServicePurchase::STATUS_CANCELLED,
                            'cancelled_at' => now(),
                            'internal_notes' => $data['internal_notes'],
                        ]);
                        Notification::make()->title('Compra cancelada')->warning()->send();
                    }),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Detalles de la compra')
                ->schema([
                    // Snapshot de precio y nombre — inmutables desde admin (dehydrated false
                    // evita que el valor viaje en el POST aunque alguien lo manipule en el DOM).
                    Forms\Components\TextInput::make('service_name_snapshot')
                        ->label('Servicio')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\TextInput::make('amount_mxn')
                        ->label('Monto')
                        ->disabled()
                        ->dehydrated(false)
                        ->prefix('$'),
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options(PremiumServicePurchase::STATUSES)
                        ->required(),
                    Forms\Components\Select::make('assigned_to')
                        ->label('Asignado a')
                        ->options(User::whereIn('role', ['admin', 'super_admin'])->pluck('name', 'id'))
                        ->searchable(),
                ])->columns(2),

            Forms\Components\Section::make('Intake del cliente')
                ->schema([
                    Forms\Components\KeyValue::make('intake_data')
                        ->label('Datos del intake')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Textarea::make('client_notes')
                        ->label('Notas del cliente')
                        ->rows(3)
                        ->disabled()
                        ->dehydrated(false),
                ])
                ->visible(fn ($record) => !empty($record?->intake_data) || !empty($record?->client_notes)),

            Forms\Components\Section::make('Entrega')
                ->schema([
                    Forms\Components\Textarea::make('delivery_notes')
                        ->label('Notas de entrega')
                        ->rows(3),
                    Forms\Components\Textarea::make('internal_notes')
                        ->label('Notas internas (solo admin)')
                        ->rows(3),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPremiumServicePurchases::route('/'),
            'view' => Pages\ViewPremiumServicePurchase::route('/{record}'),
            'edit' => Pages\EditPremiumServicePurchase::route('/{record}/edit'),
        ];
    }
}
