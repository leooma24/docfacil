<?php

namespace App\Filament\Doctor\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use PragmaRX\Google2FAQRCode\Google2FA;

class Security extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Seguridad';

    protected static ?string $title = 'Seguridad de la cuenta';

    protected static ?string $slug = 'security';

    protected static string $view = 'filament.doctor.pages.security';

    protected static ?string $navigationGroup = 'Mi cuenta';

    public ?string $secret = null;
    public ?string $qrCodeSvg = null;
    public string $verificationCode = '';

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
                ->body('Revisa el código de tu app y vuelve a intentarlo.')
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
            ->body('A partir de ahora te pediremos un código al iniciar sesión.')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }

    public function disable2FA(): void
    {
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
