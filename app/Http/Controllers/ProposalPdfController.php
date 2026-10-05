<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Support\LoQueTraeCadaPlan;
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

        $pdf = Pdf::loadView('pdf.proposal', self::datos($prospect, auth()->user()->name));

        return $pdf->stream("propuesta-{$prospect->name}.pdf");
    }

    /**
     * Lo que lleva la propuesta. Los planes salen de LoQueTraeCadaPlan,
     * la misma fuente de la página de inicio y del panel.
     */
    public static function datos(Prospect $prospect, string $repName): array
    {
        $plans = [];

        foreach (['basico', 'profesional', 'clinica'] as $key) {
            $plan = LoQueTraeCadaPlan::plan($key);

            $plans[] = [
                'name' => $plan['name'],
                'price' => \App\Models\Commission::monthlyPriceForPlan($key),
                'popular' => $plan['popular'],
                'limits' => $plan['limits'],
                'lead' => $plan['lead'],
                'features' => $plan['features'],
            ];
        }

        return [
            'prospect' => $prospect,
            'plans' => $plans,
            'date' => now()->translatedFormat('d \d\e F \d\e Y'),
            'repName' => $repName,
        ];
    }
}
