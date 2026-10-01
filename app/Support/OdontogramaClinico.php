<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Odontogram;
use App\Models\OdontogramTooth;
use App\Models\Service;
use App\Models\TreatmentPlan;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Lo que conecta el odontograma con el resto del consultorio:
 *
 *  - la consulta lo actualiza (la resina en el 36 cura su caries),
 *  - cada visita deja su propia versión, para ver cómo cambió la boca,
 *  - lo que falta tratar se vuelve presupuesto con los precios del catálogo,
 *  - y la cita ya sabe en qué diente se va a trabajar.
 *
 * El catálogo de servicios es texto libre de cada consultorio, así que el
 * servicio se reconoce por su nombre ("Resina", "Extracción de tercer molar").
 */
class OdontogramaClinico
{
    /** Palabra del nombre del servicio → lo que le deja al diente. */
    private const QUE_HACE = [
        'resina' => 'filling',
        'obturaci' => 'filling',
        'amalgama' => 'filling',
        'incrustaci' => 'filling',
        'extracci' => 'missing',
        'exodoncia' => 'missing',
        'endodoncia' => 'root_canal',
        'conducto' => 'root_canal',
        'corona' => 'crown',
        'carilla' => 'veneer',
        'implante' => 'implant',
        'sellante' => 'sealant',
        'sellador' => 'sealant',
        'puente' => 'bridge',
    ];

    /** Lo que el servicio resuelve de lo que estaba por tratar. */
    private const RESUELVE = [
        'filling' => ['decay'],
        'missing' => ['extraction'],
        'sealant' => ['pending'],
    ];

    public static function condicionDeServicio(?string $nombre): ?string
    {
        $nombre = Str::lower(Str::ascii((string) $nombre));

        foreach (self::QUE_HACE as $palabra => $condicion) {
            if (str_contains($nombre, Str::ascii($palabra))) {
                return $condicion;
            }
        }

        return null;
    }

    /** Los números FDI válidos que vengan en el texto ("16, 17" → [16, 17]). */
    public static function dientes(?string $texto): array
    {
        preg_match_all('/\b([1-8][1-8])\b/', (string) $texto, $m);

        return array_values(array_unique(array_map('intval', array_filter(
            $m[1],
            fn ($n) => (int) $n % 10 <= (intdiv((int) $n, 10) >= 5 ? 5 : 8)
        ))));
    }

    /** El último odontograma del paciente, o null. */
    public static function ultimo(int $clinicId, int $patientId, ?int $antesDe = null): ?Odontogram
    {
        return Odontogram::with('teeth')
            ->where('clinic_id', $clinicId)
            ->where('patient_id', $patientId)
            ->when($antesDe, fn ($q) => $q->where('id', '!=', $antesDe))
            ->orderByDesc('evaluation_date')
            ->orderByDesc('id')
            ->first();
    }

    /** Copia los dientes de un odontograma a otro (el nuevo arranca de lo que ya se sabe). */
    public static function copiarDientes(Odontogram $desde, Odontogram $hacia): void
    {
        foreach ($desde->teeth as $diente) {
            OdontogramTooth::updateOrCreate(
                ['odontogram_id' => $hacia->id, 'tooth_number' => $diente->tooth_number],
                $diente->only(['condition', 'notes', ...array_values(OdontogramTooth::CARAS)])
            );
        }
    }

