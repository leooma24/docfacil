<?php

namespace App\Filament\Paciente\Widgets;

use App\Filament\Paciente\Pages\FirmarConsentimiento;
use App\Models\Appointment;
use App\Models\ConsentForm;
use App\Models\Encuesta;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Support\EstadoDeCuenta;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

/**
 * Lo del paciente en una sola tarjeta (día 9 del plan, 10-oct-2026): su
 * próxima cita, su saldo, sus presupuestos, lo que falta firmar y «¿Cómo le
 * fue en su visita?».
 */
class MiResumen extends Widget
{
    protected static string $view = 'filament.paciente.mi-resumen';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    private function paciente(): ?Patient
    {
        return Patient::withoutGlobalScopes()->where('user_id', auth()->id())->first();
    }

    /** La visita que todavía no califica: de los últimos 14 días. */
    private function visitaPorCalificar(Patient $p): ?Appointment
    {
        return Appointment::withoutGlobalScopes()->with(['doctor.user'])
            ->where('clinic_id', $p->clinic_id)->where('patient_id', $p->id)
            ->where('status', 'completed')
            ->where('starts_at', '>=', now()->subDays(14))
            ->whereNotExists(fn ($q) => $q->selectRaw(1)->from('encuestas')->whereColumn('encuestas.appointment_id', 'appointments.id'))
            ->latest('starts_at')->first();
    }

    public function calificar(int $cita, int $estrellas, ?string $comentario = null): void
    {
        $p = $this->paciente();
        if (! $p || $estrellas < 1 || $estrellas > 5) {
            return;
        }
        $visita = Appointment::withoutGlobalScopes()->where('clinic_id', $p->clinic_id)->where('patient_id', $p->id)
            ->where('status', 'completed')->find($cita);
        if (! $visita || Encuesta::withoutGlobalScopes()->where('appointment_id', $visita->id)->exists()) {
            return;
        }

        Encuesta::withoutGlobalScopes()->create([
            'clinic_id' => $p->clinic_id,
            'patient_id' => $p->id,
            'appointment_id' => $visita->id,
            'doctor_id' => $visita->doctor_id,
            'estrellas' => $estrellas,
            'comentario' => filled($comentario) ? mb_substr(trim($comentario), 0, 500) : null,
        ]);
        Notification::make()->title('¡Gracias! Su opinión le llega solo al consultorio.')->success()->send();
    }

    protected function getViewData(): array
    {
        $p = $this->paciente();
        if (! $p) {
            return ['paciente' => null];
        }

        return [
            'paciente' => $p,
            'proxima' => Appointment::withoutGlobalScopes()->with(['service', 'clinic'])
                ->where('clinic_id', $p->clinic_id)->where('patient_id', $p->id)
                ->whereIn('status', Appointment::ESTADOS_QUE_OCUPAN)->where('starts_at', '>=', now())
                ->orderBy('starts_at')->first(),
            'cuenta' => EstadoDeCuenta::de($p),
            'presupuestos' => TreatmentPlan::withoutGlobalScopes()->where('clinic_id', $p->clinic_id)->where('patient_id', $p->id)
                ->whereIn('status', ['sent', 'accepted'])->whereNotNull('public_token')->latest()->get(),
            'porFirmar' => ConsentForm::withoutGlobalScopes()->where('clinic_id', $p->clinic_id)->where('patient_id', $p->id)
                ->whereNull('signed_at')->latest()->get(),
            'visita' => $this->visitaPorCalificar($p),
            'ligaFirmar' => fn (ConsentForm $c) => FirmarConsentimiento::getUrl(['consentimiento' => $c->id]),
        ];
    }
}
