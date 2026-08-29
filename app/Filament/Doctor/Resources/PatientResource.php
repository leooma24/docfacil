<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\PatientResource\Pages;
use App\Mail\PatientPortalInviteMail;
use App\Models\Patient;
use App\Models\User;
use App\Services\PatientPortalInvite;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PatientResource extends Resource
{
    protected static ?string $slug = 'pacientes';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pacientes';

    protected static ?string $modelLabel = 'Paciente';

    protected static ?string $pluralModelLabel = 'Pacientes';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return "{$record->first_name} {$record->last_name}";
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'email', 'phone'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Tel' => $record->phone ?? '-',
            'Email' => $record->email ?? '-',
        ];
    }

    public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): string
    {
        return route('filament.doctor.pages.perfil-paciente', ['patient' => $record->id]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos Personales')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Apellidos')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Fecha de nacimiento')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Select::make('gender')
                            ->label('Género')
                            ->options([
                                'male' => 'Masculino',
                                'female' => 'Femenino',
                                'other' => 'Otro',
                            ]),
                        Forms\Components\Select::make('blood_type')
                            ->label('Tipo de sangre')
                            ->options([
                                'A+' => 'A+', 'A-' => 'A-',
                                'B+' => 'B+', 'B-' => 'B-',
                                'AB+' => 'AB+', 'AB-' => 'AB-',
                                'O+' => 'O+', 'O-' => 'O-',
                            ]),
                        Forms\Components\Textarea::make('address')
                            ->label('Dirección')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Información Médica')
                    ->schema([
                        Forms\Components\Textarea::make('allergies')
                            ->label('Alergias')
                            ->rows(2),
                        Forms\Components\Textarea::make('medical_notes')
                            ->label('Notas médicas')
                            ->rows(3),
                    ]),
                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Apellidos')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Nacimiento')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('profile')
                        ->label('Ver perfil')
                        ->icon('heroicon-o-user-circle')
                        ->color('primary')
                        ->url(fn ($record) => route('filament.doctor.pages.perfil-paciente', ['patient' => $record->id])),
                    Tables\Actions\EditAction::make()->label('Editar datos'),
                    Tables\Actions\Action::make('whatsapp')
                        ->label('WhatsApp')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->visible(fn ($record) => !empty($record->phone))
                        ->url(function ($record) {
                            $phone = preg_replace('/\D/', '', $record->phone);
                            if (strlen($phone) === 10) $phone = '52' . $phone;
                            return "https://wa.me/{$phone}";
                        })
                        ->openUrlInNewTab(),

                    // Portal del paciente: le manda su liga por WhatsApp para
                    // que elija contrasena. Solo aparece si el plan lo incluye
                    // y si el paciente todavia no tiene cuenta.
                    Tables\Actions\Action::make('dar_acceso_portal')
                        ->label('Dar acceso al portal')
                        ->icon('heroicon-o-key')
                        ->color('info')
                        ->visible(fn ($record) => empty($record->user_id)
                            && ! empty($record->phone)
                            && auth()->user()->clinic?->hasFeature('patient_portal'))
                        ->modalHeading('Dar acceso al portal')
                        ->modalDescription('Le mandamos su liga por WhatsApp para que elija su contrasena. Ahi va a poder ver sus citas, recetas y pagos.')
                        ->modalSubmitActionLabel('Abrir WhatsApp')
                        ->form([
                            // El correo es con lo que entra despues, asi que es
                            // obligatorio aunque el paciente no lo tuviera.
                            Forms\Components\TextInput::make('email')
                                ->label('Correo del paciente')
                                ->email()
                                ->required()
                                ->default(fn ($record) => $record->email)
                                ->helperText('Con este correo va a entrar al portal.'),
                        ])
                        ->action(function (Patient $record, array $data) {
                            $correo = trim($data['email']);

                            if (User::where('email', $correo)->exists()) {
                                Notification::make()
                                    ->title('Ese correo ya tiene cuenta en DocFacil')
                                    ->body('Registra otro correo para este paciente.')
                                    ->danger()
                                    ->send();

                                return null;
                            }

                            $record->update(['email' => $correo]);

                            // El correo es refuerzo: si falla, el paciente
                            // igual recibe su liga por WhatsApp.
                            try {
                                Mail::to($correo)->send(new PatientPortalInviteMail($record));
                            } catch (\Throwable $e) {
                                Log::warning('PatientPortalInviteMail fallo', [
                                    'patient_id' => $record->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }

                            return redirect()->away(PatientPortalInvite::urlWhatsApp($record));
                        }),

                    // Ya tiene cuenta: se lo decimos, sin accion que ejecutar.
                    Tables\Actions\Action::make('portal_activo')
                        ->label('Portal activo')
                        ->icon('heroicon-o-check-badge')
                        ->color('gray')
                        ->disabled()
                        ->visible(fn ($record) => ! empty($record->user_id)
                            && auth()->user()->clinic?->hasFeature('patient_portal')),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // Sin esto Filament dice "No se encontraron registros", que no
            // le dice al doctor que hacer ni con que llenarlo.
            ->emptyStateHeading('Todavía no tienes pacientes')
            ->emptyStateDescription('Agrega el primero con su nombre y teléfono. Lo demás lo llenas cuando lo tengas a la mano.')
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }
}
