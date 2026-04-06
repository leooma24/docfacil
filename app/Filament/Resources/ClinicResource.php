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
                Forms\Components\Tabs::make('Consultorio')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Información')
                            ->icon('heroicon-o-building-office')
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
                                Forms\Components\FileUpload::make('logo')->label('Logo')->image()->directory('clinic-logos')->maxSize(2048),
                            ]),
                        Forms\Components\Tabs\Tab::make('Plan y Suscripción')
                            ->icon('heroicon-o-credit-card')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Select::make('plan')
                                    ->label('Plan actual')
                                    ->options([
                                        'free' => 'Gratis',
                                        'basico' => 'Básico - $149/mes',
                                        'profesional' => 'Profesional - $299/mes',
                                        'clinica' => 'Clínica - $499/mes',
                                    ])
                                    ->default('free'),
                                Forms\Components\DateTimePicker::make('trial_ends_at')->label('Trial termina')->native(false),
                                Forms\Components\Toggle::make('is_active')->label('Activo')->default(true),
                                Forms\Components\Toggle::make('is_founder')
                                    ->label('Precio de fundador')
                                    ->helperText('50% de descuento permanente')
                                    ->reactive(),
                                Forms\Components\TextInput::make('founder_price')
                                    ->label('Precio fundador (mensual)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->placeholder('149')
                                    ->visible(fn (Forms\Get $get) => $get('is_founder')),
                            ]),
                        Forms\Components\Tabs\Tab::make('Programa Beta')
                            ->icon('heroicon-o-beaker')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_beta')
                                    ->label('Es beta tester')
                                    ->helperText('6 meses gratis del Plan Profesional')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $set('plan', 'profesional');
                                            $set('beta_starts_at', now()->toDateTimeString());
                                            $set('beta_ends_at', now()->addMonths(6)->toDateTimeString());
                                        }
                                    }),
                                Forms\Components\Select::make('onboarding_status')
                                    ->label('Estado de onboarding')
                                    ->options([
                                        'pending' => 'Pendiente — No iniciado',
                                        'scheduled' => 'Agendado — Llamada programada',
                                        'completed' => 'Completado — Ya configurado',
                                    ])
                                    ->default('pending'),
                                Forms\Components\DateTimePicker::make('beta_starts_at')
                                    ->label('Inicio del beta')
                                    ->native(false)
                                    ->visible(fn (Forms\Get $get) => $get('is_beta')),
                                Forms\Components\DateTimePicker::make('beta_ends_at')
                                    ->label('Fin del beta')
                                    ->native(false)
                                    ->visible(fn (Forms\Get $get) => $get('is_beta')),
                                Forms\Components\Textarea::make('beta_notes')
                                    ->label('Notas del beta / feedback')
                                    ->rows(4)
                                    ->columnSpanFull()
                                    ->helperText('Anota aquí el feedback de cada llamada semanal'),
                            ]),
                        Forms\Components\Tabs\Tab::make('Caso de Éxito')
                            ->icon('heroicon-o-star')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Toggle::make('show_as_case_study')
                                    ->label('Mostrar como caso de éxito en landing')
                                    ->helperText('Con permiso del doctor')
                                    ->reactive(),
                                Forms\Components\FileUpload::make('case_study_logo')
                                    ->label('Logo del consultorio')
                                    ->image()
                                    ->directory('case-studies')
                                    ->maxSize(2048)
                                    ->visible(fn (Forms\Get $get) => $get('show_as_case_study')),
                                Forms\Components\Textarea::make('case_study_testimonial')
                                    ->label('Testimonial del doctor')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->placeholder('"DocFácil me ahorra 2 horas al día..." — Dr. Juan Pérez')
                                    ->visible(fn (Forms\Get $get) => $get('show_as_case_study')),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('city')->label('Ciudad')->searchable(),
                Tables\Columns\BadgeColumn::make('plan')
                    ->label('Plan')
                    ->colors([
                        'gray' => 'free',
                        'info' => 'basico',
                        'success' => 'profesional',
                        'primary' => 'clinica',
                    ]),
                Tables\Columns\IconColumn::make('is_beta')
                    ->label('Beta')
                    ->boolean()
                    ->trueIcon('heroicon-o-beaker')
                    ->trueColor('warning'),
                Tables\Columns\IconColumn::make('is_founder')
                    ->label('Fundador')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->trueColor('success'),
                Tables\Columns\BadgeColumn::make('onboarding_status')
                    ->label('Onboarding')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pendiente',
                        'scheduled' => 'Agendado',
                        'completed' => 'Completado',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'scheduled',
                        'success' => 'completed',
                    ]),
                Tables\Columns\TextColumn::make('doctors_count')->label('Doctores')->counts('doctors'),
                Tables\Columns\TextColumn::make('patients_count')->label('Pacientes')->counts('patients'),
                Tables\Columns\TextColumn::make('beta_ends_at')
                    ->label('Beta termina')
                    ->date('d/m/Y')
                    ->placeholder('-'),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_beta')->label('Beta testers'),
                Tables\Filters\TernaryFilter::make('is_founder')->label('Fundadores'),
                Tables\Filters\SelectFilter::make('onboarding_status')
                    ->label('Onboarding')
                    ->options([
                        'pending' => 'Pendiente',
                        'scheduled' => 'Agendado',
                        'completed' => 'Completado',
                    ]),
                Tables\Filters\SelectFilter::make('plan')
                    ->label('Plan')
                    ->options([
                        'free' => 'Gratis',
                        'basico' => 'Básico',
                        'profesional' => 'Profesional',
                        'clinica' => 'Clínica',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('activate_beta')
                    ->label('Activar Beta')
                    ->icon('heroicon-o-beaker')
                    ->color('warning')
                    ->visible(fn (Clinic $record) => !$record->is_beta)
                    ->requiresConfirmation()
                    ->modalDescription('Se activará Plan Profesional gratis por 6 meses')
                    ->action(function (Clinic $record) {
                        $record->update([
                            'is_beta' => true,
                            'is_founder' => true,
                            'founder_price' => 149,
                            'plan' => 'profesional',
                            'beta_starts_at' => now(),
                            'beta_ends_at' => now()->addMonths(6),
                            'trial_ends_at' => now()->addMonths(6),
                        ]);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
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
