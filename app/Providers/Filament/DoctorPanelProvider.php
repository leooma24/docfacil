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

class DoctorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('doctor')
            ->path('doctor')
            ->login(\App\Filament\Doctor\Pages\Login::class)
            ->registration(\App\Filament\Doctor\Pages\Register::class)
            ->emailVerification(\Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt::class)
            ->brandName('DocFácil')
            ->brandLogo(asset('images/solo_logo_white.png'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('favicon-32x32.png'))
            ->colors([
                'primary' => Color::Teal,
            ])
            ->font('Inter')
            ->plugin(\Saade\FilamentFullCalendar\FilamentFullCalendarPlugin::make())
            ->sidebarCollapsibleOnDesktop()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchFieldSuffix(fn () => 'Ctrl+K')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->renderHook('panels::head.end', fn () => view('filament.custom.theme-styles'))
            ->renderHook('panels::body.end', fn () => \Livewire\Livewire::mount('assistant-chat') . \Livewire\Livewire::mount('command-palette'))
            ->discoverResources(in: app_path('Filament/Doctor/Resources'), for: 'App\\Filament\\Doctor\\Resources')
            ->discoverPages(in: app_path('Filament/Doctor/Pages'), for: 'App\\Filament\\Doctor\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Doctor/Widgets'), for: 'App\\Filament\\Doctor\\Widgets')
            ->widgets([
                \App\Filament\Doctor\Widgets\DashboardHeroWidget::class,
                \App\Filament\Doctor\Widgets\AIInsightsWidget::class,
                \App\Filament\Doctor\Widgets\NextAppointment::class,
                \App\Filament\Doctor\Widgets\QuickActions::class,
                \App\Filament\Doctor\Widgets\StatsOverview::class,
                \App\Filament\Doctor\Widgets\TodayAppointments::class,
                \App\Filament\Doctor\Widgets\AppointmentsChart::class,
                \App\Filament\Doctor\Widgets\IncomeChart::class,
                \App\Filament\Doctor\Widgets\PendingPayments::class,
                \App\Filament\Doctor\Widgets\AlertsWidget::class,
                \App\Filament\Doctor\Widgets\TopServicesChart::class,
                \App\Filament\Doctor\Widgets\DoctorProductionWidget::class,
                \App\Filament\Doctor\Widgets\BirthdaysTodayWidget::class,
                \App\Filament\Doctor\Widgets\OverdueDebtorsWidget::class,
                \App\Filament\Doctor\Widgets\PendingRecallsWidget::class,
                \App\Filament\Doctor\Widgets\PublicPortalShareWidget::class,
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
                \App\Http\Middleware\VerifyClinicPlan::class,
                \App\Http\Middleware\DemoMode::class,
            ]);
    }
}
