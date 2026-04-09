<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommissionResource\Pages;
use App\Models\Commission;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CommissionResource extends Resource
{
    protected static ?string $model = Commission::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Comisiones';

    protected static ?string $modelLabel = 'Comisión';

    protected static ?string $pluralModelLabel = 'Comisiones';

    protected static ?string $navigationGroup = 'Ventas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options([
                    'pending' => 'Pendiente',
                    'paid' => 'Pagada',
                    'clawed_back' => 'Clawback',
                    'cancelled' => 'Cancelada',
                ])
                ->required(),
            Forms\Components\DateTimePicker::make('paid_at')->label('Fecha de pago'),
            Forms\Components\Textarea::make('notes')->label('Notas'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('earned_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Vendedor')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('clinic.name')->label('Clínica')->searchable(),
                Tables\Columns\BadgeColumn::make('tier')
                    ->label('Pago')
                    ->formatStateUsing(fn ($state) => $state === 'first' ? '1ra mitad' : '2da mitad')
                    ->colors(['primary' => 'first', 'info' => 'second']),
                Tables\Columns\TextColumn::make('plan_at_sale')->label('Plan')->formatStateUsing(fn ($s) => ucfirst($s)),
                Tables\Columns\TextColumn::make('amount')->label('Monto')->money('MXN')->weight('bold')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'clawed_back',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pendiente',
                        'paid' => 'Pagada',
                        'clawed_back' => 'Clawback',
                        'cancelled' => 'Cancelada',
                    }),
                Tables\Columns\TextColumn::make('earned_at')->label('Ganada')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('paid_at')->label('Pagada')->date('d/m/Y')->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'paid' => 'Pagada',
                        'clawed_back' => 'Clawback',
                        'cancelled' => 'Cancelada',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Vendedor')
                    ->options(fn () => User::where('role', 'sales')->pluck('name', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('markAsPaid')
                    ->label('Marcar como pagadas')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $count = 0;
                        foreach ($records as $r) {
                            if ($r->status === 'pending') {
                                $r->update(['status' => 'paid', 'paid_at' => now()]);
                                $count++;
                            }
                        }
                        Notification::make()
                            ->title("{$count} comisiones marcadas como pagadas")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissions::route('/'),
            'edit' => Pages\EditCommission::route('/{record}/edit'),
        ];
    }
}
