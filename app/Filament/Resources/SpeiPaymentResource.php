<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpeiPaymentResource\Pages;
use App\Models\Commission;
use App\Models\SpeiPayment;
use App\Services\SpeiReviewService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SpeiPaymentResource extends Resource
{
    protected static ?string $model = SpeiPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Pagos SPEI';

    protected static ?string $modelLabel = 'Pago SPEI';

    protected static ?string $pluralModelLabel = 'Pagos SPEI';

    protected static ?string $navigationGroup = 'Facturación';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', SpeiPayment::STATUS_PENDING)->count();
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
                    ->label('Recibido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clinic.name')
                    ->label('Clínica')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan')
                    ->label('Plan')
                    ->formatStateUsing(fn ($state) => \App\Models\Clinic::displayNameForPlan($state))
                    ->badge(),
                Tables\Columns\TextColumn::make('billing_cycle')
                    ->label('Ciclo')
                    ->formatStateUsing(fn ($state) => $state === 'annual' ? 'Anual' : 'Mensual')
                    ->badge()
                    ->color(fn ($state) => $state === 'annual' ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('MXN')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_code')
                    ->label('Referencia')
                    ->fontFamily('mono')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        SpeiPayment::STATUS_PENDING => 'Pendiente',
                        SpeiPayment::STATUS_APPROVED => 'Aprobado',
                        SpeiPayment::STATUS_REJECTED => 'Rechazado',
                        SpeiPayment::STATUS_EXPIRED => 'Expirado',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        SpeiPayment::STATUS_PENDING => 'warning',
                        SpeiPayment::STATUS_APPROVED => 'success',
                        SpeiPayment::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Revisado por')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        SpeiPayment::STATUS_PENDING => 'Pendiente',
                        SpeiPayment::STATUS_APPROVED => 'Aprobado',
                        SpeiPayment::STATUS_REJECTED => 'Rechazado',
                        SpeiPayment::STATUS_EXPIRED => 'Expirado',
                    ])
                    ->default(SpeiPayment::STATUS_PENDING),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Ver comprobante'),
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === SpeiPayment::STATUS_PENDING)
                    ->form([
                        Forms\Components\Textarea::make('review_notes')
                            ->label('Nota interna (opcional)')
                            ->rows(2),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar pago SPEI')
                    ->modalDescription(fn ($record) => "Activarás el plan {$record->plan} ({$record->billing_cycle}) para " . ($record->clinic->name ?? 'la clínica') . " por " . ($record->billing_cycle === 'annual' ? '365' : '30') . ' días.')
                    ->action(function ($record, array $data) {
                        app(SpeiReviewService::class)->approve($record, auth()->user(), $data['review_notes'] ?? null);
                        Notification::make()
                            ->title('Pago aprobado y plan activado')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === SpeiPayment::STATUS_PENDING)
                    ->form([
                        Forms\Components\Textarea::make('review_notes')
                            ->label('Motivo del rechazo (se envía al cliente)')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        app(SpeiReviewService::class)->reject($record, auth()->user(), $data['review_notes']);
                        Notification::make()
                            ->title('Pago rechazado')
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Detalles del pago')
                ->schema([
                    Infolists\Components\TextEntry::make('clinic.name')->label('Clínica'),
                    Infolists\Components\TextEntry::make('user.name')->label('Usuario que subió')->placeholder('—'),
                    Infolists\Components\TextEntry::make('plan')->formatStateUsing(fn ($state) => \App\Models\Clinic::displayNameForPlan($state)),
                    Infolists\Components\TextEntry::make('billing_cycle')->label('Ciclo')->formatStateUsing(fn ($state) => $state === 'annual' ? 'Anual' : 'Mensual'),
                    Infolists\Components\TextEntry::make('amount')->money('MXN'),
                    Infolists\Components\TextEntry::make('reference_code')->label('Referencia')->copyable(),
                    Infolists\Components\TextEntry::make('client_notes')->label('Notas del cliente')->placeholder('—')->columnSpanFull(),
                ])
                ->columns(3),

            Infolists\Components\Section::make('Comprobante')
                ->schema([
                    Infolists\Components\TextEntry::make('receipt_original_name')
                        ->label('Archivo subido')
                        ->placeholder('Sin archivo'),
                    Infolists\Components\TextEntry::make('receipt_mime')->label('Tipo'),
                    Infolists\Components\TextEntry::make('receipt_size_bytes')
                        ->label('Tamaño')
                        ->formatStateUsing(fn ($state) => $state ? round($state / 1024) . ' KB' : '—'),
                    Infolists\Components\Actions::make([
                        Infolists\Components\Actions\Action::make('download')
                            ->label('Ver / descargar comprobante')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('primary')
                            ->url(fn ($record) => $record->receiptUrl(), shouldOpenInNewTab: true)
                            ->visible(fn ($record) => (bool) $record->receipt_path),
                    ])->columnSpanFull(),
                ])
                ->columns(3),

            Infolists\Components\Section::make('Revisión')
                ->schema([
                    Infolists\Components\TextEntry::make('status')->badge(),
                    Infolists\Components\TextEntry::make('reviewer.name')->label('Revisado por')->placeholder('—'),
                    Infolists\Components\TextEntry::make('reviewed_at')->dateTime('d/m/Y H:i')->placeholder('—'),
                    Infolists\Components\TextEntry::make('review_notes')->label('Notas de revisión')->placeholder('—')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('plan_activated_until')->label('Plan activado hasta')->date('d/m/Y')->placeholder('—'),
                ])
                ->columns(3),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpeiPayments::route('/'),
            'view' => Pages\ViewSpeiPayment::route('/{record}'),
        ];
    }
}
