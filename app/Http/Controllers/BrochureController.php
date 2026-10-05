<?php

namespace App\Http\Controllers;

use App\Support\LoQueTraeCadaPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;

class BrochureController extends Controller
{
    public function web()
    {
        return view('brochure.index', $this->viewData('web'));
    }

    public function pdf(Request $request)
    {
        $pdf = Pdf::loadView('pdf.brochure', $this->viewData('pdf'))
            ->setPaper('letter', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $request->boolean('view')
            ? $pdf->stream('DocFacil-Brochure.pdf')
            : $pdf->download('DocFacil-Brochure-2026.pdf');
    }

    private function viewData(string $mode): array
    {
        $screenFiles = [
            'dashboard'   => 'images/screenshots/01-dashboard.png',
            'citas'       => 'images/screenshots/02-citas-lista.png',
            'calendario'  => 'images/screenshots/03-calendario.png',
            'pacientes'   => 'images/screenshots/04-pacientes.png',
            'expediente'  => 'images/screenshots/05-expediente.png',
            'recetas'     => 'images/screenshots/06-recetas.png',
            'odontograma' => 'images/screenshots/07-odontograma-editor.png',
            'cobros'      => 'images/screenshots/08-cobros.png',
            'consulta'    => 'images/screenshots/09-consulta.png',
            'servicios'   => 'images/screenshots/10-servicios.png',
            'landing'     => 'images/screenshots/11-landing-hero.png',
        ];

        $screens = [];
        foreach ($screenFiles as $key => $relPath) {
            $screens[$key] = $mode === 'pdf'
                ? public_path($relPath)
                : asset($relPath);
        }

        return [
            'mode' => $mode,
            'registerUrl' => url('/doctor/register?source=brochure'),
            'qrDataUri' => $this->qrCodeDataUri(url('/doctor/register?source=brochure-qr')),
            'whatsappLink' => 'https://wa.me/526682493398',
            'pages' => $this->buildPages(),
            'screens' => $screens,
        ];
    }

    private function buildPages(): array
    {
        // Los planes salen de una sola fuente: lo que el sistema hace hoy.
        return [
            'plans' => LoQueTraeCadaPlan::planes(),
        ];
    }

    private function qrCodeDataUri(string $url): string
    {
        try {
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($url)
                ->size(260)
                ->margin(4)
                ->build();

            return $result->getDataUri();
        } catch (\Throwable $e) {
            return 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($url);
        }
    }
}
