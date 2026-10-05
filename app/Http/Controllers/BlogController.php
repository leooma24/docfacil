<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;

class BlogController extends Controller
{
    /**
     * Los artículos tal como vivían escritos a mano, antes de que el blog
     * tuviera tabla propia. Solo lo usa la migración que los pasó a la base;
     * el sitio ya lee de blog_posts.
     *
     * Se conserva para que la migración siga siendo reproducible en un
     * entorno nuevo. No agregues artículos aquí.
     *
     * El 4-oct-2026 se corrigieron los textos (WhatsApp automático, cifras
     * sin fuente, competencia, "médicos", de tú a de usted). La migración
     * 2026_10_04_120000_blog_sin_promesas_falsas lleva esta versión a las
     * filas que ya existían en producción.
     */
    public static function articulosHeredados(): array
    {
        return [
            'cuanto-cuesta-abrir-consultorio-dental-mexico' => [
                'title' => 'Cuánto cuesta abrir un consultorio dental en México (números reales 2026)',
                'description' => 'Desglose honesto de la inversión: equipo, permisos COFEPRIS, adecuación del local y los gastos mensuales que casi nadie menciona. Incluye el cálculo de cuántos pacientes necesita al mes para no perder dinero.',
                'image' => '/images/blog/costo-consultorio-dental.jpg',
                'date' => '2026-08-29',
                'read_time' => '9 min',
                'category' => 'Finanzas',
                'content' => [
                    ['type' => 'p', 'text' => 'La respuesta corta que va a encontrar en todos lados es "entre 200 y 300 mil pesos". Es cierta, y a la vez es la que más consultorios ha quebrado, porque solo cuenta el equipo. Deja fuera los permisos, la adecuación del local, los tres o cuatro meses en que todavía no tiene pacientes, y los gastos fijos que llegan puntuales aunque su agenda esté vacía.'],
                    ['type' => 'p', 'text' => 'En México operan 76,188 consultorios dentales, según datos de la Secretaría de Economía a mayo de 2026. Es un mercado enorme y también muy competido. Aquí está el desglose de lo que de verdad cuesta abrir uno, con precios de 2026, y al final el número que casi nadie calcula antes de firmar la renta: cuántos pacientes al mes necesita para no estar perdiendo dinero.'],

                    ['type' => 'h2', 'text' => '1. El equipo: de dónde salen los 200 mil'],
                    ['type' => 'p', 'text' => 'El equipamiento es la parte más visible y la que más varía. Depende menos de su gusto que de una decisión clínica: qué procedimientos va a hacer usted y cuáles va a referir.'],
                    ['type' => 'table',
                     'head' => ['Nivel', 'Inversión en equipo', 'Qué incluye'],
                     'rows' => [
                        ['Básico', '$250,000 – $600,000', 'Unidad dental, compresor, autoclave, piezas de mano, cámara intraoral, rayos X periapical'],
                        ['Intermedio', '$700,000 – $1,600,000', 'Lo anterior + radiovisiógrafo, endomotor, lámpara de fotocurado, cavitador'],
                        ['Especializado', '$1,200,000 – $3,000,000+', 'Lo anterior + escáner intraoral, CAD/CAM, fresadora, láser, tomógrafo'],
                     ],
                     'caption' => 'Precios de referencia 2026 en pesos mexicanos. Varían por marca, ciudad y si compra de contado o a crédito.'],
                    ['type' => 'p', 'text' => 'La unidad dental sola —el sillón con su equipo esencial— va de 55,900 a 170,900 pesos en paquete, y las premium rondan los 190 mil. Aquí se decide buena parte de su inversión inicial, y es donde más gente se endeuda de más.'],
                    ['type' => 'h3', 'text' => 'Dos formas de bajar el número sin bajar la calidad'],
                    ['type' => 'p', 'text' => 'La primera es arrancar con una unidad reacondicionada de buena procedencia y cambiarla cuando el consultorio ya produzca. Muchos dentistas con consultorio propio empezaron así y no se les nota en el resultado clínico.'],
                    ['type' => 'p', 'text' => 'La segunda es rentar sillón por horas en un consultorio establecido durante los primeros meses. No invierte en equipo, construye su cartera de pacientes, y da el salto cuando ya tiene con qué sostenerlo.'],
                    ['type' => 'p', 'text' => 'Ninguna de las dos es un atajo de mala calidad. Son la diferencia entre abrir con una deuda manejable o abrir debiendo el equivalente a dos años de sus utilidades.'],

                    ['type' => 'h2', 'text' => '2. Los permisos: baratos, pero le clausuran sin ellos'],
                    ['type' => 'p', 'text' => 'Esta es la parte que casi nadie presupuesta y que, irónicamente, sale barata. Lo caro no es el trámite: es no tenerlo.'],
                    ['type' => 'p', 'text' => 'El documento central es el Aviso de Funcionamiento y de Responsable Sanitario ante COFEPRIS (trámite COFEPRIS-05-036). Se hace en línea por la plataforma DIGIPRiS con su RFC y su firma electrónica, y tiene que estar a la vista dentro del consultorio. Es obligatorio antes de empezar a operar, no después.'],
                    ['type' => 'ul', 'items' => [
                        'Título y cédula profesional vigentes.',
                        'Alta en el SAT y RFC del consultorio.',
                        'Uso de suelo compatible con servicios de salud, que otorga su municipio.',
                        'Condiciones mínimas de sanidad, ventilación e iluminación.',
                        'Aviso de Funcionamiento y Responsable Sanitario ante COFEPRIS.',
                    ]],
                    ['type' => 'p', 'text' => 'Operar sin el aviso le expone a multa administrativa y, en casos serios, a clausura temporal o definitiva. Hay quien lo deja para después porque el trámite es gratuito y en línea; el problema es que después llega una visita de verificación.'],
                    ['type' => 'p', 'text' => 'Un consejo que vale los nueve minutos de este artículo: revise el uso de suelo antes de firmar el contrato de arrendamiento, no después. Es el error más caro y más común, porque implica mudarse con todo el equipo ya instalado.'],

                    ['type' => 'h2', 'text' => '3. La adecuación del local'],
                    ['type' => 'p', 'text' => 'Un consultorio dental necesita instalaciones que un local vacío no trae: tomas de agua y drenaje donde va el sillón, instalación eléctrica que aguante el compresor y el autoclave, y muros con protección radiológica si va a tener rayos X.'],
                    ['type' => 'p', 'text' => 'Según cómo reciba el local, aquí se van entre 80 y 250 mil pesos. Si encuentra un espacio que ya fue consultorio dental se ahorra buena parte de esto, y por eso esos locales se rentan tan rápido.'],

                    ['type' => 'h2', 'text' => '4. Los gastos mensuales: donde de verdad se decide'],
                    ['type' => 'p', 'text' => 'La inversión inicial la calcula una vez. Los gastos fijos llegan cada mes, tenga pacientes o no, y son los que determinan si el consultorio sobrevive al primer año.'],
                    ['type' => 'table',
                     'head' => ['Concepto', 'Rango mensual', 'Nota'],
                     'rows' => [
                        ['Renta', '$8,000 – $35,000', 'Según ciudad y zona. Es su gasto menos flexible.'],
                        ['Asistente dental', '$9,000 – $14,000', 'El salario promedio del sector es de $9,170 (Secretaría de Economía, 2026).'],
                        ['Insumos y material', '$8,000 – $20,000', 'Sube con su producción: es un costo variable disfrazado de fijo.'],
                        ['Servicios y limpieza', '$3,000 – $6,000', 'Luz, agua, internet y recolección de RPBI.'],
                        ['Software de gestión', '$0 – $2,000', 'Agenda, expediente, recordatorios y cobros.'],
                        ['Marketing', '$2,000 – $8,000', 'El primer año no es opcional: nadie sabe que usted existe.'],
                        ['Depreciación de equipo', '$2,000 – $21,000', 'El que casi nadie aparta, y el que le deja sin con qué renovar.'],
                     ],
                     'caption' => 'Pesos mexicanos al mes. Un consultorio de un solo dentista suele caer entre $35,000 y $60,000 de gasto fijo.'],
                    ['type' => 'h3', 'text' => 'La depreciación: el gasto que no ve salir'],
                    ['type' => 'p', 'text' => 'Su unidad dental se va a desgastar. En unos ocho o diez años va a tener que reemplazarla, y ese día el dinero tiene que salir de algún lado. Si nunca lo apartó mes con mes, sale de su bolsillo o de un crédito.'],
                    ['type' => 'p', 'text' => 'Por eso la depreciación aparece en la tabla aunque nunca la vea salir de su cuenta. Si su equipo costó 400 mil pesos y espera que dure diez años, está consumiendo unos 3,300 pesos al mes de ese equipo. Cóbrelo en sus precios y guárdelo, o en una década va a sentir que el consultorio nunca fue tan rentable como parecía.'],

                    ['type' => 'h2', 'text' => '5. El número que casi nadie calcula'],
                    ['type' => 'p', 'text' => 'Su punto de equilibrio es cuántos pacientes necesita al mes para que los ingresos igualen a los gastos. Todo lo que atienda por encima de ese número es utilidad; todo lo que quede por debajo lo está pagando usted.'],
                    ['type' => 'p', 'text' => 'La cuenta es más simple de lo que parece: tome sus gastos fijos mensuales y divídalos entre lo que le deja cada paciente después de descontar el material que usó con él.'],
                    ['type' => 'p', 'text' => 'Con un gasto fijo de 45,000 pesos al mes y un ticket promedio de 900 pesos por consulta, de los cuales unos 250 se van en material, cada paciente le deja 650. Divide 45,000 entre 650 y le da 70 pacientes al mes: unos 18 por semana, entre tres y cuatro al día.'],
                    ['type' => 'p', 'text' => 'Ese es su piso, no su meta. Debajo de esos 70 pacientes está poniendo dinero de su bolsa para tener abierto el consultorio.'],
                    ['type' => 'p', 'text' => 'Haga esta cuenta con sus propios números antes de abrir. Si le sale que necesita 140 pacientes al mes para no perder, ya sabe que la renta que está por firmar es demasiado cara para el ticket que va a cobrar.'],

                    ['type' => 'h2', 'text' => '6. Los tres errores que más caro salen'],
                    ['type' => 'h3', 'text' => 'Presupuestar solo la apertura'],
                    ['type' => 'p', 'text' => 'Un consultorio nuevo tarda entre tres y seis meses en llenar la agenda. Si gastó hasta el último peso en equipo, esos meses los va a vivir con la angustia de no poder pagar la renta. Aparte desde el principio seis meses de gastos fijos, aunque eso signifique comprar menos equipo al inicio.'],
                    ['type' => 'h3', 'text' => 'Mezclar el dinero del consultorio con el personal'],
                    ['type' => 'p', 'text' => 'Es el error más común en consultorios chicos y hace imposible saber si de verdad está ganando. Cuando todo sale de la misma cuenta, un mes bueno se siente igual que uno malo. Abra una cuenta aparte desde el día uno y páguese un sueldo fijo, aunque sea pequeño.'],
                    ['type' => 'h3', 'text' => 'No medir las sillas vacías'],
                    ['type' => 'p', 'text' => 'Cada paciente que no llega es dinero que ya tenía apartado en la agenda y que no entró. Haga la cuenta con sus números: si 3 pacientes a la semana no llegan y su consulta cuesta $900, son unos $10,800 al mes. Muchas veces no es mala suerte: al paciente se le olvidó, y eso se ataca con un recordatorio el día anterior.'],

                    ['type' => 'h2', 'text' => 'El resumen honesto'],
                    ['type' => 'p', 'text' => 'Abrir un consultorio dental básico en México en 2026 cuesta, de forma realista, entre 400 y 700 mil pesos: equipo, adecuación del local, permisos y un colchón de seis meses. Puede empezar con bastante menos si renta sillón por horas o compra equipo reacondicionado, y en muchos casos esa es la decisión más sensata.'],
                    ['type' => 'p', 'text' => 'Pero el número que va a decidir si su consultorio funciona no es la inversión inicial: es su punto de equilibrio y qué tan rápido lo alcanza. Un consultorio con equipo modesto y agenda llena gana dinero. Uno con equipo de exposición y agenda a la mitad, no.'],
                    ['type' => 'p', 'text' => 'Si va a abrir, haga hoy la cuenta del punto de equilibrio con sus precios y sus gastos. Es media hora de trabajo y le va a decir más sobre su proyecto que cualquier catálogo de equipo.'],
                    ['type' => 'cta', 'text' => 'DocFácil le lleva la agenda, el expediente y los cobros. El recordatorio de cada cita ya queda escrito: usted lo manda a 1 clic desde su WhatsApp y el paciente confirma o cancela con un link. Pruébelo 15 días gratis, sin tarjeta.'],
                ],
            ],

            'como-reducir-inasistencias-consultorio' => [
                'title' => 'Cómo reducir inasistencias en su consultorio',
                'description' => 'Lo que sí ayuda a que sus pacientes lleguen a su cita: un recordatorio el día anterior, pedirles que confirmen y tener a quién ofrecerle el espacio si alguien cancela.',
                'image' => '/images/blog/inasistencias.jpg',
                'date' => '2026-04-01',
                'read_time' => '4 min',
                'category' => 'Gestión',
                'content' => [
                    ['type' => 'p', 'text' => 'Si usted es dentista, conoce la escena: el sillón listo, la hora apartada y el paciente que no llega. Esa hora ya no se recupera, y quizá otro paciente que sí quería cita ese día tuvo que esperar a otra fecha.'],
                    ['type' => 'p', 'text' => 'Haga la cuenta con sus propios números. Si 3 pacientes a la semana no llegan y su consulta cuesta $900, son unos $10,800 al mes que tenía en la agenda y que no entraron.'],
                    ['type' => 'h2', 'text' => '¿Por qué no llegan?'],
                    ['type' => 'p', 'text' => 'Casi siempre por lo mismo: se les olvidó, les surgió algo y no avisaron, o no tenían claro el día y la hora. La mayoría de esas razones se atacan con algo sencillo: recordarles a tiempo y darles una forma fácil de avisar.'],
                    ['type' => 'h2', 'text' => 'Tres cosas que ayudan'],
                    ['type' => 'h3', 'text' => '1. Un recordatorio el día anterior'],
                    ['type' => 'p', 'text' => 'Casi todos sus pacientes usan WhatsApp, y un mensaje ahí se lee más que un correo o una llamada que no contestan. Basta algo corto y amable: "Buenas tardes, María. Le recordamos su cita de mañana a las 10:00 con el Dr. López." Un día antes da tiempo de reacomodar la agenda si el paciente no puede.'],
                    ['type' => 'h3', 'text' => '2. Pedir que confirme o cancele'],
                    ['type' => 'p', 'text' => 'No solo recuerde: pida una respuesta. Un paciente que confirma ya se comprometió, y uno que cancela con un día de anticipación le deja el espacio libre para alguien más. Lo que más cuesta es la cita que nadie canceló y nadie ocupó.'],
                    ['type' => 'h3', 'text' => '3. Tener a quién ofrecerle el espacio'],
                    ['type' => 'p', 'text' => 'Una cancelación a tiempo sirve si tiene a quién llamar. Anote a los pacientes que querían una cita antes, para ofrecerles el espacio que se libere. Y si con algún paciente quiere asegurarse el mismo día, mándele usted un mensaje unas horas antes desde su WhatsApp.'],
                    ['type' => 'h2', 'text' => 'Lo importante'],
                    ['type' => 'p', 'text' => 'Ninguna de estas tres cosas es complicada. Lo difícil es hacerlas todos los días, con cada paciente, cuando la agenda va llena. Por eso conviene que el recordatorio ya esté escrito y que usted solo tenga que mandarlo.'],
                    ['type' => 'cta', 'text' => 'En DocFácil el recordatorio de cada cita ya queda escrito: usted lo manda a 1 clic desde su WhatsApp y el paciente confirma o cancela con un link. Pruébelo 15 días gratis, sin tarjeta.'],
                ],
            ],

            'software-consultorio-medico-mexico-guia' => [
                'title' => 'Guía 2026: Cómo elegir software para su consultorio dental en México',
                'description' => 'Qué buscar, qué evitar y qué preguntar antes de pagar por un software para su consultorio dental.',
                'image' => '/images/blog/software-guia.jpg',
                'date' => '2026-03-28',
                'read_time' => '6 min',
                'category' => 'Tecnología',
                'content' => [
                    ['type' => 'p', 'text' => 'Elegir un software para su consultorio es una decisión importante. Uno que no le acomoda le va a quitar más tiempo del que le ahorra. Esta guía le ayuda a decidir con sus propias preguntas, no con lo que le quieran vender.'],
                    ['type' => 'h2', 'text' => 'Lo mínimo que debe tener'],
                    ['type' => 'p', 'text' => 'Antes de ver marcas, defina qué necesita. Lo básico para un dentista: agenda de citas, expediente clínico, odontograma, recetas y recordatorios para sus pacientes.'],
                    ['type' => 'h2', 'text' => '¿En la nube o instalado en una computadora?'],
                    ['type' => 'p', 'text' => 'Un sistema instalado vive en una computadora: si esa computadora falla y no hay respaldo, la información se puede perder. Un sistema en la nube funciona desde cualquier dispositivo con internet y se actualiza solo. En cualquiera de los dos, pregunte cada cuánto se respalda la información y dónde se guarda.'],
                    ['type' => 'h2', 'text' => '¿Cuánto cuesta?'],
                    ['type' => 'p', 'text' => 'Hay planes gratuitos con límites y planes de paga que cobran por mes o por año. Más que el precio, compare qué incluye cada plan, cuántos doctores y pacientes permite, y si puede cancelar cuando quiera. Como referencia, en DocFácil el plan Free cuesta $0 para siempre (1 doctor, 15 pacientes y 10 citas al mes), el Básico $499 al mes, el Pro $999 y el Clínica $1,999.'],
                    ['type' => 'h2', 'text' => 'Errores comunes'],
                    ['type' => 'p', 'text' => '1) Pagar por funciones que nunca va a usar. 2) No revisar que tenga soporte en español. 3) No probarlo antes de pagar. 4) Elegir el más caro pensando que es el mejor.'],
                    ['type' => 'h2', 'text' => '¿Qué preguntar antes de contratar?'],
                    ['type' => 'p', 'text' => '¿Lo puedo probar gratis? ¿Tiene soporte en español por WhatsApp? ¿Puedo cancelar sin penalización? ¿Mis datos están seguros? ¿Funciona en mi celular? Si alguna respuesta es "no", piénselo dos veces.'],
                    ['type' => 'cta', 'text' => 'DocFácil cumple con esto: 15 días gratis sin tarjeta, soporte por WhatsApp y cancela cuando quiera.'],
                ],
            ],

            'expediente-clinico-digital-nom-004' => [
                'title' => 'Expediente clínico digital: Qué dice la NOM-004 y cómo cumplirla',
                'description' => 'La norma oficial mexicana exige ciertos datos en el expediente clínico. Le explicamos cómo cumplir sin complicarse.',
                'image' => '/images/blog/nom-004.jpg',
                'date' => '2026-03-20',
                'read_time' => '5 min',
                'category' => 'Legal',
                'content' => [
                    ['type' => 'p', 'text' => 'La NOM-004-SSA3-2012 es la norma oficial mexicana que regula el expediente clínico. Aplica a todos los prestadores de servicios de salud, desde consultorios pequeños hasta hospitales. Si usted es dentista, le aplica.'],
                    ['type' => 'h2', 'text' => '¿Qué exige la norma?'],
                    ['type' => 'p', 'text' => 'En resumen: cada consulta debe tener fecha, nombre del paciente, motivo, diagnóstico, tratamiento, y nombre del médico responsable. El expediente debe ser confidencial, ordenado cronológicamente e integrado (toda la información en un solo lugar).'],
                    ['type' => 'h2', 'text' => '¿Es válido el expediente digital?'],
                    ['type' => 'p', 'text' => 'Sí. La NOM-004 permite usar medios electrónicos en el expediente (numeral 5.12), en los términos de las demás disposiciones aplicables. Lo que no cambia: cada nota lleva fecha, hora, nombre completo y firma de quien la elabora (5.10), sin enmendaduras ni tachaduras (5.11); el expediente es confidencial y se conserva al menos 5 años desde el último acto médico (5.4). Y como son datos de salud, también le aplica la ley de protección de datos personales.'],
                    ['type' => 'h2', 'text' => 'Ventajas del digital sobre el papel'],
                    ['type' => 'p', 'text' => 'El papel se pierde, se moja y no se puede buscar. Un expediente digital le permite buscar por nombre en segundos, es más difícil de perder (con respaldos), puede guardar el historial de cambios de cada nota y genera reportes.'],
                    ['type' => 'h2', 'text' => 'Consentimiento informado'],
                    ['type' => 'p', 'text' => 'La norma también pide carta de consentimiento informado en ciertos casos, como cirugía mayor, anestesia general o regional y procedimientos de alto riesgo, con el nombre completo y la firma del paciente (numerales 10.1.1 y 10.1.2); en odontología, la NOM-013 pide además la firma de un testigo. Que el paciente firme con el dedo en la pantalla le ahorra papel y deja registradas la fecha y la hora, pero no es una firma electrónica avanzada (e.firma). Si tiene dudas sobre un procedimiento en particular, consúltelo con su abogado.'],
                    ['type' => 'cta', 'text' => 'DocFácil está pensado para ayudarle con la NOM-004: las notas se bloquean 24 horas después de guardarlas, queda historial de cambios y los diagnósticos usan el catálogo CIE-10. En el plan Pro, además, el paciente firma sus consentimientos en pantalla. Pruébelo 15 días gratis, sin tarjeta.'],
                ],
            ],

            'recetas-electronicas-mexico-guia-completa' => [
                'title' => 'Recetas electrónicas en México: guía para dentistas',
                'description' => 'Lo que dice hoy la regulación sobre recetas electrónicas, qué datos debe llevar la receta y cómo hacerla en PDF con su cédula.',
                'image' => '/images/blog/recetas.jpg',
                'date' => '2026-03-15',
                'read_time' => '4 min',
                'category' => 'Legal',
                'content' => [
                    ['type' => 'p', 'text' => 'Cada vez más dentistas en México hacen sus recetas en computadora en vez de escribirlas a mano. Se ven más profesionales, evitan errores de dosis por letra ilegible y quedan guardadas en el expediente.'],
                    ['type' => 'h2', 'text' => '¿Qué dice la regulación?'],
                    ['type' => 'p', 'text' => 'Hoy no hay en México una regla general de receta electrónica. El Reglamento de Insumos para la Salud (artículo 29) pide que la receta lleve impresos el nombre, el domicilio y la cédula profesional de quien prescribe, además de la fecha y la firma autógrafa. El reglamento de la Ley General de Salud en materia de atención médica (artículo 64) acepta firma digital o electrónica "en caso de contar con medios tecnológicos" y pide también el nombre de la institución que expidió su título. Por eso una receta que solo lleva firma electrónica puede ser rechazada en la farmacia; lo práctico es imprimirla y firmarla a mano. Además debe indicar el medicamento por su nombre genérico, con presentación, dosis, vía de administración, frecuencia y duración del tratamiento.'],
                    ['type' => 'h2', 'text' => 'Excepciones importantes'],
                    ['type' => 'p', 'text' => 'Los medicamentos de la fracción I del artículo 226 de la Ley General de Salud (estupefacientes) no van en receta ordinaria: necesitan el recetario especial de COFEPRIS. En los de la fracción II la farmacia retiene la receta y en los de la fracción III la sella y la registra, así que ahí lo práctico es la receta impresa y firmada a mano. Y desde el 14 de julio de 2026 el tramadol se vende como medicamento controlado: basta una receta ordinaria con su cédula, y la farmacia la sella y anota la fecha y la cantidad.'],
                    ['type' => 'h2', 'text' => 'Cómo hacerlas con un software'],
                    ['type' => 'p', 'text' => 'En DocFácil usted llena el medicamento, la dosis y la frecuencia, y el sistema arma un PDF con su nombre, especialidad, cédula, los datos de su consultorio, la fecha y una línea para su firma autógrafa. Lo imprime y lo firma. La receta queda guardada en el expediente, y el paciente también la puede ver en su portal.'],
                    ['type' => 'h2', 'text' => 'Ventajas sobre las recetas de papel'],
                    ['type' => 'p', 'text' => '1) Se leen siempre. 2) Sus datos y los del consultorio salen impresos en cada receta. 3) Quedan archivadas en el expediente. 4) Si el paciente la pierde, usted la vuelve a imprimir.'],
                    ['type' => 'cta', 'text' => 'Con DocFácil hace sus recetas en PDF con su cédula, listas para imprimir y firmar. Pruébelo 15 días gratis, sin tarjeta.'],
                ],
            ],

            'odontograma-digital-beneficios-dentistas' => [
                'title' => 'Odontograma digital: por qué su consultorio dental lo necesita',
                'description' => 'El odontograma interactivo le ayuda a explicarle al paciente lo que tiene, a llevar su historial y a ordenar el plan de tratamiento.',
                'image' => '/images/blog/odontograma.jpg',
                'date' => '2026-03-10',
                'read_time' => '4 min',
                'category' => 'Odontología',
                'content' => [
                    ['type' => 'p', 'text' => 'Si usted es dentista, sabe que el odontograma es su herramienta principal de diagnóstico y plan de tratamiento. Si todavía lo hace en papel o en una hoja de Excel, le cuesta más tiempo y es más difícil enseñárselo al paciente.'],
                    ['type' => 'h2', 'text' => '¿Qué es un odontograma digital?'],
                    ['type' => 'p', 'text' => 'Es un diagrama dental interactivo donde marca la condición de cada diente con clics: caries, extracción, corona, puente, obturación, etc. Cada condición tiene su color para reconocerla de un vistazo.'],
                    ['type' => 'h2', 'text' => 'Beneficios sobre el papel'],
                    ['type' => 'p', 'text' => '1) Historial visual: ve cómo ha cambiado la boca del paciente a lo largo de los meses. 2) Comunicación: le enseña al paciente en pantalla qué dientes necesitan trabajo. 3) Orden: marcar con un clic es más rápido que dibujar a mano, y cualquiera en su equipo lo entiende igual.'],
                    ['type' => 'h2', 'text' => 'Cómo ayuda a que el paciente acepte su tratamiento'],
                    ['type' => 'p', 'text' => 'Cuando el paciente ve en color qué dientes tienen caries o necesitan corona, entiende mejor lo que usted le explica. Y un paciente que entiende su diagnóstico decide con más confianza.'],
                    ['type' => 'h2', 'text' => 'Qué buscar en un odontograma digital'],
                    ['type' => 'p', 'text' => 'Que sea interactivo (un clic para marcar, no teclear códigos), que se guarde solo, que esté junto al expediente del paciente y que funcione en tablet para usarlo durante la consulta.'],
                    ['type' => 'cta', 'text' => 'DocFácil tiene odontograma interactivo con 13 condiciones, junto al expediente, y lo puede imprimir o guardar en PDF para dárselo al paciente. Pruébelo 15 días gratis, sin tarjeta.'],
                ],
            ],
        ];
    }

    /**
     * Artículos publicados, en la forma que espera la vista.
     *
     * Sigue devolviendo un arreglo indexado por slug porque asi nació el
     * blog y asi lo consumen las vistas y el sitemap.
     */
    public static function articles(): array
    {
        return BlogPost::query()
            ->publicados()
            ->get()
            ->mapWithKeys(fn (BlogPost $post) => [$post->slug => $post->paraLaVista()])
            ->all();
    }

    public function index()
    {
        return view('blog.index', ['articles' => self::articles()]);
    }

    public function show(string $slug)
    {
        $post = BlogPost::query()->publicados()->where('slug', $slug)->first();

        if (! $post) {
            abort(404);
        }

        return view('blog.show', [
            'article' => $post->paraLaVista(),
            'slug' => $slug,
            'related' => collect(self::articles())->except($slug)->take(2)->all(),
        ]);
    }
}
