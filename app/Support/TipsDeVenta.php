<?php

namespace App\Support;

/**
 * Los tips, uno a la vez y pegados al momento en que se usan.
 *
 * Omar lo pidió así: no quiere que el sistema venda por él, quiere que le
 * recuerde lo mismo hasta que le salga natural. Una lista de 25 tips no sirve
 * para eso —se lee una vez y no se vuelve a abrir—; lo que sirve es que el
 * consejo aparezca arriba del botón, justo antes de hacer la cosa.
 *
 * Cada tip está atado a una etapa de `SiguientePaso`, así el que toca sale
 * solo. Los de etapa `siempre` rotan en el escritorio.
 *
 * Los que se dejaron fuera a propósito: los que traen cifras que no podemos
 * sostener ("pierden 2 o 3 citas por semana"), el que dice no dar salida —al
 * revés de lo que medimos: la línea de "si no le interesa me lo dice" es la
 * que hizo que contestaran— y los que no son de sistema, como grabarse o leer
 * a un autor.
 */
class TipsDeVenta
{
    /**
     * @return array<int, array{clave: string, etapa: string, tip: string, porque: string}>
     */
    public const CATALOGO = [
        // ── Al escribir por primera vez ──────────────────────────
        [
            'clave' => 'desapego',
            'etapa' => 'primer_contacto',
            'tip' => 'Manda sin esperar respuesta.',
            'porque' => 'El que necesita la venta, no vende. Manda los 12 y sigue con lo tuyo: si contestan, bien; si no, también.',
        ],
        [
            'clave' => 'una_pregunta',
            'etapa' => 'primer_contacto',
            'tip' => 'Una sola pregunta, y que sea de cómo le hace hoy.',
            'porque' => 'Nadie contesta "no aviso". Todos cuentan cómo le hacen, y ahí sale solo el tiempo que se les va.',
        ],

        // ── Cuando contesta ──────────────────────────────────────
        [
            'clave' => 'cinco_minutos',
            'etapa' => 'diagnosticar',
            'tip' => 'Contéstale rápido, no mañana.',
            'porque' => 'Te escribió porque en ese momento le interesó. En unas horas ya está en otra cosa.',
        ],
        [
            'clave' => 'sus_palabras',
            'etapa' => 'diagnosticar',
            'tip' => 'Repítele lo que él te dijo, con sus palabras.',
            'porque' => '"Lo hace usted, entre pacientes" pega distinto que cualquier cosa que tú inventes. Se siente escuchado, no vendido.',
        ],
        [
            'clave' => 'una_cosa',
            'etapa' => 'diagnosticar',
            'tip' => 'Una cosa a la vez: hoy solo diagnosticar.',
            'porque' => 'El que quiere diagnosticar, agendar y cerrar en el mismo mensaje no consigue ninguna de las tres.',
        ],

        // ── Al pedir la cita ─────────────────────────────────────
        [
            'clave' => 'dos_opciones',
            'etapa' => 'pedir_cita',
            'tip' => 'Dos opciones, nunca "¿le interesa?".',
            'porque' => '"¿Le interesa?" se contesta con un no. "¿A la 1 o a las 6?" se contesta con una de las dos, o con "mejor el jueves", que también es avanzar.',
        ],
        [
            'clave' => 'proximo_paso',
            'etapa' => 'pedir_cita',
            'tip' => 'No cierres la plática sin próximo paso con fecha.',
            'porque' => '"Le mando info" no es un paso. "Le hablo el jueves a las 5" sí.',
        ],

        // ── Antes de la demo ─────────────────────────────────────
        [
            'clave' => 'setenta_treinta',
            'etapa' => 'preparar_demo',
            'tip' => 'Él habla 70, tú 30.',
            'porque' => 'Si llevas dos minutos hablando seguido, te pasaste. Pregunta algo y cállate.',
        ],
        [
            'clave' => 'lo_suyo_primero',
            'etapa' => 'preparar_demo',
            'tip' => 'Enséñale primero lo que le duele, no todo el sistema.',
            'porque' => 'Si abres con el odontograma a quien te habló de recordatorios, lo perdiste en el minuto dos.',
        ],
        [
            'clave' => 'pregunta_primero',
            'etapa' => 'preparar_demo',
            'tip' => 'Arranca preguntando cómo le fue ayer.',
            'porque' => 'Que lo diga en voz alta antes de que le enseñes nada. Con eso cierras al final.',
        ],

        // ── Al cerrar ────────────────────────────────────────────
        [
            'clave' => 'silencio',
            'etapa' => 'cerrar',
            'tip' => 'Pregunta y cállate.',
            'porque' => 'Después de pedir el cierre, el primero que habla pierde. Cuenta hasta diez si hace falta.',
        ],
        [
            'clave' => 'pide_el_no',
            'etapa' => 'cerrar',
            'tip' => 'Pregunta qué le impide empezar hoy.',
            'porque' => 'Si dice "nada", ya está. Si dice "es que...", acabas de encontrar la objeción real.',
        ],
        [
            'clave' => 'retirada',
            'etapa' => 'cerrar',
            'tip' => 'Si se pone nervioso, retírate un paso.',
            'porque' => '"Doctor, tal vez me adelanté, ¿lo dejamos para después?" A veces, cuando sueltas, el otro la persigue.',
        ],

        // ── Todos los días ───────────────────────────────────────
        [
            'clave' => 'vende_el_ahorro',
            'etapa' => 'siempre',
            'tip' => 'No vendes software: vendes el rato que se le va.',
            'porque' => 'Cada función tradúcela: "recordatorio automático" es "no se sale de la consulta a escribir mensajes".',
        ],
        [
            'clave' => 'tres_no',
            'etapa' => 'siempre',
            'tip' => 'Un "no" no es el final, son tres.',
            'porque' => '"No me interesa" → pregunta por qué. "Déjame pensarlo" → pregunta qué. Al tercero, lo sueltas con clase.',
        ],
        [
            'clave' => 'despide_bien',
            'etapa' => 'siempre',
            'tip' => 'Al que dice que no, despídelo bien.',
            'porque' => 'En Los Mochis los dentistas se conocen. El que se va bien atendido no te bloquea, y a veces regresa.',
        ],
        [
            'clave' => 'uno_por_ciento',
            'etapa' => 'siempre',
            'tip' => 'Mejora una sola cosa hoy.',
            'porque' => 'Hoy el mensaje, mañana la pregunta, pasado el cierre. Querer arreglar todo de golpe es no arreglar nada.',
        ],
    ];

    /** Los que aplican a lo que estás a punto de hacer. */
    public static function paraLaEtapa(string $etapa): array
    {
        return array_values(array_filter(
            self::CATALOGO,
            fn (array $tip) => $tip['etapa'] === $etapa,
        ));
    }

    public static function porClave(string $clave): ?array
    {
        foreach (self::CATALOGO as $tip) {
            if ($tip['clave'] === $clave) {
                return $tip;
            }
        }

        return null;
    }
}
