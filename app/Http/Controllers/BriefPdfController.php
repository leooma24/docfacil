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

    /**
     * Lo que usa el brief: capturas de la demo (public/images/brief, tomadas
     * del consultorio de práctica), los planes de la misma fuente que la
     * landing y la demo con sus accesos.
     */
    private function viewData(string $mode): array
    {
        $img = fn (string $nombre) => $mode === 'pdf'
            ? public_path("images/brief/{$nombre}.jpg")
            : asset("images/brief/{$nombre}.jpg");

        return [
            'mode' => $mode,
            'img' => $img,
            'planes' => \App\Support\LoQueTraeCadaPlan::planes(),
            'registerUrl' => url('/doctor/register?source=brief'),
            'qrDataUri' => $this->qrCodeDataUri(url('/doctor/register?source=brief-qr')),
            'demoUrl' => url('/demo'),
            'whatsappLink' => 'https://wa.me/526682493398?text=' . urlencode('Hola Omar, vi el brief de DocFácil y tengo una pregunta.'),
            'logoPath' => $mode === 'pdf' ? public_path('images/logo_doc_facil.png') : asset('images/logo_doc_facil.png'),
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
