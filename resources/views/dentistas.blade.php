<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DocFácil: software para Consultorio Dental en México</title>
    <meta name="description" content="Para el consultorio dental que lleva todo en papel: agenda, recetas con cédula, odontograma, presupuestos y quién le debe, en el celular. 15 días gratis, sin tarjeta.">
    <meta name="theme-color" content="#eef1f3">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="canonical" href="{{ url('/dentistas') }}">

    <link rel="preload" as="image" href="{{ asset('images/landing/limas.jpg') }}" fetchpriority="high">
    <link rel="dns-prefetch" href="//wa.me">

    {{-- iOS PWA --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="DocFácil">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=yes">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">

    {{-- OpenGraph (Facebook, WhatsApp, LinkedIn) --}}
    <meta property="og:title" content="DocFácil: software para Consultorio Dental en México">
    <meta property="og:description" content="Odontograma digital, recordatorios WhatsApp, recetas PDF con cédula. 15 días gratis para dentistas.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">
    <meta property="og:image:secure_url" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="DocFácil: software para consultorios dentales">
    <meta property="og:url" content="{{ url('/dentistas') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DocFácil">
    <meta property="og:locale" content="es_MX">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="DocFácil: software para Consultorio Dental en México">
    <meta name="twitter:description" content="Odontograma digital, recordatorios WhatsApp, recetas PDF con cédula. 15 días gratis para dentistas.">
    <meta name="twitter:image" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">
    <meta name="twitter:image:alt" content="DocFácil: software para consultorios dentales">
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "DocFácil para Dentistas",
        "description": "Software para consultorios dentales en México. Odontograma digital FDI, recordatorios WhatsApp 1-clic, expediente clínico pensado para la NOM-004 (notas que se bloquean a las 24 horas), recetas PDF con cédula y cobros por WhatsApp.",
        "applicationCategory": "HealthApplication",
        "operatingSystem": "Web",
        "url": "{{ url('/dentistas') }}",
        "offers": [
            { "@@type": "Offer", "price": "0", "priceCurrency": "MXN", "name": "Plan Free", "description": "1 doctor, 15 pacientes, agenda básica. Gratis de por vida." },
            { "@@type": "Offer", "price": "499", "priceCurrency": "MXN", "name": "Plan Básico", "description": "Odontograma FDI + WhatsApp + recetas PDF + cobros." },
            { "@@type": "Offer", "price": "999", "priceCurrency": "MXN", "name": "Plan Pro", "description": "Hasta 3 doctores, portal público, consentimientos, reportes avanzados." },
            { "@@type": "Offer", "price": "1999", "priceCurrency": "MXN", "name": "Plan Clínica", "description": "Doctores ilimitados, reportes por doctor, onboarding 1:1." }
        ],
        "areaServed": { "@@type": "Country", "name": "México" },
        "inLanguage": "es-MX"
    }
    </script>

    {{-- Organization schema — entidad reconocible por LLMs y Google Knowledge Graph --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "DocFácil",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('images/logo_doc_facil.png') }}",
        "description": "Software dental hecho en México para consultorios dentales. Founder-led por Omar Lerma desde Los Mochis, Sinaloa.",
        "founder": {
            "@@type": "Person",
            "name": "Omar Lerma",
            "jobTitle": "Fundador"
        },
        "areaServed": { "@@type": "Country", "name": "México" },
        "knowsAbout": [
            "Software dental",
            "Odontograma digital FDI",
            "Expediente clínico NOM-004-SSA3",
            "LFPDPPP",
            "Recordatorios WhatsApp",
            "Recetas digitales con cédula"
        ],
        "contactPoint": {
            "@@type": "ContactPoint",
            "telephone": "+52-668-249-3398",
            "contactType": "customer service",
            "areaServed": "MX",
            "availableLanguage": "Spanish"
        }
    }
    </script>

    @php
    // Los lugares del programa Fundador salen de config/founders.php, no del
    // copy: así el número que promete la página no puede desfasarse del que
    // cuenta `Clinic::lugaresDeFundador()`.
    $founderSeats = (int) config('founders.seats', 10);
    // Las mismas preguntas que se ven en la página (sección #faq): el JSON-LD
    // no puede prometer más que lo que el doctor lee.
    $landingFaqsForSchema = $landingFaqs = [
        ['q' => '¿Cuánto cuesta?', 'a' => 'Hay un plan Free para siempre (1 doctor, 15 pacientes). Básico $499 al mes, Pro $999 y Clínica $1,999. Si paga el año, le sale en 10 meses. Los primeros 15 días tiene todo, sin tarjeta, y su primer pago tiene garantía de 30 días.'],
        ['q' => '¿Y si no me llevo bien con la tecnología?', 'a' => 'Si usa WhatsApp, puede usar DocFácil. Yo le acompaño por WhatsApp las primeras semanas, sin costo extra, y le dejo cargada su agenda.'],
        ['q' => '¿Me ayudan a pasar mis pacientes?', 'a' => 'Sí. Me manda su Excel por WhatsApp y lo subo a su cuenta, sin costo y sin importar cuántos pacientes tenga. Si los tiene en la libreta, le ayudo a armar la lista con los datos que importan.'],
        ['q' => '¿Funciona en el celular?', 'a' => 'Sí. Funciona en el navegador del celular, la tablet o la computadora, y se puede instalar como app en iPhone o Android sin pasar por la tienda de apps.'],
        ['q' => '¿Mis datos y los de mis pacientes están seguros?', 'a' => 'La conexión va cifrada, hay respaldo automático diario y cada consultorio está aislado: sus datos nunca se mezclan con los de otro. Las notas clínicas y las recetas se bloquean 24 horas después de creadas y queda historial de cambios. Los servidores están en Estados Unidos (DigitalOcean).'],
        ['q' => '¿Y si no me sirve?', 'a' => 'Cancela cuando quiera, sin penalización. Y si en los primeros 30 días de su primer pago decide que no le sirve, le devolvemos ese pago completo (vea la garantía en los términos). Sus datos quedan 30 días por si quiere una copia.'],
    ];
    @endphp
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "FAQPage",
        "mainEntity": [
            @foreach($landingFaqsForSchema as $i => $faq)
            {
                "@@type": "Question",
                "name": @json($faq['q']),
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": @json($faq['a'])
                }
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
    </script>
    {{-- Captura evento beforeinstallprompt temprano para que Alpine lo pueda leer --}}
    <script>
        window.__docfacilInstallPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.__docfacilInstallPrompt = e;
            window.dispatchEvent(new CustomEvent('docfacil-install-ready'));
        });
        window.addEventListener('appinstalled', () => {
            window.__docfacilInstallPrompt = null;
            window.dispatchEvent(new CustomEvent('docfacil-install-done'));
        });
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- El plugin collapse va ANTES del core de Alpine (asi lo pide con defer). --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script>
        // El inicio arranca escondido solo si va a haber animación; si GSAP no
        // carga, al segundo y medio se ve todo igual. El titular y los botones
        // nunca se esconden.
        if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('js-mov');
            setTimeout(() => document.documentElement.classList.remove('js-mov'), 1500);
        }
    </script>
    <style>
        /* El estuche de limas. Cada paso del consultorio lleva el color de su
           lima en el código ISO (15 blanco, 20 amarillo, 25 rojo, 30 azul,
           35 verde, 40 negro) y ese color no se usa para nada más. Todo va en
           CSS propio: las utilidades responsive de Tailwind no siempre
           compilan en producción. */
        :root {
            --acero: #eef1f3; --acero-2: #dde3e7; --acero-3: #c3ccd3; --acero-4: #8f99a3;
            --tinta: #15181c; --tinta-2: #353c44; --tinta-3: #565f69;
            --iso-15: #ffffff; --iso-20: #f2c400; --iso-25: #d7262e; --iso-30: #1f5fd1; --iso-35: #178049; --iso-40: #15181c;
            --wa: #18723f; --wa-burbuja: #d9fdd3; --wa-fondo: #efe7de;
            --sale: cubic-bezier(.16, 1, .3, 1);
            --r-chico: 8px; --r-medio: 12px; --r-grande: 16px; --r-panel: 28px;
        }
        html { scroll-behavior: smooth; touch-action: manipulation; -webkit-tap-highlight-color: rgba(21,24,28,.08); }
        .saltar { position: absolute; left: 12px; top: -60px; z-index: 60; padding: 12px 16px; border-radius: var(--r-medio); background: var(--tinta); color: #fff; font-weight: 700; text-decoration: none; }
        .saltar:focus { top: 12px; }
        @@media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }
        html, body { overflow-x: clip; max-width: 100vw; }
        body { margin: 0; background: var(--acero); color: var(--tinta); font-family: 'Archivo', system-ui, sans-serif; font-size: 17px; line-height: 1.6; font-variation-settings: 'wdth' 100; -webkit-font-smoothing: antialiased; }
        ::selection { background: var(--iso-20); color: var(--tinta); }
        :focus-visible { outline: 3px solid var(--iso-30); outline-offset: 3px; border-radius: 6px; }
        a { color: inherit; }
        img, video { max-width: 100%; }
        section[id] { scroll-margin-top: 76px; }
        [x-cloak] { display: none !important; }

        .e-wrap { max-width: 1200px; margin: 0 auto; padding: 0 16px; }
        .e-cond { font-variation-settings: 'wdth' 72; }
        .e-h1 { font-size: clamp(40px, 5vw, 70px); line-height: .98; font-weight: 850; letter-spacing: -0.025em; font-variation-settings: 'wdth' 78; margin: 0; text-wrap: balance; }
        .e-h2 { font-size: clamp(34px, 4.6vw, 60px); line-height: 1.02; font-weight: 820; letter-spacing: -0.02em; font-variation-settings: 'wdth' 80; margin: 0 0 18px; text-wrap: balance; }
        .e-h3 { font-size: 22px; line-height: 1.2; font-weight: 750; margin: 0 0 6px; font-variation-settings: 'wdth' 90; }
        .e-lead { font-size: clamp(18px, 1.6vw, 21px); line-height: 1.5; margin: 22px 0 0; max-width: 34ch; color: var(--tinta-2); }
        .e-p { font-size: 17px; line-height: 1.65; margin: 0 0 14px; max-width: 56ch; }
        .e-nota { font-size: 14px; font-weight: 650; letter-spacing: .01em; }
        .e-ejemplo { font-size: 12.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; font-variation-settings: 'wdth' 75; opacity: .72; }

        /* Botones: grafito sobre acero, blanco sobre los campos de color. */
        .e-ctas { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 32px; }
        .e-btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; min-height: 54px; padding: 0 26px; border-radius: var(--r-medio); font-size: 17px; font-weight: 750; text-decoration: none; white-space: nowrap; border: 2px solid transparent; cursor: pointer; transition: transform .25s var(--sale), box-shadow .25s var(--sale), background-color .2s, color .2s; }
        .e-btn:active { transform: translateY(1px) scale(.98); }
        .e-btn svg { width: 20px; height: 20px; flex-shrink: 0; }
        .e-btn-tinta { background: var(--tinta); color: #fff; box-shadow: 0 14px 30px -14px rgba(21,24,28,.7); }
        .e-btn-tinta:hover { transform: translateY(-2px); box-shadow: 0 20px 36px -16px rgba(21,24,28,.75); }
        .e-btn-linea { background: transparent; color: var(--tinta); border-color: var(--tinta); }
        .e-btn-linea:hover { background: var(--tinta); color: #fff; }
        .e-btn-blanco { background: #fff; color: var(--tinta); box-shadow: 0 14px 30px -14px rgba(0,0,0,.45); }
        .e-btn-blanco:hover { transform: translateY(-2px); }
        .e-btn-wa { background: var(--wa); color: #fff; }
        .e-btn-wa:hover { background: #135f34; transform: translateY(-2px); }

        /* El mango de la lima: plástico moleteado, número de calibre arriba
           y el vástago de acero que sale por abajo. */
        .mango { --c: var(--iso-15); position: relative; display: block; width: 30px; height: 74px; border-radius: 7px 7px 9px 9px; background:
                repeating-linear-gradient(180deg, rgba(0,0,0,0) 0 5px, rgba(0,0,0,.13) 5px 6.5px) 0 22px / 100% 44px no-repeat,
                linear-gradient(90deg, rgba(255,255,255,.35), rgba(255,255,255,0) 35%, rgba(0,0,0,.14) 80%, rgba(0,0,0,.22)),
                var(--c);
            box-shadow: inset 0 0 0 1px rgba(0,0,0,.12), 0 8px 14px -8px rgba(21,24,28,.55); }
        .mango::after { content: ''; position: absolute; left: 50%; top: 100%; width: 3px; height: 34px; margin-left: -1.5px; border-radius: 0 0 2px 2px; background: linear-gradient(90deg, #8b949c, #f2f4f6 45%, #9aa3ab); }
        .mango b { position: absolute; top: 5px; left: 0; right: 0; text-align: center; font-size: 11px; font-weight: 800; font-variation-settings: 'wdth' 65; color: rgba(21,24,28,.78); }
        .mango[data-iso="25"] b, .mango[data-iso="30"] b, .mango[data-iso="35"] b, .mango[data-iso="40"] b { color: rgba(255,255,255,.9); }
        .mango[data-iso="15"] { --c: var(--iso-15); }
        .mango[data-iso="20"] { --c: var(--iso-20); }
        .mango[data-iso="25"] { --c: var(--iso-25); }
        .mango[data-iso="30"] { --c: var(--iso-30); }
        .mango[data-iso="35"] { --c: var(--iso-35); }
        .mango[data-iso="40"] { --c: var(--iso-40); }
        /* El mango fotografiado (recorte con transparencia) donde se ve en
           grande: el índice del inicio y la cabeza de cada paso. */
        .mango.foto { background: center / contain no-repeat; box-shadow: none; border-radius: 0; filter: drop-shadow(0 8px 10px rgba(21,24,28,.35)); }
        .mango.foto b { display: none; }
        .mango.foto::after { top: 99%; }
        @foreach (['15', '20', '25', '30', '35', '40'] as $c)
        .mango.foto[data-iso="{{ $c }}"] { background-image: url('{{ asset('images/landing/mango-' . $c . '.png') }}'); }
        @endforeach

        /* Navegación */
        .e-nav { position: fixed; inset: 0 0 auto 0; z-index: 50; height: 68px; background: rgba(238,241,243,.86); backdrop-filter: blur(14px) saturate(140%); -webkit-backdrop-filter: blur(14px) saturate(140%); border-bottom: 1px solid transparent; transition: border-color .3s, background-color .3s; }
        .e-nav.e-con-borde { border-bottom-color: var(--acero-3); background: rgba(238,241,243,.94); }
        .e-nav-in { max-width: 1200px; margin: 0 auto; height: 100%; padding: 0 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .e-nav img { height: 44px; width: auto; display: block; }
        .e-nav-links { display: none; align-items: center; gap: 28px; }
        .e-nav-links a, .e-nav-links button { font: inherit; font-size: 15px; font-weight: 600; color: var(--tinta-2); text-decoration: none; background: none; border: 0; cursor: pointer; padding: 6px 0; }
        .e-nav-links a:hover, .e-nav-links button:hover { color: var(--tinta); text-decoration: underline; text-underline-offset: 6px; text-decoration-thickness: 2px; }
        .e-nav .e-btn { min-height: 44px; padding: 0 18px; font-size: 15px; }
        .e-nav-links a.e-btn-tinta { background: var(--tinta); color: #fff; text-decoration: none; }
        .e-nav-links a.e-btn-tinta:hover { background: #000; color: #fff; text-decoration: none; }
        .e-menu-btn { display: inline-flex; align-items: center; justify-content: center; width: 46px; height: 46px; border-radius: var(--r-medio); border: 1px solid var(--acero-3); background: #fff; cursor: pointer; }
        .e-menu-btn svg { width: 22px; height: 22px; }
        .e-menu { position: fixed; top: 68px; left: 0; right: 0; z-index: 49; background: var(--acero); border-bottom: 1px solid var(--acero-3); padding: 12px 16px 18px; display: grid; gap: 4px; }
        .e-menu a, .e-menu button { display: block; padding: 12px 4px; font: inherit; font-size: 17px; font-weight: 600; color: var(--tinta); text-decoration: none; text-align: left; background: none; border: 0; }
        .e-menu .e-btn { margin-top: 8px; color: #fff; text-align: center; }

        /* Riel: en computadora, seis mangos al costado; en el celular, una
           tira de seis colores bajo la barra. El del paso en pantalla crece. */
        .riel { position: fixed; z-index: 40; pointer-events: none; opacity: 0; transition: opacity .4s var(--sale); }
        .riel.e-visible { opacity: 1; pointer-events: auto; }
        .riel ol { list-style: none; margin: 0; padding: 0; display: flex; }
        .riel a { display: block; text-decoration: none; }
        .riel-txt { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }
        .riel { top: 0; left: 50%; height: 68px; z-index: 51; transform: translateX(-50%); display: flex; align-items: center; }
        .riel ol { justify-content: center; gap: 12px; }
        .riel a { padding: 2px 4px; }
        .riel .mango { width: 12px; height: 26px; border-radius: 3px 3px 4px 4px; transition: transform .45s var(--sale), box-shadow .3s; transform-origin: top center; }
        .riel .mango::after { height: 0; }
        .riel .mango b { display: none; }
        .riel a.activo .mango { transform: scale(1.3, 1.2); box-shadow: inset 0 0 0 1px rgba(0,0,0,.12), 0 0 0 2px var(--acero), 0 0 0 3.5px var(--tinta); }

        /* 1. Inicio */
        .ini { position: relative; padding: 100px 0 0; }
        .ini-grid { display: grid; grid-template-columns: 1fr; gap: 40px; align-items: center; }
        .ini-h1 .linea { display: block; overflow: hidden; padding-bottom: .06em; }
        .ini-h1 .linea > span { display: block; }
        .ini-media { position: relative; padding: 48px 0 8px; }
        .ini-foto { position: absolute; inset: 0 0 120px 0; border-radius: var(--r-panel); overflow: hidden; background: var(--acero-2); }
        .ini-foto img { width: 100%; height: 100%; object-fit: cover; object-position: 58% 52%; display: block; transform: scale(1.08); }
        .ini-foto::after { content: ''; position: absolute; inset: 0; background: linear-gradient(200deg, rgba(21,24,28,0) 40%, rgba(21,24,28,.38)); }

        /* El celular con la agenda de mañana: un componente que se mueve, no
           una captura. Los datos son de ejemplo y lo dice. */
        .cel { position: relative; width: 296px; margin: 0 auto; border-radius: 42px; padding: 11px; background: #0d0f12; box-shadow: 0 50px 80px -40px rgba(21,24,28,.75), inset 0 0 0 1.5px #3b4148; }
        .ini .cel { position: relative; z-index: 1; }
        .cel-pantalla { position: relative; border-radius: 32px; overflow: hidden; background: #f6f8f9; height: 560px; }
        .cel-barra { display: flex; justify-content: space-between; align-items: center; padding: 12px 22px 6px; font-size: 12px; font-weight: 700; }
        .cel-isla { position: absolute; top: 9px; left: 50%; width: 86px; height: 24px; margin-left: -43px; border-radius: 20px; background: #0d0f12; }
        .ag-cab { padding: 10px 18px 12px; border-bottom: 1px solid var(--acero-2); }
        .ag-cab small { display: block; font-size: 12px; font-weight: 700; color: var(--tinta-3); }
        .ag-cab strong { display: block; font-size: 22px; font-weight: 800; font-variation-settings: 'wdth' 82; letter-spacing: -0.01em; }
        .ag-lista { list-style: none; margin: 0; padding: 8px 10px; display: grid; gap: 8px; }
        .ag-cita { display: grid; grid-template-columns: 46px 1fr auto; gap: 10px; align-items: center; padding: 11px 10px; border-radius: var(--r-medio); background: #fff; box-shadow: 0 1px 0 rgba(21,24,28,.06), 0 6px 14px -12px rgba(21,24,28,.5); transition: background-color .4s, box-shadow .4s; }
        .ag-hora { font-size: 15px; font-weight: 800; font-variation-settings: 'wdth' 75; font-variant-numeric: tabular-nums; }
        .ag-quien { min-width: 0; }
        .ag-quien strong { display: block; font-size: 14px; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ag-quien span { display: block; font-size: 12px; color: var(--tinta-3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ag-edo { font-size: 11px; font-weight: 750; padding: 5px 8px; border-radius: 999px; white-space: nowrap; background: var(--acero-2); color: var(--tinta-2); transition: background-color .35s, color .35s; }
        .ag-cita[data-edo="enviado"] .ag-edo { background: #fff4c2; color: #6b5300; }
        .ag-cita[data-edo="confirmo"] .ag-edo { background: #dff5e7; color: #12663a; }
        .ag-cita.ag-activa { box-shadow: 0 0 0 2px var(--iso-20), 0 10px 20px -12px rgba(21,24,28,.5); }
        .ag-wa { position: absolute; left: 10px; right: 10px; bottom: 10px; border-radius: 20px; background: var(--wa-fondo); padding: 14px 12px 12px; box-shadow: 0 -10px 40px -20px rgba(21,24,28,.6); transform: translateY(115%); transition: transform .55s var(--sale); }
        .ag-wa.abierto { transform: none; }
        .ag-wa-de { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: var(--tinta-2); margin-bottom: 8px; }
        .ag-wa-de svg { width: 16px; height: 16px; color: var(--wa); }
        .burbuja { position: relative; background: var(--wa-burbuja); border-radius: 10px 10px 2px 10px; padding: 9px 11px 18px; font-size: 13px; line-height: 1.42; color: #111b21; white-space: pre-line; min-height: 40px; box-shadow: 0 1px 0 rgba(0,0,0,.08); }
        .burbuja .hora { position: absolute; right: 8px; bottom: 3px; font-size: 10px; color: #667781; }
        .burbuja .cursor { display: inline-block; width: 2px; height: 1em; background: #111b21; vertical-align: -2px; animation: parpadeo 1s steps(2) infinite; }
        @keyframes parpadeo { 50% { opacity: 0; } }
        .ag-wa-enviar { display: flex; justify-content: flex-end; margin-top: 10px; }
        .ag-wa-enviar span { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; border-radius: 999px; background: var(--wa); color: #fff; font-size: 13px; font-weight: 750; transition: transform .2s; }
        .ag-wa-enviar span.toque { transform: scale(.9); }
        .cel-pie { position: absolute; left: 0; right: 0; bottom: 0; padding: 10px; text-align: center; }
        .ag-cab .e-ejemplo { display: block; margin-top: 2px; font-size: 10.5px; opacity: 1; color: var(--tinta-3); }
        .ini-otra { position: absolute; right: 16px; top: 14px; z-index: 2; font: inherit; font-size: 13px; font-weight: 700; color: var(--tinta); background: rgba(255,255,255,.88); border: 0; border-radius: 999px; padding: 8px 14px; cursor: pointer; }
        .ini-otra:hover { background: #fff; }

        /* Índice del estuche: los seis mangos, cada uno lleva a su paso. */
        .estuche { margin: 56px 0 0; padding: 26px 0 58px; border-top: 1px solid var(--acero-3); }
        .estuche ol { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 4px; }
        .estuche a { display: flex; flex-direction: column; align-items: center; gap: 30px; text-decoration: none; padding: 6px 0; border-radius: var(--r-medio); }
        .estuche .mango { width: 26px; height: 57px; }
        .estuche .mango::after { height: 24px; }
        .estuche .mango b { display: none; }
        .estuche .largo { display: none; }
        .estuche .corto { font-size: 13px; font-variation-settings: 'wdth' 72; }
        .estuche a .mango { transition: transform .45s var(--sale); }
        .estuche a:hover .mango { transform: translateY(-8px) rotate(-4deg); }
        .estuche a > span:not(.mango) { font-size: 15px; font-weight: 700; text-align: center; line-height: 1.25; }

        /* Los campos de color: cada paso abre su charola a sangre completa. */
        .paso { position: relative; padding: 96px 0; overflow: hidden; }
        .paso-campo { position: absolute; inset: 0; z-index: -1; background: var(--c); }
        .paso[data-paso="agenda"] { --c: var(--iso-15); }
        .paso[data-paso="recordatorio"] { --c: var(--iso-20); }
        .paso[data-paso="consulta"] { --c: var(--iso-25); color: #fff; }
        .paso[data-paso="odontograma"] { --c: var(--iso-30); color: #fff; }
        .paso[data-paso="mensualidades"] { --c: var(--iso-35); color: #fff; }
        .paso[data-paso="omar"] { --c: var(--iso-40); color: #fff; }
        .paso-cabeza { display: flex; gap: 22px; align-items: flex-start; margin-bottom: 40px; }
        .paso-cabeza .mango { flex-shrink: 0; margin-top: 6px; width: 34px; height: 74px; }
        .paso-cabeza .e-p { margin-top: 4px; }
        .paso[data-paso="agenda"] .e-p, .paso[data-paso="recordatorio"] .e-p { color: var(--tinta-2); }
        .paso[data-paso="consulta"] .e-p { color: #ffe9ea; }
        .paso[data-paso="odontograma"] .e-p { color: #e6eeff; }
        .paso[data-paso="mensualidades"] .e-p { color: #e3f6ea; }
        .paso[data-paso="omar"] .e-p { color: #d8dde2; }
        .lp-video { margin: 0; }
        .lp-video video { display: block; width: 100%; height: auto; aspect-ratio: 4 / 5; object-fit: cover; border-radius: var(--r-grande); background: var(--tinta); box-shadow: 0 30px 60px -30px rgba(0,0,0,.6); }
        .lp-video figcaption { font-size: 14px; font-weight: 650; margin-top: 10px; opacity: .85; }

        /* 15 · Agenda: la libreta y el celular que la cruza. */
        .ag-grid { display: grid; grid-template-columns: 1fr; gap: 36px; align-items: center; }
        .libreta { position: relative; }
        .libreta-foto { display: block; width: 100%; height: auto; aspect-ratio: 4 / 3; object-fit: cover; border-radius: var(--r-panel); }
        .libreta-tel { position: absolute; right: 6%; bottom: -8%; width: 40%; max-width: 250px; height: auto; border-radius: 22px; border: 7px solid #0d0f12; background: #0d0f12; box-shadow: 0 40px 60px -30px rgba(21,24,28,.7); }
        .puntos { list-style: none; margin: 26px 0 0; padding: 0; display: grid; gap: 14px; }
        .puntos li { display: grid; grid-template-columns: 26px 1fr; gap: 12px; align-items: start; font-size: 17px; line-height: 1.5; }
        .puntos svg { width: 22px; height: 22px; margin-top: 2px; }

        /* 20 · Recordatorio: pruébelo con su número. */
        .rec-grid { display: grid; grid-template-columns: 1fr; gap: 28px; align-items: start; }
        .rec-prueba { background: #fff; color: var(--tinta); border-radius: var(--r-panel); padding: 26px 20px; box-shadow: 0 40px 70px -40px rgba(80,62,0,.55); display: grid; gap: 22px; }
        .rec-campos { display: grid; gap: 14px; }
        .campo label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 6px; color: var(--tinta-2); }
        .campo input, .campo textarea { width: 100%; box-sizing: border-box; min-height: 52px; padding: 12px 14px; border-radius: var(--r-medio); border: 1.5px solid var(--acero-3); background: #fff; font: inherit; font-size: 17px; color: var(--tinta); transition: border-color .2s, box-shadow .2s; }
        .campo input::placeholder, .campo textarea::placeholder { color: #6c757f; }
        .campo input:focus, .campo textarea:focus { outline: none; border-color: var(--tinta); box-shadow: 0 0 0 4px rgba(242,196,0,.45); }
        .campo .error { font-size: 14px; font-weight: 650; color: #a3161d; margin: 6px 0 0; }
        .campo .ayuda { font-size: 13.5px; color: var(--tinta-3); margin: 6px 0 0; }
        .chat { border-radius: var(--r-grande); background: var(--wa-fondo); padding: 16px 14px; }
        .chat .burbuja { font-size: 15px; max-width: 34ch; margin-left: auto; }
        .rec-explica { font-size: 15px; line-height: 1.5; color: var(--tinta-2); margin: 0; }
        .rec-explica strong { color: var(--tinta); }
        .rec-lado { display: grid; gap: 18px; }
        .llega { display: grid; grid-template-columns: 1fr; gap: 16px; margin-top: 56px; }
        .llega-celda { margin: 0; border-radius: var(--r-grande); overflow: hidden; background: #fff; color: var(--tinta); display: flex; flex-direction: column; }
        .llega-celda figcaption { padding: 20px 22px 0; font-size: 16px; line-height: 1.5; color: var(--tinta-2); }
        .llega-celda figcaption strong { display: block; font-size: 19px; color: var(--tinta); margin-bottom: 4px; font-variation-settings: 'wdth' 88; }
        .llega-celda img { display: block; width: 74%; max-width: 290px; margin: 18px auto 0; border-radius: 16px 16px 0 0; box-shadow: 0 -10px 30px -14px rgba(21,24,28,.35); }
        .llega-celda.oscura { background: var(--tinta); color: #fff; padding: 20px; }
        .llega-celda.oscura figcaption { padding: 0 0 14px; color: #cfd5da; }
        .llega-celda.oscura figcaption strong { color: #fff; }
        .llega-celda .lp-video video { aspect-ratio: 4 / 5; }
        .llega-espera { padding: 22px; background: var(--tinta); color: #fff; }
        .llega-espera strong { font-size: 21px; }

        /* 25 · Consulta: el video al centro y lo que pasa alrededor. */
        .con-grid { display: grid; grid-template-columns: 1fr; gap: 28px; align-items: center; }
        .con-grid .lp-video { max-width: 380px; width: 100%; justify-self: center; }
        .nota-c { border-top: 2px solid rgba(255,255,255,.55); padding-top: 14px; max-width: 30ch; }
        .nota-c strong { display: block; font-size: 21px; line-height: 1.2; margin-bottom: 6px; font-variation-settings: 'wdth' 85; }
        .nota-c p { margin: 0; font-size: 16px; line-height: 1.55; color: #ffe1e3; }

        /* 30 · Odontograma: el componente real del sistema, y el presupuesto
           que sale de él. Al pasar una línea, su diente se marca. */
        .odo-grid { display: grid; grid-template-columns: 1fr; gap: 22px; align-items: start; }
        .odo-hoja { background: #fff; color: var(--tinta); border-radius: var(--r-panel); padding: 22px 16px 18px; box-shadow: 0 40px 70px -40px rgba(4,20,60,.7); }
        .odo-hoja .e-ejemplo { display: block; color: var(--tinta-3); opacity: 1; }
        .odo-hoja .odo-diente { border-radius: 10px; transition: transform .45s var(--sale), box-shadow .45s var(--sale), background-color .45s; }
        .odo-hoja .odo-ver-sup .odo-escoge [data-arcada="sup"], .odo-hoja .odo-ver-inf .odo-escoge [data-arcada="inf"] { background: var(--iso-30); border-color: var(--iso-30); color: #fff; }
        .odo-hoja .odo-diente.marcado { transform: translateY(-4px); background: #eaf1ff; box-shadow: 0 0 0 2px var(--iso-30); }
        .ticket { background: #fff; color: var(--tinta); border-radius: var(--r-panel); padding: 24px 22px 20px; box-shadow: 0 40px 70px -40px rgba(4,20,60,.7); }
        .ticket-cab { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; padding-bottom: 14px; border-bottom: 1.5px dashed var(--acero-3); }
        .ticket-cab strong { font-size: 20px; font-variation-settings: 'wdth' 85; }
        .ticket ol { list-style: none; margin: 0; padding: 6px 0; }
        .ticket li { display: grid; grid-template-columns: 42px 1fr auto; gap: 10px; align-items: center; padding: 11px 6px; border-radius: var(--r-chico); transition: background-color .35s; }
        .ticket li.marcado { background: #eaf1ff; }
        .ticket .num { font-size: 13px; font-weight: 800; text-align: center; padding: 4px 0; border-radius: 6px; background: var(--iso-30); color: #fff; font-variant-numeric: tabular-nums; }
        .ticket .que { font-size: 15.5px; line-height: 1.3; }
        .ticket .precio { font-size: 15.5px; font-weight: 750; font-variant-numeric: tabular-nums; }
        .ticket-total { display: flex; justify-content: space-between; align-items: baseline; padding-top: 14px; border-top: 1.5px dashed var(--acero-3); }
        .ticket-total strong { font-size: 30px; font-weight: 850; font-variation-settings: 'wdth' 78; font-variant-numeric: tabular-nums; }
        .ticket-acepta { margin-top: 16px; display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: var(--r-medio); background: #e9f7ef; color: #12663a; font-size: 15px; font-weight: 700; opacity: 0; transform: translateY(8px); transition: opacity .5s var(--sale), transform .5s var(--sale); }
        .ticket-acepta.visto { opacity: 1; transform: none; }
        .ticket-acepta svg { width: 20px; height: 20px; flex-shrink: 0; }
        .odo-pie { display: grid; grid-template-columns: 1fr; gap: 28px; margin-top: 44px; align-items: center; }
        .odo-pie .lp-video { max-width: 340px; }
        .incluido { display: inline-block; font-size: 16px; font-weight: 700; padding: 12px 16px; border-radius: var(--r-medio); background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.35); }

        /* 35 · Mensualidades: la escalera de pagos se llena al bajar. */
        .men-grid { display: grid; grid-template-columns: 1fr; gap: 36px; align-items: center; }
        .plan-pagos { background: #fff; color: var(--tinta); border-radius: var(--r-panel); padding: 24px 20px; box-shadow: 0 40px 70px -40px rgba(3,40,20,.7); }
        .plan-pagos .e-ejemplo { color: var(--tinta-3); opacity: 1; }
        .pp-cab { display: flex; justify-content: space-between; gap: 12px; align-items: baseline; margin: 0 0 18px; }
        .pp-cab strong { font-size: 21px; font-variation-settings: 'wdth' 85; }
        .pp-cab span { font-size: 14px; color: var(--tinta-3); }
        .pp-escalera { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 8px; }
        .pp-escalera li { position: relative; aspect-ratio: 1 / 1.25; border-radius: var(--r-chico); background: var(--acero); border: 1.5px solid var(--acero-3); display: flex; flex-direction: column; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: var(--tinta-3); overflow: hidden; }
        .pp-escalera li i { position: absolute; inset: auto 0 0 0; height: 100%; background: var(--iso-35); transform: scaleY(0); transform-origin: bottom; }
        .pp-escalera li.pagada i { transform: none; }
        .pp-escalera li b, .pp-escalera li small { position: relative; z-index: 1; }
        .pp-escalera li b { font-size: 16px; font-variation-settings: 'wdth' 75; }
        .pp-escalera li small { font-size: 10.5px; }
        .pp-escalera li.pagada { color: #fff; border-color: var(--iso-35); }
        .pp-escalera li.vencida { color: var(--tinta); border: 2px solid var(--tinta); background: repeating-linear-gradient(135deg, #fff 0 6px, var(--acero-2) 6px 12px); }
        .pp-leyenda { display: flex; flex-wrap: wrap; gap: 8px 18px; margin-top: 16px; font-size: 14px; color: var(--tinta-2); }
        .pp-leyenda span { display: inline-flex; align-items: center; gap: 8px; }
        .pp-leyenda i { width: 14px; height: 14px; border-radius: 4px; display: inline-block; }
        .pp-cobrar { margin-top: 18px; display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 14px; border-radius: var(--r-medio); background: var(--acero); }
        .pp-cobrar strong { font-size: 16px; }
        .pp-cobrar span { display: inline-flex; align-items: center; height: 40px; padding: 0 16px; border-radius: var(--r-medio); background: var(--tinta); color: #fff; font-size: 14px; font-weight: 750; }
        .men-lado { display: grid; gap: 24px; }
        .men-lado .lp-video { max-width: 320px; }

        /* 40 · Omar */
        .omar-grid { display: grid; grid-template-columns: 1fr; gap: 36px; align-items: center; }
        .omar-foto { position: relative; margin: 0; border-radius: var(--r-panel); overflow: hidden; aspect-ratio: 750 / 920; max-width: 460px; background: #2a2f35; }
        .omar-foto img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .omar-foto figcaption { position: absolute; left: 0; right: 0; bottom: 0; padding: 60px 22px 20px; background: linear-gradient(0deg, rgba(13,15,18,.92), rgba(13,15,18,0)); font-size: 15px; color: #d8dde2; }
        .omar-foto figcaption strong { display: block; font-size: 22px; color: #fff; font-variation-settings: 'wdth' 85; }
        .omar-tel { display: inline-flex; align-items: center; gap: 10px; margin-top: 18px; font-size: 22px; font-weight: 800; font-variation-settings: 'wdth' 80; color: #fff; text-decoration: none; font-variant-numeric: tabular-nums; }
        .omar-tel:hover { text-decoration: underline; text-underline-offset: 6px; }
        .omar-pasos { list-style: none; margin: 26px 0 0; padding: 0; display: grid; gap: 0; border-top: 1px solid #3a4148; }
        .omar-pasos li { display: grid; grid-template-columns: 1fr; padding: 16px 0; border-bottom: 1px solid #3a4148; font-size: 17px; color: #d8dde2; }
        .omar-pasos strong { color: #fff; }

        /* Precios, preguntas y contacto: de vuelta a la charola de acero. */
        .bloque { padding: 96px 0; }
        .fundador { max-width: 820px; margin: 0 auto 44px; display: grid; grid-template-columns: 1fr; gap: 18px; align-items: center; padding: 26px 22px; border-radius: var(--r-panel); background: #fff; border: 2px solid var(--tinta); }
        .fundador .e-h3 { font-size: 24px; }
        .fundador p { margin: 6px 0 0; font-size: 16px; line-height: 1.55; color: var(--tinta-2); }
        .fundador-lugares { display: flex; gap: 6px; flex-wrap: wrap; margin: 12px 0 2px; }
        .fundador-lugares span { width: 14px; height: 14px; border-radius: 50%; border: 2px solid var(--tinta); }
        .fundador-lugares span.tomado { background: var(--tinta); }
        .anual { max-width: 560px; margin: 0 auto 40px; text-align: center; padding: 16px; border-radius: var(--r-grande); background: #fff; border: 1px solid var(--acero-3); }
        .planes { display: grid; grid-template-columns: 1fr; gap: 16px; max-width: 1160px; margin: 0 auto; }
        .plan { position: relative; display: flex; flex-direction: column; border-radius: var(--r-grande); padding: 26px 22px 22px; background: #fff; border: 1px solid var(--acero-3); transition: transform .35s var(--sale), box-shadow .35s var(--sale); }
        .plan:hover { transform: translateY(-4px); box-shadow: 0 30px 50px -30px rgba(21,24,28,.45); }
        .plan.popular { background: var(--tinta); color: #fff; border-color: var(--tinta); }
        .plan-sello { position: absolute; top: -13px; left: 22px; padding: 5px 12px; border-radius: 999px; background: var(--iso-20); color: var(--tinta); font-size: 12.5px; font-weight: 800; letter-spacing: .02em; }
        .plan h3 { margin: 0; font-size: 22px; font-weight: 800; font-variation-settings: 'wdth' 85; }
        .plan .ideal { font-size: 14px; color: var(--tinta-3); margin-top: 2px; }
        .plan.popular .ideal, .plan.popular .sub, .plan.popular .limites, .plan.popular li, .plan.popular .chico { color: #cfd5da; }
        .plan .precio { margin-top: 18px; font-size: 48px; font-weight: 850; line-height: 1; font-variation-settings: 'wdth' 75; font-variant-numeric: tabular-nums; letter-spacing: -0.02em; }
        .plan .sub { font-size: 14px; color: var(--tinta-3); margin-top: 6px; }
        .plan .anualito { display: inline-block; margin-top: 10px; font-size: 13px; font-weight: 700; padding: 4px 10px; border-radius: var(--r-chico); background: var(--acero); color: var(--tinta-2); }
        .plan.popular .anualito { background: #2a3037; color: #e6e9ec; }
        .plan .limites { font-size: 13.5px; color: var(--tinta-3); margin: 16px 0; padding-bottom: 16px; border-bottom: 1px solid var(--acero-2); line-height: 1.5; }
        .plan.popular .limites { border-bottom-color: #343b43; }
        .plan .lead { font-size: 13px; font-weight: 800; margin-bottom: 10px; }
        .plan ul { list-style: none; margin: 0 0 16px; padding: 0; display: grid; gap: 10px; }
        .plan li { display: grid; grid-template-columns: 18px 1fr; gap: 10px; font-size: 15px; line-height: 1.4; color: var(--tinta-2); }
        .plan li svg { width: 18px; height: 18px; margin-top: 1px; }
        .plan .mas { font: inherit; font-size: 14px; font-weight: 700; background: none; border: 0; padding: 6px 0; margin-bottom: 12px; cursor: pointer; text-decoration: underline; text-underline-offset: 4px; color: inherit; text-align: left; }
        .plan .e-btn { width: 100%; box-sizing: border-box; }
        .plan.popular .e-btn-tinta { background: #fff; color: var(--tinta); }
        .plan .chico { text-align: center; font-size: 13px; color: var(--tinta-3); margin-top: 8px; }

        .faq { max-width: 780px; margin: 0 auto; }
        .faq details { border-bottom: 1px solid var(--acero-3); }
        .faq summary { cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 22px 2px; font-size: 19px; font-weight: 750; font-variation-settings: 'wdth' 90; min-height: 52px; }
        .faq summary::-webkit-details-marker { display: none; }
        .faq summary .mango { width: 16px; height: 34px; border-radius: 4px 4px 5px 5px; flex-shrink: 0; transition: transform .4s var(--sale); }
        .faq summary .mango::after { height: 0; }
        .faq details[open] summary .mango { transform: rotate(90deg); }
        .faq details p { margin: 0 0 22px; font-size: 17px; line-height: 1.65; color: var(--tinta-2); max-width: 64ch; }

        .contacto-grid { display: grid; grid-template-columns: 1fr; gap: 40px; align-items: start; }
        .contacto-datos { list-style: none; margin: 28px 0 0; padding: 0; display: grid; gap: 18px; }
        .contacto-datos li { display: grid; grid-template-columns: 26px 1fr; gap: 12px; font-size: 16px; color: var(--tinta-2); }
        .contacto-datos svg { width: 22px; height: 22px; margin-top: 2px; }
        .contacto-datos strong { display: block; color: var(--tinta); }
        .forma { background: #fff; border-radius: var(--r-panel); padding: 26px 20px; display: grid; gap: 16px; box-shadow: 0 40px 70px -40px rgba(21,24,28,.45); position: relative; }
        .forma .doble { display: grid; grid-template-columns: 1fr; gap: 16px; }
        .forma .mas-datos { font: inherit; font-size: 15px; font-weight: 700; background: none; border: 0; cursor: pointer; text-decoration: underline; text-underline-offset: 4px; padding: 4px 0; text-align: left; color: var(--tinta-2); }
        .forma-ok { background: #fff; border-radius: var(--r-panel); padding: 36px 24px; text-align: center; }

        .pie { background: var(--tinta); color: #b7bfc7; padding: 64px 0 120px; font-size: 15px; }
        .pie-grid { display: grid; grid-template-columns: 1fr; gap: 32px; }
        .pie h4 { margin: 0 0 12px; font-size: 15px; color: #fff; font-weight: 750; }
        .pie ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
        .pie a { color: #b7bfc7; text-decoration: none; }
        .pie a:hover { color: #fff; text-decoration: underline; text-underline-offset: 4px; }
        .pie-ciudades { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px 14px; }
        .pie-fin { margin-top: 44px; padding-top: 24px; border-top: 1px solid #2c3238; display: flex; flex-wrap: wrap; gap: 10px 22px; justify-content: space-between; font-size: 14px; }
        .pie-mangos { display: flex; gap: 10px; margin-top: 18px; }
        .pie-mangos .mango { width: 14px; height: 30px; border-radius: 4px; }
        .pie-mangos .mango::after, .pie-mangos .mango b { display: none; }

        .fijo { position: fixed; left: 0; right: 0; bottom: 0; z-index: 45; padding: 10px 12px 12px; background: rgba(238,241,243,.96); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-top: 1px solid var(--acero-3); transform: translateY(110%); transition: transform .45s var(--sale); }
        .fijo.e-visible { transform: none; }
        .fijo .e-btn { width: 100%; box-sizing: border-box; }
        .fijo p { margin: 6px 0 0; text-align: center; font-size: 13px; color: var(--tinta-3); }

        @@media (max-width: 767px) {
            #df-chat-bubble { bottom: 104px !important; right: 14px !important; width: 56px !important; height: 56px !important; }
        }
        @@media (min-width: 640px) {
            .estuche ol { gap: 12px; }
            .estuche a { gap: 46px; }
            .estuche .mango { width: 36px; height: 79px; }
            .estuche .mango::after { height: 34px; }
            .estuche .mango b { display: block; }
            .estuche .largo { display: inline; }
            .estuche .corto { display: none; }
            .llega { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .forma .doble { grid-template-columns: 1fr 1fr; }
            .pp-escalera { grid-template-columns: repeat(12, minmax(0, 1fr)); }
            .planes { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @@media (min-width: 900px) {
            .e-nav-links { display: flex; }
            .e-menu-btn { display: none; }
            .e-wrap { padding: 0 32px; }
            .ini { padding-top: 112px; }
            .ini-grid { grid-template-columns: 1.2fr .8fr; gap: 40px; }
            .ini-media { min-height: 600px; padding: 0; }
            .ini-foto { inset: 0 -40px 0 70px; }
            .ini .cel { position: absolute; left: 0; bottom: -24px; margin: 0; }
            .riel { top: 50%; left: 18px; right: auto; height: auto; transform: translateY(-50%); }
            .riel ol { flex-direction: column; gap: 8px; justify-content: flex-start; }
            .riel { display: block; height: auto; z-index: 40; }
            .riel a { position: relative; padding: 2px 0; }
            .riel .mango { width: 14px; height: 32px; border-radius: 4px 4px 5px 5px; transform-origin: left center; }
            .riel a:hover .mango, .riel a:focus-visible .mango { transform: scale(1.2) translateX(2px); }
            .riel a.activo .mango { transform: scale(1.4) translateX(4px); }
            .riel .riel-txt { position: absolute; left: 34px; top: 50%; width: auto; height: auto; clip: auto; overflow: visible; transform: translate(-6px, -50%); opacity: 0; white-space: nowrap; font-size: 13px; font-weight: 750; padding: 5px 10px; border-radius: 999px; background: var(--tinta); color: #fff; transition: opacity .25s, transform .35s var(--sale); pointer-events: none; }
            .riel a:hover .riel-txt, .riel a:focus-visible .riel-txt { opacity: 1; transform: translate(0, -50%); }
            .paso { padding: 128px 0; }
            .paso-cabeza { max-width: 820px; }
            .ag-grid { grid-template-columns: 1.15fr .85fr; gap: 72px; }
            .rec-grid { grid-template-columns: 1.1fr .9fr; gap: 48px; }
            .rec-prueba { grid-template-columns: 1fr 1fr; padding: 30px; gap: 26px; align-items: start; }
            .llega { grid-template-columns: 1fr 1fr 1.05fr; grid-template-rows: auto auto; }
            .llega-celda.oscura { grid-row: span 2; }
            .llega-espera { grid-column: span 2; }
            .con-grid { grid-template-columns: 1fr auto 1fr; gap: 48px; }
            .con-grid .nota-c:first-child { justify-self: end; }
            .con-col { display: grid; gap: 40px; }
            .odo-grid { grid-template-columns: 1.55fr 1fr; gap: 24px; }
            .odo-hoja { padding: 26px 22px 20px; }
            .odo-pie { grid-template-columns: auto 1fr; gap: 48px; }
            .men-grid { grid-template-columns: 1.15fr .85fr; gap: 64px; }
            .omar-grid { grid-template-columns: .85fr 1.15fr; gap: 72px; }
            .bloque { padding: 128px 0; }
            .fundador { grid-template-columns: 1fr auto; padding: 30px 32px; }
            .planes { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .plan.popular { transform: translateY(-14px); }
            .plan.popular:hover { transform: translateY(-18px); }
            .contacto-grid { grid-template-columns: 1fr 1fr; gap: 72px; }
            .forma { padding: 32px; }
            .pie-grid { grid-template-columns: 1.2fr 1fr 1.4fr 1fr 1fr; }
            .fijo { display: none; }
        }
        @@media (min-width: 1280px) {
            .riel { left: 26px; }
        }

        .js-mov [data-cel], .js-mov [data-estuche] .mango { opacity: 0; }
        .js-mov .ini-foto { clip-path: inset(12% 8% 12% 8% round 28px); }

        /* Sin movimiento para quien lo pidió: todo se queda en su lugar final. */
        @@media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; }
            .ini-foto img { transform: none; }
        }
    </style>
    @include('partials.analytics')
</head>
<body>
    <a class="saltar" href="#contenido">Saltar al contenido</a>
    @php
        $waOmar = 'https://wa.me/526682493398?text=' . urlencode('Hola Omar, vi la página de DocFacil y quiero ver cómo funcionaría en mi consultorio.');
        $videos = [
            'v4-recordatorios' => ['dur' => '27 s', 'titulo' => 'El recordatorio, de la agenda a su WhatsApp'],
            'v5-sala' => ['dur' => '31 s', 'titulo' => 'La pantalla de la sala de espera'],
            'v1-corto' => ['dur' => '27 s', 'titulo' => 'La receta sin papel'],
            'v2-presupuesto' => ['dur' => '34 s', 'titulo' => 'Del odontograma al presupuesto'],
            'v3-ortodoncia' => ['dur' => '32 s', 'titulo' => 'Mensualidades de brackets'],
        ];
        $pasos = [
            ['agenda', '15', 'Agenda', 'Agenda'],
            ['recordatorio', '20', 'Recordatorio', 'Recordar'],
            ['consulta', '25', 'Consulta y receta', 'Consulta'],
            ['odontograma', '30', 'Presupuesto', 'Presup.'],
            ['mensualidades', '35', 'Mensualidades', 'Pagos'],
            ['omar', '40', 'Empezar con Omar', 'Omar'],
        ];
        $ico = [
            'check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>',
            'wa' => '<path fill="currentColor" stroke="none" d="M12 2.2a9.8 9.8 0 00-8.4 14.8L2.2 21.8l4.9-1.3A9.8 9.8 0 1012 2.2zm0 17.9a8.1 8.1 0 01-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8.1 8.1 0 1112 20.1zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.6 6.6 0 01-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 00-.7.3 3 3 0 00-.9 2.2c0 1.3.9 2.5 1.1 2.7.1.2 1.8 2.8 4.4 3.9 1.6.7 2.3.8 3.1.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3z"/>',
            'flecha' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>',
            'tel' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>',
            'correo' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>',
            'reloj' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'menu' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>',
        ];
        $svg = fn ($n, $w = 2) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $w . '" aria-hidden="true">' . $ico[$n] . '</svg>';

        // Los ejemplos de la página. Son inventados y la página lo dice; el
        // formato es el que usa el sistema.
        $agendaEjemplo = [
            ['09:00', 'Ana Ruiz', 'Limpieza'],
            ['10:30', 'Luis Ortega', 'Resina en el 16'],
            ['12:00', 'Carmen Félix', 'Ajuste de brackets'],
            ['17:00', 'Jorge Valdez', 'Valoración'],
        ];
        $odontoEjemplo = [
            16 => ['condition' => 'decay', 'surfaces' => ['oclusal' => 'decay', 'mesial' => 'decay']],
            26 => ['condition' => 'root_canal'],
            36 => ['condition' => 'extraction'],
            46 => ['condition' => 'decay', 'surfaces' => ['oclusal' => 'decay']],
            11 => ['condition' => 'filling', 'surfaces' => ['vestibular' => 'filling']],
            47 => ['condition' => 'crown'],
        ];
        $presupuestoEjemplo = [
            [16, 'Resina en dos caras', 1200],
            [46, 'Resina oclusal', 850],
            [26, 'Endodoncia molar', 4500],
            [36, 'Extracción', 900],
        ];
        $totalEjemplo = array_sum(array_column($presupuestoEjemplo, 2));
    @endphp

    {{-- Navegación --}}
    <nav class="e-nav" id="navbar" x-data="{ abierto: false }" aria-label="Principal">
        <div class="e-nav-in">
            <a href="/" aria-label="DocFácil, inicio"><img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" width="132" height="44"></a>
            <div class="e-nav-links">
                <a href="#paso-agenda">Cómo le ayuda</a>
                <a href="#pricing">Precios</a>
                <a href="#faq">Preguntas</a>
                <a href="#contacto">Contacto</a>
                <button
                    x-data="{ show: !!window.__docfacilInstallPrompt, installing: false }"
                    x-show="show" x-cloak
                    x-on:docfacil-install-ready.window="show = true"
                    x-on:docfacil-install-done.window="show = false"
                    x-on:click="
                        if (!window.__docfacilInstallPrompt) return;
                        installing = true;
                        window.__docfacilInstallPrompt.prompt();
                        window.__docfacilInstallPrompt.userChoice.finally(() => { installing = false; window.__docfacilInstallPrompt = null; show = false; });
                    "
                    type="button"><span x-text="installing ? 'Instalando…' : 'Instalar app'"></span></button>
                <a href="{{ url('/doctor/login') }}">Iniciar sesión</a>
                <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="navbar" data-track-text="prueba_gratis" class="e-btn e-btn-tinta">Probar 15 días gratis</a>
            </div>
            <button type="button" class="e-menu-btn" x-on:click="abierto = !abierto" :aria-expanded="abierto" aria-controls="mobile-menu" aria-label="Menú">{!! $svg('menu') !!}</button>
        </div>
        <div id="mobile-menu" class="e-menu" x-show="abierto" x-cloak x-transition.opacity x-on:click.outside="abierto = false" x-on:keydown.escape.window="abierto = false">
            <a href="#paso-agenda" x-on:click="abierto = false">Cómo le ayuda</a>
            <a href="#pricing" x-on:click="abierto = false">Precios</a>
            <a href="#faq" x-on:click="abierto = false">Preguntas</a>
            <a href="#contacto" x-on:click="abierto = false">Contacto</a>
            <button
                x-data="{ show: !!window.__docfacilInstallPrompt }"
                x-show="show" x-cloak
                x-on:docfacil-install-ready.window="show = true"
                x-on:docfacil-install-done.window="show = false"
                x-on:click="
                    if (!window.__docfacilInstallPrompt) return;
                    window.__docfacilInstallPrompt.prompt();
                    window.__docfacilInstallPrompt.userChoice.finally(() => { window.__docfacilInstallPrompt = null; show = false; });
                "
                type="button">Instalar como app</button>
            <a href="{{ url('/doctor/login') }}">Iniciar sesión</a>
            <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="navbar_mobile" data-track-text="prueba_gratis" class="e-btn e-btn-tinta">Probar 15 días gratis</a>
        </div>
    </nav>

    {{-- El riel del estuche: dónde va el doctor en el recorrido. --}}
    <nav class="riel" id="riel" aria-label="Pasos del recorrido">
        <ol>
            @foreach ($pasos as [$id, $iso, $nombre])
            <li data-iso="{{ $iso }}"><a href="#paso-{{ $id }}" data-riel="{{ $id }}"><span class="mango" data-iso="{{ $iso }}" aria-hidden="true"><b>{{ $iso }}</b></span><span class="riel-txt">{{ $nombre }}</span></a></li>
            @endforeach
        </ol>
    </nav>

    <main id="contenido">
    {{-- INICIO: la libreta pasada al celular, y la agenda de mañana que se
         va confirmando sola en el celular. --}}
    <section class="ini" id="inicio">
        <div class="e-wrap">
            <div class="ini-grid">
                <div>
                    <h1 class="e-h1 ini-h1">
                        <span class="linea"><span>Su consultorio en papel,</span></span>
                        <span class="linea"><span>pasado al celular.</span></span>
                    </h1>
                    <p class="e-lead" data-entra>Agenda, recordatorios, odontograma, recetas y cobros en un solo lugar. Y yo le ayudo a empezar.</p>
                    <div class="e-ctas" data-entra>
                        <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="hero" data-track-text="probar_15_dias" class="e-btn e-btn-tinta">Probar 15 días gratis</a>
                        <a href="{{ $waOmar }}" target="_blank" rel="noopener" data-track="whatsapp_clicked" data-track-location="hero" class="e-btn e-btn-linea">{!! $svg('wa') !!}Escribirle a Omar</a>
                    </div>
                </div>

                <div class="ini-media" x-data="agendaDeMañana()" x-init="arrancar()">
                    <div class="ini-foto">
                        <img src="{{ asset('images/landing/limas.jpg') }}" alt="Limas de endodoncia con mangos de color sobre una charola de acero" width="1600" height="1067" fetchpriority="high" data-paralaje>
                        <button type="button" class="ini-otra" x-on:click="otraVez()" x-show="terminado" x-cloak>Ver otra vez</button>
                    </div>
                    <div class="cel" data-cel>
                        <div class="cel-pantalla">
                            <div class="cel-isla"></div>
                            <div class="cel-barra"><span>9:41</span><span aria-hidden="true">•••</span></div>
                            <div class="ag-cab"><small>Citas de mañana</small><strong>Jueves</strong><span class="e-ejemplo">Datos de ejemplo</span></div>
                            <ol class="ag-lista">
                                @foreach ($agendaEjemplo as $i => [$hora, $quien, $que])
                                <li class="ag-cita" :data-edo="estados[{{ $i }}]" :class="{ 'ag-activa': activa === {{ $i }} }">
                                    <span class="ag-hora">{{ $hora }}</span>
                                    <span class="ag-quien"><strong>{{ $quien }}</strong><span>{{ $que }}</span></span>
                                    <span class="ag-edo" x-text="etiqueta(estados[{{ $i }}])">Por confirmar</span>
                                </li>
                                @endforeach
                            </ol>
                            <div class="ag-wa" :class="{ abierto: whatsapp }" aria-live="polite">
                                <div class="ag-wa-de">{!! $svg('wa') !!}<span x-text="'Su WhatsApp · ' + (activa !== null ? nombres[activa] : '')"></span></div>
                                <div class="burbuja"><span x-text="texto"></span><span class="cursor" x-show="escribiendo"></span><span class="hora">9:41</span></div>
                                <div class="ag-wa-enviar"><span :class="{ toque: tocando }">{!! $svg('flecha', 2.4) !!}Enviar</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="estuche" aria-label="El recorrido">
                <ol>
                    @foreach ($pasos as [$id, $iso, $nombre, $corto])
                    <li><a href="#paso-{{ $id }}" data-estuche><span class="mango foto" data-iso="{{ $iso }}" aria-hidden="true"><b>{{ $iso }}</b></span><span><span class="largo">{{ $nombre }}</span><span class="corto" aria-hidden="true">{{ $corto }}</span></span></a></li>
                    @endforeach
                </ol>
            </nav>
        </div>
    </section>

    {{-- 15 · AGENDA --}}
    <section class="paso" data-paso="agenda" id="paso-agenda">
        <div class="paso-campo" data-campo></div>
        <div class="e-wrap ag-grid">
            <div class="libreta">
                <img class="libreta-foto" src="{{ asset('images/landing/hero-libreta.jpg') }}" alt="Libreta de citas de papel junto a un celular en la recepción de un consultorio" width="1200" height="900" loading="lazy">
                <img class="libreta-tel" src="{{ asset('images/landing/agenda-celular.jpg') }}" alt="La agenda de DocFácil en el celular: cada cita con su estado" width="480" height="708" loading="lazy" data-cruza>
            </div>
            <div>
                <div class="paso-cabeza">
                    <span class="mango foto" data-iso="15" aria-hidden="true"><b>15</b></span>
                    <div>
                        <h2 class="e-h2">Su libreta, pero que le avisa.</h2>
                        <p class="e-p">Cada cita con su estado: por confirmar, confirmó, llegó, atendida. En el celular, la tablet o la computadora de recepción.</p>
                    </div>
                </div>
                <ul class="puntos">
                    <li>{!! $svg('check', 2.4) !!}<span>Me manda su Excel por WhatsApp y le dejo cargados sus pacientes, sin costo.</span></li>
                    <li>{!! $svg('check', 2.4) !!}<span>Si los tiene en la libreta, le ayudo a armar la lista con los datos que importan.</span></li>
                    <li>{!! $svg('check', 2.4) !!}<span>Se instala como app en iPhone o Android, sin pasar por la tienda.</span></li>
                </ul>
            </div>
        </div>
    </section>

    {{-- 20 · RECORDATORIO: el visitante se manda el recordatorio a su propio
         WhatsApp. El texto es el de RecordatorioDeCita; la liga, la de la cita
         real, aquí solo se nombra. --}}
    <section class="paso" data-paso="recordatorio" id="paso-recordatorio">
        <div class="paso-campo" data-campo></div>
        <div class="e-wrap">
            <div class="paso-cabeza">
                <span class="mango foto" data-iso="20" aria-hidden="true"><b>20</b></span>
                <div>
                    <h2 class="e-h2">El recordatorio sale de su WhatsApp.</h2>
                    <p class="e-p">Un clic en la cita abre su WhatsApp con el mensaje ya escrito, y usted da enviar. Sin costo por mensaje. El paciente confirma con un toque y su agenda cambia sola.</p>
                </div>
            </div>
            <div class="rec-grid">
                <div class="rec-prueba" x-data="pruebaRecordatorio()">
                    <div class="rec-campos">
                        <h3 class="e-h3">Pruébelo con su número</h3>
                        <div class="campo">
                            <label for="rp-nombre">Nombre del paciente</label>
                            <input id="rp-nombre" type="text" x-model="nombre" maxlength="30" autocomplete="off">
                        </div>
                        <div class="campo">
                            <label for="rp-consultorio">Su consultorio</label>
                            <input id="rp-consultorio" type="text" x-model="consultorio" maxlength="60" autocomplete="organization">
                        </div>
                        <div class="campo">
                            <label for="rp-tel">Su WhatsApp, 10 dígitos</label>
                            <input id="rp-tel" type="tel" inputmode="numeric" x-model="telefono" placeholder="668 123 4567" autocomplete="tel-national" :aria-invalid="error ? 'true' : 'false'" aria-describedby="rp-tel-ayuda rp-tel-error">
                            <p class="ayuda" id="rp-tel-ayuda">El mensaje se abre en su WhatsApp para que vea cómo le llega a su paciente. No se guarda su número.</p>
                            <p class="error" id="rp-tel-error" x-show="error" x-text="error" x-cloak></p>
                        </div>
                        <a :href="liga" target="_blank" rel="noopener" class="e-btn e-btn-wa" x-on:click="abrir($event)" data-track="reminder_demo_opened" data-track-location="recordatorio">{!! $svg('wa') !!}Abrirlo en mi WhatsApp</a>
                    </div>
                    <div>
                        <div class="chat" aria-live="polite">
                            <div class="burbuja"><span x-text="mensaje">Hola Ana, le recordamos su cita en su consultorio jueves a las 10:30.

Confirme o cancele aquí:
(la liga de su cita)

¡Le esperamos!</span><span class="hora">9:41</span></div>
                        </div>
                        <p class="rec-explica" style="margin-top:14px;"><strong>Así le llega a su paciente.</strong> En su cuenta, la liga abre una página donde confirma o cancela, y la cita cambia de estado en su agenda.</p>
                    </div>
                </div>
                <div class="rec-lado">
                    @include('partials.landing-video', ['id' => 'v4-recordatorios', 'v' => $videos['v4-recordatorios'], 'poster' => 'videos/v4-recordatorios-cuadro.jpg'])
                </div>
            </div>

            <div class="llega">
                <figure class="llega-celda">
                    <figcaption><strong>Confirma desde su celular.</strong>Ve su cita y elige: confirmar o cancelar.</figcaption>
                    <img src="{{ asset('images/landing/paciente-confirma.jpg') }}" alt="Pantalla donde el paciente confirma o cancela su cita" width="560" height="1005" loading="lazy">
                </figure>
                <figure class="llega-celda">
                    <figcaption><strong>Llega y escanea el QR.</strong>A usted le avisa y su cita queda como “llegó”.</figcaption>
                    <img src="{{ asset('images/landing/check-in-qr.jpg') }}" alt="Registro con QR en la sala de espera" width="560" height="790" loading="lazy">
                </figure>
                <figure class="llega-celda oscura">
                    <figcaption><strong>La pantalla de la sala.</strong>Quién está en consulta y quién sigue, con nombre e inicial. Nunca dice a qué viene.</figcaption>
                    @include('partials.landing-video', ['id' => 'v5-sala', 'v' => $videos['v5-sala'], 'poster' => 'videos/v5-sala-cuadro.jpg'])
                </figure>
                <div class="llega-celda llega-espera">
                    <strong>¿Alguien canceló?</strong>
                    <p class="e-p" style="color:#cfd5da;margin:8px 0 0;">La lista de espera le dice quién quería ese día. “Ofrecer a…” abre su WhatsApp con el mensaje y deja apartado el hueco. Plan Pro.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 25 · CONSULTA --}}
    <section class="paso" data-paso="consulta" id="paso-consulta">
        <div class="paso-campo" data-campo></div>
        <div class="e-wrap">
            <div class="paso-cabeza">
                <span class="mango foto" data-iso="25" aria-hidden="true"><b>25</b></span>
                <div>
                    <h2 class="e-h2">Una consulta completa, sin papeles.</h2>
                    <p class="e-p">Al iniciar la consulta ya viene lo que se va a hacer y en qué diente. La receta sale con su cédula y el cobro ya trae el tratamiento.</p>
                </div>
            </div>
            <div class="con-grid">
                <div class="con-col">
                    <div class="nota-c"><strong>Le avisa antes de recetar</strong><p>Si el paciente es alérgico a lo que va a recetar, se lo dice en ese momento.</p></div>
                    <div class="nota-c"><strong>Receta PDF con su cédula</strong><p>Lista para imprimir o enseñar, sin escribirla a mano.</p></div>
                </div>
                @include('partials.landing-video', ['id' => 'v1-corto', 'v' => $videos['v1-corto'], 'poster' => 'videos/v1-corto-cuadro.jpg'])
                <div class="con-col">
                    <div class="nota-c"><strong>Le sugiere la siguiente cita</strong><p>Al cerrar la consulta, ya sabe cuándo debe volver el paciente.</p></div>
                    <div class="nota-c"><strong>Atienda al que sigue</strong><p>El botón le lleva al siguiente paciente del día, primero al que ya llegó.</p></div>
                </div>
            </div>
        </div>
    </section>

    {{-- 30 · ODONTOGRAMA: el componente de arcadas del sistema, con un
         ejemplo, y el presupuesto que sale de él. --}}
    <section class="paso" data-paso="odontograma" id="paso-odontograma">
        <div class="paso-campo" data-campo></div>
        <div class="e-wrap">
            <div class="paso-cabeza">
                <span class="mango foto" data-iso="30" aria-hidden="true"><b>30</b></span>
                <div>
                    <h2 class="e-h2">Su odontograma ya es su presupuesto.</h2>
                    <p class="e-p">Marca lo que encuentra, cara por cara, y con un clic sale el presupuesto con sus precios. El paciente lo acepta desde su celular y lo aceptado se agenda en un clic.</p>
                </div>
            </div>
            <div class="odo-grid" data-odonto>
                <div class="odo-hoja">
                    <x-odontograma.arcadas :dientes="$odontoEjemplo" />
                    <span class="e-ejemplo" style="margin-top:14px;text-align:right;">Datos de ejemplo · el odontograma de DocFácil</span>
                </div>
                <div class="ticket">
                    <div class="ticket-cab"><strong>Presupuesto</strong><span class="e-ejemplo">Datos de ejemplo</span></div>
                    <ol>
                        @foreach ($presupuestoEjemplo as [$diente, $que, $precio])
                        <li data-linea="{{ $diente }}"><span class="num">{{ $diente }}</span><span class="que">{{ $que }}</span><span class="precio">${{ number_format($precio) }}</span></li>
                        @endforeach
                    </ol>
                    <div class="ticket-total"><span class="e-nota">Total</span><strong>${{ number_format($totalEjemplo) }}</strong></div>
                    <div class="ticket-acepta" data-acepta><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">{!! $ico['check'] !!}</svg>El paciente lo aceptó desde su celular</div>
                </div>
            </div>
            <div class="odo-pie">
                @include('partials.landing-video', ['id' => 'v2-presupuesto', 'v' => $videos['v2-presupuesto'], 'poster' => 'videos/v2-presupuesto-cuadro.jpg'])
                <div>
                    <p class="incluido">Incluido en todos los planes de pago, desde el Básico.</p>
                    <p class="e-p" style="margin-top:18px;">Numeración FDI, dentición permanente, mixta y temporal, y se imprime para el expediente.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 35 · MENSUALIDADES --}}
    <section class="paso" data-paso="mensualidades" id="paso-mensualidades">
        <div class="paso-campo" data-campo></div>
        <div class="e-wrap">
            <div class="paso-cabeza">
                <span class="mango foto" data-iso="35" aria-hidden="true"><b>35</b></span>
                <div>
                    <h2 class="e-h2">Sepa quién va al día y quién le debe.</h2>
                    <p class="e-p">Pone el total, el enganche y las mensualidades. Cuando el paciente viene a su ajuste, la mensualidad aparece en la consulta y queda en su corte del día.</p>
                </div>
            </div>
            <div class="men-grid">
                <div class="plan-pagos" data-escalera>
                    <div class="pp-cab"><strong>Brackets de Carmen Félix</strong><span>12 mensualidades de $800</span></div>
                    <ol class="pp-escalera">
                        @for ($m = 1; $m <= 12; $m++)
                        <li class="{{ $m <= 6 ? 'pagada' : ($m === 7 ? 'vencida' : '') }}" data-mes="{{ $m }}"><i></i><b>{{ $m }}</b></li>
                        @endfor
                    </ol>
                    <div class="pp-leyenda">
                        <span><i style="background:var(--iso-35);"></i>Pagada</span>
                        <span><i style="border:2px solid var(--tinta);background:repeating-linear-gradient(135deg,#fff 0 3px,var(--acero-2) 3px 6px);"></i>Vencida</span>
                        <span><i style="background:var(--acero);border:1.5px solid var(--acero-3);"></i>Por venir</span>
                    </div>
                    <div class="pp-cobrar"><strong>Mensualidad 7 vencida: $800</strong><span>Cobrar</span></div>
                    <span class="e-ejemplo" style="display:block;margin-top:12px;text-align:right;">Datos de ejemplo</span>
                </div>
                <div class="men-lado">
                    @include('partials.landing-video', ['id' => 'v3-ortodoncia', 'v' => $videos['v3-ortodoncia'], 'poster' => 'videos/v3-ortodoncia-cuadro.jpg'])
                </div>
            </div>
        </div>
    </section>

    {{-- 40 · OMAR --}}
    <section class="paso" data-paso="omar" id="paso-omar">
        <div class="paso-campo" data-campo></div>
        <div class="e-wrap omar-grid">
            <figure class="omar-foto">
                <img src="{{ asset('images/landing/omar.jpg') }}" alt="Omar Lerma, fundador de DocFácil" width="750" height="920" loading="lazy" decoding="async">
                <figcaption><strong>Omar Lerma</strong>Fundador de DocFácil. Los Mochis, Sinaloa.</figcaption>
            </figure>
            <div>
                <div class="paso-cabeza" style="margin-bottom:0;">
                    <span class="mango foto" data-iso="40" aria-hidden="true"><b>40</b></span>
                    <div>
                        <h2 class="e-h2">Usted no captura nada solo: yo le ayudo a empezar.</h2>
                        <p class="e-p">Me manda su Excel por WhatsApp y le dejo cargados sus pacientes; si los tiene en la libreta, le ayudo a armar la lista. Cualquier duda, le contesto yo.</p>
                    </div>
                </div>
                <ul class="omar-pasos">
                    <li><span><strong>Hoy:</strong> crea su cuenta en dos minutos, sin tarjeta.</span></li>
                    <li><span><strong>Esta semana:</strong> le cargo sus pacientes y le enseño su agenda por WhatsApp.</span></li>
                    <li><span><strong>Las primeras semanas:</strong> le acompaño, sin costo extra.</span></li>
                </ul>
                <div class="e-ctas">
                    <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="founder_section" data-track-text="probar_15_dias" class="e-btn e-btn-blanco">Probar 15 días gratis</a>
                    <a href="{{ $waOmar }}" target="_blank" rel="noopener" data-track="whatsapp_clicked" data-track-location="founder_section" class="e-btn e-btn-wa">{!! $svg('wa') !!}Escribirle a Omar</a>
                </div>
                <a class="omar-tel" href="tel:+526682493398">{!! str_replace('<svg', '<svg style="width:22px;height:22px"', $svg('tel', 1.8)) !!}668 249 3398</a>
            </div>
        </div>
    </section>

    {{-- PRECIOS --}}
    <section id="pricing" class="bloque">
        <div class="e-wrap">
            <div style="max-width:780px;margin:0 auto 48px;text-align:center;">
                <h2 class="e-h2">Con una consulta al mes, el plan Básico ya se pagó.</h2>
                <p class="e-p" style="margin:0 auto;color:var(--tinta-2);">Pruebe 15 días con todo, sin tarjeta. Si después paga y en los primeros 30 días no le sirve, le devolvemos su dinero (<a href="/terminos#garantia" style="text-decoration:underline;text-underline-offset:4px;font-weight:700;">Garantía de 30 días</a>).</p>
            </div>

            {{-- Programa Fundador. El anual a 10 meses no es promoción: es el
                 precio de siempre, sin reloj. Lo escaso son los lugares, y el
                 número sale de contar fundadores reales. --}}
            @php
                $fundador = \App\Models\Clinic::lugaresDeFundador();
                $mesesGratis = (int) config('founders.free_months', 6);
                $precioFundador = (float) config('founders.monthly_price', 499);
                $precioPro = (float) \App\Models\Commission::monthlyPriceForPlan('profesional');
                $waFundador = 'https://wa.me/526682493398?text=' . urlencode(
                    'Hola Omar, vi lo del programa Fundador de DocFacil y quiero un lugar. Soy dentista y me interesa usarlo en mi consultorio.'
                );
            @endphp

            @if ($fundador['hay'])
            <div class="fundador">
                <div>
                    <h3 class="e-h3">Programa Fundador</h3>
                    <p style="margin-top:2px;font-size:18px;font-weight:750;color:var(--tinta);">
                        @if ($fundador['tomados'] > 0)
                            Quedan {{ $fundador['quedan'] }} de {{ $fundador['total'] }} lugares
                        @else
                            Busco los primeros {{ $fundador['total'] }} consultorios
                        @endif
                    </p>
                    {{-- Los lugares, a la vista. Solo cuando ya hay alguien
                         dentro: diez círculos vacíos no dicen nada bueno. --}}
                    @if ($fundador['tomados'] > 0)
                    <div class="fundador-lugares" aria-hidden="true">
                        @for ($i = 0; $i < $fundador['total']; $i++)
                            <span class="{{ $i < $fundador['tomados'] ? 'tomado' : '' }}"></span>
                        @endfor
                    </div>
                    @endif
                    <p><strong>{{ $mesesGratis }} meses sin costo</strong> y después <strong>${{ number_format($precioFundador) }}/mes de por vida</strong>, congelado: la mitad de los ${{ number_format($precioPro) }} del plan Pro.</p>
                    <p>A cambio le pido dos cosas: que lo use en serio y que me diga la verdad, aunque la verdad sea que no le sirve.</p>
                </div>
                <a href="{{ $waFundador }}" target="_blank" rel="noopener" data-track="founder_seat_clicked" class="e-btn e-btn-tinta">Quiero un lugar</a>
            </div>
            @else
            <div class="anual">
                <strong>Paga anual y ahorra 2 meses</strong>
                <div class="e-nota" style="color:var(--tinta-3);margin-top:4px;">El año le sale en 10 meses.</div>
            </div>
            @endif

            <div class="planes">
                @php
                // Lo que trae cada plan sale de un solo lugar: lo mismo dicen el
                // folleto, el brief, la propuesta y la página de planes del panel.
                $plans = \App\Support\LoQueTraeCadaPlan::planes();
                @endphp
                @foreach($plans as $i => $plan)
                @php
                    $visible = array_slice($plan['features'], 0, 4);
                    $hidden = array_slice($plan['features'], 4);
                    // El plan Clinica no se contrata solo: se cotiza por
                    // WhatsApp porque depende de cuantos doctores son.
                    $planUrl = ($plan['cta'] ?? '') === 'Contactar ventas'
                        ? 'https://wa.me/526682493398?text=' . urlencode('Hola Omar, me interesa el plan Clinica de DocFacil para mi consultorio')
                        : url('/doctor/register');
                @endphp
                <div x-data="{ expanded: false }" class="plan {{ $plan['popular'] ? 'popular' : '' }}">
                    @if($plan['popular'])
                    <span class="plan-sello">El que le recomiendo</span>
                    @endif
                    <h3>{{ $plan['name'] }}</h3>
                    <div class="ideal">{{ $plan['ideal'] }}</div>
                    <div class="precio">${{ $plan['price'] }}</div>
                    <div class="sub">{{ $plan['subtitle'] }}</div>
                    @if($plan['annual'] > 0)
                    <div><span class="anualito">o ${{ number_format($plan['annual']) }}/año, 2 meses gratis</span></div>
                    @else
                    <div class="sub">sin tarjeta, sin compromiso</div>
                    @endif
                    <div class="limites">{{ $plan['limits'] }}</div>
                    @if($plan['lead'])
                    <div class="lead">{{ $plan['lead'] }}</div>
                    @endif
                    <ul>
                        @foreach($visible as $feature)
                        <li>{!! $svg('check', 2.6) !!}<span>{{ $feature }}</span></li>
                        @endforeach
                        @foreach($hidden as $feature)
                        <li x-show="expanded" x-collapse x-cloak>{!! $svg('check', 2.6) !!}<span>{{ $feature }}</span></li>
                        @endforeach
                    </ul>
                    <div style="margin-top:auto;">
                        @if(count($hidden) > 0)
                        <button type="button" class="mas" x-on:click="expanded = !expanded" :aria-expanded="expanded">
                            <span x-show="!expanded">Ver {{ count($hidden) }} {{ count($hidden) === 1 ? 'función' : 'funciones' }} más</span>
                            <span x-show="expanded" x-cloak>Ver menos</span>
                        </button>
                        @endif
                        <a href="{{ $planUrl }}"
                            @if(str_starts_with($planUrl, 'https://wa.me')) target="_blank" rel="noopener" @endif
                            data-track="pricing_tier_clicked"
                            data-track-tier="{{ $plan['slug'] ?? strtolower($plan['name']) }}"
                            data-track-cycle="{{ $plan['popular'] ? 'pro' : ($plan['name'] ?? 'unknown') }}"
                            class="e-btn {{ $plan['popular'] ? 'e-btn-tinta' : 'e-btn-linea' }}">{{ $plan['cta'] }}</a>
                        @if($plan['annual'] > 0 && $plan['cta'] !== 'Contactar ventas')
                        <div class="chico">15 días con todo, sin tarjeta</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Preguntas: las mismas del JSON-LD de arriba. <details> nativo: abre
         sin JavaScript y se toca fácil en el celular. --}}
    <section id="faq" class="bloque" style="padding-top:0;">
        <div class="e-wrap">
            <h2 class="e-h2" style="text-align:center;margin-bottom:28px;">Lo que más me preguntan</h2>
            <div class="faq">
                @foreach ($landingFaqs as $i => $faq)
                <details>
                    <summary>{{ $faq['q'] }}<span class="mango" data-iso="15" aria-hidden="true"></span></summary>
                    <p>{{ $faq['a'] }}</p>
                </details>
                @endforeach
            </div>
        </div>
    </section>

    <section id="contacto" class="bloque" style="padding-top:0;">
        <div class="e-wrap contacto-grid">
            <div>
                <h2 class="e-h2">¿Le mando un video o le enseño su agenda ya cargada?</h2>
                <p class="e-p" style="color:var(--tinta-2);">Escríbame por WhatsApp o déjeme sus datos y le contesto yo, Omar, en menos de 24 horas. Sin compromiso.</p>
                <ul class="contacto-datos">
                    <li>{!! $svg('tel', 1.8) !!}<span><strong>WhatsApp / Teléfono</strong><a href="https://wa.me/526682493398" target="_blank" rel="noopener" data-track="whatsapp_clicked" data-track-location="contact_section" style="text-decoration:underline;text-underline-offset:4px;">668 249 3398</a></span></li>
                    <li>{!! $svg('correo', 1.8) !!}<span><strong>Correo</strong>contacto@docfacil.com</span></li>
                    <li>{!! $svg('reloj', 1.8) !!}<span><strong>Horario de atención</strong>Lunes a viernes, 9:00 a 18:00</span></li>
                </ul>
            </div>

            <div>
                @if(session('contact_success'))
                <div class="forma-ok">
                    <h3 class="e-h3">Mensaje enviado</h3>
                    <p class="e-p" style="margin:8px auto 0;color:var(--tinta-2);">Gracias. Le escribo pronto.</p>
                </div>
                @else
                {{-- Pre-fill via query params (los manda TrackController al
                     hacer click en correos del pipeline). --}}
                <form x-data="{ showMore: false }" action="{{ route('contact.store') }}" method="POST"
                    onsubmit="window.trackEvent && window.trackEvent('form_submitted', { form_type: 'contact' })"
                    class="forma">
                    @csrf
                    {{-- Honeypot anti-bot --}}
                    <div style="position:absolute;left:-9999px" aria-hidden="true">
                        <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                    </div>
                    <input type="hidden" name="form_rendered_at" value="{{ time() }}">
                    <div class="campo">
                        <label for="c-name">Nombre</label>
                        <input id="c-name" type="text" name="name" required value="{{ old('name', request()->query('name')) }}" placeholder="Dra. Laura Medina" autocomplete="name">
                        @error('name') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="campo">
                        <label for="c-email">Correo</label>
                        <input id="c-email" type="email" name="email" required value="{{ old('email', request()->query('email')) }}" placeholder="doctora@correo.com" autocomplete="email" spellcheck="false">
                        @error('email') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="campo">
                        <label for="c-phone">Teléfono / WhatsApp</label>
                        <input id="c-phone" type="tel" name="phone" value="{{ old('phone', request()->query('phone')) }}" placeholder="668 123 4567" autocomplete="tel">
                    </div>

                    <button type="button" class="mas-datos" x-show="!showMore" x-on:click="showMore = true">Agregar datos del consultorio (opcional)</button>

                    <div x-show="showMore" x-cloak x-collapse style="display:grid;gap:16px;">
                        <div class="doble">
                            <div class="campo">
                                <label for="c-city">Ciudad</label>
                                <input id="c-city" type="text" name="city" value="{{ old('city', request()->query('city')) }}" placeholder="Los Mochis">
                            </div>
                            <div class="campo">
                                <label for="c-specialty">Especialidad</label>
                                <input id="c-specialty" type="text" name="specialty" value="{{ old('specialty', request()->query('specialty')) }}" placeholder="Ortodoncia">
                            </div>
                        </div>
                        <div class="campo">
                            <label for="c-clinic">Nombre del consultorio</label>
                            <input id="c-clinic" type="text" name="clinic_name" value="{{ old('clinic_name', request()->query('clinic_name')) }}" placeholder="Consultorio Dental Medina">
                        </div>
                        <div class="campo">
                            <label for="c-message">Mensaje</label>
                            <textarea id="c-message" name="message" rows="3" placeholder="Cuénteme qué necesita o pregúnteme lo que quiera…">{{ old('message') }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="e-btn e-btn-tinta" style="width:100%;">Hablar con Omar</button>
                    <p class="e-nota" style="text-align:center;color:var(--tinta-3);margin:0;">Le contesto en menos de 24 horas.</p>
                </form>
                @endif
            </div>
        </div>
    </section>
    </main>

    <footer class="pie">
        <div class="e-wrap">
            <div class="pie-grid">
                <div>
                    <img src="{{ asset('images/logo_doc_facil_white.png') }}" alt="DocFácil" width="120" height="40" style="height:40px;width:auto;" loading="lazy" decoding="async">
                    <p style="margin:14px 0 0;">Software para consultorios dentales. Hecho en México.</p>
                    <div class="pie-mangos" aria-hidden="true">
                        @foreach ($pasos as [$id, $iso])<span class="mango" data-iso="{{ $iso }}"></span>@endforeach
                    </div>
                </div>
                <div>
                    <h4>Producto</h4>
                    <ul>
                        <li><a href="#paso-agenda">Cómo le ayuda</a></li>
                        <li><a href="#pricing">Precios</a></li>
                        <li><a href="{{ route('brochure.web') }}">Brochure</a></li>
                        <li><a href="{{ route('brochure.pdf') }}">Descargar PDF</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Ciudades</h4>
                    @php
                        $cityLinks = [
                            ['slug' => 'cdmx', 'name' => 'CDMX'], ['slug' => 'guadalajara', 'name' => 'Guadalajara'],
                            ['slug' => 'monterrey', 'name' => 'Monterrey'], ['slug' => 'merida', 'name' => 'Mérida'],
                            ['slug' => 'culiacan', 'name' => 'Culiacán'], ['slug' => 'queretaro', 'name' => 'Querétaro'],
                            ['slug' => 'tijuana', 'name' => 'Tijuana'], ['slug' => 'cancun', 'name' => 'Cancún'],
                            ['slug' => 'leon', 'name' => 'León'], ['slug' => 'puebla', 'name' => 'Puebla'],
                            ['slug' => 'hermosillo', 'name' => 'Hermosillo'], ['slug' => 'ciudad-obregon', 'name' => 'Cd. Obregón'],
                            ['slug' => 'ciudad-juarez', 'name' => 'Cd. Juárez'], ['slug' => 'saltillo', 'name' => 'Saltillo'],
                            ['slug' => 'torreon', 'name' => 'Torreón'], ['slug' => 'mazatlan', 'name' => 'Mazatlán'],
                            ['slug' => 'los-mochis', 'name' => 'Los Mochis'], ['slug' => 'aguascalientes', 'name' => 'Aguascalientes'],
                            ['slug' => 'cuernavaca', 'name' => 'Cuernavaca'], ['slug' => 'metepec', 'name' => 'Metepec'],
                            ['slug' => 'toluca', 'name' => 'Toluca'], ['slug' => 'morelia', 'name' => 'Morelia'],
                            ['slug' => 'chihuahua', 'name' => 'Chihuahua'], ['slug' => 'san-luis-potosi', 'name' => 'SLP'],
                            ['slug' => 'boca-del-rio', 'name' => 'Boca del Río'], ['slug' => 'playa-del-carmen', 'name' => 'Playa del Carmen'],
                            ['slug' => 'zapopan', 'name' => 'Zapopan'], ['slug' => 'san-pedro-garza-garcia', 'name' => 'San Pedro G.G.'],
                        ];
                    @endphp
                    <div class="pie-ciudades">
                        @foreach ($cityLinks as $c)
                            <a href="/software-dental/{{ $c['slug'] }}">{{ $c['name'] }}</a>
                        @endforeach
                    </div>
                </div>
                <div>
                    {{-- Las comparativas son las de mayor intención de compra
                         (quien busca "alternativas a X" ya se quiere cambiar). --}}
                    @php
                        $competidores = [
                            ['slug' => 'dentalink', 'name' => 'Dentalink'],
                            ['slug' => 'doctorum', 'name' => 'Doctorum'],
                        ];
                    @endphp
                    <h4>Comparativas</h4>
                    <ul>
                        @foreach ($competidores as $comp)
                            <li><a href="/vs/{{ $comp['slug'] }}">DocFácil vs {{ $comp['name'] }}</a></li>
                        @endforeach
                        @foreach ($competidores as $comp)
                            <li><a href="/alternativas-a-{{ $comp['slug'] }}">Alternativas a {{ $comp['name'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4>Acceso</h4>
                    <ul>
                        <li><a href="/doctor/login">Panel Doctor</a></li>
                        <li><a href="/paciente/login">Portal Paciente</a></li>
                        <li><a href="/admin/login">Administración</a></li>
                    </ul>
                </div>
            </div>
            <div class="pie-fin">
                <div style="display:flex;flex-wrap:wrap;gap:8px 20px;">
                    <a href="/blog">Blog</a>
                    <a href="/privacidad">Aviso de Privacidad</a>
                    <a href="/terminos">Términos y Condiciones</a>
                </div>
                @php
                    // Última actualización del archivo del template (señal de freshness para AI/SEO).
                    $manifestPath = public_path('build/manifest.json');
                    $sourceFile = is_file($manifestPath) ? $manifestPath : __FILE__;
                    $lastUpdated = \Carbon\Carbon::createFromTimestamp(filemtime($sourceFile))->translatedFormat('j \d\e F \d\e Y');
                @endphp
                <span>&copy; {{ date('Y') }} DocFácil. Última actualización: {{ $lastUpdated }}</span>
            </div>
        </div>
    </footer>

    {{-- Botón fijo en el celular, aparece después del inicio. --}}
    <div id="sticky-cta" class="fijo">
        <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="sticky_mobile" data-track-text="probar_15_dias" class="e-btn e-btn-tinta">Probar 15 días gratis</a>
        <p>Sin tarjeta. Garantía de 30 días.</p>
    </div>

<script>
// PWA
if ('serviceWorker' in navigator) { navigator.serviceWorker.register('/sw.js'); }

const sinMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// La agenda de mañana en el celular del inicio: cada cita se recuerda por
// WhatsApp (se escribe el mensaje, se da enviar) y el paciente confirma.
// Corre una vez; "Ver otra vez" la repite.
function agendaDeMañana() {
    return {
        nombres: @json(array_column($agendaEjemplo, 1)),
        horas: @json(array_column($agendaEjemplo, 0)),
        estados: ['pendiente', 'pendiente', 'pendiente', 'pendiente'],
        activa: null, whatsapp: false, texto: '', escribiendo: false, tocando: false, terminado: false, corrida: 0,
        etiqueta(e) { return { pendiente: 'Por confirmar', enviado: 'Recordado', confirmo: 'Confirmó' }[e]; },
        mensaje(i) {
            const nombre = this.nombres[i].split(' ')[0];
            return 'Hola ' + nombre + ', le recordamos su cita en Consultorio Dental Mochis jueves a las ' + this.horas[i] + '.\n\nConfirme o cancele aquí:\n(la liga de su cita)\n\n¡Le esperamos!';
        },
        espera(ms) { const c = this.corrida; return new Promise((ok, no) => setTimeout(() => c === this.corrida ? ok() : no(), ms)); },
        async arrancar() {
            if (sinMovimiento) { this.estados = ['confirmo', 'confirmo', 'enviado', 'pendiente']; this.terminado = true; return; }
            const visto = await new Promise(ok => new IntersectionObserver(([e], o) => { if (e.isIntersecting) { o.disconnect(); ok(); } }, { threshold: .4 }).observe(this.$el));
            this.correr();
        },
        async correr() {
            this.corrida++; this.terminado = false;
            this.estados = ['pendiente', 'pendiente', 'pendiente', 'pendiente'];
            try {
                await this.espera(900);
                for (let i = 0; i < 3; i++) {
                    this.activa = i; await this.espera(450);
                    this.whatsapp = true; this.texto = ''; this.escribiendo = true;
                    const completo = this.mensaje(i);
                    const paso = i === 0 ? 2 : 6;
                    for (let k = 0; k <= completo.length; k += paso) { this.texto = completo.slice(0, k); await this.espera(i === 0 ? 22 : 12); }
                    this.texto = completo; this.escribiendo = false;
                    await this.espera(i === 0 ? 700 : 350);
                    this.tocando = true; await this.espera(160); this.tocando = false;
                    this.whatsapp = false; this.estados[i] = 'enviado';
                    await this.espera(650);
                }
                this.activa = null;
                for (let i = 0; i < 2; i++) { await this.espera(700); this.estados[i] = 'confirmo'; }
                this.terminado = true;
            } catch (e) { /* se reinició */ }
        },
        otraVez() { this.whatsapp = false; this.activa = null; this.correr(); },
    };
}

// El recordatorio de prueba: el mismo texto que arma RecordatorioDeCita.
function pruebaRecordatorio() {
    return {
        nombre: 'Ana', consultorio: '', telefono: '', error: '',
        get mensaje() {
            const n = (this.nombre || '').trim() || 'Hola';
            const c = (this.consultorio || '').trim() || 'su consultorio';
            return 'Hola ' + n + ', le recordamos su cita en ' + c + ' jueves a las 10:30.\n\nConfirme o cancele aquí:\n(la liga de su cita)\n\n¡Le esperamos!';
        },
        get digitos() { return (this.telefono || '').replace(/\D/g, ''); },
        get liga() {
            let t = this.digitos;
            if (t.length === 10) t = '52' + t;
            return 'https://wa.me/' + t + '?text=' + encodeURIComponent(this.mensaje);
        },
        abrir(ev) {
            const t = this.digitos;
            if (t.length !== 10 && !(t.length === 12 && t.startsWith('52'))) {
                ev.preventDefault();
                this.error = t.length === 0 ? 'Escriba su número de WhatsApp para abrir el mensaje.' : 'El número debe tener 10 dígitos, por ejemplo 668 123 4567.';
                return;
            }
            this.error = '';
        },
    };
}

document.addEventListener('DOMContentLoaded', () => {
    const nav = document.getElementById('navbar');
    const riel = document.getElementById('riel');
    const fijo = document.getElementById('sticky-cta');
    const inicio = document.getElementById('inicio');

    // Barra con borde, riel y botón fijo: aparecen cuando el inicio se va.
    new IntersectionObserver(([e]) => {
        const fuera = !e.isIntersecting;
        nav.classList.toggle('e-con-borde', fuera);
        riel.classList.toggle('e-visible', fuera);
        fijo.classList.toggle('e-visible', fuera);
    }, { rootMargin: '-60% 0px 0px 0px' }).observe(inicio);

    // El paso en pantalla enciende su mango en el riel.
    const enlaces = [...riel.querySelectorAll('[data-riel]')];
    const pasos = [...document.querySelectorAll('.paso')];
    const obsPaso = new IntersectionObserver((entradas) => {
        entradas.forEach(en => {
            if (!en.isIntersecting) return;
            const id = en.target.dataset.paso;
            enlaces.forEach(a => a.classList.toggle('activo', a.dataset.riel === id));
        });
    }, { rootMargin: '-45% 0px -50% 0px' });
    pasos.forEach(p => obsPaso.observe(p));

    // El odontograma y su presupuesto: cada línea marca su diente.
    const odonto = document.querySelector('[data-odonto]');
    const marcarPresupuesto = () => {
        const lineas = [...odonto.querySelectorAll('[data-linea]')];
        lineas.forEach((li, i) => setTimeout(() => {
            lineas.forEach(l => l.classList.remove('marcado'));
            odonto.querySelectorAll('.odo-diente.marcado').forEach(d => d.classList.remove('marcado'));
            li.classList.add('marcado');
            const d = odonto.querySelector('.odo-diente[title^="Diente ' + li.dataset.linea + ' "]');
            if (d) d.classList.add('marcado');
        }, sinMovimiento ? 0 : 500 + i * 900));
        setTimeout(() => odonto.querySelector('[data-acepta]').classList.add('visto'), sinMovimiento ? 0 : 500 + lineas.length * 900);
    };
    new IntersectionObserver(([e], o) => { if (e.isIntersecting) { o.disconnect(); marcarPresupuesto(); } }, { threshold: .35 }).observe(odonto);

    if (sinMovimiento || !window.gsap || !window.ScrollTrigger) return;
    gsap.registerPlugin(ScrollTrigger);

    // Entrada del inicio: el titular sube renglón por renglón, el celular y
    // los mangos del estuche caen en su lugar.
    const tl = gsap.timeline({ defaults: { ease: 'expo.out' } });
    tl.fromTo('.ini-foto', { clipPath: 'inset(12% 8% 12% 8% round 28px)' }, { clipPath: 'inset(0% 0% 0% 0% round 28px)', duration: 1.4 })
      .fromTo('[data-cel]', { y: 80, opacity: 0 }, { y: 0, opacity: 1, duration: 1.1 }, '-=1.1')
      .fromTo('[data-estuche] .mango', { y: -60, opacity: 0, rotate: -12 }, { y: 0, opacity: 1, rotate: 0, duration: .9, stagger: .07, ease: 'back.out(1.6)' }, '-=.8')
      .add(() => document.documentElement.classList.remove('js-mov'));

    gsap.to('[data-paralaje]', { yPercent: 8, ease: 'none', scrollTrigger: { trigger: '.ini', start: 'top top', end: 'bottom top', scrub: true } });

    // Cada charola de color se abre desde su mango: un círculo que nace en
    // el mango de la sección y crece hasta cubrirla, mientras el mango cae.
    document.querySelectorAll('.paso').forEach(paso => {
        const campo = paso.querySelector('[data-campo]');
        const mango = paso.querySelector('.paso-cabeza .mango');
        const centro = () => {
            const s = paso.getBoundingClientRect(), m = mango.getBoundingClientRect();
            const x = m.left - s.left + m.width / 2, y = m.top - s.top + m.height / 2;
            return { x, y, r: Math.hypot(Math.max(x, s.width - x), Math.max(y, s.height - y)) };
        };
        gsap.fromTo(campo,
            { clipPath: () => { const c = centro(); return `circle(0px at ${c.x}px ${c.y}px)`; } },
            { clipPath: () => { const c = centro(); return `circle(${c.r}px at ${c.x}px ${c.y}px)`; }, ease: 'power1.in',
              scrollTrigger: { trigger: paso, start: 'top 95%', end: 'top 45%', scrub: .4, invalidateOnRefresh: true } });
        gsap.fromTo(mango, { y: -80, rotate: -18 }, { y: 0, rotate: 0, duration: 1, ease: 'back.out(1.8)', scrollTrigger: { trigger: paso, start: 'top 92%' } });
    });

    // El celular cruza la libreta al bajar.
    gsap.fromTo('[data-cruza]', { xPercent: 40, rotate: 8 }, { xPercent: 0, rotate: -3, ease: 'none', scrollTrigger: { trigger: '.libreta', start: 'top 90%', end: 'bottom 40%', scrub: .8 } });

    // La escalera de mensualidades se llena al bajar.
    const pagadas = gsap.utils.toArray('.pp-escalera li.pagada i');
    gsap.timeline({ scrollTrigger: { trigger: '[data-escalera]', start: 'top 75%' } })
        .fromTo(pagadas, { scaleY: 0 }, { scaleY: 1, duration: .5, stagger: .14, ease: 'power3.out' })
        .from('.pp-escalera li.vencida', { scale: .6, opacity: 0, duration: .6, ease: 'back.out(2)' })
        .fromTo('.pp-cobrar', { y: 14, opacity: 0 }, { y: 0, opacity: 1, duration: .6, ease: 'expo.out' }, '-=.2');
});
</script>

<x-chatbot-widget />
</body>
</html>
