<?php

namespace Database\Seeders;

use App\Models\PremiumService;
use Illuminate\Database\Seeder;

class PremiumServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'slug' => 'setup-migracion',
                'name' => 'Setup + migración de pacientes',
                'category' => 'setup',
                'price_mxn' => 2500,
                'pricing_type' => 'one_time',
                'sla_days' => 2,
                'short_desc' => 'Te dejamos tu consultorio 100% listo para usar en 48 horas.',
                'long_desc' => "Olvídate del onboarding. Nosotros te lo hacemos:\n\n- Importamos tus pacientes desde Excel, CSV o tu libreta actual (hasta 500 pacientes incluidos).\n- Configuramos tu agenda con horarios reales, doctores y servicios del consultorio.\n- Subimos tus servicios/tratamientos con precios.\n- Te entregamos una cuenta demo-ready para que tú solo entres y empieces a atender.\n\nTe ahorra 6-8 horas de setup tedioso. Lo hacemos en 48 horas hábiles.",
                'bullets' => [
                    'Importación de hasta 500 pacientes',
                    'Configuración de agenda y horarios',
                    'Carga del catálogo de servicios',
                    'Entrega en 48 hrs hábiles',
                ],
                'target_audience' => 'all',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 10,
                'requires_intake' => true,
                'intake_form_schema' => [
                    ['field' => 'excel_url', 'label' => 'Liga a tu Excel de pacientes (Drive/Dropbox)', 'type' => 'text', 'required' => true],
                    ['field' => 'schedule_desc', 'label' => 'Tu horario actual', 'type' => 'textarea', 'required' => true],
                    ['field' => 'services_list', 'label' => 'Lista de servicios + precios', 'type' => 'textarea', 'required' => true],
                ],
                'seller_commission_pct' => 20,
            ],
            [
                'slug' => 'capacitacion-staff',
                'name' => 'Capacitación 1-a-1 (1.5 hrs)',
                'category' => 'capacitacion',
                'price_mxn' => 1500,
                'pricing_type' => 'one_time',
                'sla_days' => 3,
                'short_desc' => 'Sesión en vivo con el doctor y su recepcionista — uso real del sistema.',
                'long_desc' => "Videollamada de 90 minutos donde te enseñamos DocFácil con tu caso real:\n\n- Agenda y bloqueos de horario\n- Expediente clínico paso a paso\n- Recetas PDF y cobros por WhatsApp\n- Check-in con QR y consentimientos\n\nQuedan todas las dudas resueltas. Grabación incluida para tu staff que no pudo conectarse.",
                'bullets' => [
                    'Videollamada en vivo 90 min',
                    'Incluye doctor + recepcionista',
                    'Basado en tu caso real',
                    'Grabación de la sesión incluida',
                ],
                'target_audience' => 'all',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 20,
                'requires_intake' => true,
                'intake_form_schema' => [
                    ['field' => 'preferred_date', 'label' => 'Fecha/hora preferida', 'type' => 'text', 'required' => true],
                    ['field' => 'attendees', 'label' => 'Quiénes van a estar', 'type' => 'textarea', 'required' => false],
                    ['field' => 'specific_doubts', 'label' => 'Dudas específicas o áreas donde quieres enfoque', 'type' => 'textarea', 'required' => false],
                ],
                'seller_commission_pct' => 20,
            ],
            [
                'slug' => 'diseno-recetas-branding',
                'name' => 'Diseño de recetas y branding',
                'category' => 'branding',
                'price_mxn' => 1500,
                'pricing_type' => 'one_time',
                'sla_days' => 5,
                'short_desc' => 'Tu receta PDF con logo y colores — te ves como clínica grande.',
                'long_desc' => "Un diseñador profesional te arma la plantilla visual de tu receta PDF y documentos del consultorio.\n\nIncluye:\n\n- Plantilla de receta PDF con tu logo, cédula, colores y tipografía\n- Membrete de consulta y orden de laboratorio (mismo estilo)\n- Entrega en 5 días hábiles\n- 2 rondas de revisión\n\nSi no tienes logo todavía, te lo creamos por +$1,000 extra.",
                'bullets' => [
                    'Receta PDF con tu branding',
                    'Membrete de consulta y orden de lab',
                    '2 rondas de revisión',
                    'Entrega en 5 días hábiles',
                ],
                'target_audience' => 'all',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 30,
                'requires_intake' => true,
                'intake_form_schema' => [
                    ['field' => 'logo_url', 'label' => 'Liga a tu logo (si tienes)', 'type' => 'text', 'required' => false],
                    ['field' => 'color_preferences', 'label' => 'Colores preferidos', 'type' => 'textarea', 'required' => false],
                    ['field' => 'style_reference', 'label' => 'Alguna receta/documento que te guste como referencia', 'type' => 'textarea', 'required' => false],
                    ['field' => 'need_logo', 'label' => '¿Necesitas también el diseño de logo? (+$1,000)', 'type' => 'checkbox', 'required' => false],
                ],
                'seller_commission_pct' => 20,
            ],
            [
                'slug' => 'whatsapp-business-api-setup',
                'name' => 'WhatsApp Business API — setup y activación',
                'category' => 'whatsapp',
                'price_mxn' => 3000,
                'pricing_type' => 'one_time',
                'sla_days' => 7,
                'short_desc' => 'Registramos tu número en Meta Business y lo conectamos a DocFácil.',
                'long_desc' => "El setup oficial de WhatsApp Business API es un laberinto — lo hacemos por ti.\n\nIncluye:\n\n- Registro de tu empresa en Meta Business Suite\n- Verificación del número de WhatsApp del consultorio\n- Creación de plantillas de recordatorios y cobros aprobadas por Meta\n- Conexión con DocFácil para que los recordatorios sean 100% automáticos\n- Capacitación al staff sobre cómo responder desde la plataforma\n\nDespués del setup, quedan $500/mes por mantenimiento y resolución de issues con Meta (opcional).",
                'bullets' => [
                    'Registro en Meta Business',
                    'Verificación del número',
                    'Plantillas aprobadas por Meta',
                    'Conexión con DocFácil',
                    'Capacitación al staff',
                ],
                'target_audience' => 'all',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 40,
                'requires_intake' => true,
                'intake_form_schema' => [
                    ['field' => 'business_name', 'label' => 'Razón social / nombre comercial', 'type' => 'text', 'required' => true],
                    ['field' => 'rfc', 'label' => 'RFC', 'type' => 'text', 'required' => true],
                    ['field' => 'wa_number', 'label' => 'Número de WhatsApp del consultorio (con LADA)', 'type' => 'text', 'required' => true],
                    ['field' => 'meta_business_owner', 'label' => '¿Ya tienes cuenta de Facebook Business Manager?', 'type' => 'checkbox', 'required' => false],
                ],
                'seller_commission_pct' => 20,
            ],
            [
                'slug' => 'campana-reengagement-mensual',
                'name' => 'Campaña de re-engagement mensual',
                'category' => 'marketing',
                'price_mxn' => 1500,
                'pricing_type' => 'monthly',
                'sla_days' => 30,
                'short_desc' => 'Reactivamos a tus pacientes inactivos por WhatsApp cada mes.',
                'long_desc' => "Tu consultorio tiene pacientes que llevan meses sin regresar. Les mandamos un mensaje personalizado por WhatsApp cada mes para reactivarlos.\n\nIncluye:\n\n- Análisis mensual de pacientes inactivos (6+ meses sin cita)\n- Redacción del mensaje personalizado según tu tono y especialidad\n- Envío por WhatsApp desde tu número\n- Reporte al final del mes: cuántos se reactivaron y cuánto ingreso generaron\n\nROI promedio: 8-12 citas recuperadas al mes = $4,800-7,200 MXN en ingreso adicional.",
                'bullets' => [
                    'Mensaje personalizado por tu especialidad',
                    'Envío mensual automático',
                    'Reporte de reactivación',
                    'Cancelas cuando quieras',
                ],
                'target_audience' => 'all',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 50,
                'requires_intake' => true,
                'intake_form_schema' => [
                    ['field' => 'specialty', 'label' => 'Especialidad principal', 'type' => 'text', 'required' => true],
                    ['field' => 'tone', 'label' => 'Tono preferido (formal, cercano, casual)', 'type' => 'text', 'required' => true],
                    ['field' => 'offer', 'label' => '¿Quieres ofrecer algún incentivo? (ej. 10% descuento en limpieza)', 'type' => 'textarea', 'required' => false],
                ],
                'seller_commission_pct' => 15,
            ],
            [
                'slug' => 'paquete-inauguracion-digital',
                'name' => 'Paquete Inauguración Digital',
                'category' => 'setup',
                'price_mxn' => 9500,
                'pricing_type' => 'one_time',
                'sla_days' => 10,
                'short_desc' => 'Todo listo: setup + capacitación + receta + WhatsApp, en un solo paquete (ahorras $1,500).',
                'long_desc' => "El combo completo para arrancar en grande:\n\n- Setup + migración de pacientes (incluye)\n- Sesión de capacitación 1-a-1 al staff (incluye)\n- Diseño profesional de receta y membrete (incluye)\n- Setup completo de WhatsApp Business API (incluye)\n\nValor individual: $11,000. **Precio paquete: $9,500** — ahorras $1,500.\n\nEn 10 días hábiles pasas de 'no sé ni por dónde empezar' a 'consultorio 100% digital corriendo como reloj'.",
                'bullets' => [
                    'Setup + migración incluidos',
                    'Capacitación 1-a-1',
                    'Branding y recetas',
                    'WhatsApp Business API',
                    'Entrega en 10 días hábiles',
                    'Ahorras $1,500 vs comprar separado',
                ],
                'target_audience' => 'all',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 5,
                'requires_intake' => true,
                'intake_form_schema' => [
                    ['field' => 'notes', 'label' => 'Cuéntanos tu situación actual y qué esperas del paquete', 'type' => 'textarea', 'required' => true],
                ],
                'seller_commission_pct' => 20,
            ],
        ];

        foreach ($services as $data) {
            PremiumService::updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );
        }

        $this->command->info('Seeded ' . count($services) . ' premium services.');
    }
}
