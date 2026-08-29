<?php

namespace App\Http\Controllers;

class BlogController extends Controller
{
    public static function articles(): array
    {
        return [
            'cuanto-cuesta-abrir-consultorio-dental-mexico' => [
                'title' => 'Cuánto cuesta abrir un consultorio dental en México (números reales 2026)',
                'description' => 'Desglose honesto de la inversión: equipo, permisos COFEPRIS, adecuación del local y los gastos mensuales que casi nadie menciona. Incluye el cálculo de cuántos pacientes necesitas al mes para no perder dinero.',
                'image' => '/images/blog/costo-consultorio-dental.jpg',
                'date' => '2026-08-29',
                'read_time' => '9 min',
                'category' => 'Finanzas',
                'content' => [
                    ['type' => 'p', 'text' => 'La respuesta corta que vas a encontrar en todos lados es "entre 200 y 300 mil pesos". Es cierta, y a la vez es la que más consultorios ha quebrado, porque solo cuenta el equipo. Deja fuera los permisos, la adecuación del local, los tres o cuatro meses en que todavía no tienes pacientes, y los gastos fijos que llegan puntuales aunque tu agenda esté vacía.'],
                    ['type' => 'p', 'text' => 'En México operan 76,188 consultorios dentales, según datos de la Secretaría de Economía a mayo de 2026. Es un mercado enorme y también muy competido. Aquí está el desglose de lo que de verdad cuesta abrir uno, con precios de 2026, y al final el número que casi nadie calcula antes de firmar la renta: cuántos pacientes al mes necesitas para no estar perdiendo dinero.'],

                    ['type' => 'h2', 'text' => '1. El equipo: de dónde salen los 200 mil'],
                    ['type' => 'p', 'text' => 'El equipamiento es la parte más visible y la que más varía. Depende menos de tu gusto que de una decisión clínica: qué procedimientos vas a hacer tú y cuáles vas a referir.'],
                    ['type' => 'table',
                     'head' => ['Nivel', 'Inversión en equipo', 'Qué incluye'],
                     'rows' => [
                        ['Básico', '$250,000 – $600,000', 'Unidad dental, compresor, autoclave, piezas de mano, cámara intraoral, rayos X periapical'],
                        ['Intermedio', '$700,000 – $1,600,000', 'Lo anterior + radiovisiógrafo, endomotor, lámpara de fotocurado, cavitador'],
                        ['Especializado', '$1,200,000 – $3,000,000+', 'Lo anterior + escáner intraoral, CAD/CAM, fresadora, láser, tomógrafo'],
                     ],
                     'caption' => 'Precios de referencia 2026 en pesos mexicanos. Varían por marca, ciudad y si compras de contado o a crédito.'],
                    ['type' => 'p', 'text' => 'La unidad dental sola —el sillón con su equipo esencial— va de 55,900 a 170,900 pesos en paquete, y las premium rondan los 190 mil. Aquí se decide buena parte de tu inversión inicial, y es donde más gente se endeuda de más.'],
                    ['type' => 'h3', 'text' => 'Dos formas de bajar el número sin bajar la calidad'],
                    ['type' => 'p', 'text' => 'La primera es arrancar con una unidad reacondicionada de buena procedencia y cambiarla cuando el consultorio ya produzca. Muchos dentistas con consultorio propio empezaron así y no se les nota en el resultado clínico.'],
                    ['type' => 'p', 'text' => 'La segunda es rentar sillón por horas en un consultorio establecido durante los primeros meses. No inviertes en equipo, construyes cartera de pacientes, y das el salto cuando ya tienes con qué sostenerlo.'],
                    ['type' => 'p', 'text' => 'Ninguna de las dos es un atajo de mala calidad. Son la diferencia entre abrir con una deuda manejable o abrir debiendo el equivalente a dos años de tus utilidades.'],

                    ['type' => 'h2', 'text' => '2. Los permisos: baratos, pero te clausuran sin ellos'],
                    ['type' => 'p', 'text' => 'Esta es la parte que casi nadie presupuesta y que, irónicamente, sale barata. Lo caro no es el trámite: es no tenerlo.'],
                    ['type' => 'p', 'text' => 'El documento central es el Aviso de Funcionamiento y de Responsable Sanitario ante COFEPRIS (trámite COFEPRIS-05-036). Se hace en línea por la plataforma DIGIPRiS con tu RFC y tu firma electrónica, y tiene que estar a la vista dentro del consultorio. Es obligatorio antes de empezar a operar, no después.'],
                    ['type' => 'ul', 'items' => [
                        'Título y cédula profesional vigentes.',
                        'Alta en el SAT y RFC del consultorio.',
                        'Uso de suelo compatible con servicios de salud, que otorga tu municipio.',
                        'Condiciones mínimas de sanidad, ventilación e iluminación.',
                        'Aviso de Funcionamiento y Responsable Sanitario ante COFEPRIS.',
                    ]],
                    ['type' => 'p', 'text' => 'Operar sin el aviso te expone a multa administrativa y, en casos serios, a clausura temporal o definitiva. Hay quien lo deja para después porque el trámite es gratuito y en línea; el problema es que después llega una visita de verificación.'],
                    ['type' => 'p', 'text' => 'Un consejo que vale los nueve minutos de este artículo: revisa el uso de suelo antes de firmar el contrato de arrendamiento, no después. Es el error más caro y más común, porque implica mudarte con todo el equipo ya instalado.'],

                    ['type' => 'h2', 'text' => '3. La adecuación del local'],
                    ['type' => 'p', 'text' => 'Un consultorio dental necesita instalaciones que un local vacío no trae: tomas de agua y drenaje donde va el sillón, instalación eléctrica que aguante el compresor y el autoclave, y muros con protección radiológica si vas a tener rayos X.'],
                    ['type' => 'p', 'text' => 'Según cómo recibas el local, aquí se van entre 80 y 250 mil pesos. Si encuentras un espacio que ya fue consultorio dental te ahorras buena parte de esto, y por eso esos locales se rentan tan rápido.'],

                    ['type' => 'h2', 'text' => '4. Los gastos mensuales: donde de verdad se decide'],
                    ['type' => 'p', 'text' => 'La inversión inicial la calculas una vez. Los gastos fijos llegan cada mes, tengas pacientes o no, y son los que determinan si el consultorio sobrevive al primer año.'],
                    ['type' => 'table',
                     'head' => ['Concepto', 'Rango mensual', 'Nota'],
                     'rows' => [
                        ['Renta', '$8,000 – $35,000', 'Según ciudad y zona. Es tu gasto menos flexible.'],
                        ['Asistente dental', '$9,000 – $14,000', 'El salario promedio del sector es de $9,170 (Secretaría de Economía, 2026).'],
                        ['Insumos y material', '$8,000 – $20,000', 'Sube con tu producción: es un costo variable disfrazado de fijo.'],
                        ['Servicios y limpieza', '$3,000 – $6,000', 'Luz, agua, internet y recolección de RPBI.'],
                        ['Software de gestión', '$0 – $2,000', 'Agenda, expediente, recordatorios y cobros.'],
                        ['Marketing', '$2,000 – $8,000', 'El primer año no es opcional: nadie sabe que existes.'],
                        ['Depreciación de equipo', '$2,000 – $21,000', 'El que casi nadie aparta, y el que te deja sin con qué renovar.'],
                     ],
                     'caption' => 'Pesos mexicanos al mes. Un consultorio de un solo dentista suele caer entre $35,000 y $60,000 de gasto fijo.'],
                    ['type' => 'h3', 'text' => 'La depreciación: el gasto que no ves salir'],
                    ['type' => 'p', 'text' => 'Tu unidad dental se va a desgastar. En unos ocho o diez años vas a tener que reemplazarla, y ese día el dinero tiene que salir de algún lado. Si nunca lo apartaste mes con mes, sale de tu bolsillo o de un crédito.'],
                    ['type' => 'p', 'text' => 'Por eso la depreciación aparece en la tabla aunque nunca la veas salir de tu cuenta. Si tu equipo costó 400 mil pesos y esperas que dure diez años, estás consumiendo unos 3,300 pesos al mes de ese equipo. Cóbralo en tus precios y guárdalo, o en una década vas a sentir que el consultorio nunca fue tan rentable como parecía.'],

                    ['type' => 'h2', 'text' => '5. El número que casi nadie calcula'],
                    ['type' => 'p', 'text' => 'Tu punto de equilibrio es cuántos pacientes necesitas al mes para que los ingresos igualen a los gastos. Todo lo que atiendas por encima de ese número es utilidad; todo lo que quede por debajo lo estás pagando tú.'],
                    ['type' => 'p', 'text' => 'La cuenta es más simple de lo que parece: toma tus gastos fijos mensuales y divídelos entre lo que te deja cada paciente después de descontar el material que usaste con él.'],
                    ['type' => 'p', 'text' => 'Con un gasto fijo de 45,000 pesos al mes y un ticket promedio de 900 pesos por consulta, de los cuales unos 250 se van en material, cada paciente te deja 650. Divides 45,000 entre 650 y te da 70 pacientes al mes: unos 18 por semana, entre tres y cuatro al día.'],
                    ['type' => 'p', 'text' => 'Ese es tu piso, no tu meta. Debajo de esos 70 pacientes estás poniendo dinero de tu bolsa para tener abierto el consultorio.'],
                    ['type' => 'p', 'text' => 'Haz esta cuenta con tus propios números antes de abrir. Si te sale que necesitas 140 pacientes al mes para no perder, ya sabes que la renta que estás por firmar es demasiado cara para el ticket que vas a cobrar.'],

                    ['type' => 'h2', 'text' => '6. Los tres errores que más caro salen'],
                    ['type' => 'h3', 'text' => 'Presupuestar solo la apertura'],
                    ['type' => 'p', 'text' => 'Un consultorio nuevo tarda entre tres y seis meses en llenar la agenda. Si gastaste hasta el último peso en equipo, esos meses los vas a vivir con la angustia de no poder pagar la renta. Aparta desde el principio seis meses de gastos fijos, aunque eso signifique comprar menos equipo al inicio.'],
                    ['type' => 'h3', 'text' => 'Mezclar el dinero del consultorio con el personal'],
                    ['type' => 'p', 'text' => 'Es el error más común en consultorios chicos y hace imposible saber si de verdad estás ganando. Cuando todo sale de la misma cuenta, un mes bueno se siente igual que uno malo. Abre una cuenta aparte desde el día uno y págate un sueldo fijo, aunque sea pequeño.'],
                    ['type' => 'h3', 'text' => 'No medir las sillas vacías'],
                    ['type' => 'p', 'text' => 'Entre tres y cinco pacientes por semana no llegan a su cita. Con un ticket de 900 pesos, eso son entre 10,800 y 18,000 pesos al mes que ya tenías apartados en la agenda y que no entraron. No es mala suerte: es falta de recordatorios, y se resuelve con un mensaje el día anterior.'],

                    ['type' => 'h2', 'text' => 'El resumen honesto'],
                    ['type' => 'p', 'text' => 'Abrir un consultorio dental básico en México en 2026 cuesta, de forma realista, entre 400 y 700 mil pesos: equipo, adecuación del local, permisos y un colchón de seis meses. Puedes empezar con bastante menos si rentas sillón por horas o compras equipo reacondicionado, y en muchos casos esa es la decisión más sensata.'],
                    ['type' => 'p', 'text' => 'Pero el número que va a decidir si tu consultorio funciona no es la inversión inicial: es tu punto de equilibrio y qué tan rápido lo alcanzas. Un consultorio con equipo modesto y agenda llena gana dinero. Uno con equipo de exposición y agenda a la mitad, no.'],
                    ['type' => 'p', 'text' => 'Si vas a abrir, haz hoy la cuenta del punto de equilibrio con tus precios y tus gastos. Es media hora de trabajo y te va a decir más sobre tu proyecto que cualquier catálogo de equipo.'],
                    ['type' => 'cta', 'text' => 'DocFácil te lleva la agenda, el expediente y los cobros, y manda los recordatorios por WhatsApp para que esas sillas no se queden vacías. 15 días gratis con todo incluido, sin tarjeta.'],
                ],
            ],

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
