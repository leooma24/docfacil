<?php

namespace App\Filament\Doctor\Pages;

use App\Filament\Doctor\Concerns\GatedByPlanFeature;
use Filament\Pages\Page;

class CheckInQR extends Page
{
    use GatedByPlanFeature;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Check-in QR';

    protected static ?string $title = 'Check-in por QR';

    protected static ?string $slug = 'check-in-qr';

    protected static string $view = 'filament.doctor.pages.check-in-qr';

    protected static ?string $navigationGroup = 'Consultorio';

    protected static ?int $navigationSort = 50;

    protected static function planFeature(): string
    {
        return 'qr_checkin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::clinicHasPlanFeature();
    }

    public static function canAccess(): bool
    {
        return static::clinicHasPlanFeature();
    }

    public function getCheckInUrl(): string
    {
        $clinic = auth()->user()->clinic;

        // Firmada y sin caducidad: el QR se imprime una vez y vive pegado en
        // la recepcion, pero la direccion deja de ser adivinable a partir del
        // nombre del consultorio.
        return \Illuminate\Support\Facades\URL::signedRoute('checkin.show', ['slug' => $clinic->slug]);
    }

    public function getQrCodeUrl(): string
    {
        $url = urlencode($this->getCheckInUrl());
        return "https://quickchart.io/qr?text={$url}&size=400&margin=2&ecLevel=M";
    }
}
