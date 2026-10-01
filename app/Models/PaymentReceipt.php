<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Un abono: cuánto entró, cuándo y cómo. La suma de los recibos de un cobro
 * es lo pagado; el corte de caja cuenta los recibos por su fecha, no por la
 * del cobro.
 */
class PaymentReceipt extends Model
{
    use BelongsToClinic;

    protected $fillable = ['clinic_id', 'payment_id', 'amount', 'payment_method', 'paid_at', 'notes'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Le da su recibo a cada cobro que no tiene: el liquidado por el total y
     * el de a plazos por lo abonado, con la fecha del cobro. Correrlo dos
     * veces no duplica nada.
     */
    public static function respaldarCobrosSinRecibos(): void
    {
        DB::table('payments')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('payment_receipts')->whereColumn('payment_receipts.payment_id', 'payments.id'))
            ->where(fn ($q) => $q->where('status', 'paid')->orWhere('amount_paid', '>', 0))
            ->orderBy('id')
            ->chunk(500, function ($cobros) {
                $filas = [];
                foreach ($cobros as $c) {
                    $monto = $c->status === 'paid' ? $c->amount : $c->amount_paid;
                    if ((float) $monto <= 0) {
                        continue;
                    }
                    $filas[] = [
                        'clinic_id' => $c->clinic_id,
                        'payment_id' => $c->id,
                        'amount' => $monto,
                        'payment_method' => $c->payment_method,
                        'paid_at' => $c->payment_date,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                if ($filas) {
                    DB::table('payment_receipts')->insert($filas);
                }
            });
    }
}
