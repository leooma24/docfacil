<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProspectResource\Pages;
use App\Models\Prospect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProspectResource extends Resource
{
    protected static ?string $model = Prospect::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationLabel = 'Prospectos';

    protected static ?string $modelLabel = 'Prospecto';

    protected static ?string $pluralModelLabel = 'Prospectos';

    protected static ?string $navigationGroup = 'CRM';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos del Prospecto')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')->label('Nombre')->required(),
                        Forms\Components\TextInput::make('email')->label('Email')->email()->required()->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')->label('Teléfono')->tel(),
                        Forms\Components\TextInput::make('clinic_name')->label('Nombre del consultorio'),
                        Forms\Components\TextInput::make('city')->label('Ciudad'),
                        Forms\Components\TextInput::make('specialty')->label('Especialidad'),
                        Forms\Components\Select::make('source')
                            ->label('Fuente')
                            ->options([
                                'landing' => 'Landing page',
                                'referral' => 'Referido',
                                'google' => 'Google',
                                'social' => 'Redes sociales',
                                'other' => 'Otro',
                            ])
                            ->default('landing'),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'new' => 'Nuevo',
                                'contacted' => 'Contactado',
                                'interested' => 'Interesado',
                                'trial' => 'En trial',
                                'converted' => 'Convertido',
                                'lost' => 'Perdido',
                            ])
                            ->default('new')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state === 'contacted') {
                                    $set('contacted_at', now()->toDateTimeString());
                                }
                                if ($state === 'converted') {
                                    $set('converted_at', now()->toDateTimeString());
                                }
                            }),
                        Forms\Components\DateTimePicker::make('contacted_at')->label('Fecha contacto'),
                        Forms\Components\DateTimePicker::make('converted_at')->label('Fecha conversión'),
                        Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull()->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Teléfono'),
                Tables\Columns\TextColumn::make('clinic_name')->label('Consultorio'),
                Tables\Columns\BadgeColumn::make('source')
                    ->label('Fuente')
                    ->colors([
                        'primary' => 'landing',
                        'success' => 'referral',
                        'info' => 'google',
                        'warning' => 'social',
                    ]),
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
                        'converted' => 'Convertido',
                        'lost' => 'Perdido',
                    }),
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
                Tables\Filters\SelectFilter::make('source')
                    ->label('Fuente')
                    ->options([
                        'landing' => 'Landing page',
                        'referral' => 'Referido',
                        'google' => 'Google',
                        'social' => 'Redes sociales',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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
