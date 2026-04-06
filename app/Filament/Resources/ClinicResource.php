<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClinicResource\Pages;
use App\Models\Clinic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClinicResource extends Resource
{
    protected static ?string $model = Clinic::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Consultorios';

    protected static ?string $modelLabel = 'Consultorio';

    protected static ?string $pluralModelLabel = 'Consultorios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Consultorio')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')->label('Nombre')->required(),
                        Forms\Components\TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')->label('Teléfono')->tel(),
                        Forms\Components\TextInput::make('email')->label('Email')->email(),
                        Forms\Components\Textarea::make('address')->label('Dirección')->columnSpanFull(),
                        Forms\Components\TextInput::make('city')->label('Ciudad'),
                        Forms\Components\TextInput::make('state')->label('Estado'),
                        Forms\Components\TextInput::make('zip_code')->label('C.P.'),
                        Forms\Components\Select::make('plan')
                            ->label('Plan')
                            ->options([
                                'free' => 'Gratis',
                                'basico' => 'Básico - $149/mes',
                                'profesional' => 'Profesional - $299/mes',
                                'clinica' => 'Clínica - $499/mes',
                            ])
                            ->default('free'),
                        Forms\Components\DateTimePicker::make('trial_ends_at')->label('Trial termina'),
                        Forms\Components\Toggle::make('is_active')->label('Activo')->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('city')->label('Ciudad')->searchable(),
                Tables\Columns\BadgeColumn::make('plan')->label('Plan'),
                Tables\Columns\TextColumn::make('doctors_count')->label('Doctores')->counts('doctors'),
                Tables\Columns\TextColumn::make('patients_count')->label('Pacientes')->counts('patients'),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Registrado')->date('d/m/Y')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClinics::route('/'),
            'create' => Pages\CreateClinic::route('/create'),
            'edit' => Pages\EditClinic::route('/{record}/edit'),
        ];
    }
}
