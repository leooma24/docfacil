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

        $cedula = \App\Models\Doctor::where('clinic_id', $clinicId)->whereNotNull('license_number')->where('license_number', '!=', '')->exists();

        $items = [
            // Los servicios ya los pone el registro y el logo es opcional
            // (11-oct-2026): queda lo que sí hace falta para trabajar.
            // Lo primero que se ofrece es subir el Excel, no capturar uno por
            // uno: el dentista que ya tiene 300 pacientes en una hoja no va a
            // empezar tecleando el primero. `?action=import` abre directo la
            // ventana del importador en la lista de pacientes.
            [
                'key' => 'patient',
                'title' => 'Suba sus pacientes',
                'desc' => $patientsCount > 0
                    ? "Tiene {$patientsCount} " . ($patientsCount === 1 ? 'paciente' : 'pacientes') . '.'
                    : '¿Viene de otro sistema o de Excel? Traiga sus pacientes, sus citas que vienen y lo que le deben. Si no, capture el primero: solo nombre y teléfono.',
                'done' => $patientsCount > 0,
                'cta' => $patientsCount > 0 ? 'Ver pacientes' : 'Traer mis datos',
                // «Traer sus datos» (10-oct-2026): pacientes, citas y saldos en
                // una sola pantalla, en ese orden.
                'url' => $patientsCount > 0
                    ? \App\Filament\Doctor\Resources\PatientResource::getUrl('index')
                    : \App\Filament\Doctor\Pages\TraerMisDatos::getUrl(),
                'cta2' => $patientsCount > 0 ? null : 'Crear uno',
                'url2' => $patientsCount > 0 ? null : \App\Filament\Doctor\Resources\PatientResource::getUrl('create'),
                'icon' => 'user-group',
            ],
            [
                'key' => 'appointment',
                'title' => 'Cree su primera cita',
                'desc' => $appointmentsCount > 0
                    ? "Tiene {$appointmentsCount} " . ($appointmentsCount === 1 ? 'cita agendada' : 'citas agendadas') . '.'
                    : 'Seleccione paciente, fecha y servicio.',
                'done' => $appointmentsCount > 0,
                'cta' => $appointmentsCount > 0 ? 'Ver agenda' : 'Crear cita',
                'url' => $appointmentsCount > 0
                    ? \App\Filament\Doctor\Resources\AppointmentResource::getUrl('index')
                    : \App\Filament\Doctor\Resources\AppointmentResource::getUrl('create'),
                'icon' => 'calendar-days',
            ],
            [
                'key' => 'payment',
                'title' => 'Registre su primer cobro',
                'desc' => $paymentsCount > 0
                    ? "Tiene {$paymentsCount} cobros registrados."
                    : 'Desde la cita o desde Cobros. Mándelo por WhatsApp en 1 clic.',
                'done' => $paymentsCount > 0,
                'cta' => $paymentsCount > 0 ? 'Ver cobros' : 'Registrar cobro',
                'url' => \App\Filament\Doctor\Resources\PaymentResource::getUrl('index'),
                'icon' => 'banknotes',
            ],
            [
                'key' => 'cedula',
                'title' => 'Ponga su cédula profesional',
                'desc' => $cedula ? 'Ya sale en sus recetas.' : 'Para que salga en sus recetas.',
                'done' => $cedula,
                'cta' => $cedula ? 'Ver mi perfil' : 'Poner mi cédula',
                'url' => \App\Filament\Doctor\Pages\PerfilProfesional::getUrl(),
                'icon' => 'identification',
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
