<?php

namespace App\Filament\Doctor\Imports;

use App\Filament\Doctor\Imports\Concerns\EncuentraAlPaciente;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Service;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Las citas que ya tenía agendadas en su sistema anterior o en su libreta
 * (10-oct-2026). Sin esto, quien se cambia pierde las próximas semanas de
 * agenda, y nadie se cambia así.
 *
 * Solo se traen las que vienen: las que ya pasaron no sirven en la agenda.
 * Subir el mismo archivo dos veces no duplica (mismo paciente, misma hora).
 */
class AppointmentImporter extends Importer
{
    use EncuentraAlPaciente;

    protected static ?string $model = Appointment::class;

    public static function getColumns(): array
    {
        $soloSeLee = fn () => null;

        return [
            ImportColumn::make('paciente')->label('Paciente (nombre completo)')->requiredMapping()->fillRecordUsing($soloSeLee)
                ->guess(['paciente', 'nombre', 'nombre del paciente', 'nombre completo', 'cliente', 'patient', 'name'])->example('Ana Ruiz López'),
            ImportColumn::make('telefono')->label('Teléfono')->fillRecordUsing($soloSeLee)
                ->guess(['telefono', 'teléfono', 'tel', 'celular', 'cel', 'whatsapp', 'phone'])->example('6681234567'),
            ImportColumn::make('fecha')->label('Fecha')->requiredMapping()->fillRecordUsing($soloSeLee)
                ->guess(['fecha', 'dia', 'día', 'fecha de la cita', 'date'])->example('15/10/2026'),
            ImportColumn::make('hora')->label('Hora')->requiredMapping()->fillRecordUsing($soloSeLee)
                ->guess(['hora', 'horario', 'hora de la cita', 'inicio', 'time'])->example('4:30 pm'),
            ImportColumn::make('duracion')->label('Duración (minutos)')->fillRecordUsing($soloSeLee)
                ->guess(['duracion', 'duración', 'minutos', 'min', 'duration'])->example('30'),
            ImportColumn::make('motivo')->label('Motivo o tratamiento')->fillRecordUsing($soloSeLee)
                ->guess(['motivo', 'tratamiento', 'servicio', 'procedimiento', 'notas', 'asunto', 'reason'])->example('Limpieza'),
            ImportColumn::make('doctor')->label('Doctor')->fillRecordUsing($soloSeLee)
                ->guess(['doctor', 'doctora', 'dentista', 'odontologo', 'odontólogo', 'profesional', 'atiende'])->example('Dra. Paola'),
        ];
    }

    public function resolveRecord(): ?Appointment
    {
        $clinicId = (int) $this->import->user->clinic_id;

        $fecha = PatientImporter::fechaMexicana($this->data['fecha'] ?? null, puedeSerFutura: true);
        $hora = self::horaMexicana($this->data['hora'] ?? null);
        if (! $fecha) {
            throw new RowImportFailedException('No se entendió la fecha. Escríbala como 15/10/2026.');
        }
        if (! $hora) {
            throw new RowImportFailedException('No se entendió la hora. Escríbala como 16:30 o 4:30 pm.');
        }

        $inicio = Carbon::parse("{$fecha} {$hora}", config('app.timezone'));
        if ($inicio->isPast()) {
            throw new RowImportFailedException('Ya pasó: solo se traen las citas que vienen.');
        }

        $paciente = $this->pacienteDelRenglon($clinicId, $this->data['paciente'] ?? null, $this->data['telefono'] ?? null);

        $existente = Appointment::withoutGlobalScopes()->where('clinic_id', $clinicId)
            ->where('patient_id', $paciente->id)->where('starts_at', $inicio)->first();
        if ($existente) {
            return $existente;
        }

        $motivo = trim((string) ($this->data['motivo'] ?? ''));
        $servicio = $motivo !== '' ? Service::withoutGlobalScopes()->where('clinic_id', $clinicId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($motivo)])->first() : null;
        $minutos = (int) preg_replace('/\D/', '', (string) ($this->data['duracion'] ?? ''));
        $minutos = $minutos >= 5 && $minutos <= 480 ? $minutos : (int) ($servicio?->duration_minutes ?: 30);

        $cita = new Appointment();
        $cita->clinic_id = $clinicId;
        $cita->patient_id = $paciente->id;
        $cita->doctor_id = $this->doctorDelRenglon($clinicId, $this->data['doctor'] ?? null)?->id;
        $cita->service_id = $servicio?->id;
        $cita->starts_at = $inicio;
        $cita->ends_at = $inicio->copy()->addMinutes($minutos);
        $cita->status = 'scheduled';
        $cita->notes = $servicio || $motivo === '' ? null : $motivo;

        if (! $cita->doctor_id) {
            throw new RowImportFailedException('El consultorio no tiene doctores dados de alta.');
        }

        return $cita;
    }

    /**
     * El doctor por su nombre ("paola", "Dra. Méndez"); si no viene o no se
     * reconoce, el de quien sube el archivo, o el único del consultorio.
     */
    private function doctorDelRenglon(int $clinicId, ?string $nombre): ?Doctor
    {
        $doctores = Doctor::withoutGlobalScopes()->with('user')->where('clinic_id', $clinicId)->get();
        $buscado = Str::of((string) $nombre)->lower()->ascii()->replace(['dra.', 'dr.', 'dra ', 'dr '], '')->trim()->toString();

        if ($buscado !== '') {
            $encontrado = $doctores->first(fn (Doctor $d) => Str::contains(Str::of((string) $d->user?->name)->lower()->ascii()->toString(), $buscado));
            if ($encontrado) {
                return $encontrado;
            }
        }

        return $doctores->firstWhere('user_id', $this->import->user->id) ?? $doctores->first();
    }

    /** "16:30", "4:30 pm", "4 pm", "4:30 p.m.", "10.30" → "16:30". */
    public static function horaMexicana(?string $valor): ?string
    {
        $v = Str::of((string) $valor)->lower()
            ->replace(['a. m.', 'p. m.', 'a.m.', 'p.m.', ' '], ['am', 'pm', 'am', 'pm', ''])
            ->replace(['hrs', 'hr', 'h'], '')
            ->replace('.', ':')
            ->toString();
        if (! preg_match('/^(\d{1,2})(?::(\d{2}))?(?::\d{2})?(am|pm)?$/', $v, $m)) {
            return null;
        }
        $h = (int) $m[1];
        $min = ($m[2] ?? '') !== '' ? (int) $m[2] : 0;
        $sufijo = $m[3] ?? '';
        if ($sufijo === 'pm' && $h < 12) {
            $h += 12;
        } elseif ($sufijo === 'am' && $h === 12) {
            $h = 0;
        }

        return $h <= 23 && $min <= 59 ? sprintf('%02d:%02d', $h, $min) : null;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $cuerpo = 'Se trajeron ' . number_format($import->successful_rows) . ' ' . Str::plural('cita', $import->successful_rows) . '.';
        if ($fallidas = $import->getFailedRowsCount()) {
            $cuerpo .= ' ' . number_format($fallidas) . ' ' . Str::plural('renglón', $fallidas) . ' no ' . ($fallidas === 1 ? 'entró' : 'entraron')
                . ': descargue el archivo de errores para ver cuáles y por qué.';
        }

        return $cuerpo;
    }
}
