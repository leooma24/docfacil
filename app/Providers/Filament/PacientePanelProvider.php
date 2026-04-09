<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PacientePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('paciente')
            ->path('paciente')
            ->login()
            ->brandName('DocFácil - Portal Paciente')
            ->brandLogo(asset('images/solo_logo_white.png'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('favicon-32x32.png'))
            ->colors([
                'primary' => Color::Teal,
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->renderHook('panels::head.end', fn () => view('filament.custom.theme-styles'))
            ->discoverResources(in: app_path('Filament/Paciente/Resources'), for: 'App\\Filament\\Paciente\\Resources')
            ->discoverPages(in: app_path('Filament/Paciente/Pages'), for: 'App\\Filament\\Paciente\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Paciente/Widgets'), for: 'App\\Filament\\Paciente\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
