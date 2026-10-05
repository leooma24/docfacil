<!DOCTYPE html>
<html lang="es" style="scroll-behavior:smooth;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DocFácil: software para Consultorio Dental en México</title>
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
           no siempre compilan en producción). Tema claro, un solo acento (el
           verde azulado de la marca) y radios de 16px en tarjetas, 14px en
           botones. Texto de 17px: casi todos llegan desde WhatsApp en el celular. */
        :root { --lp-acento: #0f766e; --lp-acento-2: #115e59; --lp-tinta: #0f172a; --lp-texto: #334155; --lp-suave: #475569; --lp-fondo: #f6faf9; }
        .lp-wrap { max-width: 1120px; margin: 0 auto; padding: 0 16px; }
        .lp-kicker { font-size: 13px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--lp-acento); margin: 0 0 14px; }
        .lp-h1 { font-size: 38px; line-height: 1.08; font-weight: 800; letter-spacing: -0.03em; color: var(--lp-tinta); margin: 0; }
        .lp-lead { font-size: 19px; line-height: 1.55; color: var(--lp-texto); margin: 18px 0 0; max-width: 34ch; }
        .lp-h2 { font-size: 30px; line-height: 1.15; font-weight: 800; letter-spacing: -0.02em; color: var(--lp-tinta); margin: 0 0 14px; }
        .lp-p { font-size: 17px; line-height: 1.65; color: var(--lp-texto); margin: 0 0 12px; max-width: 58ch; }
        .lp-dolor { font-size: 18px; line-height: 1.45; font-style: italic; color: #9a3412; font-weight: 600; margin: 0 0 12px; padding-bottom: 2px; max-width: 46ch; }
        .lp-dolor-claro { color: #fdba74; }
        .lp-nota { font-size: 15px; color: var(--lp-acento-2); background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 12px; padding: 10px 14px; display: inline-block; margin: 6px 0 0; font-weight: 600; }
        .lp-centro { text-align: center; max-width: 760px; margin: 0 auto 36px; }
        .lp-centro .lp-dolor { margin-left: auto; margin-right: auto; }

        .lp-ctas { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
        .lp-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 52px; padding: 0 24px; border-radius: 14px; font-size: 17px; font-weight: 700; text-decoration: none; white-space: nowrap; transition: transform .2s cubic-bezier(.16,1,.3,1), box-shadow .2s, background .2s; }
        .lp-btn:hover { transform: translateY(-2px); }
        .lp-btn:active { transform: translateY(0) scale(.98); }
        .lp-btn-primary { background: var(--lp-acento); color: #fff; box-shadow: 0 12px 28px -12px rgba(15,118,110,.7); }
        .lp-btn-primary:hover { background: var(--lp-acento-2); }
        .lp-btn-wa { background: #fff; color: #14532d; border: 2px solid #16a34a; }
        .lp-btn-wa:hover { background: #f0fdf4; }

        /* Inicio: texto a la izquierda; la foto de la libreta con el celular encima. */
        .lp-hero { padding: 104px 0 40px; background: radial-gradient(1200px 500px at 85% 10%, #ccfbf1 0%, rgba(204,251,241,0) 60%), #fff; overflow: hidden; }
        .lp-hero-grid { display: grid; grid-template-columns: 1fr; gap: 36px; align-items: center; }
        .lp-hero-media { position: relative; padding-bottom: 40px; }
        .lp-hero-foto { display: block; width: 100%; height: auto; aspect-ratio: 4 / 3; object-fit: cover; border-radius: 20px; box-shadow: 0 30px 60px -30px rgba(15,23,42,.45); }
        .lp-hero-tel { position: absolute; left: 10px; bottom: 0; width: 38%; max-width: 220px; height: auto; border-radius: 18px; border: 6px solid #0f172a; background: #0f172a; box-shadow: 0 24px 48px -16px rgba(15,23,42,.55); transform: rotate(-4deg); }

        /* Franja de confianza */
        .lp-franja { border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; background: #fff; }
        .lp-franja-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 16px; padding-top: 22px; padding-bottom: 22px; }
        .lp-franja-item { display: flex; gap: 10px; align-items: flex-start; }
        .lp-franja-item svg { width: 26px; height: 26px; color: var(--lp-acento); flex-shrink: 0; margin-top: 1px; }
        .lp-franja-item strong { display: block; font-size: 15px; color: var(--lp-tinta); }
        .lp-franja-item span { display: block; font-size: 14px; line-height: 1.4; color: var(--lp-suave); margin-top: 2px; }

        .lp-sec { padding: 64px 0; }
        .lp-tinte { background: var(--lp-fondo); }
        .lp-oscuro { background: linear-gradient(160deg, #134e4a 0%, #0f3d3a 100%); }
        .lp-split { display: grid; grid-template-columns: 1fr; gap: 32px; align-items: center; }
        .lp-lista { list-style: none; padding: 0; margin: 14px 0 0; display: grid; gap: 10px; }
        .lp-lista li { display: flex; gap: 10px; align-items: flex-start; font-size: 16px; color: var(--lp-texto); }
        .lp-lista svg { width: 20px; height: 20px; color: var(--lp-acento); flex-shrink: 0; margin-top: 2px; }

        /* Video: póster con el gancho, se reproduce con sonido al tocarlo. */
        .lp-video { margin: 0; width: 100%; max-width: 360px; justify-self: center; }
        .lp-video video { display: block; width: 100%; height: auto; aspect-ratio: 4 / 5; border-radius: 20px; background: #0f766e; box-shadow: 0 28px 56px -24px rgba(15,23,42,.55); }
        .lp-video figcaption { font-size: 14px; color: var(--lp-suave); text-align: center; margin-top: 10px; }
        .lp-oscuro .lp-video figcaption { color: #99f6e4; }

        /* Mosaico de recordatorios: tres celdas distintas, no tres tarjetas iguales. */
        .lp-bento { display: grid; grid-template-columns: 1fr; gap: 16px; }
        .lp-celda { margin: 0; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; }
        .lp-celda figcaption { padding: 20px 22px 0; font-size: 16px; line-height: 1.55; color: var(--lp-texto); }
        .lp-celda figcaption strong { display: block; font-size: 18px; color: var(--lp-tinta); margin-bottom: 4px; }
        .lp-celda img { display: block; width: 78%; max-width: 300px; height: auto; margin: 18px auto 0; border-radius: 16px 16px 0 0; box-shadow: 0 -8px 30px -12px rgba(15,23,42,.25); }
        .lp-celda-a { background: linear-gradient(180deg, #ccfbf1 0%, #f0fdfa 100%); }
        .lp-celda-b { background: #fff; border: 1px solid #e2e8f0; }
        .lp-celda-c { background: #fff7ed; border: 1px solid #fed7aa; padding: 22px; justify-content: center; }
        .lp-celda-c strong { font-size: 22px; color: #7c2d12; }
        .lp-celda-c p { font-size: 16px; line-height: 1.55; color: #7c2d12; margin: 8px 0 0; }
        .lp-celda-c span { font-weight: 700; }

        /* Mensualidades: video al centro, una idea a cada lado. */
        .lp-trio { display: grid; grid-template-columns: 1fr; gap: 24px; align-items: center; justify-items: center; }
        .lp-trio-txt { max-width: 300px; text-align: center; }
        .lp-trio-txt strong { display: block; font-size: 20px; color: #f0fdfa; margin-bottom: 6px; }
        .lp-trio-txt p { font-size: 16px; line-height: 1.6; color: #ccfbf1; margin: 0; }

        /* Empezar */
        .lp-omar-grid { display: grid; grid-template-columns: 1fr; gap: 32px; align-items: center; }
        .lp-omar { display: flex; gap: 16px; align-items: center; background: var(--lp-fondo); border: 1px solid #ccfbf1; border-radius: 16px; padding: 18px; margin: 8px 0 20px; }
        .lp-omar img { width: 88px; height: 88px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 3px solid #14b8a6; }
        .lp-omar p { margin: 0; font-size: 16px; line-height: 1.6; color: var(--lp-texto); }
        .lp-sigue-fig { margin: 0; justify-self: center; max-width: 380px; }
        .lp-sigue-fig img { display: block; width: 100%; height: auto; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 24px 50px -24px rgba(15,23,42,.35); }
        .lp-sigue-fig figcaption { font-size: 14px; color: var(--lp-suave); text-align: center; margin-top: 10px; }

        .lp-faq details { border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; margin-bottom: 10px; }
        .lp-faq summary { cursor: pointer; list-style: none; padding: 16px 18px; font-size: 17px; font-weight: 700; color: var(--lp-tinta); min-height: 52px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .lp-faq summary::-webkit-details-marker { display: none; }
        .lp-faq summary::after { content: '+'; font-size: 24px; color: var(--lp-acento); font-weight: 400; }
        .lp-faq details[open] summary::after { content: '-'; }
        .lp-faq details p { padding: 0 18px 16px; margin: 0; font-size: 16px; line-height: 1.6; color: var(--lp-texto); }
        .lp-narrow { max-width: 760px; }
        .lp-alt { background: var(--lp-fondo); }

        /* Aparecer al entrar en pantalla: dice "esto es lo siguiente". Sin
           movimiento para quien lo pidió en su sistema. */
        @@media (prefers-reduced-motion: no-preference) {
            [data-lp-reveal] { opacity: 0; transform: translateY(18px); transition: opacity .7s cubic-bezier(.16,1,.3,1), transform .7s cubic-bezier(.16,1,.3,1); }
            [data-lp-reveal].lp-visto { opacity: 1; transform: none; }
        }

        @@media (min-width: 768px) {
            .lp-hero { padding: 120px 0 64px; }
            .lp-hero-grid { grid-template-columns: 1.05fr 1fr; gap: 56px; }
            .lp-h1 { font-size: 54px; }
            .lp-h2 { font-size: 38px; }
            .lp-hero-tel { left: -40px; }
            .lp-franja-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .lp-sec { padding: 96px 0; }
            .lp-split { grid-template-columns: 1.1fr .9fr; gap: 64px; }
            .lp-rev > :first-child { order: 2; }
            .lp-bento { grid-template-columns: 1.3fr 1fr; grid-template-rows: auto auto; }
            .lp-celda-a { grid-row: span 2; }
            .lp-trio { grid-template-columns: 1fr auto 1fr; gap: 40px; }
            .lp-trio-txt:first-child { text-align: right; justify-self: end; }
            .lp-trio-txt:last-child { text-align: left; justify-self: start; }
            .lp-omar-grid { grid-template-columns: 1.15fr .85fr; gap: 64px; }
        }
        /* En el celular la burbuja del chat va arriba del botón fijo de abajo. */
        @@media (max-width: 767px) { #df-chat-bubble { bottom: 104px !important; right: 14px !important; width: 56px !important; height: 56px !important; } }
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
                    Probar 15 días gratis
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
            <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="navbar_mobile" data-track-text="prueba_gratis" class="block py-2 px-4 bg-teal-600 text-white text-center rounded-lg">Probar 15 días gratis</a>
        </div>
    </nav>

    {{-- Cada sección arranca con el problema del dentista, con sus palabras,
         y enseña cómo se resuelve: con el video corto que Omar manda por
         WhatsApp o con capturas reales del producto (datos de demo). Antes
         era una lista de funciones; quien abre la página quiere saber si le
         arregla un problema. Nada de cifras sin fuente ni testimonios. --}}
    @php
        $waOmar = 'https://wa.me/526682493398?text=' . urlencode('Hola Omar, vi la página de DocFacil y quiero ver cómo funcionaría en mi consultorio.');
        $videos = [
            'v1-consulta' => ['dur' => '40 s', 'titulo' => 'De la cita a la receta'],
            'v2-presupuesto' => ['dur' => '34 s', 'titulo' => 'Del odontograma al presupuesto'],
            'v3-ortodoncia' => ['dur' => '32 s', 'titulo' => 'Mensualidades de brackets'],
        ];
        $ico = [
            'tarjeta' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>',
            'escudo' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>',
            'chat' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>',
            'celular' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>',
            'check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>',
        ];
    @endphp

    {{-- 1. INICIO: la libreta y el celular --}}
    <section class="lp-hero">
        <div class="lp-wrap lp-hero-grid">
            <div class="lp-hero-txt" data-lp-reveal>
                <p class="lp-kicker">Para el consultorio dental que va empezando</p>
                <h1 class="lp-h1">Su consultorio en papel, pasado al celular.</h1>
                <p class="lp-lead">Agenda, recetas, odontograma y cobros en un solo lugar. Pruébelo 15 días gratis y yo le ayudo a cargar su agenda.</p>
                <div class="lp-ctas">
                    <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="hero" data-track-text="probar_15_dias" class="lp-btn lp-btn-primary">Probar 15 días gratis</a>
                    <a href="{{ $waOmar }}" target="_blank" rel="noopener" data-track="whatsapp_clicked" data-track-location="hero" class="lp-btn lp-btn-wa">Escribirle a Omar</a>
                </div>
            </div>
            <div class="lp-hero-media" data-lp-reveal>
                <img src="{{ asset('images/landing/hero-libreta.jpg') }}" alt="Libreta de citas de papel junto a un celular en la recepción de un consultorio" width="1200" height="900" fetchpriority="high" class="lp-hero-foto">
                <img src="{{ asset('images/landing/agenda-celular.jpg') }}" alt="La agenda de DocFácil en el celular: cada cita con su estado" width="480" height="708" loading="lazy" class="lp-hero-tel">
            </div>
        </div>
    </section>

    {{-- 2. Lo que quita el miedo, en una franja --}}
    <section class="lp-franja">
        <div class="lp-wrap lp-franja-grid">
            @foreach ([
                ['tarjeta', 'Sin tarjeta', '15 días con todo, sin dar datos de pago.'],
                ['escudo', 'Garantía de 30 días', 'Si su primer pago no le sirve, se lo devuelvo.'],
                ['chat', 'Le contesto yo', 'Soporte directo por WhatsApp con Omar.'],
                ['celular', 'En su celular', 'También en la tablet o la computadora.'],
            ] as [$i, $t, $d])
            <div class="lp-franja-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">{!! $ico[$i] !!}</svg>
                <div><strong>{{ $t }}</strong><span>{{ $d }}</span></div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- 3. LA CONSULTA (video 1) --}}
    <section id="problema" class="lp-sec">
        <div class="lp-wrap lp-split">
            <div data-lp-reveal>
                <p class="lp-dolor">“Entre apuntar, hacer la receta a mano y cobrar, se me va media consulta.”</p>
                <h2 class="lp-h2">Una consulta completa, sin papeles.</h2>
                <p class="lp-p">Al iniciar la consulta ya viene lo que se va a hacer y en qué diente. La receta sale con su cédula y el cobro ya trae el tratamiento.</p>
                <ul class="lp-lista">
                    <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">{!! $ico['check'] !!}</svg>Le avisa si el paciente es alérgico antes de recetar</li>
                    <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">{!! $ico['check'] !!}</svg>Al cerrar, le sugiere la siguiente cita</li>
                </ul>
            </div>
            @include('partials.landing-video', ['id' => 'v1-consulta', 'v' => $videos['v1-consulta']])
        </div>
    </section>

    {{-- 4. RECORDATORIOS: tres capturas reales en mosaico --}}
    <section class="lp-sec lp-tinte">
        <div class="lp-wrap">
            <div class="lp-centro" data-lp-reveal>
                <p class="lp-dolor">“Se me olvida confirmar y el sillón se queda vacío.”</p>
                <h2 class="lp-h2">El recordatorio por WhatsApp, a un clic desde su agenda.</h2>
            </div>
            <div class="lp-bento">
                <figure class="lp-celda lp-celda-a" data-lp-reveal>
                    <figcaption><strong>El paciente confirma desde su celular.</strong> Un clic en la cita abre WhatsApp con el mensaje y la liga. Sin costo por mensaje.</figcaption>
                    <img src="{{ asset('images/landing/paciente-confirma.jpg') }}" alt="Pantalla donde el paciente confirma o cancela su cita" width="560" height="1005" loading="lazy">
                </figure>
                <figure class="lp-celda lp-celda-b" data-lp-reveal>
                    <figcaption><strong>Llega, escanea el QR y a usted le avisa.</strong> Su cita queda como "llegó".</figcaption>
                    <img src="{{ asset('images/landing/check-in-qr.jpg') }}" alt="Registro con QR en la sala de espera" width="560" height="790" loading="lazy">
                </figure>
                <div class="lp-celda lp-celda-c" data-lp-reveal>
                    <strong>¿Alguien canceló?</strong>
                    <p>La lista de espera le dice quién quería ese día, y aparta la cita en un clic. <span>Plan Pro.</span></p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. PRESUPUESTO (video 2) --}}
    <section id="presupuestos" class="lp-sec">
        <div class="lp-wrap lp-split lp-rev">
            <div data-lp-reveal>
                <p class="lp-dolor">“Armar el presupuesto a mano, diente por diente, me quita la tarde.”</p>
                <h2 class="lp-h2">Su odontograma ya es su presupuesto.</h2>
                <p class="lp-p">Marca lo que encuentra, cara por cara, y con un clic sale el presupuesto con sus precios. El paciente lo acepta desde su celular y lo aceptado se agenda en un clic.</p>
                <p class="lp-nota">Incluido en todos los planes de pago, desde el Básico.</p>
            </div>
            @include('partials.landing-video', ['id' => 'v2-presupuesto', 'v' => $videos['v2-presupuesto']])
        </div>
    </section>

    {{-- 6. MENSUALIDADES (video 3): el video al centro, dos ideas a los lados --}}
    <section class="lp-sec lp-oscuro">
        <div class="lp-wrap">
            <div class="lp-centro" data-lp-reveal>
                <p class="lp-dolor lp-dolor-claro">“No sé quién me debe la mensualidad de brackets.”</p>
                <h2 class="lp-h2" style="color:#f0fdfa;">Sepa quién va al día y quién le debe.</h2>
            </div>
            <div class="lp-trio">
                <div class="lp-trio-txt" data-lp-reveal>
                    <strong>Cada pago con su fecha</strong>
                    <p>Pone el total, el enganche y las mensualidades. Ve quién va al corriente y quién tiene pagos vencidos.</p>
                </div>
                @include('partials.landing-video', ['id' => 'v3-ortodoncia', 'v' => $videos['v3-ortodoncia']])
                <div class="lp-trio-txt" data-lp-reveal>
                    <strong>Se cobra en el ajuste</strong>
                    <p>Cuando viene a su cita, la mensualidad aparece en la consulta y queda en su corte del día.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 7. EMPEZAR: Omar y "lo que sigue" --}}
    <section class="lp-sec">
        <div class="lp-wrap lp-omar-grid">
            <div class="lp-omar-carta" data-lp-reveal>
                <p class="lp-dolor">“Pasar todo del papel me va a costar trabajo.”</p>
                <h2 class="lp-h2">Usted no captura nada solo: yo le ayudo a empezar.</h2>
                <div class="lp-omar">
                    @if (file_exists(public_path('images/founder-omar-320.jpg')))
                    <img src="{{ asset('images/founder-omar-320.jpg') }}" alt="Omar Lerma, fundador de DocFácil" width="88" height="88" loading="lazy" decoding="async">
                    @endif
                    <p>Soy Omar Lerma, de Los Mochis, Sinaloa. Me manda su Excel por WhatsApp y le dejo cargados sus pacientes; si los tiene en la libreta, le ayudo a armar la lista. Cualquier duda, le contesto yo.</p>
                </div>
                <a href="{{ $waOmar }}" target="_blank" rel="noopener" data-track="whatsapp_clicked" data-track-location="founder_section" class="lp-btn lp-btn-wa">Escribirle a Omar</a>
            </div>
            <figure class="lp-sigue-fig" data-lp-reveal>
                <img src="{{ asset('images/landing/lo-que-sigue.jpg') }}" alt="En el perfil de cada paciente, lo que sigue: su próxima cita, su mensualidad y sus alergias" width="560" height="620" loading="lazy">
                <figcaption>En cada paciente, lo que sigue: a un clic.</figcaption>
            </figure>
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
                    <svg style="width:40px;height:40px;color:#b45309;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/></svg>
                    <div class="flex-1">
                        <div class="font-extrabold text-amber-800" style="font-size:14px;">Programa Fundador</div>
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
                            <strong>${{ number_format($precioFundador) }}/mes de por vida</strong>, congelado:
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
                                <div>
                    <div class="font-bold text-emerald-900">Paga anual y ahorra 2 meses</div>
                    <div class="text-sm text-emerald-800">El año le sale en 10 meses.</div>
                </div>
            </div>
            @endif

            <div class="pricing-grid grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto" data-animate>
                @php
                // Lo que trae cada plan sale de un solo lugar: lo mismo dicen el
                // folleto, el brief, la propuesta y la página de planes del panel.
                $plans = \App\Support\LoQueTraeCadaPlan::planes();
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

// Botón fijo del celular y sombra del menú: aparecen cuando el inicio
// sale de la pantalla (IntersectionObserver, sin escuchar cada scroll).
(function() {
    const hero = document.querySelector('.lp-hero');
    const stickyCta = document.getElementById('sticky-cta');
    const navbar = document.getElementById('navbar');
    if (!hero) return;
    new IntersectionObserver(([e]) => {
        const fuera = !e.isIntersecting;
        if (stickyCta) stickyCta.classList.toggle('opacity-0', !fuera), stickyCta.classList.toggle('pointer-events-none', !fuera), stickyCta.classList.toggle('translate-y-full', !fuera);
        if (navbar) navbar.classList.toggle('shadow-lg', fuera), navbar.classList.toggle('border-b', !fuera);
    }, { threshold: 0.05 }).observe(hero);
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

// Secciones nuevas: aparecen una vez, al entrar en pantalla.
const lpVisto = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('lp-visto'); lpVisto.unobserve(e.target); } });
}, { threshold: 0.15 });
document.querySelectorAll('[data-lp-reveal]').forEach(el => lpVisto.observe(el));

</script>

<x-chatbot-widget />
</body>
</html>
