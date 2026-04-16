<div style="max-height:65vh;overflow-y:auto;display:flex;flex-direction:column;gap:12px;">
    @php
    $objeciones = [
        ['cat' => 'PRECIO', 'items' => [
            ['q' => '"Está caro" / "No tengo presupuesto"',
             'a' => 'Doctor, ¿cuánto cobra por consulta? ¿$600? DocFácil Básico cuesta $499 — menos de 1 consulta al mes. Y con los recordatorios WhatsApp recupera entre 8 y 12 citas que antes perdía. Son $4,800+ extra al mes por $499. Se paga casi 10 veces. Y tiene garantía de 30 días.',
             'follow' => '¿Cuántas citas pierde a la semana porque los pacientes no llegan?'],
            ['q' => '"¿Por qué pagar si puedo usar Excel/libreta?"',
             'a' => 'Puede seguir con Excel, pero Excel no le manda WhatsApp a sus pacientes. Tampoco genera recetas PDF con su cédula ni expedientes que cumplan la NOM-004. DocFácil hace todo eso por $499/mes y ahorra horas al día.',
             'follow' => '¿Cuánto tiempo al día le toma organizar su agenda actualmente?'],
            ['q' => '"No sé si lo voy a usar"',
             'a' => 'Por eso los primeros 15 días son completamente gratis, sin meter tarjeta. Y tenemos garantía de 30 días: si no ve resultados le devolvemos su dinero. Sin preguntas.',
             'follow' => '¿Qué tal si lo probamos juntos ahorita? Toma 2 minutos registrarse.'],
        ]],
        ['cat' => 'TECNOLOGÍA', 'items' => [
            ['q' => '"No soy tecnológico" / "No le entiendo"',
             'a' => 'Precisamente por eso DocFácil es tan simple. Si sabe usar WhatsApp, sabe usar DocFácil. Y lo acompañamos paso a paso en la configuración, sin costo extra.',
             'follow' => '¿Su secretaria maneja algún celular o computadora? Ella puede ayudarle al principio.'],
            ['q' => '"Ya tengo un sistema" (Doctoralia, otro)',
             'a' => '¿Su sistema actual le manda recordatorios por WhatsApp a sus pacientes? ¿Le genera recetas PDF con su cédula? ¿Tiene portal donde el paciente ve su historial? DocFácil hace todo eso por $499. Pruébelo 15 días sin cancelar lo que ya tiene.',
             'follow' => '¿Qué es lo que más le gusta de su sistema actual? ¿Y qué le falta?'],
            ['q' => '"¿Y si se cae el internet?"',
             'a' => 'DocFácil funciona en la nube, pero sus datos no se pierden nunca — hay respaldos automáticos diarios. Y hoy en día el internet está en todos lados. Además funciona desde su celular con datos móviles.',
             'follow' => '¿En su consultorio tiene WiFi normalmente?'],
        ]],
        ['cat' => 'CONFIANZA', 'items' => [
            ['q' => '"¿Mis datos están seguros?"',
             'a' => 'Totalmente. Encriptación SSL, respaldos automáticos diarios, y sus datos están completamente separados de otras clínicas. Cumplimos con la LFPDPPP y la NOM-004. Ningún otro software en su rango de precio le da eso.',
             'follow' => '¿Le gustaría ver cómo se ve el expediente de un paciente dentro del sistema?'],
            ['q' => '"No los conozco" / "¿Quién está detrás?"',
             'a' => 'Somos de aquí de Culiacán, Sinaloa. Equipo local, soporte directo por WhatsApp, no un call center. Ya tenemos consultorios usándolo aquí en la ciudad. Si quiere, le paso el contacto de un doctor que ya lo usa para que le pregunte.',
             'follow' => '¿Le gustaría ver una demo en vivo? Son 10 minutos y me dice usted si le convence.'],
            ['q' => '"Necesito pensarlo"',
             'a' => 'Claro doctor, tómese su tiempo. Le activo la prueba gratuita de 14 días para que lo explore con calma. Sin tarjeta, sin compromiso. Si al día 14 no le convence, simplemente no paga nada. ¿Le parece?',
             'follow' => '¿Cuál es su principal duda? Tal vez se la puedo resolver ahorita.'],
        ]],
        ['cat' => 'TIMING', 'items' => [
            ['q' => '"Ahorita no es buen momento"',
             'a' => 'Lo entiendo perfectamente. Pero piénselo así: cada semana que pasa sin recordatorios son 3-5 citas que pierde. En un mes son $8,000+ que no recupera. La prueba es gratis y se configura en 10 minutos.',
             'follow' => '¿Cuándo sería buen momento? Le agendo un recordatorio para contactarle.'],
            ['q' => '"Cuando tenga más pacientes"',
             'a' => 'Justamente DocFácil le ayuda a no PERDER los que ya tiene. El 30% de las citas se pierden por olvido. Con recordatorios WhatsApp eso baja al 10%. Además tiene portal del paciente donde ellos pueden ver sus citas — eso da imagen profesional.',
             'follow' => '¿Cuántos pacientes atiende por semana actualmente?'],
        ]],
        ['cat' => 'ESPECÍFICAS', 'items' => [
            ['q' => '"Mi consultorio es muy pequeño"',
             'a' => 'El plan Básico está hecho exactamente para consultorios como el suyo: 1 doctor, hasta 200 pacientes, citas ilimitadas. $499/mes. No necesita nada más grande. Y si crece, sube de plan.',
             'follow' => '¿Cuántos pacientes tiene registrados actualmente?'],
            ['q' => '"Ya estoy viejo para esto"',
             'a' => 'Doctor, tenemos usuarios de 60+ años que lo usan sin problema. La interfaz es muy simple — si usa WhatsApp en su celular, esto es igual de fácil. Además su secretaria puede manejarlo mientras usted atiende.',
             'follow' => '¿Tiene alguien que le ayude con lo administrativo?'],
        ]],
    ];
    @endphp

    @foreach($objeciones as $grupo)
    <div style="background:linear-gradient(135deg,rgba(245,158,11,0.08),rgba(239,68,68,0.05));border:1px solid rgba(245,158,11,0.2);border-radius:12px;padding:16px;">
        <div style="font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:#92400e;margin-bottom:10px;">{{ $grupo['cat'] }}</div>
        @foreach($grupo['items'] as $obj)
        <div style="background:white;border-radius:10px;padding:14px;margin-bottom:8px;border:1px solid #f3f4f6;">
            <div style="font-size:0.85rem;font-weight:700;color:#b91c1c;margin-bottom:6px;">{{ $obj['q'] }}</div>
            <div style="font-size:0.82rem;color:#374151;line-height:1.55;margin-bottom:6px;">{{ $obj['a'] }}</div>
            <div style="font-size:0.75rem;color:#0d9488;font-weight:600;">↳ Pregunta: {{ $obj['follow'] }}</div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>
