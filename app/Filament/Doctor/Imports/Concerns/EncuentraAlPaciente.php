<?php

namespace App\Filament\Doctor\Imports\Concerns;

use App\Filament\Doctor\Imports\PatientImporter;
use App\Models\Clinic;
use App\Models\Patient;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;

/**
 * Las citas y los saldos que se importan traen al paciente por su nombre y,
 * a veces, su teléfono. Se busca primero por teléfono (lo único que de verdad
 * lo identifica), luego por nombre completo; si no está, se da de alta con lo
 * que traiga el renglón, igual que el importador de pacientes.
 */
trait EncuentraAlPaciente
{
    protected function pacienteDelRenglon(int $clinicId, ?string $nombre, ?string $telefono): Patient
    {
        $nombre = trim(preg_replace('/\s+/', ' ', (string) $nombre));
        $telefono = PatientImporter::telefonoLimpio($telefono);

        if ($telefono && strlen($telefono) >= 10) {
            $limpio = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), '.', '')";
            $porTelefono = Patient::withoutGlobalScopes()->where('clinic_id', $clinicId)
                ->whereRaw("{$limpio} LIKE ?", ['%' . substr($telefono, -10)])
                ->first();
            if ($porTelefono) {
                return $porTelefono;
            }
        }

        if ($nombre === '') {
            throw new RowImportFailedException('Falta el nombre del paciente.');
        }

        [$pila, $apellidos] = PatientImporter::partirNombre($nombre);
        $porNombre = Patient::withoutGlobalScopes()->where('clinic_id', $clinicId)
            ->where(fn ($q) => $q
                ->where(fn ($w) => $w->whereRaw('LOWER(first_name) = ?', [mb_strtolower($pila)])->whereRaw('LOWER(last_name) = ?', [mb_strtolower($apellidos)]))
                ->orWhere(fn ($w) => $w->whereRaw('LOWER(first_name) = ?', [mb_strtolower($nombre)])->where(fn ($v) => $v->whereNull('last_name')->orWhere('last_name', ''))))
            ->first();
        if ($porNombre) {
            return $porNombre;
        }

        $clinica = Clinic::withoutGlobalScopes()->find($clinicId);
        if ($clinica && ! $clinica->puedeAgregarPacientes()) {
            throw new RowImportFailedException($clinica->mensajeDeTopeDePacientes());
        }

        return Patient::withoutGlobalScopes()->create([
            'clinic_id' => $clinicId,
            'first_name' => $pila,
            'last_name' => $apellidos,
            'phone' => $telefono,
            'is_active' => true,
        ]);
    }
}
