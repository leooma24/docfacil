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
use Illuminate\Support\Facades\Mail;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                Forms\Components\Section::make('Datos del Consultorio')
                    ->schema([
                        Forms\Components\TextInput::make('clinic_name')
                            ->label('Nombre del consultorio')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('specialty')
                            ->label('Especialidad')
                            ->placeholder('Ej: Odontología, Medicina General, Pediatría')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('clinic_phone')
                            ->label('Teléfono del consultorio')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('referral_code')
                            ->label('Código de referido (opcional)')
                            ->placeholder('Ej: DRGARCIA123')
                            ->maxLength(20)
                            ->default(request()->query('ref'))
                            ->helperText('Si un colega te invitó, pon su código y ambos reciben 15 días gratis extra'),
                    ]),
            ]);
    }

    protected function handleRegistration(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Create clinic
        $clinic = Clinic::create([
            'name' => $data['clinic_name'],
            'phone' => $data['clinic_phone'] ?? null,
            'plan' => 'free',
            'trial_ends_at' => now()->addDays(15),
        ]);

        // Create user
        $user = $this->getUserModel()::forceCreate([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        // Create doctor profile
        Doctor::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'specialty' => $data['specialty'] ?? null,
        ]);

        // Create prospect for CRM tracking
        Prospect::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['clinic_phone'] ?? null,
            'clinic_name' => $data['clinic_name'],
            'specialty' => $data['specialty'] ?? null,
            'source' => 'landing',
            'status' => 'trial',
        ]);

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

        return $user;
    }
}
