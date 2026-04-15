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
        $pdf = Pdf::loadView('pdf.brief', $this->viewData())
            ->setPaper('letter', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'helvetica');

        return $request->boolean('view')
            ? $pdf->stream('DocFacil-Brief.pdf')
            : $pdf->download('DocFacil-Brief-2026.pdf');
    }

    public function web()
    {
        return view('pdf.brief', $this->viewData());
    }

    private function viewData(): array
    {
        return [
            'mode' => 'pdf',
            'registerUrl' => url('/doctor/register?source=brief'),
            'qrDataUri' => $this->qrCodeDataUri(url('/doctor/register?source=brief-qr')),
            'whatsappLink' => 'https://wa.me/526682493398',
            'logoPath' => public_path('images/logo_doc_facil.png'),
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
            // Fallback: servicio público si endroid falla por falta de GD/Imagick
            return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($url);
        }
    }
}
