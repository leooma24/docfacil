<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use Filament\Widgets\Widget;

/**
 * Checklist de configuración inicial del consultorio. Muestra % completado y
 * los pasos pendientes con CTA directo. Se oculta automáticamente al 100%
 * (o cuando el doctor lo dismiss-ea por session).
 *
 * Pasos rastreados:
 *  - logo de clínica
 *  - 5+ servicios
 *  - 1+ paciente
 *  - 1+ cita
 *  - 1+ pago registrado (señal de uso real, no solo setup)
 */
class SetupChecklistWidget extends Widget
{
    protected static string $view = 'filament.doctor.widgets.setup-checklist';

    protected int|string|array $columnSpan = 'full';

    // Justo debajo del DashboardHeroWidget (sort -10) y arriba del resto
    protected static ?int $sort = -8;

    public static function canView(): bool
    {
        $clinic = auth()->user()?->clinic;
        if (! $clinic) {
            return false;
        }

        // No mostrar si el doctor lo dismiss-eó esta sesión
        if (session()->get('df_setup_checklist_hidden')) {
            return false;
        }

        // Ocultar al 100%
        return self::computeProgress($clinic->id, $clinic->logo)['percent'] < 100;
    }

    public function getViewData(): array
    {
        $clinic = auth()->user()->clinic;
        $progress = self::computeProgress($clinic->id, $clinic->logo);

        return [
            'percent' => $progress['percent'],
            'items' => $progress['items'],
            'completed_count' => $progress['completed_count'],
            'total_count' => $progress['total_count'],
        ];
    }

    public function dismiss(): void
    {
        session()->put('df_setup_checklist_hidden', true);
    }

    private static function computeProgress(int $clinicId, ?string $logo): array
    {
        $servicesCount = Service::where('clinic_id', $clinicId)->where('is_active', true)->count();
        $patientsCount = Patient::where('clinic_id', $clinicId)->count();
        $appointmentsCount = Appointment::where('clinic_id', $clinicId)->count();
        $paymentsCount = Payment::where('clinic_id', $clinicId)->count();

        $items = [
            [
                'key' => 'logo',
                'title' => 'Sube el logo de tu consultorio',
                'desc' => 'Aparece en recetas y portal público.',
                'done' => ! empty($logo),
                'cta' => 'Subir logo',
                'url' => \App\Filament\Doctor\Pages\ClinicSettings::getUrl(),
                'icon' => '🏥',
            ],
            [
                'key' => 'services',
                'title' => 'Configura al menos 5 servicios',
                'desc' => $servicesCount > 0
                    ? "Tienes {$servicesCount} de 5 mínimos sugeridos."
                    : 'Limpieza, consulta, extracciones, etc. Cada uno con su precio.',
                'done' => $servicesCount >= 5,
                'cta' => $servicesCount > 0 ? 'Agregar más' : 'Agregar servicios',
                'url' => \App\Filament\Doctor\Resources\ServiceResource::getUrl('index'),
                'icon' => '🦷',
            ],
            [
                'key' => 'patient',
                'title' => 'Agrega tu primer paciente',
                'desc' => $patientsCount > 0
                    ? "Tienes {$patientsCount} " . ($patientsCount === 1 ? 'paciente' : 'pacientes') . '.'
                    : 'Solo nombre y teléfono. Lo demás lo llenas en su expediente.',
                'done' => $patientsCount > 0,
                'cta' => $patientsCount > 0 ? 'Ver pacientes' : 'Crear paciente',
                'url' => $patientsCount > 0
                    ? \App\Filament\Doctor\Resources\PatientResource::getUrl('index')
                    : \App\Filament\Doctor\Resources\PatientResource::getUrl('create'),
                'icon' => '👤',
            ],
            [
                'key' => 'appointment',
                'title' => 'Crea tu primera cita',
                'desc' => $appointmentsCount > 0
                    ? "Tienes {$appointmentsCount} " . ($appointmentsCount === 1 ? 'cita agendada' : 'citas agendadas') . '.'
                    : 'Selecciona paciente, fecha y servicio.',
                'done' => $appointmentsCount > 0,
                'cta' => $appointmentsCount > 0 ? 'Ver agenda' : 'Crear cita',
                'url' => $appointmentsCount > 0
                    ? \App\Filament\Doctor\Resources\AppointmentResource::getUrl('index')
                    : \App\Filament\Doctor\Resources\AppointmentResource::getUrl('create'),
                'icon' => '📅',
            ],
            [
                'key' => 'payment',
                'title' => 'Registra tu primer cobro',
                'desc' => $paymentsCount > 0
                    ? "Tienes {$paymentsCount} cobros registrados."
                    : 'Desde la cita o desde Cobros. Mándalo por WhatsApp en 1 clic.',
                'done' => $paymentsCount > 0,
                'cta' => $paymentsCount > 0 ? 'Ver cobros' : 'Registrar cobro',
                'url' => \App\Filament\Doctor\Resources\PaymentResource::getUrl('index'),
                'icon' => '💰',
            ],
        ];

        $completedCount = collect($items)->where('done', true)->count();
        $totalCount = count($items);
        $percent = (int) round(($completedCount / $totalCount) * 100);

        return [
            'items' => $items,
            'completed_count' => $completedCount,
            'total_count' => $totalCount,
            'percent' => $percent,
        ];
    }
}
