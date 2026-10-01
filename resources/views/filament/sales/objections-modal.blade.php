{{--
    Las 17 objeciones del catálogo, con su clave.

    Las claves son las mismas de Prospect::OBJECTION_CATALOG y de
    .agents/objection-playbook.md: así, lo que se registra en un prospecto se
    puede buscar aquí, y lo que se cambia aquí se puede rastrear allá.

    Lo que se quitó al reescribirlo, en septiembre:

    - "Somos de aquí de Culiacán". Es Los Mochis, y es una persona, no un
      equipo. Un doctor que note esa mentira chica ya no cree la grande.
    - "Ya tenemos consultorios usándolo aquí" y "le paso el contacto de un
      doctor que ya lo usa". Todavía no hay ninguno. Prometer un contacto que
      no existe se descubre en el primer "sí, páseme el contacto".
    - "Recupera entre 8 y 12 citas al mes, $4,800 extra, se paga casi 10
      veces". Números sin fuente. Lo que sí se puede decir es lo que los
      dentistas contestaron: que los recordatorios los escriben ellos, a mano,
      entre paciente y paciente.

    La regla: aquí solo va lo que se puede sostener si el doctor pregunta de
    dónde salió.
--}}
<div style="max-height:65vh;overflow-y:auto;display:flex;flex-direction:column;gap:12px;">
    @php
    $objeciones = [
        ['cat' => 'PRECIO', 'items' => [
            ['clave' => 'price_expensive',
             'q' => 'Está caro / No tengo presupuesto',
             'a' => 'Doctor, ¿cuánto cobra usted por una consulta? El plan Básico cuesta $499 al mes, menos de lo que cobra por un paciente. Si con los recordatorios recupera una sola cita al mes, ya se pagó solo.',
             'follow' => '¿Cuántos pacientes al mes se le quedan sin venir?'],
            ['clave' => 'price_excel',
             'q' => '¿Por qué pagar si uso Excel?',
             'a' => 'Excel le funciona hasta que se le juntan los pacientes, y sobre todo: Excel no le arma la lista de a quién avisarle mañana con el mensaje ya escrito. Eso es lo que le quita el rato que hoy se le va escribiendo uno por uno.',
             'follow' => '¿Cuánto tiempo al día se le va mandando los recordatorios?'],
            ['clave' => 'price_free',
             'q' => 'Hay opciones gratis',
             'a' => 'Sí las hay, y el plan Free de DocFácil también lo es: un doctor y 15 pacientes, de por vida. Úselo así el tiempo que quiera. Se paga cuando ya no le alcanza, no antes.',
             'follow' => '¿Cuántos pacientes tiene hoy en su expediente?'],
            ['clave' => 'price_unsure',
             'q' => 'No sé si lo voy a usar',
             'a' => 'Por eso son 15 días completos sin meter tarjeta, y el plan Free no vence nunca. Si no lo usa, no pierde nada; y si lo usa, usted me dice.',
             'follow' => '¿Qué tendría que hacer el sistema para que valiera la pena?'],
        ]],

        ['cat' => 'TECNOLOGÍA', 'items' => [
            ['clave' => 'tech_not_techie',
             'q' => 'No soy tecnológico',
             'a' => 'Si usa WhatsApp, esto le va a salir. Y la configuración la hago yo con usted, en una llamada, sin costo. No le dejo un tutorial.',
             'follow' => '¿Quién le ayuda hoy con la computadora del consultorio?'],
            ['clave' => 'tech_paper_works',
             'q' => 'El papel me funciona bien',
             'a' => 'No le vengo a quitar el papel. Lo que no hace el papel es avisarle al paciente que mañana tiene cita, ni guardar la receta con su cédula para cuando se la pidan.',
             'follow' => '¿Cómo le avisa hoy a un paciente que tiene cita mañana?'],
            ['clave' => 'tech_has_system',
             'q' => 'Ya tengo otro sistema',
             'a' => '¿Cuál usa? Se lo pregunto en serio, no para ofrecerle lo que ya tiene. Lo que me interesa saber es qué le falta, porque eso es lo que puedo construir.',
             'follow' => '¿Ese sistema le arma la lista de a quién avisarle mañana?'],
            ['clave' => 'tech_internet',
             'q' => '¿Y si se cae el internet?',
             'a' => 'Mientras no haya internet no entra al sistema, igual que al banco. Su información no se pierde: está en el servidor, no en la computadora del consultorio, así que si se le descompone la máquina sigue estando todo.',
             'follow' => '¿Se le va seguido la señal en el consultorio?'],
        ]],

        ['cat' => 'CONFIANZA', 'items' => [
            ['clave' => 'trust_data',
             'q' => '¿Mis datos están seguros?',
             'a' => 'Cada consultorio ve solo lo suyo, la conexión va cifrada y se puede activar verificación en dos pasos. Cada nota queda con quién la escribió y a qué hora, y lo firmado no se modifica. Hay respaldo cifrado todos los días. Los servidores están en Estados Unidos, con DigitalOcean.',
             'follow' => '¿Hay algo en particular que le preocupe de eso?'],
            ['clave' => 'trust_who',
             'q' => '¿Quién está detrás? No los conozco',
             'a' => 'Soy Omar Lerma, ingeniero, de aquí de Los Mochis. No es una empresa con call center: el que le contesta el WhatsApp soy yo, al 668 249 3398. Eso tiene sus dos caras y se las digo: me tiene a mí directo, y también depende de que yo conteste.',
             'follow' => '¿Le late si nos vemos y se lo enseño en persona?'],
            ['clave' => 'trust_disappear',
             'q' => '¿Y si desaparecen?',
             'a' => 'Es la pregunta correcta. Su información es suya: el expediente de cada paciente se descarga en PDF desde el sistema, cuando quiera. Y si algún día deja de usarlo, yo le entrego todo lo suyo para que se lo lleve.',
             'follow' => '¿Hoy podría sacar su información del sistema que usa?'],
            ['clave' => 'trust_think',
             'q' => 'Necesito pensarlo',
             'a' => 'Claro que sí. ¿Qué es lo que quiere pensar? Si es algo que le puedo resolver ahorita, se lo resuelvo; y si prefiere dejarlo, me lo dice y no le insisto.',
             'follow' => '¿Qué tendría que pasar para que sí le convenga?'],
        ]],

        ['cat' => 'MOMENTO', 'items' => [
            ['clave' => 'timing_not_now',
             'q' => 'Ahorita no es buen momento',
             'a' => 'Lo entiendo. ¿Le parece si le escribo en un mes? Con que me diga cuándo, le hablo entonces y no antes.',
             'follow' => '¿En qué mes le queda mejor?'],
            ['clave' => 'timing_more_patients',
             'q' => 'Cuando tenga más pacientes',
             'a' => 'Ahí está el detalle: es más fácil empezar con 40 pacientes que con 400. Cuando ya son muchos, pasarlos cuesta. Y el plan Free le alcanza justo para empezar sin pagar.',
             'follow' => '¿Cuántos pacientes lleva hoy?'],
        ]],

        ['cat' => 'CASOS ESPECÍFICOS', 'items' => [
            ['clave' => 'specific_govt',
             'q' => 'Solo atiendo IMSS/ISSSTE',
             'a' => 'Entonces no le sirve, y se lo digo de frente. Esto es para consulta privada. Si algún día abre consultorio propio, aquí ando.',
             'follow' => '¿Conoce a algún colega con consultorio particular?'],
            ['clave' => 'specific_old',
             'q' => 'Ya estoy viejo para esto',
             'a' => 'Usted sabe de dientes más que yo de todo lo demás. Esto no le cambia cómo trabaja: le quita el rato de andar escribiendo recordatorios. Y si no le gusta, lo deja.',
             'follow' => '¿Quién le ayuda con la agenda en el consultorio?'],
            ['clave' => 'specific_small',
             'q' => 'Mi consultorio es muy pequeño',
             'a' => 'Está hecho justo para ese tamaño. El que tiene consultorio chico es el que hace todo: agenda, avisa, cobra y atiende. Ahí es donde más se nota que algo le quite pasos.',
             'follow' => '¿Usted mismo hace la agenda y los recordatorios?'],
        ]],
    ];
    @endphp

    @foreach($objeciones as $grupo)
        <div>
            <div style="font-size:0.7rem;font-weight:700;letter-spacing:0.08em;color:#6b7280;margin-bottom:6px;">{{ $grupo['cat'] }}</div>

            @foreach($grupo['items'] as $o)
                <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;margin-bottom:8px;">
                    <div style="display:flex;justify-content:space-between;gap:10px;align-items:baseline;">
                        <div style="font-weight:700;font-size:0.9rem;">“{{ $o['q'] }}”</div>
                        <code style="font-size:0.65rem;color:#9ca3af;">{{ $o['clave'] }}</code>
                    </div>
                    <div style="font-size:0.86rem;margin-top:6px;">{{ $o['a'] }}</div>
                    <div style="font-size:0.8rem;color:#0f766e;margin-top:6px;">Y luego pregunta: {{ $o['follow'] }}</div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
