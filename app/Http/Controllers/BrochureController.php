<?php

namespace App\Http\Controllers;

use App\Support\LoQueTraeCadaPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;

class BrochureController extends Controller
{
    /** La misma hoja que el PDF, para verla en el navegador (11-oct-2026). */
    public function web()
    {
        return view('pdf.brochure', $this->viewData('web'));
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
        // Capturas del 11-oct-2026, de la demo con datos inventados.
        $screenFiles = [
            'escritorio' => 'images/brochure/escritorio.jpg',
            'agenda' => 'images/brochure/agenda-doctor.jpg',
            'consulta' => 'images/brochure/consulta.jpg',
            'cobro' => 'images/brochure/cobro.jpg',
            'presupuesto' => 'images/brochure/presupuesto-cel.jpg',
            'llamar' => 'images/brochure/llamar.jpg',
            'ortodoncia' => 'images/brochure/ortodoncia.jpg',
            'comisiones' => 'images/brochure/comisiones.jpg',
            'portal' => 'images/brochure/portal-cel.jpg',
            'traer' => 'images/brochure/traer.jpg',
            'perfil' => 'images/brochure/perfil.jpg',
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
