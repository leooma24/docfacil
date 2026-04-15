<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;

class BriefPdfController extends Controller
{
    public function download(Request $request)
    {
        $pdf = Pdf::loadView('pdf.brief', $this->viewData('pdf'))
            ->setPaper('letter', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $request->boolean('view')
            ? $pdf->stream('DocFacil-Brief.pdf')
            : $pdf->download('DocFacil-Brief-2026.pdf');
    }

    public function web()
    {
        return view('pdf.brief', $this->viewData('web'));
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
            'registerUrl' => url('/doctor/register?source=brief'),
            'qrDataUri' => $this->qrCodeDataUri(url('/doctor/register?source=brief-qr')),
            'whatsappLink' => 'https://wa.me/526682493398',
            'logoPath' => public_path('images/logo_doc_facil.png'),
            'screens' => $screens,
        ];
    }

    private function qrCodeDataUri(string $url): string
    {
        try {
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($url)
                ->size(220)
                ->margin(4)
                ->build();

            return $result->getDataUri();
        } catch (\Throwable $e) {
            return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($url);
        }
    }
}
