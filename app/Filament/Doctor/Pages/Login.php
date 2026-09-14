<?php

namespace App\Filament\Doctor\Pages;

use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;

/**
 * Entrada al panel del doctor, con el segundo paso cuando tiene 2FA.
 *
 * La pantalla de Seguridad dejaba activar el código de la app y prometía "te
 * pediremos un código al iniciar sesión", pero aquí nunca se pedía: con la
 * contraseña bastaba. Ahora, si la cuenta tiene 2FA, la contraseña correcta
 * no abre la sesión hasta que escribe el código.
 */
class Login extends BaseLogin
{
    /** Quién ya puso bien su contraseña y le falta el código. Vive en la sesión, no en el navegador. */
    private const PENDIENTE = 'doctor_login_2fa_pendiente';

    /** Minutos para escribir el código antes de volver a pedir la contraseña. */
    private const MINUTOS_PARA_EL_CODIGO = 5;

    public bool $pidiendoCodigo = false;

    public function getBrandLogo(): string|Htmlable|null
    {
        return asset('images/logo_doc_facil.png');
    }

    public function mount(): void
    {
        parent::mount();

        if (session()->has('demo_credentials')) {
            $creds = session('demo_credentials');
            $this->form->fill([
                'email' => $creds['email'] ?? '',
                'password' => $creds['password'] ?? '',
                'remember' => false,
            ]);
        }
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if ($this->pidiendoCodigo) {
            return $this->entrarConCodigo((string) ($data['code'] ?? ''));
        }

        // Se revisa la contraseña sin abrir la sesión: si tiene 2FA, la
        // sesión no debe existir ni un instante antes del código.
        $credenciales = $this->getCredentialsFromFormData($data);
        $proveedor = Filament::auth()->getProvider();
        $user = $proveedor->retrieveByCredentials($credenciales);

        if (! $user || ! $proveedor->validateCredentials($user, $credenciales)) {
            $this->throwFailureValidationException();
        }

        if (($user instanceof FilamentUser) && ! $user->canAccessPanel(Filament::getCurrentPanel())) {
            $this->throwFailureValidationException();
        }

        $recordar = (bool) ($data['remember'] ?? false);

        if ($user instanceof User && $user->hasTwoFactorEnabled()) {
            session()->put(self::PENDIENTE, [
                'id' => $user->getKey(),
                'recordar' => $recordar,
                'hasta' => now()->addMinutes(self::MINUTOS_PARA_EL_CODIGO)->timestamp,
            ]);

            $this->pidiendoCodigo = true;

            return null;
        }

        return $this->abrirSesion($user, $recordar);
    }

    private function entrarConCodigo(string $codigo): ?LoginResponse
    {
        $pendiente = session()->get(self::PENDIENTE);

        if (! is_array($pendiente) || ($pendiente['hasta'] ?? 0) < now()->timestamp) {
            $this->empezarDeNuevo();

            throw ValidationException::withMessages([
                'data.email' => 'Pasó mucho tiempo. Vuelve a escribir tu correo y contraseña.',
            ]);
        }

        $user = User::find($pendiente['id']);
        $codigo = preg_replace('/\D/', '', $codigo);

        $valido = $user
            && $user->hasTwoFactorEnabled()
            && strlen($codigo) === 6
            && (new Google2FA())->verifyKey($user->two_factor_secret, $codigo);

        if (! $valido) {
            throw ValidationException::withMessages([
                'data.code' => 'Ese código no coincide. Escribe el que muestra tu app en este momento.',
            ]);
        }

        session()->forget(self::PENDIENTE);

        return $this->abrirSesion($user, (bool) $pendiente['recordar']);
    }

    private function abrirSesion($user, bool $recordar): LoginResponse
    {
        Filament::auth()->login($user, $recordar);

        session()->regenerate();

        return app(LoginResponse::class);
    }

    public function empezarDeNuevo(): void
    {
        session()->forget(self::PENDIENTE);
        $this->pidiendoCodigo = false;
        $this->form->fill();
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()->hidden(fn () => $this->pidiendoCodigo);
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()->hidden(fn () => $this->pidiendoCodigo);
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()->hidden(fn () => $this->pidiendoCodigo);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                        TextInput::make('code')
                            ->label('Código de tu app de autenticación')
                            ->helperText('Abre Google Authenticator, Authy o la app que usaste y escribe los 6 dígitos.')
                            ->placeholder('000000')
                            ->maxLength(6)
                            ->autocomplete('one-time-code')
                            ->extraInputAttributes(['inputmode' => 'numeric', 'autofocus' => true])
                            ->required()
                            ->visible(fn () => $this->pidiendoCodigo),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction()
                ->label(fn () => $this->pidiendoCodigo ? 'Entrar' : __('filament-panels::pages/auth/login.form.actions.authenticate.label')),
            Action::make('empezarDeNuevo')
                ->label('Usar otra cuenta')
                ->link()
                ->color('gray')
                ->action('empezarDeNuevo')
                ->visible(fn () => $this->pidiendoCodigo),
        ];
    }

    public function getHeading(): string|Htmlable
    {
        return $this->pidiendoCodigo ? 'Escribe tu código' : parent::getHeading();
    }
}
