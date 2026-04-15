<?php

namespace App\Http\Controllers;

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
        return [
            'features' => [
                ['icon' => '📅', 'title' => 'Agenda inteligente', 'desc' => 'Calendario visual multi-doctor, arrastrar y soltar citas, acceso desde cualquier dispositivo. Vista diaria, semanal, mensual.'],
                ['icon' => '💬', 'title' => 'Recordatorios WhatsApp', 'desc' => 'Mensajes automáticos 24h y 2h antes de la cita. Los consultorios reportan hasta 40% menos inasistencias.'],
                ['icon' => '📋', 'title' => 'Expediente clínico digital', 'desc' => 'Historial completo, alergias, padecimientos, notas SOAP. Todo organizado por paciente y consulta.'],
                ['icon' => '📄', 'title' => 'Recetas PDF profesionales', 'desc' => 'Con logo del consultorio, cédula profesional y firma digital. El paciente recibe PDF por WhatsApp.'],
                ['icon' => '🦷', 'title' => 'Odontograma interactivo', 'desc' => '13 condiciones dentales, colores por estado, compartible con el paciente. Historial visual de cada pieza.'],
                ['icon' => '💰', 'title' => 'Cobro por WhatsApp', 'desc' => 'Envía el monto y link de pago directo al chat. Control automático de cobros pendientes por paciente.'],
                ['icon' => '📱', 'title' => 'Check-in con QR', 'desc' => 'El paciente escanea al llegar, firma consentimiento en tablet o celular. Sin papel, sin filas.'],
                ['icon' => '✍', 'title' => 'Firma digital legal', 'desc' => 'Consentimientos informados firmados en pantalla táctil, con timestamp y respaldo legal.'],
                ['icon' => '👥', 'title' => 'Portal del paciente', 'desc' => 'Tus pacientes ven sus citas, recetas, pagos e historial. Reduce llamadas de consulta rutinaria.'],
                ['icon' => '📊', 'title' => 'Dashboard con gráficas', 'desc' => 'Ingresos, citas por doctor, cobros pendientes, pacientes activos. Datos del mes vs. mes anterior.'],
                ['icon' => '🔔', 'title' => 'Alertas inteligentes', 'desc' => 'Pacientes inactivos, recetas vencidas, cumpleaños, cobros atrasados. El sistema te avisa.'],
                ['icon' => '🏥', 'title' => 'Multi-doctor y multi-sede', 'desc' => 'Gestiona varios doctores o sucursales con comisiones automáticas entre ellos. Reportes por doctor.'],
            ],
            'testimonials' => [
                ['name' => 'Dra. María Fernández', 'city' => 'CDMX', 'specialty' => 'Odontología', 'quote' => 'Bajé las inasistencias de 30% a 8% el primer mes. El recordatorio por WhatsApp cambió mi consulta.'],
                ['name' => 'Dr. Carlos Mendoza', 'city' => 'Guadalajara', 'specialty' => 'Medicina General', 'quote' => 'Antes perdía 2 horas al día buscando expedientes en papel. Ahora tengo todo en mi celular.'],
                ['name' => 'Dra. Ana Torres', 'city' => 'Monterrey', 'specialty' => 'Ortodoncia', 'quote' => 'El odontograma y las recetas PDF le dan un aire profesional que mis pacientes notan y valoran.'],
            ],
            'plans' => [
                ['name' => 'Free', 'price' => 0, 'ideal' => 'Probar el sistema sin tarjeta', 'features' => ['1 doctor', '30 pacientes', 'Agenda básica', '20 citas / mes']],
                ['name' => 'Básico', 'price' => 149, 'ideal' => 'Consultorios individuales que arrancan', 'features' => ['1 doctor', '200 pacientes', 'WhatsApp + recetas PDF', 'Check-in QR', 'Expediente completo']],
                ['name' => 'Pro', 'price' => 299, 'popular' => true, 'ideal' => 'Consultorios establecidos', 'features' => ['Hasta 3 doctores', 'Pacientes ilimitados', 'Odontograma interactivo', 'Portal del paciente', 'Reportes avanzados', 'Soporte prioritario']],
                ['name' => 'Clínica', 'price' => 499, 'ideal' => 'Clínicas con varios doctores o sedes', 'features' => ['Doctores ilimitados', 'Multi-sucursal', 'Comisiones entre doctores', 'Reportes por doctor', 'Onboarding 1 a 1']],
            ],
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
