<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Concerns\GatedByPlanFeature;
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
    use GatedByPlanFeature;

    protected static ?string $slug = 'invitar-doctores';

    protected static function planFeature(): string
    {
        return 'multi_doctor';
    }

    /**
     * Su equipo: desde el Básico invita a su asistente; desde el Pro, también
     * a otros doctores. Es cosa del doctor: la asistente no invita a nadie.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ! $user->esAsistente()
            && ($user->clinic?->hasFeature('asistente') || static::clinicHasPlanFeature());
    }

    /**
     * Si todavía puede invitar a otro doctor: el plan lo permite (Pro en
     * adelante) y no ha llegado al tope. Cuentan los doctores que ya están y
     * las invitaciones pendientes, para no pasarse invitando.
     */
    public static function puedeInvitarDoctores(): bool
    {
        $clinica = auth()->user()?->clinic;
        if (! $clinica || ! static::clinicHasPlanFeature()) {
            return false;
        }
        if ($clinica->hasFeature('unlimited_doctors')) {
            return true;
        }

        $ocupados = $clinica->doctors()->count()
            + DoctorInvitation::where('clinic_id', $clinica->id)->where('role', 'doctor')
                ->where('status', 'pending')->where('expires_at', '>', now())->count();

        return $ocupados < 3;
    }

    /** La liga de la invitación, lista para mandar por WhatsApp a quien la elija el doctor. */
    public static function ligaDeWhatsApp(DoctorInvitation $invitacion): string
    {
        $como = $invitacion->esDeAsistente() ? 'asistente' : 'doctor';
        $texto = "Hola {$invitacion->name}, le comparto la liga para entrar a DocFácil como {$como} de "
            . ($invitacion->clinic?->name ?? 'nuestro consultorio') . '. Ahí pone su contraseña y listo: '
            . route('invitation.accept', ['token' => $invitacion->token]);

        return 'https://wa.me/?text=' . urlencode($texto);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    protected static ?string $model = DoctorInvitation::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationLabel = 'Su equipo';

    protected static ?string $modelLabel = 'Invitación';

    protected static ?string $pluralModelLabel = 'Invitaciones';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Consultorio';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('¿A quién invita?')
                    ->description('Le llega una liga para poner su contraseña. También se la puede mandar por WhatsApp desde la lista.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Radio::make('role')
                            ->label('Va a entrar como')
                            ->options(fn () => static::puedeInvitarDoctores()
                                ? ['staff' => 'Asistente o recepcionista', 'doctor' => 'Doctor']
                                : ['staff' => 'Asistente o recepcionista'])
                            ->default('staff')
                            ->required()
                            ->in(fn () => static::puedeInvitarDoctores() ? ['staff', 'doctor'] : ['staff'])
                            ->validationMessages(['in' => 'Para invitar a otro doctor se necesita el plan Pro, con lugar libre (hasta 3 doctores).'])
                            ->live()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->placeholder(fn (Forms\Get $get) => $get('role') === 'doctor' ? 'Dra. Paola Ruiz' : 'Lupita'),
                        Forms\Components\TextInput::make('email')
                            ->label('Correo (con él entra)')
                            ->email()
                            ->required()
                            ->unique('users', 'email')
                            ->validationMessages(['unique' => 'Ese correo ya tiene cuenta en DocFácil.'])
                            ->placeholder('nombre@gmail.com'),
                        Forms\Components\TextInput::make('specialty')
                            ->label('Especialidad')
                            ->placeholder('Ej: Ortodoncia, Endodoncia')
                            ->visible(fn (Forms\Get $get) => $get('role') === 'doctor'),
                        Forms\Components\Toggle::make('ve_dinero')
                            ->label('¿Puede ver el corte, los gastos y los ingresos?')
                            ->helperText('Los cobros de los pacientes los ve siempre, para poder cobrar. Esto es lo demás del dinero del consultorio.')
                            ->default(false)
                            ->visible(fn (Forms\Get $get) => $get('role') !== 'doctor')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Entra como')
                    ->formatStateUsing(fn (?string $state) => $state === 'staff' ? 'Asistente' : 'Doctor'),
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
                Tables\Actions\Action::make('whatsapp')
                    ->label('Mandar por WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (DoctorInvitation $record) => static::ligaDeWhatsApp($record), shouldOpenInNewTab: true)
                    ->visible(fn (DoctorInvitation $record) => $record->isPending()),
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
