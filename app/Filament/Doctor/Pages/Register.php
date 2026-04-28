<?php

namespace App\Filament\Doctor\Pages;

use App\Mail\WelcomeOnboardingMail;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Prospect;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Register extends BaseRegister
{
    /**
     * Honeypot field — bots typically fill every visible input. If this is
     * non-empty at submit time, we reject silently (record in the log for
     * later analysis, but don't signal to the bot why).
     */
    public ?string $website_url_backup = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent()->default(request()->query('name')),
                $this->getEmailFormComponent()->default(request()->query('email')),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                Forms\Components\TextInput::make('website_url_backup')
                    ->label('')
                    ->extraAttributes([
                        'autocomplete' => 'off',
                        'tabindex' => '-1',
                        'aria-hidden' => 'true',
                    ])
                    ->extraInputAttributes([
                        'autocomplete' => 'off',
                    ])
                    ->extraFieldWrapperAttributes([
                        'style' => 'position:absolute;left:-9999px;top:-9999px;height:0;width:0;overflow:hidden;',
                        'aria-hidden' => 'true',
                    ])
                    ->dehydrated(true),
                Forms\Components\Section::make('Datos del Consultorio')
                    ->schema([
                        Forms\Components\TextInput::make('clinic_name')
                            ->label('Nombre del consultorio')
                            ->required()
                            ->default(request()->query('clinic_name'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('specialty')
                            ->label('Especialidad')
                            ->placeholder('Ej: Odontología, Medicina General, Pediatría')
                            ->default(request()->query('specialty'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('license_number')
                            ->label('Cédula profesional')
                            ->required()
                            ->helperText('Obligatoria por NOM-004-SSA3-2012 para emitir recetas y expedientes')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('clinic_phone')
                            ->label('Teléfono del consultorio')
                            ->tel()
                            ->default(request()->query('phone'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->label('Ciudad')
                            ->placeholder('Ej: Culiacán')
                            ->default(request()->query('city'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('referral_code')
                            ->label('Código de referido (opcional)')
                            ->placeholder('Ej: DRGARCIA123')
                            ->maxLength(20)
                            ->default(request()->query('ref'))
                            ->helperText('Si un colega te invitó, pon su código y ambos reciben 15 días gratis extra'),
                    ]),
                Forms\Components\Checkbox::make('terms_accepted')
                    ->label(new \Illuminate\Support\HtmlString(
                        'He leído y acepto los <a href="/terminos" target="_blank" class="text-teal-600 underline">Términos y Condiciones</a> ' .
                        'y el <a href="/privacidad" target="_blank" class="text-teal-600 underline">Aviso de Privacidad</a>. ' .
                        'Me comprometo a obtener el consentimiento expreso de mis pacientes antes de cargar sus datos.'
                    ))
                    ->accepted()
                    ->required()
                    ->validationMessages([
                        'accepted' => 'Debes aceptar los Términos y el Aviso de Privacidad para registrarte.',
                    ]),
            ]);
    }

    protected function handleRegistration(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Honeypot: humanos NO llenan website_url_backup (esta oculto).
        // Los bots si. Si llega con valor lo loggeamos y abortamos con mensaje
        // generico para no revelar el honeypot.
        if (!empty($data['website_url_backup'])) {
            Log::warning('Registration honeypot triggered', [
                'email' => $data['email'] ?? null,
                'ip' => request()->ip(),
                'ua' => request()->userAgent(),
            ]);
            throw new \Illuminate\Validation\ValidationException(
                \Illuminate\Support\Facades\Validator::make([], [])
                    ->after(fn ($v) => $v->errors()->add('email', 'No pudimos procesar tu registro. Intenta de nuevo en unos minutos.'))
            );
        }

        // Detectar si viene de un vendedor via ?vnd=CODIGO
        // Validamos formato del código para evitar enumeración con SQL caro:
        // el formato real es VND-XXXXX## (4-20 chars alfanuméricos con guion).
        $salesRep = null;
        $vndCode = request()->query('vnd');
        if ($vndCode && is_string($vndCode) && preg_match('/^VND-[A-Z0-9]{3,16}$/i', $vndCode)) {
            $salesRep = \App\Models\User::where('role', 'sales')
                ->where('sales_rep_code', strtoupper($vndCode))
                ->where('is_active_sales_rep', true)
                ->first();
        }

        // Create clinic (sold_by_user_id NO está en fillable — requiere forceFill)
        $clinic = new Clinic();
        $clinic->forceFill([
            'name' => $data['clinic_name'],
            'phone' => $data['clinic_phone'] ?? null,
            'city' => $data['city'] ?? null,
            'plan' => 'free',
            'trial_ends_at' => now()->addDays(15),
            'sold_by_user_id' => $salesRep?->id,
            'sold_at' => $salesRep ? now() : null,
        ])->save();

        // Email verification skip si vino de un link trackeado de correo:
        // TrackController::click puso 'prospect_email_verified' en sesion al
        // validar el HMAC del token. Si el email del registro matchea el
        // del prospect que dio clic, marcamos email_verified_at=now() para
        // saltar el paso de verificacion (ya demostro acceso al inbox).
        $verifiedFromTrackedClick = false;
        $sessionVerified = session('prospect_email_verified');
        if (is_array($sessionVerified)
            && !empty($sessionVerified['email'])
            && strcasecmp($sessionVerified['email'], $data['email']) === 0) {
            $verifiedFromTrackedClick = true;
            session()->forget('prospect_email_verified');
        }

        // Create user
        $user = $this->getUserModel()::forceCreate([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
            'terms_accepted_at' => now(), // LFPDPPP art. 9
            'email_verified_at' => $verifiedFromTrackedClick ? now() : null,
        ]);

        // Create doctor profile
        Doctor::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'specialty' => $data['specialty'] ?? null,
            'license_number' => $data['license_number'],
        ]);

        // Create/update prospect for CRM tracking
        $existingProspect = Prospect::where('email', $data['email'])->first();
        if ($existingProspect) {
            $existingProspect->update(array_filter([
                'status' => 'converted',
                'converted_at' => now(),
                'converted_clinic_id' => $clinic->id,
                'phone' => $existingProspect->phone ?: ($data['clinic_phone'] ?? null),
                'city' => $existingProspect->city ?: ($data['city'] ?? null),
                'clinic_name' => $existingProspect->clinic_name ?: $data['clinic_name'],
                'specialty' => $existingProspect->specialty ?: ($data['specialty'] ?? null),
            ], fn ($v) => $v !== null));
        } else {
            Prospect::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['clinic_phone'] ?? null,
                'clinic_name' => $data['clinic_name'],
                'specialty' => $data['specialty'] ?? null,
                'city' => $data['city'] ?? null,
                'source' => $salesRep ? 'prospecting' : 'landing',
                'status' => 'converted',
                'converted_at' => now(),
                'converted_clinic_id' => $clinic->id,
                'assigned_to_sales_rep_id' => $salesRep?->id,
            ]);
        }

        // Process referral if code provided
        if (!empty($data['referral_code'])) {
            \App\Models\Referral::processReferral($user, $data['referral_code']);
        }

        // Send welcome email
        try {
            Mail::to($user->email)->send(new WelcomeOnboardingMail($user));
        } catch (\Exception $e) {
            // Don't block registration if email fails
        }

        // Analytics: flash event para que el partial lo dispare en el proximo
        // pageload (sera el dashboard/onboarding del doctor recien creado).
        session()->push('analytics_events', [
            'name' => 'signup_completed',
            'params' => [
                'method' => 'email',
                'from_email_track' => $verifiedFromTrackedClick,
                'has_sales_rep' => $salesRep !== null,
                'plan' => 'free',
            ],
        ]);

        return $user;
    }
}
