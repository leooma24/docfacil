<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentPlanItem extends Model
{
    protected $fillable = [
        'treatment_plan_id', 'service_id',
        'description', 'quantity', 'unit_price', 'subtotal',
        'tooth_number', 'sort_order', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'sort_order' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (TreatmentPlanItem $item) {
            $item->subtotal = (float) $item->unit_price * (int) $item->quantity;
        });
    }

    public function treatmentPlan(): BelongsTo { return $this->belongsTo(TreatmentPlan::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }

    public function appointments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** La cita que viene para este tratamiento, si ya se agendó. */
    public function citaPendiente(): ?Appointment
    {
        return $this->appointments()
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->where('starts_at', '>=', now()->subHours(12))
            ->orderBy('starts_at')
            ->first();
    }

    /** "Hecho el 05/10", "Agendado: lun 20/10 10:00" o "Por agendar". */
    public function estado(): string
    {
        if ($this->completed_at) {
            return 'Hecho el ' . $this->completed_at->format('d/m/Y');
        }

        $cita = $this->citaPendiente();

        return $cita
            ? 'Agendado: ' . $cita->starts_at->locale('es')->isoFormat('ddd D/MM HH:mm')
            : 'Por agendar';
    }
}
