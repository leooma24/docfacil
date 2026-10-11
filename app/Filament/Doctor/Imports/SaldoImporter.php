<?php

namespace App\Filament\Doctor\Imports;

use App\Filament\Doctor\Imports\Concerns\EncuentraAlPaciente;
use App\Models\Payment;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

/**
 * Lo que le deben, traído de su libreta o de su sistema anterior
 * (10-oct-2026). Cada renglón queda como un cobro pendiente del paciente, y
 * aparece en «Le deben» y en «Cobrar $X» de su ficha desde el primer día.
 *
 * Subir el mismo archivo dos veces no duplica (mismo paciente, mismo monto y
 * mismo concepto).
 */
class SaldoImporter extends Importer
{
    use EncuentraAlPaciente;

    protected static ?string $model = Payment::class;

    public static function getColumns(): array
    {
        $soloSeLee = fn () => null;

        return [
            ImportColumn::make('paciente')->label('Paciente (nombre completo)')->requiredMapping()->fillRecordUsing($soloSeLee)
                ->guess(['paciente', 'nombre', 'nombre del paciente', 'nombre completo', 'cliente', 'patient', 'name'])->example('Ana Ruiz López'),
            ImportColumn::make('telefono')->label('Teléfono')->fillRecordUsing($soloSeLee)
                ->guess(['telefono', 'teléfono', 'tel', 'celular', 'cel', 'whatsapp', 'phone'])->example('6681234567'),
            ImportColumn::make('monto')->label('Cuánto debe')->requiredMapping()->fillRecordUsing($soloSeLee)
                ->guess(['debe', 'saldo', 'adeudo', 'monto', 'pendiente', 'por pagar', 'balance'])->example('1250.50'),
            ImportColumn::make('concepto')->label('Concepto')->fillRecordUsing($soloSeLee)
                ->guess(['concepto', 'tratamiento', 'motivo', 'descripcion', 'descripción', 'notas'])->example('Corona 36'),
            ImportColumn::make('fecha')->label('Desde cuándo')->fillRecordUsing($soloSeLee)
                ->guess(['fecha', 'desde', 'fecha del tratamiento', 'date'])->example('01/09/2026'),
        ];
    }

    public function resolveRecord(): ?Payment
    {
        $clinicId = (int) $this->import->user->clinic_id;

        $monto = round((float) preg_replace('/[^\d.]/', '', str_replace(',', '', (string) ($this->data['monto'] ?? ''))), 2);
        if ($monto <= 0) {
            throw new RowImportFailedException('No se entendió cuánto debe. Escríbalo como 1250.50.');
        }
        $concepto = trim((string) ($this->data['concepto'] ?? '')) ?: 'Saldo anterior';
        $fecha = PatientImporter::fechaMexicana($this->data['fecha'] ?? null) ?? today()->toDateString();

        $paciente = $this->pacienteDelRenglon($clinicId, $this->data['paciente'] ?? null, $this->data['telefono'] ?? null);

        $existente = Payment::withoutGlobalScopes()->where('clinic_id', $clinicId)->where('patient_id', $paciente->id)
            ->where('amount', $monto)->where('notes', $concepto)->first();
        if ($existente) {
            return $existente;
        }

        $cobro = new Payment();
        $cobro->clinic_id = $clinicId;
        $cobro->patient_id = $paciente->id;
        $cobro->amount = $monto;
        $cobro->amount_paid = 0;
        $cobro->status = 'pending';
        $cobro->payment_method = 'cash';
        $cobro->notes = $concepto;
        $cobro->payment_date = $fecha;
        $cobro->due_date = $fecha;

        return $cobro;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $cuerpo = 'Se trajeron ' . number_format($import->successful_rows) . ' ' . Str::plural('saldo', $import->successful_rows) . '. Ya salen en «Le deben».';
        if ($fallidas = $import->getFailedRowsCount()) {
            $cuerpo .= ' ' . number_format($fallidas) . ' ' . Str::plural('renglón', $fallidas) . ' no ' . ($fallidas === 1 ? 'entró' : 'entraron')
                . ': descargue el archivo de errores para ver cuáles y por qué.';
        }

        return $cuerpo;
    }
}
