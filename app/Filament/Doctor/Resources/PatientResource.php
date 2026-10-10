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

    protected static ?string $navigationGroup = 'Pacientes';

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
                    // Con el nombre y el telefono ya se puede agendar y
                    // recordarle la cita. Lo demas se llena cuando lo tengas.
                    ->description('Con el nombre y el teléfono es suficiente para empezar. Lo demás lo llena cuando lo tenga.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Nombre')
                            ->placeholder('María Elena')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Apellidos')
                            ->placeholder('García López')
                            ->required()
                            ->maxLength(255),
                        // NOM-024 (6.5): la CURP identifica al paciente. Se valida
                        // y de ella se toman nacimiento, sexo y estado; nunca se
                        // inventa. Opcional para no frenar el alta.
                        Forms\Components\TextInput::make('curp')
                            ->label('CURP')
                            ->placeholder('GALR850315MSLRPS05')
                            ->helperText('La pide la NOM-024. Al escribirla se llenan solos la fecha de nacimiento, el sexo y el estado donde nació.')
                            ->maxLength(18)
                            ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Forms\Set $set) {
                                if ($datos = \App\Support\Curp::datos($state)) {
                                    foreach ($datos as $campo => $valor) {
                                        $set($campo, $valor);
                                    }
                                }
                            })
                            ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                                if (filled($value) && ! \App\Support\Curp::valida($value)) {
                                    $fail('Esa CURP no es válida: revise que tenga las 18 letras y números bien.');
                                }
                            })
                            ->unique('patients', 'curp', ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('clinic_id', auth()->user()->clinic_id))
                            ->dehydrateStateUsing(fn (?string $state) => \App\Support\Curp::limpia($state))
                            ->validationMessages(['unique' => 'Ya hay un paciente con esa CURP en su consultorio.']),
                        Forms\Components\Select::make('entidad_nacimiento')
                            ->label('Estado donde nació')
                            ->options(\App\Support\Curp::ENTIDADES)
                            ->searchable(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->placeholder('maria@correo.com')
                            ->helperText('Lo necesita si le va a dar acceso al portal.')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->placeholder('55 1234 5678')
                            ->helperText('A este número le llegan los recordatorios por WhatsApp.')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Fecha de nacimiento')
                            ->placeholder('dd/mm/aaaa')
                            ->helperText('Con esto aparece en "Cumpleaños de hoy" del escritorio, para felicitarlo con un clic.')
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
                        Forms\Components\Select::make('nacionalidad')
                            ->label('Nacionalidad')
                            ->options(['MEX' => 'Mexicana', 'EXT' => 'Otra']),
                        Forms\Components\Select::make('estado_residencia')
                            ->label('Estado donde vive')
                            ->options(collect(\App\Support\Curp::ENTIDADES)->except('NE')->all())
                            ->searchable(),
                        Forms\Components\TextInput::make('municipio_residencia')
                            ->label('Municipio donde vive')
                            ->placeholder('Ahome')
                            ->maxLength(100),
                        // Para niños: a la mamá le llegan los recordatorios y
                        // con ella se suma lo que debe la familia.
                        Forms\Components\Select::make('responsable_id')
                            ->label('Responsable (mamá, papá o quien lo trae)')
                            ->helperText('Los mensajes de WhatsApp le llegan a su responsable, y en su perfil se ve lo que debe toda la familia.')
                            ->relationship('responsable', 'first_name', fn ($query, $record) => $query
                                ->where('clinic_id', auth()->user()->clinic_id)
                                ->when($record, fn ($q) => $q->whereKeyNot($record->id)))
                            ->getOptionLabelFromRecordUsing(fn (Patient $p) => trim("{$p->first_name} {$p->last_name}") . ($p->phone ? " · {$p->phone}" : ''))
                            ->searchable(['first_name', 'last_name', 'phone'])
                            ->rule(fn ($record) => \Illuminate\Validation\Rule::exists('patients', 'id')
                                ->where('clinic_id', auth()->user()->clinic_id)
                                ->when($record, fn ($r) => $r->whereNot('id', $record->id)))
                            ->createOptionForm([
                                Forms\Components\TextInput::make('first_name')->label('Nombre')->required(),
                                Forms\Components\TextInput::make('last_name')->label('Apellidos')->required(),
                                Forms\Components\TextInput::make('phone')->label('WhatsApp')->tel()->required(),
                            ])
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Información Médica')
                    ->description('Opcional, pero lo que ponga aquí le sale como alerta antes de cada consulta.')
                    ->schema([
                        Forms\Components\CheckboxList::make('riesgos')
                            ->label('Antecedentes importantes')
                            ->helperText('Marque lo que tenga. Le sale en rojo en su cita, su perfil y su consulta, y avisa al recetar.')
                            ->options(\App\Support\AlertasClinicas::OPCIONES)
                            ->columns(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('allergies')
                            ->label('Alergias')
                            ->placeholder('Penicilina, látex, anestesia...')
                            ->helperText('Le aparece en rojo al abrir su consulta.')
                            ->rows(2),
                        Forms\Components\Textarea::make('medical_notes')
                            ->label('Notas médicas')
                            ->placeholder('Toma anticoagulantes. Diabético. Hipertenso...')
                            ->helperText('Lo que quieras tener presente antes de atenderlo.')
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
                    ->visibleFrom('2xl')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->visibleFrom('xl')
                    ->label('Nacimiento')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->visibleFrom('xl')
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
                                    ->body('Registre otro correo para este paciente.')
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

                    // El paciente que el doctor capturó o importó no ha aceptado
                    // el aviso de privacidad (ley de datos, arts. 8 y 17). Se le
                    // manda por WhatsApp con una liga firmada, o se registra que
                    // lo firmó en papel.
                    Tables\Actions\Action::make('aviso_whatsapp')
                        ->label('Mandar aviso de privacidad')
                        ->icon('heroicon-o-shield-check')
                        ->color('warning')
                        ->visible(fn ($record) => filled($record->phone)
                            && ! \App\Support\AvisoDePrivacidad::aceptoElVigente($record))
                        ->url(fn ($record) => \App\Support\AvisoDePrivacidad::urlWhatsApp($record))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('aviso_en_papel')
                        ->label('Firmó el aviso en papel')
                        ->icon('heroicon-o-document-check')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('¿Firmó el aviso de privacidad en papel?')
                        ->modalDescription('Úsalo solo si el paciente firmó la hoja del aviso en el consultorio. Guárdala en su expediente.')
                        ->visible(fn ($record) => ! \App\Support\AvisoDePrivacidad::aceptoElVigente($record))
                        ->action(fn ($record) => \App\Support\AvisoDePrivacidad::registrarAceptacion($record, 'consultorio')),

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
                    // Antes borraba a todos los seleccionados, y con ellos su
                    // expediente en cascada. Ahora solo a los que no tienen
                    // nada clínico, y dice cuántos se conservaron y por qué.
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            [$conExpediente, $sinExpediente] = $records->partition(fn ($paciente) => $paciente->porQueNoSeBorra() !== null);

                            $sinExpediente->each->delete();

                            if ($sinExpediente->isNotEmpty()) {
                                Notification::make()
                                    ->title($sinExpediente->count() === 1 ? 'Se borró 1 paciente' : "Se borraron {$sinExpediente->count()} pacientes")
                                    ->success()
                                    ->send();
                            }

                            if ($conExpediente->isNotEmpty()) {
                                Notification::make()
                                    ->title($conExpediente->count() === 1 ? '1 paciente no se borró' : "{$conExpediente->count()} pacientes no se borraron")
                                    ->body('Tienen expediente clínico (la NOM-004 pide conservarlo al menos 5 años) o cobros registrados (se irían de la caja y del corte).')
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            }
                        }),
                ]),
            ])
            // Sin esto Filament dice "No se encontraron registros", que no
            // le dice al doctor que hacer ni con que llenarlo.
            ->emptyStateHeading('Todavía no tiene pacientes')
            ->emptyStateDescription('Agregue el primero con su nombre y teléfono. Lo demás lo llena cuando lo tenga a la mano.')
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
