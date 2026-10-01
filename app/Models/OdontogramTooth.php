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

    /** incisivo, canino, premolar o molar, por el segundo dígito FDI. */
    public static function tipo(int $numero): string
    {
        return match ($numero % 10) {
            1, 2 => 'incisivo',
            3 => 'canino',
            4, 5 => 'premolar',
            default => 'molar',
        };
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
