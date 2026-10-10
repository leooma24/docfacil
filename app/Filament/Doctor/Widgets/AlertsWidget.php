<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use Filament\Widgets\Widget;

class AlertsWidget extends Widget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static string $view = 'filament.doctor.widgets.alerts-widget';

    public function getAlerts(): array
    {
        $clinicId = auth()->user()->clinic_id;
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
                'desc' => 'Hace más de 6 meses que no vienen. Envíales un recordatorio.',
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
                'title' => "{$overduePayments} pagos vencidos",
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

        // Today's income
        $todayIncome = Payment::cobradoEntre($clinicId, today(), today());

        if ($todayIncome > 0) {
            $alerts[] = [
                'type' => 'success',
                'icon' => 'heroicon-o-banknotes',
                'title' => 'Ingresos hoy: $' . number_format($todayIncome, 0),
                'desc' => 'Buen trabajo.',
            ];
        }

        return $alerts;
    }
}
