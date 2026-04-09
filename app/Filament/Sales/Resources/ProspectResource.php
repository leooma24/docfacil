<?php

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\ProspectResource\Pages;
use App\Models\Prospect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProspectResource extends Resource
{
    protected static ?string $model = Prospect::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationLabel = 'Mis Prospectos';

    protected static ?string $modelLabel = 'Prospecto';

    protected static ?string $pluralModelLabel = 'Prospectos';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('assigned_to_sales_rep_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del prospecto')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                    Forms\Components\TextInput::make('clinic_name')->label('Consultorio')->maxLength(255),
                    Forms\Components\TextInput::make('phone')->label('Teléfono')->tel()->required(),
                    Forms\Components\TextInput::make('email')->label('Email')->email(),
                    Forms\Components\TextInput::make('city')->label('Ciudad'),
                    Forms\Components\TextInput::make('specialty')->label('Especialidad'),
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options([
                            'new' => 'Nuevo',
                            'contacted' => 'Contactado',
                            'interested' => 'Interesado',
                            'trial' => 'En trial',
                            'lost' => 'Perdido',
                        ])
                        ->default('new')
                        ->disableOptionWhen(fn (string $value): bool => $value === 'converted'),
                    Forms\Components\DateTimePicker::make('next_followup_at')
                        ->label('Siguiente seguimiento'),
                    Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull()->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('clinic_name')->label('Consultorio')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Teléfono')->searchable(),
                Tables\Columns\TextColumn::make('city')->label('Ciudad')->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'gray' => 'new',
                        'info' => 'contacted',
                        'warning' => 'interested',
                        'primary' => 'trial',
                        'success' => 'converted',
                        'danger' => 'lost',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'new' => 'Nuevo',
                        'contacted' => 'Contactado',
                        'interested' => 'Interesado',
                        'trial' => 'En trial',
                        'converted' => 'Convertido ✓',
                        'lost' => 'Perdido',
                    }),
                Tables\Columns\TextColumn::make('next_followup_at')
                    ->label('Siguiente seguimiento')
                    ->dateTime('d/m/Y H:i')
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('contacted_at')->label('Contactado')->date('d/m/Y')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('Registrado')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'new' => 'Nuevo',
                        'contacted' => 'Contactado',
                        'interested' => 'Interesado',
                        'trial' => 'En trial',
                        'converted' => 'Convertido',
                        'lost' => 'Perdido',
                    ]),
                Tables\Filters\Filter::make('pending_followup')
                    ->label('Seguimiento pendiente')
                    ->query(fn ($q) => $q->whereNotNull('next_followup_at')->where('next_followup_at', '<=', now())),
            ])
            ->actions([
                Tables\Actions\Action::make('contact')
                    ->label('Marcar contactado')
                    ->icon('heroicon-o-phone')
                    ->color('info')
                    ->visible(fn (Prospect $r) => $r->status === 'new')
                    ->action(function (Prospect $record) {
                        $record->update([
                            'status' => 'contacted',
                            'contacted_at' => now(),
                            'last_followup_at' => now(),
                        ]);
                        Notification::make()->title('Marcado como contactado')->success()->send();
                    }),
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->visible(fn (Prospect $r) => !empty($r->phone))
                    ->url(function (Prospect $record) {
                        $phone = preg_replace('/[\s\-\(\)\+]/', '', $record->phone);
                        if (strlen($phone) === 10) $phone = '52' . $phone;
                        return "https://wa.me/{$phone}";
                    })
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProspects::route('/'),
            'create' => Pages\CreateProspect::route('/create'),
            'edit' => Pages\EditProspect::route('/{record}/edit'),
        ];
    }
}
