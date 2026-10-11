{{-- El folleto (11-oct-2026): para el dentista que ya tiene sistema, o su
     libreta en Excel, y quiere más. Solo lo que DocFácil hace hoy, con
     capturas de la demo (datos inventados). Se ve igual en el navegador
     (/brochure) que en el PDF (/brochure.pdf). --}}
@php
    $planes = $pages['plans'];
    $verde = '#0d9488';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DocFácil · Folleto</title>
    <style>
        @page { margin: 1.1cm 1.2cm; }
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; color: #1f2937; font-size: 9.5pt; line-height: 1.45; margin: 0; }
        @if($mode === 'web') body { max-width: 820px; margin: 24px auto; padding: 0 16px; background: #f8fafc; } .hoja { background: #fff; padding: 28px 30px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 18px rgba(15,23,42,.06); } @endif
        .hoja { page-break-after: always; }
        .hoja:last-child { page-break-after: auto; }
        .cabeza { width: 100%; border-collapse: collapse; border-bottom: 3px solid #14b8a6; margin-bottom: 14px; }
        .cabeza td { padding-bottom: 6px; vertical-align: bottom; }
        .marca { font-size: 16pt; font-weight: 800; color: {{ $verde }}; }
        .cabeza-der { text-align: right; font-size: 8.5pt; color: #64748b; }
        h1 { font-size: 23pt; line-height: 1.15; margin: 4px 0 8px; color: #0f172a; letter-spacing: -0.5px; }
        h2 { font-size: 15pt; color: {{ $verde }}; margin: 0 0 4px; letter-spacing: -0.3px; }
        .sub { font-size: 10.5pt; color: #475569; margin: 0 0 12px; }
        .captura { border: 1px solid #e2e8f0; border-radius: 8px; padding: 4px; background: #fff; }
        .captura img { width: 100%; display: block; border-radius: 6px; }
        .pie-foto { font-size: 8pt; color: #64748b; margin: 4px 0 0; }
        .tres { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 12px -8px 0; }
        .tres td { width: 33%; vertical-align: top; background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 8px; padding: 10px 11px; }
        .tres strong { display: block; color: #0f766e; font-size: 10pt; margin-bottom: 2px; }
        .tres span { font-size: 8.5pt; color: #475569; }
        .bloque { width: 100%; border-collapse: collapse; margin-bottom: 12px; page-break-inside: avoid; }
        .bloque td { vertical-align: top; }
        .bloque .texto { padding-left: 14px; }
        .bloque .texto.izq { padding-left: 0; padding-right: 14px; }
        .dolor { font-size: 8.5pt; font-weight: 700; color: #b45309; margin-bottom: 2px; }
        .bloque h3 { font-size: 11.5pt; margin: 0 0 4px; color: #0f172a; }
        .bloque p { margin: 0 0 4px; color: #475569; font-size: 9pt; }
        .bloque ul { margin: 2px 0 0; padding-left: 14px; color: #475569; font-size: 8.7pt; }
        .bloque li { margin-bottom: 1px; }
        .planes { width: 100%; border-collapse: separate; border-spacing: 5px 0; margin: 6px -5px 0; }
        .plan { width: 25%; vertical-align: top; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 9px; }
        .plan.pro { border: 2px solid {{ $verde }}; background: #f0fdfa; }
        .plan h4 { margin: 0; font-size: 10.5pt; color: #0f172a; }
        .plan .precio { font-size: 14pt; font-weight: 800; color: {{ $verde }}; margin: 2px 0 0; }
        .plan .ideal { font-size: 7.6pt; color: #64748b; margin: 2px 0 4px; }
        .plan .limites { font-size: 7.6pt; font-weight: 700; color: #334155; margin-bottom: 4px; }
        .plan ul { margin: 0; padding-left: 11px; font-size: 7.5pt; color: #475569; }
        .nota { font-size: 7.8pt; color: #64748b; margin: 5px 0 0; }
        .lista-nom { width: 100%; border-collapse: separate; border-spacing: 0 4px; }
        .lista-nom td { font-size: 8.8pt; padding: 5px 9px; background: #f8fafc; border-radius: 6px; }
        .lista-nom td.ok { width: 18px; color: #047857; font-weight: 800; background: none; padding-right: 0; }
        .no-hace { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 8px 11px; font-size: 8.6pt; color: #78350f; margin-top: 8px; }
        .cta { background: #0f172a; color: #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-top: 10px; }
        .cta table { width: 100%; border-collapse: collapse; }
        .cta h3 { color: #5eead4; margin: 0 0 4px; font-size: 13pt; }
        .cta .dato { color: #5eead4; font-weight: 700; }
        .cta .qr { width: 112px; text-align: center; vertical-align: middle; }
        .cta .qr img { width: 100px; height: 100px; background: #fff; padding: 4px; border-radius: 6px; }
    </style>
</head>
<body>

{{-- ── Hoja 1: portada ─────────────────────────────────────────────── --}}
<div class="hoja">
    <table class="cabeza"><tr>
        <td class="marca">DocFácil</td>
        <td class="cabeza-der">Folleto 2026 · docfacil.tu-app.co</td>
    </tr></table>

    <h1>Su consultorio dental, en orden y conectado.</h1>
    <p class="sub">Para el dentista que ya tiene sistema (o su libreta en Excel) y quiere más: la agenda, el expediente y los cobros se hablan entre sí, y se cambia sin perder sus pacientes, sus citas ni lo que le deben.</p>

    <div class="captura"><img src="{{ $screens['escritorio'] }}" alt="El escritorio de DocFácil"></div>
    <p class="pie-foto">Al entrar: lo que hay que atender hoy, quién le debe y cómo va su mes.</p>

    <table class="tres"><tr>
        <td><strong>Cobra completo</strong><span>Abonos, anticipos, pago combinado, estado de cuenta y cierre de caja que cuadra.</span></td>
        <td><strong>No se le pierde nadie</strong><span>A quién hay que llamar se arma solo: canceló, debe, le toca revisión o tiene meses sin venir.</span></td>
        <td><strong>Expediente en regla</strong><span>CURP, catálogo CIE-10 oficial, bitácora de quién abre cada expediente y notas que no se borran.</span></td>
    </tr></table>
</div>

{{-- ── Hoja 2: el día a día ────────────────────────────────────────── --}}
<div class="hoja">
    <table class="cabeza"><tr><td class="marca">DocFácil</td><td class="cabeza-der">El día a día</td></tr></table>

    <table class="bloque"><tr>
        <td style="width:56%;"><div class="captura"><img src="{{ $screens['agenda'] }}" alt="El día por doctor"></div></td>
        <td class="texto">
            <div class="dolor">"¿A quién le cabe este paciente?"</div>
            <h3>La agenda de cada doctor, de un vistazo</h3>
            <ul>
                <li>Una columna por doctor, con sus citas y sus huecos libres.</li>
                <li>«Buscar espacio» encuentra el primer hueco que alcanza para el tratamiento.</li>
                <li>Vacaciones de un doctor sin cerrar el consultorio, y festivos de México propuestos.</li>
                <li>Recordatorio de mañana a 1 clic desde su WhatsApp, y el paciente confirma con una liga.</li>
            </ul>
        </td>
    </tr></table>

    <table class="bloque"><tr>
        <td class="texto izq">
            <div class="dolor">"¿Qué le hice la vez pasada?"</div>
            <h3>Al abrir la cita, todo a la vista</h3>
            <ul>
                <li>Alergias y anticoagulantes en rojo, en cada pantalla del paciente.</li>
                <li>Los procedimientos de la cita ya vienen con su diente, del odontograma o del presupuesto.</li>
                <li>Odontograma, nota de consulta y receta en PDF con su cédula.</li>
            </ul>
        </td>
        <td style="width:56%;"><div class="captura"><img src="{{ $screens['consulta'] }}" alt="La consulta"></div></td>
    </tr></table>

    <table class="bloque"><tr>
        <td style="width:40%;"><div class="captura"><img src="{{ $screens['cobro'] }}" alt="Cobrar"></div></td>
        <td class="texto">
            <div class="dolor">"Le pago después"</div>
            <h3>Cobrar sin hacer cuentas</h3>
            <ul>
                <li>Al cobrar ya viene lo que se le hizo en la consulta, con su precio.</li>
                <li>Paga todo, una parte o nada hoy; en efectivo, tarjeta o combinado.</li>
                <li>Anticipos que quedan a su favor y se descuentan solos.</li>
                <li>Estado de cuenta y recibos en PDF, planes de pago y mensualidades.</li>
                <li>Cierre de caja: lo que debía haber, lo que se contó y la diferencia.</li>
            </ul>
        </td>
    </tr></table>
</div>

{{-- ── Hoja 3: lo que hace crecer ──────────────────────────────────── --}}
<div class="hoja">
    <table class="cabeza"><tr><td class="marca">DocFácil</td><td class="cabeza-der">Lo que hace crecer el consultorio</td></tr></table>

    <table class="bloque"><tr>
        <td style="width:21%;"><div class="captura"><img src="{{ $screens['presupuesto'] }}" alt="Presupuesto en el celular"></div></td>
        <td class="texto">
            <div class="dolor">"Lo voy a pensar"</div>
            <h3>Presupuestos que se firman</h3>
            <ul>
                <li>Por etapas, con descuento y con lo opcional aparte, sin sumarlo al total.</li>
                <li>Usted elige qué ve el paciente: el precio de cada cosa, solo el total o sin precios.</li>
                <li>Le llega una liga; lo firma con el dedo en su celular y lo aceptado se agenda en un clic.</li>
            </ul>
        </td>
    </tr></table>

    <table class="bloque"><tr>
        <td class="texto izq">
            <div class="dolor">"Se me olvidó hablarle"</div>
            <h3>A quién hay que llamar</h3>
            <ul>
                <li>Se arma sola: canceló o no vino, debe, le toca su revisión o lleva 6 meses sin venir.</li>
                <li>Cada uno con su WhatsApp ya escrito; se cierra con Logrado, Volver a llamar o Cerrar caso.</li>
                <li>Al mes, cuánto se recuperó.</li>
            </ul>
        </td>
        <td style="width:56%;"><div class="captura"><img src="{{ $screens['llamar'] }}" alt="A quién hay que llamar"></div></td>
    </tr></table>

    <table class="bloque"><tr>
        <td style="width:42%;" class="texto izq">
            <h3>Ortodoncia</h3>
            <p>Quién va atrasado en su control, quién no tiene su siguiente cita y a quién se le venció la mensualidad.</p>
            <h3 style="margin-top:8px;">Varios doctores</h3>
            <p>La comisión de cada doctor sobre lo ya cobrado, bitácora del consultorio y verificación en dos pasos para todo el equipo.</p>
        </td>
        <td><div class="captura"><img src="{{ $screens['comisiones'] }}" alt="Comisiones de un doctor"></div></td>
    </tr></table>
</div>

{{-- ── Hoja 4: cambiarse y cumplir ────────────────────────────────── --}}
<div class="hoja">
    <table class="cabeza"><tr><td class="marca">DocFácil</td><td class="cabeza-der">Cambiarse y cumplir</td></tr></table>

    <table class="bloque"><tr>
        <td class="texto izq">
            <div class="dolor">"¿Y mis pacientes de antes?"</div>
            <h2>Cambiarse sin perder nada</h2>
            <p>En «Traer sus datos» sube desde Excel sus pacientes (con CURP), sus citas que vienen y lo que le deben. Reconoce a cada paciente por su teléfono o su nombre y subirlo dos veces no duplica.</p>
            <p>Si prefiere, nos manda su Excel y le ayudamos a cargar sus pacientes.</p>
        </td>
        <td style="width:58%;"><div class="captura"><img src="{{ $screens['traer'] }}" alt="Traer sus datos"></div></td>
    </tr></table>

    <table class="bloque"><tr>
        <td class="texto izq">
            <h2>El expediente en regla</h2>
            <table class="lista-nom">
                <tr><td class="ok">✓</td><td>CURP validada que llena nacimiento, sexo y estado</td></tr>
                <tr><td class="ok">✓</td><td>Diagnósticos con el catálogo CIE-10 oficial de la DGIS</td></tr>
                <tr><td class="ok">✓</td><td>Bitácora: quién abrió y quién cambió cada expediente</td></tr>
                <tr><td class="ok">✓</td><td>Notas que no se borran: se corrigen con una nota ligada</td></tr>
                <tr><td class="ok">✓</td><td>Verificación en dos pasos y expediente en PDF foliado</td></tr>
            </table>
            <h2 style="margin-top:14px;">Su paciente, en su celular</h2>
            <p>En su portal ve su próxima cita, lo que debe y sus presupuestos, baja su estado de cuenta y sus recibos, firma sus consentimientos antes de llegar y le dice cómo le fue. Las opiniones solo las ve el consultorio.</p>
        </td>
        <td style="width:30%;"><div class="captura"><img src="{{ $screens['portal'] }}" alt="El portal del paciente"></div></td>
    </tr></table>
</div>

{{-- ── Hoja 5: precios ─────────────────────────────────────────────── --}}
<div class="hoja">
    <table class="cabeza"><tr><td class="marca">DocFácil</td><td class="cabeza-der">Planes y precios</td></tr></table>

    <h2>Planes</h2>
    <table class="planes"><tr>
        @foreach($planes as $plan)
            <td class="plan {{ $plan['popular'] ? 'pro' : '' }}">
                <h4>{{ $plan['name'] }}</h4>
                <div class="precio">${{ $plan['price'] }}<span style="font-size:7.5pt;color:#64748b;font-weight:400;"> al mes</span></div>
                <div class="ideal">{{ $plan['ideal'] }}</div>
                <div class="limites">{{ $plan['limits'] }}</div>
                <ul>
                    @if($plan['lead'])<li><strong>{{ $plan['lead'] }}</strong></li>@endif
                    @foreach(array_slice($plan['features'], 0, 6) as $f)<li>{{ $f }}</li>@endforeach
                </ul>
            </td>
        @endforeach
    </tr></table>
    <p class="nota">Precios en pesos mexicanos, con IVA incluido. Pagando el año son 10 meses. 15 días de prueba con todo, sin tarjeta, y garantía de 30 días en su primer pago.</p>

    <div class="no-hace"><strong>Lo que no hace, para que lo sepa desde hoy:</strong> no timbra facturas (deja anotado quién la pidió y la hace su contador) y los recordatorios se mandan a 1 clic desde el WhatsApp del consultorio.</div>

    <div class="cta">
        <table><tr>
            <td>
                <h3>Véalo con datos de ejemplo</h3>
                <div><span class="dato">docfacil.tu-app.co/demo</span> · correo <span class="dato">demo@docfacil.com</span> · contraseña <span class="dato">demo2026</span></div>
                <div style="margin-top:6px;">O cree su cuenta: 15 días con todo, sin tarjeta.</div>
                <div style="margin-top:8px;font-size:8.5pt;"><strong style="color:#fff;">Omar Lerma</strong>, fundador · Los Mochis, Sinaloa · WhatsApp <span class="dato">668 249 3398</span></div>
            </td>
            <td class="qr"><img src="{{ $qrDataUri }}" alt="Crear cuenta"></td>
        </tr></table>
    </div>
</div>
</body>
</html>
