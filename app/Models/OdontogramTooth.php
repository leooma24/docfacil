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

    public function odontogram(): BelongsTo
    {
        return $this->belongsTo(Odontogram::class);
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
