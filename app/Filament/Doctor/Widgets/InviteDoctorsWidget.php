<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Doctor;
use App\Models\DoctorInvitation;
use Filament\Widgets\Widget;

/**
 * Widget del escritorio que aparece cuando:
 * - El plan tiene multi_doctor (Pro o Clinica)
 * - Aun quedan slots por llenar (current + pending invitations < max)
 *
 * Cuando los 3 doctores estan llenos en Pro -> no se renderiza.
 * En Clinica (unlimited_doctors) siempre invita a sumar mas.
 */
class InviteDoctorsWidget extends Widget
{
    protected static string $view = 'filament.doctor.widgets.invite-doctors';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        $clinic = auth()->user()?->clinic;
        if (!$clinic || !$clinic->hasFeature('multi_doctor')) {
            return false;
        }

        // Si tiene unlimited_doctors (plan Clinica), siempre se puede invitar mas.
        if ($clinic->hasFeature('unlimited_doctors')) {
            return true;
        }

        // Plan Pro: max 3 doctores. Sumamos doctores activos + invitaciones
        // pendientes para no permitir over-invitar.
        return self::availableSlots() > 0;
    }

    public function getViewData(): array
    {
        $clinic = auth()->user()->clinic;
        $current = Doctor::where('clinic_id', $clinic->id)->count();
        $pending = DoctorInvitation::where('clinic_id', $clinic->id)
            ->where('status', 'pending')
            ->count();

        $isUnlimited = $clinic->hasFeature('unlimited_doctors');
        $max = $isUnlimited ? null : 3;
        $slotsLeft = $isUnlimited ? null : max(0, $max - $current - $pending);

        return [
            'current' => $current,
            'pending' => $pending,
            'max' => $max,
            'slotsLeft' => $slotsLeft,
            'isUnlimited' => $isUnlimited,
            'inviteUrl' => route('filament.doctor.resources.invitar-doctores.create'),
            'manageUrl' => route('filament.doctor.resources.invitar-doctores.index'),
        ];
    }

    /**
     * Helper estatico para canView() — solo plan Pro (no unlimited).
     */
    private static function availableSlots(): int
    {
        $clinic = auth()->user()->clinic;
        $current = Doctor::where('clinic_id', $clinic->id)->count();
        $pending = DoctorInvitation::where('clinic_id', $clinic->id)
            ->where('status', 'pending')
            ->count();
        return max(0, 3 - $current - $pending);
    }
}
