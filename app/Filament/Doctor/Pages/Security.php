<?php

namespace App\Filament\Doctor\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use PragmaRX\Google2FAQRCode\Google2FA;

class Security extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Seguridad';

    protected static ?string $title = 'Seguridad de la cuenta';

    protected static ?string $slug = 'seguridad';

    protected static string $view = 'filament.doctor.pages.security';

    protected static ?string $navigationGroup = 'Mi cuenta';

    public ?string $secret = null;
    public ?string $qrCodeSvg = null;
    public string $verificationCode = '';

    /**
     * El dueño pide la verificación en dos pasos a todo el equipo (día 8 del
     * plan, 10-oct-2026). Quien no la tenga, al entrar llega aquí a activarla.
     */
    public function exigirATodos(bool $si): void
    {
        $usuario = auth()->user();
        if (! $usuario->esDuenoDelConsultorio()) {
            return;
        }
        if ($si && ! $usuario->hasTwoFactorEnabled()) {
            \Filament\Notifications\Notification::make()->title('Primero actívela usted')->body('Así no se queda fuera de su propio consultorio.')->warning()->send();

            return;
        }

        $usuario->clinic->update(['exigir_dos_pasos' => $si]);
        \Filament\Notifications\Notification::make()
            ->title($si ? 'Ahora todo su equipo la necesita para entrar' : 'Ya no se le pide a todo el equipo')
            ->success()->send();
    }

    public function mount(): void
    {
        // Si ya tiene 2FA habilitado, no generar secret nuevo
        if (auth()->user()->hasTwoFactorEnabled()) {
            return;
        }

        $google2fa = new Google2FA();

        // Generar secret si no existe aún
        if (empty(auth()->user()->two_factor_secret)) {
            $secret = $google2fa->generateSecretKey();
            auth()->user()->forceFill(['two_factor_secret' => $secret])->saveQuietly();
        }

        $this->secret = auth()->user()->two_factor_secret;
        $this->qrCodeSvg = $google2fa->getQRCodeInline(
            'DocFácil',
            auth()->user()->email,
            $this->secret
        );
    }

    public function enable2FA(): void
    {
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey(
            auth()->user()->two_factor_secret,
            $this->verificationCode
        );

        if (!$valid) {
            Notification::make()
                ->title('Código inválido')
                ->body('Revise el código de su app y vuelva a intentarlo.')
                ->danger()
                ->send();
            return;
        }

        auth()->user()->forceFill([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ])->saveQuietly();

        Notification::make()
            ->title('2FA habilitado correctamente')
            ->body('A partir de ahora le pediremos un código al iniciar sesión.')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }

    public function disable2FA(): void
    {
        // Apagarlo pide el código, igual que encenderlo. Antes bastaba un clic:
        // quien se sentara en la computadora del consultorio con la sesión
        // abierta quitaba el segundo factor y se quedaba con la cuenta.
        $google2fa = new Google2FA();

        $valido = $this->verificationCode
            && $google2fa->verifyKey(auth()->user()->two_factor_secret, $this->verificationCode);

        if (! $valido) {
            Notification::make()
                ->title('Escriba su código para desactivarlo')
                ->body('Por seguridad, necesitamos el código de su app para apagar la verificación en dos pasos.')
                ->danger()
                ->send();
            return;
        }

        auth()->user()->forceFill([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ])->saveQuietly();

        Notification::make()
            ->title('2FA deshabilitado')
            ->warning()
            ->send();

        $this->redirect(static::getUrl());
    }
}
