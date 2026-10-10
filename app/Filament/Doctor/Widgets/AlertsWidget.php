<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use Filament\Widgets\Widget;

class AlertsWidget extends Widget
{
    protected static ?int $sort = -3;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.doctor.widgets.alerts-widget';

    public function getAlerts(): array
    {
        $user = auth()->user();
        $clinicId = $user->clinic_id;
        $alerts = [];

        // Patients without visit in 6+ months
        $inactivePatients = Patient::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->whereDoesntHave('appointments', function ($q) {
                $q->where('starts_at', '>=', now()->subMonths(6));
            })
            ->count();

        if ($inactivePatients > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-clock',
                'title' => "{$inactivePatients} pacientes sin visita",
                'desc' => 'Hace más de 6 meses que no vienen: vale la pena escribirles.',
            ];
        }

        // Overdue payments — con abono también cuentan: siguen debiendo.
        $overduePayments = Payment::where('clinic_id', $clinicId)
            ->withBalance()
            ->where('payment_date', '<', now()->subDays(7))
            ->count();

        if ($overduePayments > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'heroicon-o-exclamation-triangle',
                'title' => $overduePayments . ($overduePayments === 1 ? ' pago vencido' : ' pagos vencidos'),
                'desc' => 'Tienen más de 7 días pendientes.',
            ];
        }

        // Huecos de cancelaciones que alguien de la lista de espera podría
        // ocupar. Lleva directo a la lista filtrada para ese hueco.
        if (auth()->user()->clinic?->hasFeature('waitlist')) {
            foreach (Appointment::huecosLibres($clinicId)->take(2) as $hueco) {
                $candidatos = \App\Models\WaitlistEntry::candidatosPara($hueco)->count();
                if ($candidatos > 0) {
                    $cuando = $hueco->starts_at->isTomorrow() ? 'mañana' : ($hueco->starts_at->isToday() ? 'hoy' : $hueco->starts_at->locale('es')->isoFormat('dddd D'));
                    $alerts[] = [
                        'type' => 'warning',
                        'icon' => 'heroicon-o-user-group',
                        'title' => "Se liberó {$cuando} {$hueco->starts_at->format('H:i')} · {$candidatos} en lista de espera",
                        'desc' => 'Ofrézcale el horario a quien lo esperaba.',
                        'url' => $hueco->ligaAListaDeEspera(),
                    ];
                }
            }
        }

        // Lo que el paciente ya aceptó pero nadie ha agendado: dinero que se
        // queda en el presupuesto.
        $sinAgendar = \App\Models\TreatmentPlanItem::whereNull('completed_at')
            ->whereHas('treatmentPlan', fn ($q) => $q->where('clinic_id', $clinicId)->where('status', 'accepted'))
            ->get()
            ->reject(fn ($item) => $item->citaPendiente())
            ->count();

        if ($sinAgendar > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-calendar-days',
                'title' => $sinAgendar . ($sinAgendar === 1 ? ' tratamiento aceptado sin agendar' : ' tratamientos aceptados sin agendar'),
                'desc' => 'El paciente ya dijo que sí. Agéndelos desde su presupuesto.',
                'url' => \App\Filament\Doctor\Resources\TreatmentPlanResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'accepted']]], panel: 'doctor'),
            ];
        }

        // Laboratorio: que la paciente no llegue a su cita y la corona no esté.
        if ($user?->clinic?->hasFeature('laboratorio')) {
            foreach (\App\Models\LabOrder::enRiesgoDe($clinicId) as $orden) {
                $cita = $orden->appointment;
                $alerts[] = [
                    'type' => 'danger',
                    'icon' => 'heroicon-o-beaker',
                    'title' => "{$orden->trabajo} de {$orden->patient?->first_name} no ha llegado",
                    'desc' => 'Su cita es ' . ($cita->starts_at->isToday() ? 'hoy' : ($cita->starts_at->isTomorrow() ? 'mañana' : $cita->starts_at->locale('es')->isoFormat('dddd'))) . ' a las ' . $cita->starts_at->format('H:i') . '. Llámele al laboratorio ' . $orden->laboratorio . ' o reagéndela.',
                    'url' => \App\Filament\Doctor\Resources\LabOrderResource::getUrl('index', panel: 'doctor'),
                ];
            }

            $atrasadas = \App\Models\LabOrder::atrasadasDe($clinicId)->count();
            if ($atrasadas > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'heroicon-o-beaker',
                    'title' => $atrasadas . ($atrasadas === 1 ? ' trabajo atrasado con el laboratorio' : ' trabajos atrasados con el laboratorio'),
                    'desc' => 'Ya pasó la fecha que le prometieron.',
                    'url' => \App\Filament\Doctor\Resources\LabOrderResource::getUrl('index', panel: 'doctor'),
                ];
            }
        }

        // Presupuestos que se quedaron en "lo voy a pensar" y ya toca
        // recordarles (una vez al mes). Es dinero que se enfría.
        $presupuestos = \App\Support\PendientesDelConsultorio::cuantosTocan($clinicId);
        if ($presupuestos > 0 && $user?->clinic?->hasFeature('treatment_plans')) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-clipboard-document-list',
                'title' => $presupuestos . ($presupuestos === 1 ? ' presupuesto sin respuesta' : ' presupuestos sin respuesta'),
                'desc' => 'Ya pasó más de una semana. Un recordatorio amable a 1 clic.',
                'url' => \App\Filament\Doctor\Pages\PendientesPorPaciente::getUrl(panel: 'doctor'),
            ];
        }

        // Pacientes de mañana a los que falta recordarles. Se cuenta por
        // paciente (uno con tres citas recibe un solo mensaje) y no entran los
        // que ya confirmaron. Se quita al mandar el recordatorio.
        // La misma cuenta que la fila de recordatorios de mañana.
        $noReminder = \App\Support\RecordatorioDeCita::pendientesDeManana($clinicId)->count();

        if ($noReminder > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-chat-bubble-left-ellipsis',
                'title' => $noReminder . ($noReminder === 1 ? ' paciente mañana sin recordatorio' : ' pacientes mañana sin recordatorio'),
                'desc' => 'Tóquelo y se los va mandando uno por uno, sin buscarlos.',
                'url' => \App\Filament\Doctor\Pages\RecordatoriosDeManana::getUrl(panel: 'doctor'),
            ];
        }

        // El tope de pacientes, antes de toparse: en la prueba si ya pasó el
        // del Básico (para que elija sabiendo), y en el Básico cuando se acerca.
        $clinica = $user->clinic;
        if ($clinica && ! $user->esAsistente()) {
            $tieneBasico = \App\Models\Clinic::LIMITE_PACIENTES['basico'];
            $suyos = $clinica->pacientesActuales();
            if ($clinica->enPruebaVigente() && $suyos > $tieneBasico) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'heroicon-o-users',
                    'title' => "Ya tiene {$suyos} pacientes: el Básico llega a {$tieneBasico}",
                    'desc' => 'Al terminar la prueba, el Pro no tiene límite. No se borra ninguno: arriba del límite solo ya no puede agregar.',
                    'url' => \App\Filament\Doctor\Pages\Upgrade::getUrl(panel: 'doctor'),
                ];
            } elseif (! $clinica->enPruebaVigente() && $clinica->limitePacientes() === $tieneBasico && $suyos >= $tieneBasico - 20) {
                $alerts[] = [
                    'type' => $suyos >= $tieneBasico ? 'danger' : 'warning',
                    'icon' => 'heroicon-o-users',
                    'title' => "Lleva {$suyos} de {$tieneBasico} pacientes",
                    'desc' => 'Es el límite del Básico. El Pro no tiene límite.',
                    'url' => \App\Filament\Doctor\Pages\Upgrade::getUrl(panel: 'doctor'),
                ];
            }
        }

        // Huecos de los próximos días que ya tienen citas: horas para
        // ofrecerle a quien le toca volver o está en lista de espera. Un día
        // sin ninguna cita no se cuenta: "mañana libre de 9 a 19" no dice nada.
        $huecos = $clinica ? self::huecosParaOfrecer($clinica, $user->doctor?->id) : [];
        if ($huecos !== []) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-calendar',
                'title' => 'Tiene libre ' . implode('; ', $huecos),
                'desc' => 'Ofrézcalo a quien le toca volver o a quien quedó de agendar.',
                'url' => \App\Filament\Doctor\Pages\CalendarPage::getUrl(panel: 'doctor'),
            ];
        }

        return $alerts;
    }

    /**
     * Los ratos libres de 1 hora o más en los próximos días que ya tienen
     * alguna cita (hasta 2 días, en la semana que sigue).
     * Ej. ["mañana de 10:00 a 14:00"].
     *
     * @return list<string>
     */
    public static function huecosParaOfrecer(\App\Models\Clinic $clinica, ?int $doctorId): array
    {
        $dias = [];

        for ($dia = \Carbon\CarbonImmutable::tomorrow(); count($dias) < 2 && $dia->lessThan(\Carbon\CarbonImmutable::tomorrow()->addDays(6)); $dia = $dia->addDay()) {
            $tieneCitas = Appointment::where('clinic_id', $clinica->id)
                ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
                ->whereIn('status', Appointment::ESTADOS_QUE_OCUPAN)
                ->whereDate('starts_at', $dia->toDateString())
                ->exists();
            $horas = $tieneCitas ? \App\Services\HuecosDisponibles::delDia($clinica, $dia, $doctorId, 60) : [];
            if ($horas === []) {
                continue;
            }

            // Inicios de 1 hora cada 30 min → ratos seguidos: 10:00, 10:30… 13:00 = 10:00 a 14:00.
            $ratos = [];
            foreach ($horas as $hora) {
                $inicio = $dia->setTimeFromTimeString($hora);
                $ultimo = end($ratos);
                if ($ultimo && $ultimo[1]->addMinutes(\App\Services\HuecosDisponibles::PASO_MINUTOS)->equalTo($inicio)) {
                    $ratos[array_key_last($ratos)][1] = $inicio;
                } else {
                    $ratos[] = [$inicio, $inicio];
                }
            }

            $nombre = $dia->isTomorrow() ? 'mañana' : $dia->locale('es')->isoFormat('dddd');
            $dias[] = $nombre . ' ' . collect($ratos)
                ->map(fn ($r) => 'de ' . $r[0]->format('H:i') . ' a ' . $r[1]->addHour()->format('H:i'))
                ->implode(' y ');
        }

        return array_slice($dias, 0, 2);
    }
}
