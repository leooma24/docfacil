<!DOCTYPE html>
<html lang="es" style="scroll-behavior:smooth;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DocFácil — Software para Consultorio Dental en México</title>
    <meta name="description" content="Para el consultorio dental que lleva todo en papel: agenda, recetas con cédula, odontograma, presupuestos y quién le debe, en el celular. 15 días gratis, sin tarjeta.">
    <meta name="theme-color" content="#14b8a6">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="canonical" href="{{ url('/dentistas') }}">

    {{-- Performance: preload del logo (navbar LCP candidate) + dns-prefetch a WhatsApp --}}
    <link rel="preload" as="image" href="{{ asset('images/logo_doc_facil.png') }}" fetchpriority="high">
    <link rel="dns-prefetch" href="//wa.me">
    <link rel="dns-prefetch" href="//api.whatsapp.com">

    {{-- iOS PWA --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="DocFácil">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=yes">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">

    {{-- OpenGraph (Facebook, WhatsApp, LinkedIn) --}}
    <meta property="og:title" content="DocFácil — Software para Consultorio Dental en México">
    <meta property="og:description" content="Odontograma digital, recordatorios WhatsApp, recetas PDF con cédula. 15 días gratis para dentistas.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">
    <meta property="og:image:secure_url" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="DocFácil — Software para consultorios dentales">
    <meta property="og:url" content="{{ url('/dentistas') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DocFácil">
    <meta property="og:locale" content="es_MX">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="DocFácil — Software para Consultorio Dental en México">
    <meta name="twitter:description" content="Odontograma digital, recordatorios WhatsApp, recetas PDF con cédula. 15 días gratis para dentistas.">
    <meta name="twitter:image" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">
    <meta name="twitter:image:alt" content="DocFácil — Software para consultorios dentales">
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

    {{-- FAQPage schema — la lista de FAQs se inyecta dinámicamente desde el array
         $landingFaqs que aparece más abajo, generando JSON-LD válido para extracción
         por AI Overviews / Perplexity / ChatGPT. --}}
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
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- El plugin collapse va ANTES del core de Alpine (asi lo pide con defer).
         Sin el, el acordeon del FAQ abre de golpe en vez de deslizarse. --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
        /* Offset del scroll para que el navbar fijo (h-20 = 80px) no tape el título de la sección */
        section[id] { scroll-margin-top: 90px; }
        /* Hover 3D suave en pricing cards — tilt + glow teal + escala. El popular ya tiene escala 1.10, hover lo sube a 1.12 */
        .pricing-card { transition: transform 0.35s cubic-bezier(0.2, 0.9, 0.3, 1.1), box-shadow 0.35s ease; will-change: transform; }
        .pricing-card:hover { transform: translateY(-8px) rotateX(2deg) rotateY(-2deg); box-shadow: 0 30px 50px -12px rgba(13,148,136,0.25), 0 0 0 1px rgba(13,148,136,0.15); }
        .pricing-card.popular:hover { transform: translateY(-10px) scale(1.12) rotateX(2deg) rotateY(-2deg); box-shadow: 0 40px 60px -12px rgba(13,148,136,0.5), 0 0 0 1px rgba(13,148,136,0.2); }
        .pricing-grid { perspective: 1200px; }
        @keyframes gradient { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-20px)} }
        @keyframes fadeUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
        @keyframes slideIn { from{opacity:0;transform:translateX(-30px)} to{opacity:1;transform:translateX(0)} }
        @keyframes pulse-glow { 0%,100%{box-shadow:0 0 20px rgba(20,184,166,0.3)} 50%{box-shadow:0 0 40px rgba(20,184,166,0.6)} }
        @keyframes blob { 0%,100%{border-radius:60% 40% 30% 70%/60% 30% 70% 40%} 50%{border-radius:30% 60% 70% 40%/50% 60% 30% 60%} }
        @keyframes count { from{opacity:0;transform:scale(0.5)} to{opacity:1;transform:scale(1)} }
        .animate-gradient { background-size:200% 200%; animation:gradient 8s ease infinite; }
        .animate-float { animation:float 6s ease-in-out infinite; }
        .animate-fade-up { animation:fadeUp 0.8s ease forwards; }
        .animate-slide-in { animation:slideIn 0.6s ease forwards; }
        .animate-pulse-glow { animation:pulse-glow 3s ease infinite; }
        .animate-blob { animation:blob 10s ease-in-out infinite; }
        .delay-100 { animation-delay:0.1s; }
        .delay-200 { animation-delay:0.2s; }
        .delay-300 { animation-delay:0.3s; }
        .delay-400 { animation-delay:0.4s; }
        .delay-500 { animation-delay:0.5s; }
        [data-animate] { opacity:0; }
        [data-animate].visible { opacity:1; }
        [x-cloak] { display: none !important; }

        /* Prevenir scroll horizontal en mobile causado por elementos
           fixed/absolute (chatbot panel, sticky CTAs, etc.) que excedan
           viewport. overflow-x:hidden solo en body no basta; necesita html. */
        html, body { overflow-x: hidden; max-width: 100vw; }

        /* Landing: estilos propios (no utilidades responsive de Tailwind, que
           no siempre compilan en producción). Texto de 17px para leerse
           bien en el celular, de donde llega casi todo el que viene de WhatsApp. */
        .lp-wrap { max-width: 1080px; margin: 0 auto; padding: 0 16px; }
        .lp-narrow { max-width: 720px; }
        .lp-hero { padding: 112px 0 48px; background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%); }
        .lp-kicker { font-size: 15px; font-weight: 700; color: #0f766e; margin: 0 0 12px; }
        .lp-h1 { font-size: 34px; line-height: 1.15; font-weight: 800; letter-spacing: -0.02em; color: #0f172a; margin: 0; max-width: 820px; }
        .lp-lead { font-size: 19px; line-height: 1.55; color: #334155; margin: 18px 0 0; max-width: 680px; }
        .lp-ctas { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
        .lp-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 52px; padding: 0 24px; border-radius: 14px; font-size: 17px; font-weight: 700; text-decoration: none; transition: transform .15s, box-shadow .15s; }
        .lp-btn:hover { transform: translateY(-1px); }
        .lp-btn-primary { background: #0f766e; color: #fff; box-shadow: 0 10px 24px -10px rgba(15,118,110,.6); }
        .lp-btn-wa { background: #fff; color: #166534; border: 2px solid #22c55e; }
        .lp-fine { font-size: 15px; color: #475569; margin: 16px 0 0; }
        .lp-sec { padding: 64px 0; }
        .lp-alt { background: #f8fafc; }
        .lp-split { display: grid; grid-template-columns: 1fr; gap: 28px; align-items: center; }
        .lp-dolor { font-size: 18px; font-style: italic; color: #b45309; font-weight: 600; margin: 0 0 10px; }
        .lp-h2 { font-size: 28px; line-height: 1.2; font-weight: 800; letter-spacing: -0.01em; color: #0f172a; margin: 0 0 14px; }
        .lp-p { font-size: 17px; line-height: 1.65; color: #334155; margin: 0 0 12px; }
        .lp-nota { font-size: 15px; color: #475569; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; display: inline-block; margin-top: 4px; }
        .lp-video { margin: 0; width: 100%; max-width: 380px; justify-self: center; }
        .lp-video video { display: block; width: 100%; height: auto; aspect-ratio: 4 / 5; border-radius: 18px; background: #0f766e; box-shadow: 0 20px 40px -18px rgba(15,23,42,.45); }
        .lp-video figcaption { font-size: 14px; color: #475569; text-align: center; margin-top: 8px; }
        .lp-omar { display: flex; gap: 16px; align-items: center; text-align: left; background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 16px; padding: 18px; margin-top: 8px; }
        .lp-faq details { border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; margin-bottom: 10px; }
        .lp-faq summary { cursor: pointer; list-style: none; padding: 16px 18px; font-size: 17px; font-weight: 700; color: #0f172a; min-height: 52px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .lp-faq summary::-webkit-details-marker { display: none; }
        .lp-faq summary::after { content: '+'; font-size: 24px; color: #0f766e; font-weight: 400; }
        .lp-faq details[open] summary::after { content: '–'; }
        .lp-faq details p { padding: 0 18px 16px; margin: 0; font-size: 16px; line-height: 1.6; color: #334155; }
        @@media (min-width: 768px) {
            .lp-hero { padding: 150px 0 72px; }
            .lp-h1 { font-size: 48px; }
            .lp-h2 { font-size: 34px; }
            .lp-sec { padding: 88px 0; }
            .lp-split { grid-template-columns: 1.15fr 0.85fr; gap: 56px; }
            .lp-rev .lp-txt { order: 2; }
        }
    </style>
    @include('partials.analytics')
</head>
<body class="bg-white text-gray-900 antialiased overflow-x-hidden">

    {{-- Navbar --}}
    <nav class="fixed top-0 w-full bg-white/80 backdrop-blur-lg border-b border-gray-100/50 z-50 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-20">
            <a href="/" class="flex items-center gap-2">
                <img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" class="h-14 transition-transform hover:scale-105">
            </a>
            <div class="hidden md:flex items-center gap-8">
                <a href="#problema" class="text-sm text-gray-600 hover:text-teal-600 transition font-medium">Cómo le ayuda</a>
                <a href="#pricing" class="text-sm text-gray-600 hover:text-teal-600 transition font-medium">Precios</a>
                <a href="#faq" class="text-sm text-gray-600 hover:text-teal-600 transition font-medium">FAQ</a>
                <a href="#contacto" class="text-sm text-gray-600 hover:text-teal-600 transition font-medium">Contacto</a>

                {{-- Instalar app: texto sutil, solo aparece cuando PWA install está disponible --}}
                <button
                    x-data="{ show: !!window.__docfacilInstallPrompt, installing: false }"
                    x-show="show"
                    x-cloak
                    x-on:docfacil-install-ready.window="show = true"
                    x-on:docfacil-install-done.window="show = false"
                    x-on:click="
                        if (!window.__docfacilInstallPrompt) return;
                        installing = true;
                        window.__docfacilInstallPrompt.prompt();
                        window.__docfacilInstallPrompt.userChoice.finally(() => {
                            installing = false;
                            window.__docfacilInstallPrompt = null;
                            show = false;
                        });
                    "
                    type="button"
                    class="text-sm text-gray-500 hover:text-teal-600 transition font-medium">
                    <span x-text="installing ? 'Instalando...' : 'Instalar app'"></span>
                </button>

                <a href="{{ url('/doctor/login') }}" class="text-sm text-gray-500 hover:text-teal-600 transition font-medium">Iniciar sesión</a>
                {{-- CTA único primary — sin competencia visual en el nav --}}
                <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="navbar" data-track-text="prueba_gratis" class="inline-flex items-center px-5 py-2.5 bg-teal-600 text-white text-sm font-semibold rounded-xl hover:bg-teal-700 transition-all hover:shadow-lg hover:shadow-teal-200 hover:-translate-y-0.5">
                    Prueba gratis
                </a>
            </div>
            {{-- Mobile menu --}}
            <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="md:hidden p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden px-4 pb-4 space-y-2">
            <a href="#problema" class="block py-2 text-gray-600">Cómo le ayuda</a>
            <a href="#pricing" class="block py-2 text-gray-600">Precios</a>
            <a href="#faq" class="block py-2 text-gray-600">FAQ</a>
            <a href="#contacto" class="block py-2 text-gray-600">Contacto</a>
            <button
                x-data="{ show: !!window.__docfacilInstallPrompt }"
                x-show="show"
                x-cloak
                x-on:docfacil-install-ready.window="show = true"
                x-on:docfacil-install-done.window="show = false"
                x-on:click="
                    if (!window.__docfacilInstallPrompt) return;
                    window.__docfacilInstallPrompt.prompt();
                    window.__docfacilInstallPrompt.userChoice.finally(() => {
                        window.__docfacilInstallPrompt = null;
                        show = false;
                    });
                "
                type="button"
                class="block w-full py-2 px-4 text-left text-teal-700 border border-teal-200 rounded-lg font-semibold">
                📲 Instalar como app
            </button>
            <a href="{{ url('/doctor/login') }}" class="block py-2 text-gray-600">Iniciar sesión</a>
            <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="navbar_mobile" data-track-text="prueba_gratis" class="block py-2 px-4 bg-teal-600 text-white text-center rounded-lg">Prueba gratis</a>
        </div>
    </nav>

    {{-- Cada sección arranca con el problema del dentista, con sus palabras,
         y enseña cómo se resuelve con un video corto (el mismo que Omar manda
         por WhatsApp). Antes la página era una lista de funciones y capturas:
         quien la abre quiere saber si le arregla un problema, no qué botones
         tiene. Nada de cifras sin fuente ni testimonios: todavía no hay. --}}
    @php
        $waOmar = 'https://wa.me/526682493398?text=' . urlencode('Hola Omar, vi la página de DocFacil y quiero ver cómo funcionaría en mi consultorio.');
        $precioPresupuestos = (float) config('addons.treatment_plans.monthly_price', 129);
        $videos = [
            'v1-consulta' => ['dur' => '40 s', 'titulo' => 'Video: de la cita a la receta'],
            'v2-presupuesto' => ['dur' => '34 s', 'titulo' => 'Video: del odontograma al presupuesto'],
            'v3-ortodoncia' => ['dur' => '32 s', 'titulo' => 'Video: mensualidades de brackets'],
        ];
    @endphp

    {{-- 1. HERO --}}
    <section class="lp-hero">
        <div class="lp-wrap">
            <p class="lp-kicker">Para consultorios dentales que van empezando</p>
            <h1 class="lp-h1">Su consultorio en papel, pasado al celular. <span style="color:#0f766e;">Sin que se le olvide nada.</span></h1>
            <p class="lp-lead">Agenda, recetas con su cédula, odontograma y quién le debe, en un solo lugar. Pruébelo 15 días gratis, sin tarjeta, y yo le ayudo a cargar su agenda.</p>
            <div class="lp-ctas">
                <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="hero" data-track-text="probar_15_dias" class="lp-btn lp-btn-primary">Probar 15 días gratis</a>
                <a href="{{ $waOmar }}" target="_blank" rel="noopener" data-track="whatsapp_clicked" data-track-location="hero" class="lp-btn lp-btn-wa">Escribirle a Omar por WhatsApp</a>
            </div>
            <p class="lp-fine">Sin tarjeta · Garantía de 30 días · Soporte directo por WhatsApp · Funciona en el celular</p>
        </div>
    </section>

    {{-- 2. LA CONSULTA (video 1) --}}
    <section id="problema" class="lp-sec">
        <div class="lp-wrap lp-split">
            <div class="lp-txt">
                <p class="lp-dolor">“Entre apuntar, hacer la receta a mano y cobrar, se me va media consulta.”</p>
                <h2 class="lp-h2">Una consulta completa, sin papeles.</h2>
                <p class="lp-p">Al iniciar la consulta ya viene lo que se va a hacer y en qué diente. Anota el diagnóstico, la receta sale con su cédula, y el cobro ya trae el tratamiento. Si el paciente tiene alergias registradas, DocFácil le avisa antes de recetar.</p>
            </div>
            @include('partials.landing-video', ['id' => 'v1-consulta', 'v' => $videos['v1-consulta']])
        </div>
    </section>

    {{-- 3. RECORDATORIOS (sin video) --}}
    <section class="lp-sec lp-alt">
        <div class="lp-wrap lp-narrow">
            <p class="lp-dolor">“Se me olvida confirmar y el sillón se queda vacío.”</p>
            <h2 class="lp-h2">El recordatorio por WhatsApp, ya escrito, a un clic desde su agenda.</h2>
            <p class="lp-p">Le da un clic a la cita y se abre WhatsApp con el mensaje y una liga para que el paciente confirme o cancele. Se manda desde el WhatsApp de su consultorio, sin costo por mensaje. Si alguien cancela, la lista de espera le dice a quién ofrecerle ese horario (plan Pro).</p>
            <p class="lp-p">Y cuando el paciente llega y escanea el QR de su recepción, a usted le avisa que ya está en la sala.</p>
        </div>
    </section>

    {{-- 4. PRESUPUESTO (video 2) --}}
    <section id="presupuestos" class="lp-sec">
        <div class="lp-wrap lp-split lp-rev">
            <div class="lp-txt">
                <p class="lp-dolor">“Armar el presupuesto a mano, diente por diente, me quita la tarde.”</p>
                <h2 class="lp-h2">Su odontograma ya es su presupuesto.</h2>
                <p class="lp-p">Marca lo que encuentra, cara por cara, y con un clic sale el presupuesto con sus precios. Se lo manda por WhatsApp y el paciente lo acepta desde su celular. Lo aceptado se agenda en un clic y en la consulta ya aparece.</p>
                <p class="lp-nota">Incluido en la prueba de 15 días; después, ${{ number_format($precioPresupuestos) }} al mes aparte de su plan.</p>
            </div>
            @include('partials.landing-video', ['id' => 'v2-presupuesto', 'v' => $videos['v2-presupuesto']])
        </div>
    </section>

    {{-- 5. MENSUALIDADES (video 3) --}}
    <section class="lp-sec lp-alt">
        <div class="lp-wrap lp-split">
            <div class="lp-txt">
                <p class="lp-dolor">“No sé quién me debe la mensualidad de brackets.”</p>
                <h2 class="lp-h2">Sepa quién va al día y quién le debe, sin revisar la libreta.</h2>
                <p class="lp-p">Pone el total, el enganche y las mensualidades, y cada pago queda con su fecha. Cuando el paciente viene a su ajuste, la mensualidad aparece en la consulta para cobrarla ahí mismo, y queda en su corte del día.</p>
            </div>
            @include('partials.landing-video', ['id' => 'v3-ortodoncia', 'v' => $videos['v3-ortodoncia']])
        </div>
    </section>

    {{-- 6. EMPEZAR: el miedo a pasar todo del papel --}}
    <section class="lp-sec">
        <div class="lp-wrap lp-narrow" style="text-align:center;">
            <p class="lp-dolor">“Pasar todo del papel me va a costar trabajo.”</p>
            <h2 class="lp-h2">Usted no captura nada solo: yo le ayudo a empezar.</h2>
            <div class="lp-omar">
                @if (file_exists(public_path('images/founder-omar-320.jpg')))
                <img src="{{ asset('images/founder-omar-320.jpg') }}" alt="Omar Lerma, fundador de DocFácil" width="96" height="96" loading="lazy" decoding="async" style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid #14b8a6;">
                @endif
                <p class="lp-p" style="text-align:left;margin:0;">Soy Omar Lerma, de Los Mochis, Sinaloa. Me manda su Excel por WhatsApp y le dejo cargados sus pacientes; si los tiene en la libreta, le ayudo a armar la lista. Cualquier duda, me escribe a mi celular y le contesto yo.</p>
            </div>
            <a href="{{ $waOmar }}" target="_blank" rel="noopener" data-track="whatsapp_clicked" data-track-location="founder_section" class="lp-btn lp-btn-wa" style="margin-top:20px;">Escribirle a Omar: 668 249 3398</a>
        </div>
    </section>


    <section id="pricing" class="py-14 sm:py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10" data-animate>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Con una consulta al mes, el plan Básico ya se pagó.</h2>
                <p class="mt-4 text-lg text-gray-600">Pruebe 15 días con todo, sin tarjeta. Si después paga y en los primeros 30 días no le sirve, le devolvemos su dinero (<a href="/terminos#garantia" class="text-teal-700 underline">Garantía de 30 días</a>).</p>
            </div>

            {{-- Programa Fundador.
                 Antes aquí había un reloj de "oferta de lanzamiento" que
                 terminaba cada fin de mes y volvía a empezar — el anual a 10
                 meses no es promoción, es el precio de siempre, así que el
                 reloj le ponía fecha a algo que no iba a cambiar.
                 Lo que sí es escaso son los lugares, y el número sale de
                 contar fundadores reales: no se reinicia y no puede mentir. --}}
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
            <div class="max-w-2xl mx-auto mb-10 rounded-2xl p-6" style="background:linear-gradient(135deg,#fffbeb,#fef3c7); border:2px solid #fbbf24; box-shadow:0 10px 25px -12px rgba(217,119,6,.35);" data-animate>
                <div class="flex items-start gap-4">
                    <div class="text-4xl flex-shrink-0">🏅</div>
                    <div class="flex-1">
                        <div class="text-[11px] font-extrabold text-amber-800 uppercase tracking-widest">Programa Fundador</div>
                        <div class="font-extrabold text-amber-900 text-xl mt-0.5">
                            @if ($fundador['tomados'] > 0)
                                Quedan {{ $fundador['quedan'] }} de {{ $fundador['total'] }} lugares
                            @else
                                Busco los primeros {{ $fundador['total'] }} consultorios
                            @endif
                        </div>

                        {{-- Los lugares, a la vista. Solo cuando ya hay alguien
                             dentro: diez círculos vacíos no dicen nada bueno. --}}
                        @if ($fundador['tomados'] > 0)
                        <div style="display:flex; gap:5px; margin:.7rem 0; flex-wrap:wrap;">
                            @for ($i = 0; $i < $fundador['total']; $i++)
                                <span style="width:13px; height:13px; border-radius:50%; {{ $i < $fundador['tomados'] ? 'background:#d97706;' : 'background:transparent; border:2px solid #fcd34d;' }}"></span>
                            @endfor
                        </div>
                        @else
                        <div style="height:.7rem;"></div>
                        @endif

                        <p class="text-sm text-amber-900 leading-relaxed">
                            <strong>{{ $mesesGratis }} meses sin costo</strong> y después
                            <strong>${{ number_format($precioFundador) }}/mes de por vida</strong>, congelado —
                            la mitad de los ${{ number_format($precioPro) }} del plan Pro.
                        </p>
                        <p class="text-sm text-amber-800 mt-1.5 leading-relaxed">
                            A cambio le pido dos cosas: que lo use en serio y que me diga la verdad,
                            aunque la verdad sea que no le sirve.
                        </p>

                        <a href="{{ $waFundador }}" target="_blank" rel="noopener"
                           data-track="founder_seat_clicked"
                           class="inline-block mt-4 px-5 py-2.5 rounded-xl font-bold text-white transition-all hover:shadow-lg"
                           style="background:linear-gradient(135deg,#d97706,#b45309);">
                            Quiero un lugar →
                        </a>
                    </div>
                </div>
            </div>
            @else
            {{-- Ya se llenaron. El ahorro anual es el precio de siempre, sin reloj. --}}
            <div class="max-w-2xl mx-auto mb-10 rounded-xl p-4 flex items-center gap-4" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5); border:1px solid #6ee7b7;" data-animate>
                <div class="text-3xl flex-shrink-0">💰</div>
                <div>
                    <div class="font-bold text-emerald-900">Paga anual y ahorra 2 meses</div>
                    <div class="text-sm text-emerald-800">El año le sale en 10 meses.</div>
                </div>
            </div>
            @endif

            <div class="pricing-grid grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto" data-animate>
                @php
                $plans = [
                    // Los limites (doctores, pacientes) salen de la lista de
                    // features y se van a su propio renglon. Antes ocupaban 2
                    // de los 4 espacios visibles de cada tarjeta: en el lugar
                    // mas caro de la pagina se le decia al dentista lo que NO
                    // puede hacer, en vez de por que le conviene pagar.
                    [
                        'name' => 'Free',
                        'price' => '0',
                        'annual' => 0,
                        'subtitle' => 'Para siempre',
                        'ideal' => 'Para conocerlo sin prisa',
                        'limits' => '1 doctor · 15 pacientes · 10 citas al mes',
                        'lead' => null,
                        'features' => [
                            'Agenda y calendario de citas',
                            'Expediente de cada paciente',
                            'Sin tarjeta y sin vencimiento',
                            'Sube de plan cuando quiera',
                        ],
                        'cta' => 'Empezar gratis',
                        'popular' => false,
                    ],
                    [
                        'name' => 'Básico',
                        'price' => '499',
                        'annual' => 4990,
                        'subtitle' => 'por mes · cancela cuando quiera',
                        'ideal' => 'Para el dentista que trabaja solo',
                        'limits' => '1 doctor · 200 pacientes · citas ilimitadas',
                        'lead' => null,
                        // De mayor a menor por lo que le mueve la aguja a un
                        // dentista: primero lo que le trae o le cuida dinero,
                        // luego lo que lo hace verse profesional.
                        'features' => [
                            'Recordatorios WhatsApp a 1 clic',
                            'Gastos y corte del mes',
                            'Odontograma FDI interactivo',
                            'Recetas PDF con cédula',
                            'Cobro por WhatsApp a 1 clic',
                            'Confirmar cita con link',
                            'Check-in con QR',
                            'Escritorio con sus números del día',
                        ],
                        'cta' => 'Probar 15 días gratis',
                        'popular' => false,
                    ],
                    [
                        'name' => 'Pro',
                        'price' => '999',
                        'annual' => 9990,
                        'subtitle' => 'por mes · cancela cuando quiera',
                        'ideal' => 'Para consultorios de 2 o 3 doctores',
                        'limits' => 'Hasta 3 doctores · pacientes ilimitados',
                        'lead' => 'Todo lo del Básico, y además:',
                        'features' => [
                            'Sus pacientes agendan solos, a cualquier hora',
                            'Recall: a quién ya le toca volver',
                            'Lista de espera que llena los huecos',
                            'Consentimientos firmados en pantalla',
                            'Inventario de insumos: qué hay, qué se acabó y qué caduca',
                            'Reportes avanzados',
                            'Alertas inteligentes',
                            'Soporte prioritario por WhatsApp',
                        ],
                        'cta' => 'Probar Pro 15 días gratis',
                        'popular' => true,
                    ],
                    [
                        'name' => 'Clínica',
                        'price' => '1,999',
                        'annual' => 19990,
                        'subtitle' => 'por mes · cancela cuando quiera',
                        'ideal' => 'Para clínicas con varios doctores',
                        'limits' => 'Doctores y pacientes ilimitados',
                        'lead' => 'Todo lo del Pro, y además:',
                        'features' => [
                            'Producción individual por doctor',
                            'Reportes por doctor',
                            'Onboarding 1 a 1 dedicado',
                            'Soporte prioritario 7 días a la semana',
                        ],
                        'cta' => 'Contactar ventas',
                        'popular' => false,
                    ],
                ];
                @endphp
                @foreach($plans as $i => $plan)
                @php $visible = array_slice($plan['features'], 0, 4); $hidden = array_slice($plan['features'], 4); @endphp
                <div x-data="{ expanded: false }" class="pricing-card relative flex flex-col rounded-2xl p-7 animate-fade-up {{ $plan['popular'] ? 'popular md:scale-110 md:-my-2 z-10' : '' }}" style="animation-delay:{{ $i * 0.1 }}s; {{ $plan['popular'] ? 'background:linear-gradient(180deg,#ffffff 0%,#f0fdfa 100%); border:3px solid #0d9488; padding-top:2.25rem; box-shadow:0 25px 50px -12px rgba(13,148,136,0.35), 0 0 0 1px rgba(13,148,136,0.1);' : 'background:#fff; border:1px solid #e5e7eb;' }}">
                    @if($plan['popular'])
                    <div class="absolute left-1/2 flex items-center gap-1.5 px-5 py-2 text-white text-xs font-extrabold rounded-full uppercase tracking-wider whitespace-nowrap" style="top:-1.1rem; transform:translateX(-50%); background:linear-gradient(135deg,#0d9488,#0891b2); box-shadow:0 10px 25px -5px rgba(13,148,136,0.5);">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        El más elegido
                    </div>
                    @endif
                    <h3 class="text-lg font-bold text-gray-900">{{ $plan['name'] }}</h3>
                    {{-- Contesta "¿cuál me toca a mí?" antes de que lo pregunte --}}
                    <div class="text-xs text-gray-500 mt-0.5">{{ $plan['ideal'] }}</div>
                    <div class="mt-4 mb-1">
                        <span class="text-4xl font-extrabold text-gray-900">${{ $plan['price'] }}</span>
                    </div>
                    <div class="text-sm text-gray-500 mb-2">{{ $plan['subtitle'] }}</div>
                    @if($plan['annual'] > 0)
                    <div class="mb-3 inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 border border-emerald-200 rounded-md text-xs font-semibold text-emerald-700">
                        o ${{ number_format($plan['annual']) }}/año · 2 meses gratis
                    </div>
                    @else
                    <div class="mb-3 text-xs text-gray-400">sin tarjeta · sin compromiso</div>
                    @endif

                    {{-- Los límites, en su renglón. Informan sin quitarle
                         lugar a un beneficio. --}}
                    <div class="mb-4 pb-4 border-b border-gray-100 text-xs text-gray-500 leading-relaxed">
                        {{ $plan['limits'] }}
                    </div>

                    @if($plan['lead'])
                    <div class="mb-3 text-xs font-bold text-teal-700 uppercase tracking-wide">{{ $plan['lead'] }}</div>
                    @endif

                    <ul class="space-y-3 mb-4">
                        @foreach($visible as $feature)
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-teal-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                        @if(count($hidden) > 0)
                        <template x-if="expanded">
                            <div class="space-y-3">
                                @foreach($hidden as $feature)
                                <li class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-teal-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    {{ $feature }}
                                </li>
                                @endforeach
                            </div>
                        </template>
                        @endif
                    </ul>

                    <div class="mt-auto">
                        @if(count($hidden) > 0)
                        <button type="button" @click="expanded = !expanded" class="w-full mb-3 text-xs font-semibold text-teal-600 hover:text-teal-700 flex items-center justify-center gap-1">
                            <span x-show="!expanded">Ver {{ count($hidden) }} {{ count($hidden) === 1 ? 'función' : 'funciones' }} más</span>
                            <span x-show="expanded" x-cloak>Ver menos</span>
                            <svg class="w-3 h-3 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        @endif
                        @php
                            // El plan Clinica no se contrata solo: se cotiza por
                            // WhatsApp porque depende de cuantos doctores son.
                            $planUrl = ($plan['cta'] ?? '') === 'Contactar ventas'
                                ? 'https://wa.me/526682493398?text=' . urlencode('Hola Omar, me interesa el plan Clinica de DocFacil para mi consultorio')
                                : url('/doctor/register');
                        @endphp
                        <a href="{{ $planUrl }}"
                            @if(str_starts_with($planUrl, 'https://wa.me')) target="_blank" rel="noopener" @endif
                            data-track="pricing_tier_clicked"
                            data-track-tier="{{ $plan['slug'] ?? strtolower($plan['name']) }}"
                            data-track-cycle="{{ $plan['popular'] ? 'pro' : ($plan['name'] ?? 'unknown') }}"
                            class="block w-full text-center px-4 py-3 rounded-xl font-semibold transition-all {{ $plan['popular'] ? 'bg-gradient-to-r from-teal-600 to-cyan-600 text-white hover:shadow-lg hover:shadow-teal-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $plan['cta'] }}
                        </a>
                        {{-- El miedo no es el precio, es la tarjeta. Aquí es
                             donde duda, así que aquí se le quita. --}}
                        @if($plan['annual'] > 0 && $plan['cta'] !== 'Contactar ventas')
                        <div class="mt-2 text-center text-xs text-gray-500">
                            15 días con todo · sin tarjeta
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <p class="text-center text-gray-600 mt-10" style="font-size:15px;">Presupuestos en línea: ${{ number_format((float) config('addons.treatment_plans.monthly_price', 129)) }} al mes aparte de cualquier plan (incluidos en la prueba).</p>
        </div>
    </section>

    {{-- Preguntas: las mismas del JSON-LD de arriba. <details> nativo: abre
         sin JavaScript y se toca fácil en el celular. --}}
    <section id="faq" class="lp-sec lp-alt">
        <div class="lp-wrap lp-narrow lp-faq">
            <h2 class="lp-h2" style="text-align:center;margin-bottom:24px;">Lo que más me preguntan</h2>
            @foreach ($landingFaqs as $faq)
            <details>
                <summary>{{ $faq['q'] }}</summary>
                <p>{{ $faq['a'] }}</p>
            </details>
            @endforeach
        </div>
    </section>

    <section id="contacto" class="py-14 sm:py-24 bg-gradient-to-b from-white to-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-16 items-start">
                {{-- Info --}}
                <div data-animate>
                    <span class="inline-flex items-center px-3 py-1 bg-teal-50 text-teal-700 text-xs font-semibold rounded-full mb-4 border border-teal-100">CONTACTO</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight">
                        ¿Le mando un video o le enseño<br>
                        <span class="text-teal-600">su agenda ya cargada?</span>
                    </h2>
                    <p class="mt-4 text-gray-600 leading-relaxed">
                        Escríbame por WhatsApp o déjeme sus datos y le contesto yo, Omar, el mismo día. Sin compromiso.
                    </p>

                    <div class="mt-10 space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">WhatsApp / Teléfono</div>
                                <a href="https://wa.me/526682493398" target="_blank" data-track="whatsapp_clicked" data-track-location="contact_section" class="text-teal-600 hover:text-teal-700 transition font-medium">668 249 3398</a>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">Email</div>
                                <span class="text-gray-600">contacto@docfacil.com</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">Horario de atención</div>
                                <span class="text-gray-600">Lunes a Viernes, 9:00 - 18:00</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form --}}
                <div data-animate>
                    @if(session('contact_success'))
                    <div class="bg-teal-50 border border-teal-200 rounded-2xl p-8 text-center">
                        <div class="text-5xl mb-4">&#10003;</div>
                        <h3 class="text-xl font-bold text-teal-800 mb-2">¡Mensaje enviado!</h3>
                        <p class="text-teal-700">Gracias. Le escribo pronto.</p>
                    </div>
                    @else
                    {{-- Form simplificado: solo 3 campos visibles + detalles colapsables.
                         Pre-fill via query params (los manda TrackController al
                         hacer click en correos del pipeline). --}}
                    <form x-data="{ showMore: false }" action="{{ route('contact.store') }}" method="POST"
                        onsubmit="window.trackEvent && window.trackEvent('form_submitted', { form_type: 'contact' })"
                        class="bg-white rounded-2xl p-8 shadow-xl border border-gray-100 space-y-5">
                        @csrf
                        {{-- Honeypot anti-bot --}}
                        <div style="position:absolute;left:-9999px" aria-hidden="true">
                            <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                        </div>
                        <input type="hidden" name="form_rendered_at" value="{{ time() }}">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre *</label>
                            <input type="text" name="name" required value="{{ old('name', request()->query('name')) }}"
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-3"
                                placeholder="Dr. Juan Pérez">
                            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email *</label>
                            <input type="email" name="email" required value="{{ old('email', request()->query('email')) }}"
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-3"
                                placeholder="doctor@email.com">
                            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Teléfono / WhatsApp</label>
                            <input type="tel" name="phone" value="{{ old('phone', request()->query('phone')) }}"
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-3"
                                placeholder="668 123 4567">
                        </div>

                        <div x-show="!showMore" class="text-center">
                            <button type="button" @click="showMore = true" class="text-sm text-teal-600 hover:text-teal-700 font-semibold underline">
                                + Más detalles del consultorio (opcional)
                            </button>
                        </div>

                        <div x-show="showMore" x-cloak x-transition class="space-y-5 pt-2 border-t border-gray-100">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ciudad</label>
                                    <input type="text" name="city" value="{{ old('city', request()->query('city')) }}"
                                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5"
                                        placeholder="Ej: CDMX">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Especialidad</label>
                                    <input type="text" name="specialty" value="{{ old('specialty', request()->query('specialty')) }}"
                                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5"
                                        placeholder="Odontología">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nombre del consultorio</label>
                                <input type="text" name="clinic_name" value="{{ old('clinic_name', request()->query('clinic_name')) }}"
                                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5"
                                    placeholder="Consultorio Dental Sonrisas">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Mensaje</label>
                                <textarea name="message" rows="3"
                                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-2.5"
                                    placeholder="Cuénteme qué necesita o pregúnteme lo que quiera...">{{ old('message') }}</textarea>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full py-3.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-teal-200 transition-all hover:-translate-y-0.5 text-base">
                            Hablar con Omar
                        </button>
                        <p class="text-sm text-gray-500 text-center">Le contesto en menos de 24 horas.</p>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="py-16 bg-gray-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-12">
                <div>
                    <img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" class="h-10 mb-4 brightness-200" loading="lazy" decoding="async">
                    <p class="text-sm text-gray-500">Software para consultorios dentales. Hecho en México.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-300 mb-3">Producto</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#problema" class="hover:text-teal-400 transition">Cómo le ayuda</a></li>
                        <li><a href="#pricing" class="hover:text-teal-400 transition">Precios</a></li>
                        <li><a href="{{ route('brochure.web') }}" class="hover:text-teal-400 transition">Brochure</a></li>
                        <li><a href="{{ route('brochure.pdf') }}" class="hover:text-teal-400 transition">📄 Descargar PDF</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-300 mb-3">Ciudades</h4>
                    @php
                        $cityLinks = [
                            ['slug' => 'cdmx',                  'name' => 'CDMX'],
                            ['slug' => 'guadalajara',           'name' => 'Guadalajara'],
                            ['slug' => 'monterrey',             'name' => 'Monterrey'],
                            ['slug' => 'merida',                'name' => 'Mérida'],
                            ['slug' => 'culiacan',              'name' => 'Culiacán'],
                            ['slug' => 'queretaro',             'name' => 'Querétaro'],
                            ['slug' => 'tijuana',               'name' => 'Tijuana'],
                            ['slug' => 'cancun',                'name' => 'Cancún'],
                            ['slug' => 'leon',                  'name' => 'León'],
                            ['slug' => 'puebla',                'name' => 'Puebla'],
                            ['slug' => 'hermosillo',            'name' => 'Hermosillo'],
                            ['slug' => 'ciudad-obregon',        'name' => 'Cd. Obregón'],
                            ['slug' => 'ciudad-juarez',         'name' => 'Cd. Juárez'],
                            ['slug' => 'saltillo',              'name' => 'Saltillo'],
                            ['slug' => 'torreon',               'name' => 'Torreón'],
                            ['slug' => 'mazatlan',              'name' => 'Mazatlán'],
                            ['slug' => 'los-mochis',            'name' => 'Los Mochis'],
                            ['slug' => 'aguascalientes',        'name' => 'Aguascalientes'],
                            ['slug' => 'cuernavaca',            'name' => 'Cuernavaca'],
                            ['slug' => 'metepec',               'name' => 'Metepec'],
                            ['slug' => 'toluca',                'name' => 'Toluca'],
                            ['slug' => 'morelia',               'name' => 'Morelia'],
                            ['slug' => 'chihuahua',             'name' => 'Chihuahua'],
                            ['slug' => 'san-luis-potosi',       'name' => 'SLP'],
                            ['slug' => 'boca-del-rio',          'name' => 'Boca del Río'],
                            ['slug' => 'playa-del-carmen',      'name' => 'Playa del Carmen'],
                            ['slug' => 'zapopan',               'name' => 'Zapopan'],
                            ['slug' => 'san-pedro-garza-garcia','name' => 'San Pedro G.G.'],
                        ];
                    @endphp
                    <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                        @foreach ($cityLinks as $c)
                            <a href="/software-dental/{{ $c['slug'] }}" class="text-gray-500 hover:text-teal-400 transition">{{ $c['name'] }}</a>
                        @endforeach
                    </div>
                </div>
                <div>
                    {{-- Las paginas de comparativa estaban huerfanas: existian en el
                         sitemap pero nadie las enlazaba, ni la landing ni las de
                         ciudad. Son las de mayor intencion de compra (quien busca
                         "alternativas a X" ya se quiere cambiar), asi que valen un
                         lugar en el footer. --}}
                    @php
                        $competidores = [
                            ['slug' => 'dentalink',  'name' => 'Dentalink'],
                            ['slug' => 'doctorum',   'name' => 'Doctorum'],
                            ['slug' => 'eaglesoft',  'name' => 'Eaglesoft'],
                        ];
                    @endphp
                    <h4 class="font-semibold text-gray-300 mb-3">Comparativas</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        @foreach ($competidores as $comp)
                            <li><a href="/vs/{{ $comp['slug'] }}" class="hover:text-teal-400 transition">DocFácil vs {{ $comp['name'] }}</a></li>
                        @endforeach
                        @foreach ($competidores as $comp)
                            <li><a href="/alternativas-a-{{ $comp['slug'] }}" class="hover:text-teal-400 transition">Alternativas a {{ $comp['name'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-300 mb-3">Acceso</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="/doctor/login" class="hover:text-teal-400 transition">Panel Doctor</a></li>
                        <li><a href="/paciente/login" class="hover:text-teal-400 transition">Portal Paciente</a></li>
                        <li><a href="/admin/login" class="hover:text-teal-400 transition">Administración</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center space-y-3">
                <div class="flex items-center justify-center gap-4 text-sm">
                    <a href="/blog" class="text-gray-400 hover:text-teal-400 transition">Blog</a>
                    <span class="text-gray-700">&middot;</span>
                    <a href="/privacidad" class="text-gray-400 hover:text-teal-400 transition">Aviso de Privacidad</a>
                    <span class="text-gray-700">&middot;</span>
                    <a href="/terminos" class="text-gray-400 hover:text-teal-400 transition">Términos y Condiciones</a>
                </div>
                <p class="text-sm text-gray-600">&copy; {{ date('Y') }} DocFácil. Todos los derechos reservados.</p>
                @php
                    // Última actualización del archivo del template (señal de freshness para AI/SEO).
                    $manifestPath = public_path('build/manifest.json');
                    $sourceFile = is_file($manifestPath) ? $manifestPath : __FILE__;
                    $lastUpdated = \Carbon\Carbon::createFromTimestamp(filemtime($sourceFile))->translatedFormat('j \d\e F \d\e Y');
                @endphp
                <p class="text-xs text-gray-700 mt-1">Última actualización: {{ $lastUpdated }}</p>
            </div>
        </div>
    </footer>

{{-- Botón fijo en el celular, aparece después del inicio. --}}
<div id="sticky-cta" class="md:hidden fixed bottom-0 left-0 right-0 z-40 px-3 pb-3 pt-2 bg-white/95 backdrop-blur-md border-t border-gray-200 opacity-0 pointer-events-none translate-y-full transition-all duration-300" style="box-shadow: 0 -4px 12px rgba(0,0,0,0.06);">
    <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="sticky_mobile" data-track-text="probar_15_dias" class="flex items-center justify-center gap-2 w-full py-3.5 text-white font-bold rounded-xl shadow-lg" style="background:#0f766e;font-size:17px;">
        Probar 15 días gratis
    </a>
    <p class="text-center text-gray-500 mt-1.5" style="font-size:13px;">Sin tarjeta · Garantía de 30 días</p>
</div>

<script>
// PWA
if ('serviceWorker' in navigator) { navigator.serviceWorker.register('/sw.js'); }

// Sticky CTA mobile: aparece tras scrollear 500px. (Desktop usa el chatbot bubble — no compite con el sticky aquí.)
(function() {
    const stickyCta = document.getElementById('sticky-cta');
    if (!stickyCta) return;

    function update() {
        if (window.scrollY > 500) {
            stickyCta.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-full');
        } else {
            stickyCta.classList.add('opacity-0', 'pointer-events-none', 'translate-y-full');
        }
    }

    window.addEventListener('scroll', update, { passive: true });
    update();
})();

// Scroll animations
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            entry.target.querySelectorAll('.animate-fade-up, .animate-slide-in').forEach(el => {
                el.style.animationPlayState = 'running';
            });
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('[data-animate]').forEach(el => observer.observe(el));

// Navbar scroll effect
let lastScroll = 0;
window.addEventListener('scroll', () => {
    const navbar = document.getElementById('navbar');
    const scroll = window.scrollY;
    if (scroll > 100) {
        navbar.classList.add('shadow-lg');
        navbar.classList.remove('border-b');
    } else {
        navbar.classList.remove('shadow-lg');
        navbar.classList.add('border-b');
    }
    lastScroll = scroll;
});

</script>

<x-chatbot-widget />
</body>
</html>
