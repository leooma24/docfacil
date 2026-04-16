<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use Barryvdh\DomPDF\Facade\Pdf;

class ProposalPdfController extends Controller
{
    public function __invoke(Prospect $prospect)
    {
        abort_unless(
            auth()->check() && (
                auth()->user()->role === 'super_admin'
                || $prospect->assigned_to_sales_rep_id === auth()->id()
            ),
            403
        );

        $isDentist = str_contains(strtolower($prospect->specialty ?? ''), 'dent')
            || str_contains(strtolower($prospect->specialty ?? ''), 'odont');

        $plan = $isDentist ? 'basico' : 'basico';
        $price = \App\Models\Commission::monthlyPriceForPlan($plan);

        $pdf = Pdf::loadView('pdf.proposal', [
            'prospect' => $prospect,
            'isDentist' => $isDentist,
            'plans' => [
                ['name' => 'Básico', 'price' => 499, 'features' => ['1 doctor', '200 pacientes', 'Citas ilimitadas', 'Recetas PDF', 'Recordatorios WhatsApp', 'Check-in QR']],
                ['name' => 'Pro', 'price' => 999, 'popular' => true, 'features' => ['Hasta 3 doctores', 'Pacientes ilimitados', 'Todo del Básico', 'Odontograma interactivo', 'Consentimientos digitales', 'Portal del paciente', 'Soporte prioritario']],
                ['name' => 'Clínica', 'price' => 1999, 'features' => ['Doctores ilimitados', 'Multi-sucursal', 'Todo del Pro', 'Reportes por doctor', 'Onboarding 1 a 1']],
            ],
            'date' => now()->translatedFormat('d \d\e F \d\e Y'),
            'repName' => auth()->user()->name,
        ]);

        return $pdf->stream("propuesta-{$prospect->name}.pdf");
    }
}
