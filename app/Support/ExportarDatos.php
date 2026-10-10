<?php

namespace App\Support;

use App\Models\Clinic;
use App\Models\MedicalRecord;
use App\Models\PatientFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Todos los datos del consultorio en un ZIP: una hoja (CSV) por cada cosa y
 * las fotos y archivos de cada paciente en su carpeta.
 *
 * Lo pidieron los doctores de la prueba del 12-oct-2026: nadie paga sin saber
 * que se puede llevar sus expedientes. Los CSV abren en Excel (llevan BOM para
 * que salgan bien los acentos).
 */
class ExportarDatos
{
    /**
     * Archivo => [tabla, y si no tiene clinic_id: [columna, tabla de la que cuelga]].
     */
    private const HOJAS = [
        'pacientes' => ['patients'],
        'citas' => ['appointments'],
        'notas_clinicas' => ['medical_records'],
        'recetas' => ['prescriptions'],
        'receta_medicamentos' => ['prescription_items', ['prescription_id', 'prescriptions']],
        'odontogramas' => ['odontograms'],
        'odontograma_dientes' => ['odontogram_teeth', ['odontogram_id', 'odontograms']],
        'presupuestos' => ['treatment_plans'],
        'presupuesto_partidas' => ['treatment_plan_items', ['treatment_plan_id', 'treatment_plans']],
        'cobros' => ['payments'],
        'abonos' => ['payment_receipts', ['payment_id', 'payments']],
        'planes_de_pago' => ['payment_plans'],
        'consentimientos' => ['consent_forms'],
        'gastos' => ['expenses'],
        'laboratorio' => ['lab_orders'],
        'servicios' => ['services'],
        'lista_de_espera' => ['waitlist_entries'],
        'insumos' => ['supplies'],
        'insumo_movimientos' => ['supply_movements', ['supply_id', 'supplies']],
    ];

    /** Columnas que no salen: ligas privadas y llaves que no le sirven a nadie fuera. */
    private const SIN_SALIR = '/token|password|secret|remember|two_factor/i';

    public function __construct(private Clinic $clinica)
    {
    }

    /** Arma el ZIP en un archivo temporal y regresa su ruta. */
    public function zip(): string
    {
        $ruta = tempnam(sys_get_temp_dir(), 'docfacil-datos-');
        $zip = new \ZipArchive();
        $zip->open($ruta, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $zip->addFromString('LEEME.txt', $this->leeme());

        foreach (self::HOJAS as $archivo => $def) {
            if (! Schema::hasTable($def[0])) {
                continue;
            }
            $zip->addFromString("{$archivo}.csv", $this->csv($this->filas($def[0], $def[1] ?? null)));
        }

        foreach ($this->archivos() as $dentro => $enDisco) {
            $zip->addFromString($dentro, Storage::disk('local')->get($enDisco));
        }

        $zip->close();

        return $ruta;
    }

    public function nombre(): string
    {
        return 'docfacil-' . (Str::slug($this->clinica->name) ?: 'consultorio') . '-' . now()->format('Y-m-d') . '.zip';
    }

    private function filas(string $tabla, ?array $cuelgaDe): array
    {
        $q = DB::table($tabla);

        if ($cuelgaDe === null) {
            $q->where('clinic_id', $this->clinica->id);
        } else {
            [$columna, $padre] = $cuelgaDe;
            $q->whereIn($columna, DB::table($padre)->where('clinic_id', $this->clinica->id)->select('id'));
        }

        return $q->orderBy('id')->get()
            ->map(fn ($fila) => collect((array) $fila)->reject(fn ($v, $k) => preg_match(self::SIN_SALIR, $k))->all())
            ->all();
    }

    private function csv(array $filas): string
    {
        $f = fopen('php://temp', 'r+');
        fwrite($f, "\xEF\xBB\xBF");

        if ($filas !== []) {
            fputcsv($f, array_keys($filas[0]));
            foreach ($filas as $fila) {
                fputcsv($f, array_map(fn ($v) => is_scalar($v) || $v === null ? $v : json_encode($v), $fila));
            }
        }

        rewind($f);
        $csv = stream_get_contents($f);
        fclose($f);

        return $csv;
    }

    /** Ruta dentro del ZIP => ruta en el disco. Una carpeta por paciente. */
    private function archivos(): array
    {
        $disco = Storage::disk('local');
        $salen = [];
        $carpeta = fn ($p) => 'archivos/' . $p->id . '-' . (trim(preg_replace('/[^A-Za-z0-9]+/', '-', Str::ascii("{$p->first_name} {$p->last_name}")), '-') ?: 'paciente') . '/';

        PatientFile::withoutGlobalScopes()->with(['patient' => fn ($q) => $q->withoutGlobalScopes()])
            ->where('clinic_id', $this->clinica->id)->get()
            ->each(function ($archivo) use (&$salen, $disco, $carpeta) {
                if ($archivo->patient && $disco->exists($archivo->path)) {
                    $salen[$this->libre($salen, $carpeta($archivo->patient) . ($archivo->nombre ?: basename($archivo->path)))] = $archivo->path;
                }
            });

        MedicalRecord::withoutGlobalScopes()->with(['patient' => fn ($q) => $q->withoutGlobalScopes()])
            ->where('clinic_id', $this->clinica->id)->whereNotNull('attachments')->get()
            ->each(function ($nota) use (&$salen, $disco, $carpeta) {
                foreach ((array) $nota->attachments as $ruta) {
                    if ($nota->patient && is_string($ruta) && $disco->exists($ruta)) {
                        $salen[$this->libre($salen, $carpeta($nota->patient) . 'consulta-' . $nota->id . '-' . basename($ruta))] = $ruta;
                    }
                }
            });

        return $salen;
    }

    /** Si ya hay un archivo con ese nombre en la carpeta, le pone (2), (3)… */
    private function libre(array $salen, string $nombre): string
    {
        $i = 2;
        $base = $nombre;
        while (isset($salen[$nombre])) {
            $ext = pathinfo($base, PATHINFO_EXTENSION);
            $nombre = preg_replace('/(\.' . preg_quote($ext, '/') . ')?$/', " ({$i})" . ($ext ? ".{$ext}" : ''), $base, 1);
            $i++;
        }

        return $nombre;
    }

    private function leeme(): string
    {
        return "Datos de {$this->clinica->name}\n"
            . 'Bajados de DocFácil el ' . now()->format('d/m/Y H:i') . "\n\n"
            . "Cada archivo .csv se abre en Excel o en Hojas de cálculo de Google.\n"
            . "- pacientes.csv: los datos de cada paciente, con alergias y antecedentes.\n"
            . "- citas.csv, notas_clinicas.csv, recetas.csv (y receta_medicamentos.csv), odontogramas.csv (y odontograma_dientes.csv).\n"
            . "- presupuestos.csv (y presupuesto_partidas.csv), cobros.csv, abonos.csv, planes_de_pago.csv.\n"
            . "- consentimientos.csv, gastos.csv, laboratorio.csv, servicios.csv, lista_de_espera.csv, insumos.csv.\n"
            . "- archivos/: las fotos, radiografías y documentos, una carpeta por paciente.\n\n"
            . "La columna patient_id dice de qué paciente es cada cosa: es el id de pacientes.csv.\n"
            . "Son datos de salud de sus pacientes: guárdelos en un lugar seguro.\n";
    }
}
