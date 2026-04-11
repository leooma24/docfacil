<?php

namespace App\Filament\Doctor\Pages;

use Filament\Pages\Page;

class CheckInQR extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Check-in QR';

    protected static ?string $title = 'Check-in por QR';

    protected static ?string $slug = 'check-in-qr';

    protected static string $view = 'filament.doctor.pages.check-in-qr';

    protected static ?string $navigationGroup = 'Consultorio';

    protected static ?int $navigationSort = 50;

    public function getCheckInUrl(): string
    {
        $clinic = auth()->user()->clinic;
        return route('checkin.show', ['slug' => $clinic->slug]);
    }

    public function getQrCodeUrl(): string
    {
        $url = urlencode($this->getCheckInUrl());
        return "https://quickchart.io/qr?text={$url}&size=400&margin=2&ecLevel=M";
    }
}
