<?php

namespace App\Http\Controllers;

class BlogController extends Controller
{
    public static function articles(): array
    {
        return [
            'como-reducir-inasistencias-consultorio' => [
                'title' => 'Cómo reducir inasistencias en tu consultorio un 40%',
                'description' => 'Estrategias probadas para que tus pacientes lleguen a sus citas. Recordatorios WhatsApp, confirmación automática y más.',
                'image' => '/images/blog/inasistencias.jpg',
                'date' => '2026-04-01',
                'read_time' => '4 min',
                'category' => 'Gestión',
                'content' => [
                    ['type' => 'p', 'text' => 'Si eres médico o dentista en México, probablemente pierdes entre 3 y 5 citas a la semana porque los pacientes simplemente no llegan. Eso es dinero, tiempo y una silla vacía que pudiste llenar con otro paciente.'],
                    ['type' => 'h2', 'text' => '¿Por qué no llegan?'],
                    ['type' => 'p', 'text' => 'Las principales razones son: se les olvidó, surgió algo, no confirmaron, o simplemente no saben cuándo era. La buena noticia es que la mayoría de estas se resuelven con un sistema simple de recordatorios.'],
                    ['type' => 'h2', 'text' => '3 estrategias que funcionan'],
                    ['type' => 'h3', 'text' => '1. Recordatorio WhatsApp 24 horas antes'],
                    ['type' => 'p', 'text' => 'El 95% de los mexicanos revisan WhatsApp. Un mensaje automático que diga "Hola María, te recordamos que mañana a las 10am tienes cita con el Dr. López" reduce inasistencias un 30% desde el primer mes.'],
                    ['type' => 'h3', 'text' => '2. Confirmación con respuesta'],
                    ['type' => 'p', 'text' => 'No solo recordar, sino pedir que confirmen. "Responde SÍ para confirmar o llámanos para reagendar." Los pacientes que confirman tienen 85% de probabilidad de llegar.'],
                    ['type' => 'h3', 'text' => '3. Recordatorio 2 horas antes'],
                    ['type' => 'p', 'text' => 'El último push. Algunos pacientes confirmaron ayer pero hoy se les complica. Un mensaje 2 horas antes les da la opción de avisar si no pueden, y tú puedes llenar esa silla.'],
                    ['type' => 'h2', 'text' => 'Resultado real'],
                    ['type' => 'p', 'text' => 'Consultorios que implementan las 3 estrategias juntas reportan una reducción del 40% en inasistencias. Con 80 pacientes al mes, eso son 12 citas recuperadas = más de $7,000 pesos mensuales de ingreso que antes perdías.'],
                    ['type' => 'cta', 'text' => 'DocFácil envía estos recordatorios automáticamente por WhatsApp. Pruébalo 14 días gratis.'],
                ],
            ],

            'software-consultorio-medico-mexico-guia' => [
                'title' => 'Guía 2026: Cómo elegir software para tu consultorio médico en México',
                'description' => 'Qué buscar, qué evitar, y cuánto cuesta realmente digitalizar tu práctica médica. Comparativa actualizada.',
                'image' => '/images/blog/software-guia.jpg',
                'date' => '2026-03-28',
                'read_time' => '6 min',
                'category' => 'Tecnología',
                'content' => [
                    ['type' => 'p', 'text' => 'Elegir un software para tu consultorio es una decisión importante. Un mal software te costará más tiempo del que ahorras. Esta guía te ayuda a decidir sin depender de lo que te vendan.'],
                    ['type' => 'h2', 'text' => 'Lo mínimo que debe tener'],
                    ['type' => 'p', 'text' => 'Antes de ver marcas, define qué necesitas. Los imprescindibles son: agenda de citas, expediente clínico, recetas digitales y recordatorios para pacientes. Si eres dentista, agrega odontograma.'],
                    ['type' => 'h2', 'text' => '¿Nube o instalado?'],
                    ['type' => 'p', 'text' => 'Los sistemas instalados (como iPraxis) requieren una computadora específica y si esa compu falla, perdiste todo. Los sistemas en la nube (como DocFácil) funcionan desde cualquier dispositivo con internet, se actualizan solos y hacen backups automáticos.'],
                    ['type' => 'h2', 'text' => '¿Cuánto cuesta?'],
                    ['type' => 'p', 'text' => 'Los precios en México van desde $0 (planes gratuitos limitados) hasta $25,000+ anuales (Dentrix, Eaglesoft). Un rango razonable para un doctor individual es entre $100 y $500 pesos al mes. Para clínicas con varios doctores, entre $500 y $1,500.'],
                    ['type' => 'h2', 'text' => 'Errores comunes'],
                    ['type' => 'p', 'text' => '1) Comprar por features que nunca usarás. 2) No verificar que tenga soporte en español. 3) No probar antes de pagar. 4) Elegir el más caro pensando que es el mejor.'],
                    ['type' => 'h2', 'text' => '¿Qué preguntar antes de contratar?'],
                    ['type' => 'p', 'text' => '¿Puedo probarlo gratis? ¿Tiene soporte en español por WhatsApp? ¿Puedo cancelar sin penalización? ¿Mis datos están seguros? ¿Funciona en mi celular? Si la respuesta a alguna es "no", piénsalo dos veces.'],
                    ['type' => 'cta', 'text' => 'DocFácil cumple con todo esto: 14 días gratis, sin tarjeta, soporte WhatsApp directo, cancela cuando quieras.'],
                ],
            ],

            'expediente-clinico-digital-nom-004' => [
                'title' => 'Expediente clínico digital: Qué dice la NOM-004 y cómo cumplirla',
                'description' => 'La norma oficial mexicana exige ciertos datos en el expediente clínico. Te explicamos cómo cumplir sin complicarte.',
                'image' => '/images/blog/nom-004.jpg',
                'date' => '2026-03-20',
                'read_time' => '5 min',
                'category' => 'Legal',
                'content' => [
                    ['type' => 'p', 'text' => 'La NOM-004-SSA3-2012 es la norma oficial mexicana que regula el expediente clínico. Aplica a todos los prestadores de servicios de salud, desde consultorios pequeños hasta hospitales. Si eres médico o dentista, te aplica.'],
                    ['type' => 'h2', 'text' => '¿Qué exige la norma?'],
                    ['type' => 'p', 'text' => 'En resumen: cada consulta debe tener fecha, nombre del paciente, motivo, diagnóstico, tratamiento, y nombre del médico responsable. El expediente debe ser confidencial, ordenado cronológicamente e integrado (toda la info en un solo lugar).'],
                    ['type' => 'h2', 'text' => '¿Es válido el expediente digital?'],
                    ['type' => 'p', 'text' => 'Sí. La NOM-004 no exige papel. Un expediente digital es válido siempre que: sea legible, esté resguardado con medidas de seguridad, tenga respaldo (backups), y cumpla con la LFPDPPP (protección de datos personales).'],
                    ['type' => 'h2', 'text' => 'Ventajas del digital sobre el papel'],
                    ['type' => 'p', 'text' => 'El papel se pierde, se moja, no se puede buscar. Un expediente digital te permite buscar por nombre en segundos, nunca se pierde (backups automáticos), cumple con la trazabilidad que pide la norma, y genera reportes.'],
                    ['type' => 'h2', 'text' => 'Consentimiento informado'],
                    ['type' => 'p', 'text' => 'La norma también exige consentimiento informado para procedimientos. La firma digital en tablet o celular es legalmente válida en México desde la Ley de Firma Electrónica Avanzada. Esto elimina el papeleo sin perder validez legal.'],
                    ['type' => 'cta', 'text' => 'DocFácil genera expedientes que cumplen con NOM-004, con firma digital incluida. Pruébalo gratis 14 días.'],
                ],
            ],

            'recetas-electronicas-mexico-guia-completa' => [
                'title' => 'Recetas electrónicas en México: Guía completa para médicos',
                'description' => 'Todo lo que necesitas saber sobre recetas electrónicas: validez legal, qué datos deben llevar, y cómo generarlas en segundos.',
                'image' => '/images/blog/recetas.jpg',
                'date' => '2026-03-15',
                'read_time' => '4 min',
                'category' => 'Legal',
                'content' => [
                    ['type' => 'p', 'text' => 'Cada vez más médicos en México usan recetas electrónicas en vez de escribirlas a mano. Además de verse más profesional, evitas errores de dosis por letra ilegible y el paciente puede guardarla en su celular.'],
                    ['type' => 'h2', 'text' => '¿Son legalmente válidas?'],
                    ['type' => 'p', 'text' => 'Sí. La COFEPRIS acepta recetas electrónicas siempre que contengan: nombre y cédula del médico, institución, nombre del paciente, fecha, medicamento con presentación, dosis, vía de administración, frecuencia y duración del tratamiento.'],
                    ['type' => 'h2', 'text' => 'Excepciones importantes'],
                    ['type' => 'p', 'text' => 'Para medicamentos controlados (Grupo I, II, III del cuadro básico), aún se requiere receta especial con formato oficial de la SSA. Las recetas electrónicas aplican para medicamentos de venta libre y con receta simple.'],
                    ['type' => 'h2', 'text' => 'Cómo generarlas en segundos'],
                    ['type' => 'p', 'text' => 'Con un software médico como DocFácil, llenas el medicamento, dosis y frecuencia, y el sistema genera un PDF con tu cédula, nombre de la clínica, logotipo y firma digital. El paciente lo recibe por WhatsApp o lo descarga directo.'],
                    ['type' => 'h2', 'text' => 'Ventajas sobre las recetas de papel'],
                    ['type' => 'p', 'text' => '1) Legibles siempre. 2) Incluyen todos los datos legales automáticamente. 3) Quedan archivadas en el expediente. 4) El paciente no las pierde. 5) Se envían por WhatsApp al instante.'],
                    ['type' => 'cta', 'text' => 'Con DocFácil generas recetas PDF profesionales en 10 segundos. Pruébalo gratis.'],
                ],
            ],

            'odontograma-digital-beneficios-dentistas' => [
                'title' => 'Odontograma digital: Por qué tu consultorio dental lo necesita',
                'description' => 'El odontograma interactivo mejora la comunicación con pacientes, agiliza diagnósticos y digitaliza tu práctica dental.',
                'image' => '/images/blog/odontograma.jpg',
                'date' => '2026-03-10',
                'read_time' => '4 min',
                'category' => 'Odontología',
                'content' => [
                    ['type' => 'p', 'text' => 'Si eres dentista, sabes que el odontograma es tu herramienta principal de diagnóstico y plan de tratamiento. Pero si todavía lo haces en papel o en una hoja de Excel, estás perdiendo tiempo y oportunidades.'],
                    ['type' => 'h2', 'text' => '¿Qué es un odontograma digital?'],
                    ['type' => 'p', 'text' => 'Es un diagrama dental interactivo donde marcas la condición de cada diente con clics: caries, extracción, corona, puente, obturación, etc. Normalmente soporta entre 8 y 15 condiciones diferentes con colores para identificarlas.'],
                    ['type' => 'h2', 'text' => 'Beneficios sobre el papel'],
                    ['type' => 'p', 'text' => '1) Historial visual: ves cómo ha evolucionado la boca del paciente a lo largo de meses. 2) Comunicación: le muestras al paciente en pantalla qué dientes necesitan trabajo. 3) Rapidez: marcar 5 condiciones toma 10 segundos vs 2 minutos dibujando.'],
                    ['type' => 'h2', 'text' => 'Cómo ayuda a vender tratamientos'],
                    ['type' => 'p', 'text' => 'Cuando el paciente VE en color qué dientes tienen caries o necesitan corona, entiende mejor y acepta más tratamientos. Estudios muestran que la aceptación de tratamiento sube un 25% cuando se usa un odontograma visual.'],
                    ['type' => 'h2', 'text' => 'Qué buscar en un odontograma digital'],
                    ['type' => 'p', 'text' => 'Que sea interactivo (click para marcar, no teclear códigos), que tenga al menos 10 condiciones, que se guarde automáticamente, que esté integrado al expediente del paciente, y que funcione en tablet para usarlo durante la consulta.'],
                    ['type' => 'cta', 'text' => 'DocFácil tiene odontograma interactivo con 13 condiciones, integrado al expediente y compartible con el paciente. Pruébalo gratis 14 días.'],
                ],
            ],
        ];
    }

    public function index()
    {
        return view('blog.index', ['articles' => self::articles()]);
    }

    public function show(string $slug)
    {
        $articles = self::articles();
        if (!isset($articles[$slug])) {
            abort(404);
        }

        return view('blog.show', [
            'article' => $articles[$slug],
            'slug' => $slug,
            'related' => collect($articles)->except($slug)->take(2)->all(),
        ]);
    }
}
