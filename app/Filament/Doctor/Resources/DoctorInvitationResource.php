<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\DoctorInvitationResource\Pages;
use App\Models\DoctorInvitation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DoctorInvitationResource extends Resource
{
    protected static ?string $slug = 'invitar-doctores';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    protected static ?string $model = DoctorInvitation::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationLabel = 'Invitar Doctores';

    protected static ?string $modelLabel = 'Invitación';

    protected static ?string $pluralModelLabel = 'Invitaciones';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationGroup = 'Consultorio';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invitar Doctor al Consultorio')
                    ->description('Envía una invitación para que otro doctor se una a tu consultorio.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del doctor')
                            ->required()
                            ->placeholder('Dr. Juan Pérez'),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->placeholder('doctor@email.com'),
                        Forms\Components\TextInput::make('specialty')
                            ->label('Especialidad')
                            ->placeholder('Ej: Ortodoncia, Endodoncia'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Doctor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('specialty')
                    ->label('Especialidad')
                    ->placeholder('Sin especificar'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state, $record) => match (true) {
                        $state === 'accepted' => 'Aceptada',
                        $state === 'pending' && $record->isExpired() => 'Expirada',
                        $state === 'pending' => 'Pendiente',
                        default => 'Expirada',
                    })
                    ->colors([
                        'warning' => fn ($state, $record) => $state === 'pending' && !$record->isExpired(),
                        'success' => 'accepted',
                        'danger' => fn ($state, $record) => $state === 'expired' || ($state === 'pending' && $record->isExpired()),
                    ]),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expira')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Enviada')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('resend')
                    ->label('Reenviar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (DoctorInvitation $record) => $record->status === 'pending')
                    ->action(function (DoctorInvitation $record) {
                        $record->update([
                            'expires_at' => now()->addDays(7),
                            'token' => \Illuminate\Support\Str::random(64),
                        ]);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctorInvitations::route('/'),
            'create' => Pages\CreateDoctorInvitation::route('/create'),
        ];
    }
}
