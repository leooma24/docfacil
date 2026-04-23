<?php

namespace App\Filament\Doctor\Widgets;

use Filament\Widgets\Widget;

/**
 * Widget viral: le muestra al doctor su URL publica de reservas lista
 * para compartir en Instagram bio, WhatsApp status, firma de email, QR
 * en recepcion. Cada paciente que use la URL ve la marca del consultorio
 * y el badge 'Powered by DocFacil' al pie = acquisition loop gratis.
 *
 * Feature-gated por public_booking (Pro+).
 */
class PublicPortalShareWidget extends Widget
{
    protected static string $view = 'filament.doctor.widgets.public-portal-share';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $clinic = auth()->user()?->clinic;
        return (bool) ($clinic && $clinic->hasFeature('public_booking') && $clinic->slug);
    }

    public function getViewData(): array
    {
        $clinic = auth()->user()->clinic;
        $publicUrl = route('public.booking.show', $clinic->slug);

        // QR code via qrserver.com (servicio publico gratis, SVG renderable inline)
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=M&data=' . urlencode($publicUrl);

        $shareText = "¡Agenda tu cita en {$clinic->name} en línea! 👇";
        $whatsappShareUrl = 'https://wa.me/?text=' . urlencode("{$shareText}\n{$publicUrl}");

        return [
            'clinic' => $clinic,
            'publicUrl' => $publicUrl,
            'qrUrl' => $qrUrl,
            'whatsappShareUrl' => $whatsappShareUrl,
        ];
    }
}
