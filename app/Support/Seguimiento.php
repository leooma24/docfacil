<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Seguimiento as Llamada;
use Illuminate\Support\Collection;

/**
 * A quién hay que llamar, y por qué (día 5 del plan, 10-oct-2026).
 *
 * Las «tareas automáticas» de Dentalink sin el plan caro: la lista se arma
 * sola con quien canceló o no vino y no tiene otra cita, quien debe algo
 * vencido, a quien le toca su revisión y quien lleva 6 meses sin venir. Cada
 * uno se cierra con Logrado, Volver a llamar en X días o Cerrar caso. Nada
 * se manda solo: el WhatsApp se abre con el mensaje escrito y lo manda el
 * consultorio desde su teléfono.
 */
class Seguimiento
{
    public const MOTIVOS = [
        'cita' => 'Cita perdida',
        'deuda' => 'Debe',
        'revision' => 'Revisión',
        'inactivo' => 'Sin venir',
    ];

    public const RESULTADOS = ['logrado', 'volver', 'cerrado'];

    /** Cuántos días se quita de la lista lo que ya se logró. */
    private const DIAS_LOGRADO = 60;

    /**
     * @return Collection<int, array{paciente: Patient, motivo: string, texto: string, monto: ?float, whatsapp: ?string}>
     */
    public static function lista(int $clinicId): Collection
    {
        $clinica = Clinic::withoutGlobalScopes()->find($clinicId);
        if (! $clinica) {
            return collect();
        }

        $conCita = Appointment::withoutGlobalScopes()->where('clinic_id', $clinicId)
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->where('starts_at', '>=', now())
            ->pluck('patient_id')->unique()->flip();
        $yaAtendidos = self::yaAtendidos($clinicId);
        $filas = [];
        $agrega = function (int $pacienteId, string $motivo, string $texto, ?float $monto = null) use (&$filas, $yaAtendidos) {
            if (! isset($yaAtendidos["{$pacienteId}|{$motivo}"])) {
                $filas[] = compact('pacienteId', 'motivo', 'texto', 'monto');
            }
        };

        // Canceló o no vino en los últimos 60 días, y no tiene otra cita.
        Appointment::withoutGlobalScopes()->where('clinic_id', $clinicId)
            ->whereIn('status', ['cancelled', 'no_show'])
            ->whereBetween('starts_at', [now()->subDays(60), now()])
            ->orderByDesc('starts_at')->get()
            ->unique('patient_id')
            ->reject(fn (Appointment $c) => isset($conCita[$c->patient_id]))
            ->each(fn (Appointment $c) => $agrega($c->patient_id, 'cita',
                ($c->status === 'no_show' ? 'No vino a su cita del ' : 'Canceló su cita del ') . $c->starts_at->locale('es')->isoFormat('D [de] MMMM')));

        // Debe algo vencido.
        Payment::withoutGlobalScopes()->where('clinic_id', $clinicId)->overdue()
            ->selectRaw('patient_id, SUM(amount - amount_paid) as saldo')->groupBy('patient_id')->get()
            ->each(fn ($d) => $agrega((int) $d->patient_id, 'deuda', 'Debe $' . number_format((float) $d->saldo, 0) . ' vencido', round((float) $d->saldo, 2)));

        // Revisión y 6 meses sin venir, por su última consulta terminada.
        $atendidas = Appointment::withoutGlobalScopes()->with(['service' => fn ($q) => $q->withoutGlobalScopes()])
            ->where('clinic_id', $clinicId)->where('status', 'completed')
            ->where('starts_at', '>=', now()->subYears(2))
            ->orderByDesc('starts_at')->get()
            ->groupBy('patient_id');
        $conRevision = $clinica->hasFeature('recall_automation');
        foreach ($atendidas as $pacienteId => $citas) {
            if (isset($conCita[$pacienteId])) {
                continue;
            }
            $conRecall = $conRevision ? $citas->first(fn (Appointment $c) => (int) $c->service?->recall_months > 0) : null;
            if ($conRecall && $conRecall->starts_at->copy()->addMonths((int) $conRecall->service->recall_months)->lte(now()->addDays(7))) {
                $agrega((int) $pacienteId, 'revision', 'Le toca: ' . $conRecall->service->name . ' (la última fue el ' . $conRecall->starts_at->format('d/m/Y') . ')');

                continue;
            }
            $ultima = $citas->first();
            if ($ultima->starts_at->lte(now()->subMonths(6))) {
                $agrega((int) $pacienteId, 'inactivo', 'No viene desde el ' . $ultima->starts_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY'));
            }
        }

        $pacientes = Patient::withoutGlobalScopes()->with('responsable')->where('clinic_id', $clinicId)
            ->whereIn('id', array_column($filas, 'pacienteId'))->get()->keyBy('id');
        $orden = array_flip(array_keys(self::MOTIVOS));

        return collect($filas)
            ->filter(fn ($f) => $pacientes->has($f['pacienteId']))
            ->map(fn ($f) => [
                'paciente' => $pacientes[$f['pacienteId']],
                'motivo' => $f['motivo'],
                'texto' => $f['texto'],
                'monto' => $f['monto'],
                'whatsapp' => self::whatsapp($pacientes[$f['pacienteId']], $f['motivo'], $f['monto'], $clinica->name),
            ])
            ->sortBy(fn ($f) => $orden[$f['motivo']])
            ->values();
    }

    /** Lo que ya se trabajó y no toca todavía: «paciente|motivo». */
    private static function yaAtendidos(int $clinicId): array
    {
        return Llamada::withoutGlobalScopes()->where('clinic_id', $clinicId)
            ->where(fn ($q) => $q->where('resultado', 'cerrado')
                ->orWhere(fn ($w) => $w->where('resultado', 'logrado')->where('created_at', '>=', now()->subDays(self::DIAS_LOGRADO)))
                ->orWhere(fn ($w) => $w->where('resultado', 'volver')->whereDate('volver_el', '>', today())))
            ->get(['patient_id', 'motivo'])
            ->mapWithKeys(fn ($l) => ["{$l->patient_id}|{$l->motivo}" => true])
            ->all();
    }

    /** El WhatsApp con el mensaje escrito; lo manda el consultorio desde su teléfono. */
    private static function whatsapp(Patient $paciente, string $motivo, ?float $monto, string $consultorio): ?string
    {
        if ($paciente->noQuiereWhatsapp()) {
            return null;
        }
        $telefono = preg_replace('/\D/', '', (string) $paciente->telefonoDeContacto());
        if (strlen($telefono) < 10) {
            return null;
        }
        if (strlen($telefono) === 10) {
            $telefono = '52' . $telefono;
        }

        $hola = 'Hola ' . ($paciente->nombreDeContacto() ?: '') . ', le escribimos de ' . $consultorio . '. ';
        $mensaje = $hola . match ($motivo) {
            'cita' => 'No pudimos verle en su cita. ¿Le buscamos otro horario?',
            'deuda' => 'Le recordamos que tiene un saldo pendiente de $' . number_format((float) $monto, 0) . '. ¿Cuándo le queda bien pasar?',
            'revision' => 'Ya le toca su revisión. ¿Le agendamos?',
            default => 'Hace tiempo que no le vemos. ¿Le agendamos una revisión?',
        };

        return 'https://wa.me/' . $telefono . '?text=' . rawurlencode($mensaje);
    }

    /**
     * Lo del mes: cuántos se lograron y cuánto entró de quienes se les llamó
     * por una deuda, después de la llamada.
     *
     * @return array{logrados: int, cobrado: float}
     */
    public static function resumenDelMes(int $clinicId): array
    {
        $delMes = Llamada::withoutGlobalScopes()->where('clinic_id', $clinicId)->where('resultado', 'logrado')
            ->where('created_at', '>=', now()->startOfMonth())->get();

        $cobrado = 0.0;
        foreach ($delMes->where('motivo', 'deuda') as $llamada) {
            $cobrado += (float) PaymentReceipt::withoutGlobalScopes()->queEntraron()
                ->join('payments', 'payments.id', '=', 'payment_receipts.payment_id')
                ->where('payment_receipts.clinic_id', $clinicId)
                ->where('payments.patient_id', $llamada->patient_id)
                ->where('payment_receipts.paid_at', '>=', $llamada->created_at)
                ->sum('payment_receipts.amount');
        }

        return ['logrados' => $delMes->count(), 'cobrado' => round($cobrado, 2)];
    }

    public static function marcar(int $clinicId, int $pacienteId, string $motivo, string $resultado, ?int $dias = null): bool
    {
        if (! array_key_exists($motivo, self::MOTIVOS) || ! in_array($resultado, self::RESULTADOS, true)) {
            return false;
        }
        if (! Patient::withoutGlobalScopes()->where('clinic_id', $clinicId)->whereKey($pacienteId)->exists()) {
            return false;
        }

        Llamada::create([
            'clinic_id' => $clinicId,
            'patient_id' => $pacienteId,
            'user_id' => auth()->id(),
            'motivo' => $motivo,
            'resultado' => $resultado,
            'volver_el' => $resultado === 'volver' ? today()->addDays(max(1, min(90, $dias ?? 7))) : null,
        ]);

        return true;
    }
}