    /**
     * Pasa al odontograma lo que se hizo en la consulta. La visita deja su
     * propia versión, con fecha de hoy, que arranca de la anterior; si ya
     * hay una de hoy se usa esa. El odontograma de antes no se toca.
     */
    public static function registrarConsulta(Appointment $cita): ?Odontogram
    {
        $hechos = [];
        foreach ($cita->procedures()->with('service')->get() as $procedimiento) {
            $condicion = self::condicionDeServicio($procedimiento->service?->name);
            foreach ($condicion ? self::dientes($procedimiento->tooth_number) : [] as $numero) {
                $hechos[$numero] = $condicion;
            }
        }

        if (! $hechos) {
            return null;
        }

        $odontograma = Odontogram::where('clinic_id', $cita->clinic_id)
            ->where('patient_id', $cita->patient_id)
            ->whereDate('evaluation_date', today())
            ->latest('id')
            ->first();

        if (! $odontograma) {
            $anterior = self::ultimo($cita->clinic_id, $cita->patient_id);
            $odontograma = Odontogram::create([
                'clinic_id' => $cita->clinic_id,
                'patient_id' => $cita->patient_id,
                'doctor_id' => $cita->doctor_id,
                'evaluation_date' => today(),
                'notes' => 'Actualizado desde la consulta del ' . today()->format('d/m/Y') . '.',
            ]);
            if ($anterior) {
                self::copiarDientes($anterior, $odontograma);
            }
        }

        foreach ($hechos as $numero => $condicion) {
            $diente = OdontogramTooth::firstOrNew(['odontogram_id' => $odontograma->id, 'tooth_number' => $numero]);
            self::aplicar($diente, $condicion);
            $diente->save();
        }

        return $odontograma;
    }

    /** Lo que un tratamiento hecho le deja al diente. */
    private static function aplicar(OdontogramTooth $diente, string $condicion): void
    {
        $caras = $diente->exists ? $diente->caras() : OdontogramTooth::carasVacias();

        // Diente que se fue o se cambió por implante: sus caras ya no existen.
        if (in_array($condicion, ['missing', 'implant'], true)) {
            $caras = OdontogramTooth::carasVacias();
        }

        if (in_array($condicion, OdontogramTooth::DE_CARA, true)) {
            // La resina va donde estaba la caries. Si no había caries marcada
            // no inventamos la cara: queda el diente obturado sin cara.
            $resueltas = self::RESUELVE[$condicion] ?? [];
            $tocadas = 0;
            foreach ($caras as $cara => $valor) {
                if (in_array($valor, $resueltas, true)) {
                    $caras[$cara] = $condicion;
                    $tocadas++;
                }
            }
            $diente->condition = $tocadas
                ? OdontogramTooth::resumen($diente->condition, $caras)
                : (in_array($diente->condition, OdontogramTooth::DE_DIENTE, true) ? $diente->condition : $condicion);
        } else {
            $diente->condition = $condicion;
        }

        foreach (OdontogramTooth::CARAS as $cara => $columna) {
            $diente->{$columna} = $caras[$cara];
        }
    }

