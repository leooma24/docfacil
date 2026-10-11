<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\HuecosDisponibles;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;

/**
 * El día con una columna por doctor (día 6 del plan, 10-oct-2026): sus
 * citas en orden y sus huecos libres, para que recepción vea de un vistazo
 * a quién le cabe el paciente que llama. Se abre desde el Calendario.
 */
class AgendaDelDia extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static ?string $title = 'El día por doctor';

    protected static ?string $slug = 'agenda-del-dia';

    protected static string $view = 'filament.doctor.pages.agenda-del-dia';

    protected static bool $shouldRegisterNavigation = false;

    public string $dia = '';

    public function mount(): void
    {
        $this->dia = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) request('dia')) ? request('dia') : today()->toDateString();
    }

    public function otroDia(int $cuantos): void
    {
        $this->dia = $this->fecha()->addDays(max(-31, min(31, $cuantos)))->toDateString();
    }

    public function hoy(): void
    {
        $this->dia = today()->toDateString();
    }

    private function fecha(): CarbonImmutable
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->dia) ? CarbonImmutable::parse($this->dia) : CarbonImmutable::today();
    }

    public function getViewData(): array
    {
        $clinica = auth()->user()->clinic;
        $dia = $this->fecha();

        $citas = Appointment::with(['patient', 'service'])
            ->where('clinic_id', $clinica->id)
            ->whereDate('starts_at', $dia->toDateString())
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('starts_at')
            ->get()
            ->groupBy('doctor_id');

        $columnas = Doctor::with('user')->where('clinic_id', $clinica->id)->orderBy('id')->get()
            ->map(fn (Doctor $doctor) => [
                'doctor' => $doctor,
                'citas' => $citas->get($doctor->id, collect()),
                'libres' => self::rangos(HuecosDisponibles::delDia($clinica, $dia, $doctor->id, 30)),
                'cerrado' => $clinica->cierraEse($dia, $doctor->id),
            ]);

        return [
            'columnas' => $columnas,
            'fechaTexto' => ucfirst($dia->locale('es')->isoFormat('dddd D [de] MMMM')),
            'esHoy' => $dia->isToday(),
        ];
    }

    /**
     * Junta los huecos de 30 minutos seguidos en rangos: 10:00, 10:30, 11:00
     * → «10:00 a 11:30».
     *
     * @param  list<string>  $horas
     * @return list<array{0: string, 1: string}>
     */
    public static function rangos(array $horas): array
    {
        $rangos = [];
        foreach ($horas as $hora) {
            $fin = Carbon::createFromFormat('H:i', $hora)->addMinutes(HuecosDisponibles::PASO_MINUTOS)->format('H:i');
            $ultimo = array_key_last($rangos);
            if ($ultimo !== null && $rangos[$ultimo][1] === $hora) {
                $rangos[$ultimo][1] = $fin;
            } else {
                $rangos[] = [$hora, $fin];
            }
        }

        return $rangos;
    }
}
