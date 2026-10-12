<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Support\CajaDelDia;
use App\Support\DatosFiscales;
use Carbon\Carbon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

/**
 * La lista para su contador (11-oct-2026): en Excel, los cobros del mes que
 * pidieron factura, con los datos fiscales del paciente. El CFDI va en la
 * fase 2; mientras, el dentista hace lo de siempre: se lo pasa a su contador.
 */
class ListaParaSuContadorController extends Controller
{
    public function __invoke(string $mes)
    {
        $usuario = auth()->user();
        abort_if(! $usuario->clinic_id || ! $usuario->veElDinero(), 403);
        abort_unless(preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes), 404);

        $inicio = Carbon::createFromFormat('Y-m-d', "{$mes}-01")->startOfMonth();

        $cobros = Payment::withoutGlobalScopes()
            ->with(['patient' => fn ($q) => $q->withoutGlobalScopes(), 'service'])
            ->where('clinic_id', $usuario->clinic_id)
            ->where('factura_solicitada', true)
            ->whereBetween('payment_date', [$inicio->toDateString(), $inicio->copy()->endOfMonth()->toDateString()])
            ->orderBy('payment_date')
            ->get();

        $ruta = tempnam(sys_get_temp_dir(), 'contador');
        $escritor = new Writer();
        $escritor->openToFile($ruta);
        $escritor->addRow(Row::fromValues(['Fecha', 'Paciente', 'RFC', 'Razón social', 'Régimen fiscal', 'Código postal',
            'Uso del CFDI', 'Correo', 'Concepto', 'Total', 'Pagado', 'Forma de pago', 'Ya se le mandó', 'Nota']));

        foreach ($cobros as $cobro) {
            $p = $cobro->patient;
            $completos = $p && filled($p->rfc) && filled($p->razon_social) && filled($p->regimen_fiscal) && filled($p->cp_fiscal);
            $escritor->addRow(Row::fromValues([
                $cobro->payment_date?->format('d/m/Y'),
                $p ? trim("{$p->first_name} {$p->last_name}") : '',
                $p?->rfc ?? '',
                $p?->razon_social ?? '',
                DatosFiscales::REGIMENES[$p?->regimen_fiscal] ?? '',
                $p?->cp_fiscal ?? '',
                // Si no se capturó, D01: honorarios médicos y dentales.
                DatosFiscales::USOS[$p?->uso_cfdi ?: 'D01'],
                $p?->email ?? '',
                $cobro->service?->name ?: ($cobro->notes ?: 'Consulta'),
                (float) $cobro->amount,
                (float) $cobro->amount_paid,
                CajaDelDia::FORMAS[$cobro->payment_method] ?? ($cobro->payment_method ?? ''),
                $cobro->factura_enviada_at ? 'Sí' : 'No',
                $completos ? '' : 'Faltan sus datos fiscales',
            ]));
        }
        $escritor->close();

        return response()->download($ruta, "lista-para-su-contador-{$mes}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }
}