    /**
     * Los dientes donde se va a hacer el servicio, según lo que el
     * odontograma tiene por tratar. La extracción de tercer molar solo
     * propone terceros molares.
     */
    public static function dientesPara(Service $servicio, ?Odontogram $odontograma): array
    {
        $condicion = self::condicionDeServicio($servicio->name);
        $pendientes = self::RESUELVE[$condicion] ?? [];

        if (! $odontograma || ! $pendientes) {
            return [];
        }

        $soloTerceros = str_contains(Str::lower(Str::ascii($servicio->name)), 'tercer molar');

        return $odontograma->teeth
            ->filter(fn (OdontogramTooth $d) => array_intersect($pendientes, array_merge([$d->condition], array_values($d->caras()))))
            ->filter(fn (OdontogramTooth $d) => ! $soloTerceros || $d->tooth_number % 10 === 8)
            ->pluck('tooth_number')
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Lo que el odontograma tiene por tratar, diente por diente:
     * [['numero' => 36, 'condicion' => 'decay', 'caras' => 'oclusal, mesial'], ...]
     */
    public static function porTratar(?Odontogram $odontograma): array
    {
        if (! $odontograma) {
            return [];
        }

        $nombresCara = OdontogramTooth::caraLabels();
        $lista = [];
        foreach ($odontograma->teeth as $diente) {
            $caras = $diente->caras();
            foreach (OdontogramTooth::POR_TRATAR as $condicion) {
                if ($diente->condition !== $condicion && ! in_array($condicion, $caras, true)) {
                    continue;
                }
                $lista[] = [
                    'numero' => $diente->tooth_number,
                    'condicion' => $condicion,
                    'caras' => collect($caras)->filter(fn ($c) => $c === $condicion)->keys()
                        ->map(fn ($c) => Str::lower(Str::before($nombresCara[$c], ' /')))->implode(', '),
                ];
            }
        }

        return $lista;
    }

    /** "36: Caries → Obturación" por cada diente que cambió. */
    public static function cambios(?Odontogram $antes, Odontogram $ahora): array
    {
        $etiquetas = OdontogramTooth::conditionLabels();
        $estado = fn (?Odontogram $o) => $o
            ? $o->teeth()->get()->keyBy('tooth_number')
            : collect();

        $previo = $estado($antes);
        $actual = $estado($ahora);
        $cambios = [];

        foreach ($previo->keys()->merge($actual->keys())->unique()->sort() as $numero) {
            $a = $previo->get($numero)?->condition ?? 'healthy';
            $b = $actual->get($numero)?->condition ?? 'healthy';
            if ($a !== $b) {
                $cambios[] = $numero . ': ' . ($etiquetas[$a] ?? $a) . ' → ' . ($etiquetas[$b] ?? $b);
            }
        }

        return $cambios;
    }

    /**
     * Un presupuesto en borrador con lo que falta tratar, una línea por
     * diente, con el precio del catálogo. Si el consultorio no tiene el
     * servicio, la línea queda en $0 para que el doctor la llene.
     */
    public static function presupuestoDesde(Odontogram $odontograma): TreatmentPlan
    {
        $odontograma->loadMissing('teeth');
        $servicios = Service::where('clinic_id', $odontograma->clinic_id)->where('is_active', true)->get();
        $etiquetas = OdontogramTooth::conditionLabels();

        $plan = TreatmentPlan::create([
            'clinic_id' => $odontograma->clinic_id,
            'patient_id' => $odontograma->patient_id,
            'doctor_id' => $odontograma->doctor_id,
            'title' => 'Plan de tratamiento — ' . $odontograma->evaluation_date->format('d/m/Y'),
            'description' => 'Armado desde el odontograma del ' . $odontograma->evaluation_date->format('d/m/Y') . '.',
            'status' => 'draft',
            'discount' => 0,
            'valid_until' => now()->addDays(30),
        ]);

        $orden = 0;
        foreach (self::porTratar($odontograma) as $pendiente) {
            if ($pendiente['condicion'] === 'pending') {
                continue;
            }

            $servicio = self::servicioPara($pendiente['condicion'], $pendiente['numero'], $servicios);

            $plan->items()->create([
                'service_id' => $servicio?->id,
                'description' => ($servicio?->name ?? $etiquetas[$pendiente['condicion']]) . ' — diente ' . $pendiente['numero']
                    . ($pendiente['caras'] ? ' (' . $pendiente['caras'] . ')' : ''),
                'tooth_number' => (string) $pendiente['numero'],
                'quantity' => 1,
                'unit_price' => $servicio?->price ?? 0,
                'sort_order' => $orden++,
            ]);
        }

        $plan->recalculateTotal();

        return $plan;
    }

    /** El servicio del catálogo que trata esa condición en ese diente. */
    public static function servicioPara(string $condicion, int $numero, Collection $servicios): ?Service
    {
        $buscado = ['decay' => 'filling', 'extraction' => 'missing'][$condicion] ?? null;
        if (! $buscado) {
            return null;
        }

        $candidatos = $servicios->filter(fn (Service $s) => self::condicionDeServicio($s->name) === $buscado);

        if ($buscado === 'missing') {
            $tercero = fn (Service $s) => str_contains(Str::lower(Str::ascii($s->name)), 'tercer molar');
            $esTercero = $numero % 10 === 8;
            $candidatos = $candidatos->filter(fn ($s) => $tercero($s) === $esTercero)->whenEmpty(fn () => $candidatos);
        }

        return $candidatos->sortBy('price')->first();
    }
}
