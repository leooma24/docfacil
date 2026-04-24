<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Herramientas gratis publicas (engineering-as-marketing).
 *
 * Cada URL es un activo SEO permanente: Google las indexa, traen
 * trafico organico, convierten visitantes a prospectos sin gasto en
 * ads. Sin login, sin captura obligatoria — el doctor usa la herramienta
 * y el CTA lo lleva al landing si le gusto.
 */
class ToolsController extends Controller
{
    public function calculadoraRoi()
    {
        return view('tools.calculadora-roi');
    }

    /**
     * Captura opcional del lead despues de ver el analisis ROI.
     * Crea un Prospect con source='calculator_tool' y notes con los
     * numeros del doctor para que Omar pueda mandar analisis personalizado.
     */
    public function calculadoraRoiLead(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'calc' => 'required|array',
            'calc.patients' => 'required|integer|min:0|max:500',
            'calc.noShowPct' => 'required|integer|min:0|max:100',
            'calc.avgTicket' => 'required|integer|min:0|max:10000',
            'calc.paperworkHours' => 'required|integer|min:0|max:100',
            'calc.hourlyRate' => 'required|integer|min:0|max:5000',
            'calc.forgottenPct' => 'required|integer|min:0|max:100',
            'calc.total' => 'required|numeric|min:0',
        ]);

        $phoneDigits = preg_replace('/\D/', '', $data['phone']);
        if (strlen($phoneDigits) < 10) {
            return response()->json(['error' => 'Telefono invalido'], 422);
        }

        $calc = $data['calc'];
        $totalLoss = number_format($calc['total'], 2);
        $notes = "Lead desde calculadora ROI:\n"
            . "Pacientes/mes: {$calc['patients']}\n"
            . "% no-show: {$calc['noShowPct']}%\n"
            . "Ticket promedio: \${$calc['avgTicket']}\n"
            . "Horas papeleo/sem: {$calc['paperworkHours']}\n"
            . "Valor hora: \${$calc['hourlyRate']}\n"
            . "% cobros olvidados: {$calc['forgottenPct']}%\n"
            . "TOTAL PERDIDA MENSUAL: \${$totalLoss}";

        try {
            $salesRepId = \App\Models\User::where('email', 'ventas@docfacil.com')
                ->where('role', 'sales')
                ->value('id');

            Prospect::updateOrCreate(
                ['phone' => substr($phoneDigits, -10)],
                [
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'source' => 'calculator_tool',
                    'status' => 'interested',
                    'notes' => $notes,
                    'assigned_to_sales_rep_id' => $salesRepId,
                    'next_contact_at' => now()->addMinutes(5), // contactar rapido
                ]
            );
        } catch (\Throwable $e) {
            Log::error('calculadoraRoiLead store failed', [
                'error' => $e->getMessage(),
                'phone' => $phoneDigits,
            ]);
            return response()->json(['error' => 'No pudimos guardar tus datos'], 500);
        }

        return response()->json(['ok' => true]);
    }
}
