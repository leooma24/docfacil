<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdontogramTooth extends Model
{
    protected $fillable = [
        'odontogram_id', 'tooth_number', 'condition',
        'top_surface', 'bottom_surface', 'left_surface',
        'right_surface', 'center_surface', 'notes',
    ];

    /**
     * Las cinco caras con su nombre clínico y la columna donde se guardan.
     * Las columnas se llamaron top/bottom/left/right/center cuando se creó
     * la tabla; aquí queda escrito qué es cada una.
     */
    public const CARAS = [
        'vestibular' => 'top_surface',
        'lingual' => 'bottom_surface',
        'mesial' => 'left_surface',
        'distal' => 'right_surface',
        'oclusal' => 'center_surface',
    ];

    /** Lo que se marca en una cara. Ordenado de más a menos urgente. */
    public const DE_CARA = ['decay', 'pending', 'filling', 'sealant'];

    /** Lo que se marca en el diente entero. */
    public const DE_DIENTE = ['extraction', 'missing', 'implant', 'crown', 'bridge', 'root_canal', 'veneer', 'fracture'];

    public function odontogram(): BelongsTo
    {
        return $this->belongsTo(Odontogram::class);
    }

    /** Las caras marcadas, con su nombre clínico: ['oclusal' => 'decay', ...]. */
    public function caras(): array
    {
        $caras = [];
        foreach (self::CARAS as $cara => $columna) {
            $caras[$cara] = $this->{$columna} ?: null;
        }

        return $caras;
    }

    public static function carasVacias(): array
    {
        return array_fill_keys(array_keys(self::CARAS), null);
    }

    /**
     * La condición que resume al diente: la del diente entero si la tiene
     * (una endodoncia con resina sigue siendo endodoncia), si no la cara más
     * urgente. Sin nada marcado, sano.
     */
    public static function resumen(?string $delDiente, array $caras): string
    {
        if ($delDiente && in_array($delDiente, self::DE_DIENTE, true)) {
            return $delDiente;
        }

        foreach (self::DE_CARA as $condicion) {
            if (in_array($condicion, $caras, true)) {
                return $condicion;
            }
        }

        return 'healthy';
    }

    /**
     * incisivo, canino, premolar o molar, por el segundo dígito FDI. En los
     * dientes de leche (cuadrantes 5 a 8) el 4 y el 5 son molares: no hay
     * premolares temporales.
     */
    public static function tipo(int $numero): string
    {
        $temporal = intdiv($numero, 10) >= 5;

        return match ($numero % 10) {
            1, 2 => 'incisivo',
            3 => 'canino',
            4, 5 => $temporal ? 'molar' : 'premolar',
            default => 'molar',
        };
    }

    public static function esSuperior(int $numero): bool
    {
        return in_array(intdiv($numero, 10), [1, 2, 5, 6], true);
    }

    /** Lo que falta hacer y lo que el paciente ya trae, para el resumen. */
    public const POR_TRATAR = ['decay', 'extraction', 'fracture', 'pending'];
    public const EXISTENTES = ['filling', 'crown', 'root_canal', 'implant', 'bridge', 'sealant', 'veneer'];

    /** [singular, plural] para el resumen de la boca. */
    public static function nombresParaResumen(): array
    {
        return [
            'decay' => ['caries', 'caries'],
            'extraction' => ['extracción indicada', 'extracciones indicadas'],
            'fracture' => ['fractura', 'fracturas'],
            'pending' => ['pendiente', 'pendientes'],
            'filling' => ['obturación', 'obturaciones'],
            'crown' => ['corona', 'coronas'],
            'root_canal' => ['endodoncia', 'endodoncias'],
            'implant' => ['implante', 'implantes'],
            'bridge' => ['diente con puente', 'dientes con puente'],
            'sealant' => ['sellante', 'sellantes'],
            'veneer' => ['carilla', 'carillas'],
            'missing' => ['diente ausente', 'dientes ausentes'],
        ];
    }

    /**
     * Cuántos dientes tienen cada cosa, contando el diente una vez aunque
     * tenga caries en dos caras. Recibe el arreglo del editor:
     * [numero => ['condition' => ..., 'surfaces' => [...]]].
     */
    public static function resumenDeBoca(array $dientes): array
    {
        $cuenta = [];
        foreach ($dientes as $diente) {
            $cosas = array_unique(array_filter(array_merge(
                [$diente['condition'] ?? null],
                array_values($diente['surfaces'] ?? [])
            )));
            foreach ($cosas as $cosa) {
                if ($cosa !== 'healthy') {
                    $cuenta[$cosa] = ($cuenta[$cosa] ?? 0) + 1;
                }
            }
        }

        $grupo = function (array $claves) use ($cuenta) {
            $g = [];
            foreach ($claves as $clave) {
                if (! empty($cuenta[$clave])) {
                    $g[$clave] = $cuenta[$clave];
                }
            }

            return $g;
        };

        return [
            'por_tratar' => $grupo(self::POR_TRATAR),
            'existentes' => $grupo(self::EXISTENTES),
            'ausentes' => $cuenta['missing'] ?? 0,
        ];
    }

    public static function caraLabels(): array
    {
        return [
            'vestibular' => 'Vestibular',
            'lingual' => 'Lingual / palatina',
            'mesial' => 'Mesial',
            'distal' => 'Distal',
            'oclusal' => 'Oclusal / incisal',
        ];
    }

    public static function conditionLabels(): array
    {
        return [
            'healthy' => 'Sano',
            'decay' => 'Caries',
            'filling' => 'Obturación',
            'crown' => 'Corona',
            'extraction' => 'Extracción',
            'missing' => 'Ausente',
            'implant' => 'Implante',
            'bridge' => 'Puente',
            'root_canal' => 'Endodoncia',
            'fracture' => 'Fractura',
            'sealant' => 'Sellante',
            'veneer' => 'Carilla',
            'pending' => 'Pendiente',
        ];
    }

    public static function conditionColors(): array
    {
        return [
            'healthy' => '#10b981',
            'decay' => '#ef4444',
            'filling' => '#3b82f6',
            'crown' => '#f59e0b',
            'extraction' => '#6b7280',
            'missing' => '#d1d5db',
            'implant' => '#8b5cf6',
            'bridge' => '#f97316',
            'root_canal' => '#ec4899',
            'fracture' => '#dc2626',
            'sealant' => '#06b6d4',
            'veneer' => '#a855f7',
            'pending' => '#fbbf24',
        ];
    }
}
