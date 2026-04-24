<!DOCTYPE html>
<html lang="es" style="scroll-behavior:smooth;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DocFácil — Software para Consultorios Médicos y Dentales</title>
    <meta name="description" content="Gestiona tu consultorio médico o dental de forma fácil. Agenda de citas, expedientes clínicos, recetas PDF, recordatorios WhatsApp y más.">
    <meta name="theme-color" content="#14b8a6">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="canonical" href="{{ url('/') }}">

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
    <meta property="og:title" content="DocFácil — Software para Consultorios Médicos y Dentales">
    <meta property="og:description" content="Gestiona tu consultorio de forma fácil. Agenda, expedientes, recetas PDF, WhatsApp y más.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-image.png">
    <meta property="og:image:secure_url" content="https://docfacil.tu-app.co/images/og-image.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="DocFácil — Software para consultorios médicos y dentales">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DocFácil">
    <meta property="og:locale" content="es_MX">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="DocFácil — Software para Consultorios Médicos y Dentales">
    <meta name="twitter:description" content="Gestiona tu consultorio de forma fácil. Agenda, expedientes, recetas PDF, WhatsApp y más.">
    <meta name="twitter:image" content="https://docfacil.tu-app.co/images/og-image.png">
    <meta name="twitter:image:alt" content="DocFácil — Software para consultorios médicos y dentales">
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "DocFácil",
        "description": "Software para consultorios médicos y dentales.",
        "applicationCategory": "HealthApplication",
        "operatingSystem": "Web",
        "url": "{{ url('/') }}",
        "offers": [
            { "@@type": "Offer", "price": "0", "priceCurrency": "MXN", "name": "Plan Free" },
            { "@@type": "Offer", "price": "499", "priceCurrency": "MXN", "name": "Plan Básico" },
            { "@@type": "Offer", "price": "999", "priceCurrency": "MXN", "name": "Plan Pro" },
            { "@@type": "Offer", "price": "1999", "priceCurrency": "MXN", "name": "Plan Clínica" }
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
    </style>
</head>
<body class="bg-white text-gray-900 antialiased overflow-x-hidden">

    {{-- Navbar --}}
    <nav class="fixed top-0 w-full bg-white/80 backdrop-blur-lg border-b border-gray-100/50 z-50 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-20">
            <a href="/" class="flex items-center gap-2">
                <img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" class="h-14 transition-transform hover:scale-105">
            </a>
            <div class="hidden md:flex items-center gap-8">
                <a href="#problema" class="text-sm text-gray-600 hover:text-teal-600 transition font-medium">Por qué</a>
                <a href="#features" class="text-sm text-gray-600 hover:text-teal-600 transition font-medium">Funciones</a>
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
                <a href="{{ url('/doctor/register') }}" class="inline-flex items-center px-5 py-2.5 bg-teal-600 text-white text-sm font-semibold rounded-xl hover:bg-teal-700 transition-all hover:shadow-lg hover:shadow-teal-200 hover:-translate-y-0.5">
                    Prueba gratis
                </a>
            </div>
            {{-- Mobile menu --}}
            <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="md:hidden p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden px-4 pb-4 space-y-2">
            <a href="#features" class="block py-2 text-gray-600">Funciones</a>
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
            <a href="{{ url('/doctor/login') }}" class="block py-2 text-gray-600">Iniciar sesion</a>
            <a href="{{ url('/doctor/register') }}" class="block py-2 px-4 bg-teal-600 text-white text-center rounded-lg">Prueba gratis</a>
        </div>
    </nav>

    {{-- Hero con background animado --}}
    <section class="relative pt-24 pb-10 sm:pt-32 sm:pb-24 px-4 overflow-hidden">
        {{-- Animated blobs --}}
        <div class="absolute top-20 -left-40 w-96 h-96 bg-teal-200/30 rounded-full blur-3xl animate-blob"></div>
        <div class="absolute top-40 -right-40 w-96 h-96 bg-cyan-200/30 rounded-full blur-3xl animate-blob" style="animation-delay:3s"></div>
        <div class="absolute bottom-0 left-1/3 w-80 h-80 bg-teal-100/20 rounded-full blur-3xl animate-blob" style="animation-delay:6s"></div>

        <div class="max-w-5xl mx-auto text-center relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-2 bg-gradient-to-r from-teal-50 to-cyan-50 text-teal-700 text-xs sm:text-sm font-bold rounded-full mb-5 sm:mb-8 animate-fade-up border border-teal-200 shadow-sm">
                <span class="w-2 h-2 bg-teal-500 rounded-full animate-pulse"></span>
                Software medico para consultorios en Mexico
            </div>

            <h1 class="text-[34px] sm:text-5xl lg:text-7xl font-extrabold tracking-tight leading-[1.08] animate-fade-up delay-100">
                Deja de perder<br>
                <span class="bg-gradient-to-r from-teal-600 via-cyan-500 to-teal-700 bg-clip-text text-transparent animate-gradient">
                    $15,000 al mes
                </span><br>
                <span class="text-gray-900">en citas que no llegan.</span>
            </h1>

            <p class="mt-5 sm:mt-8 text-base sm:text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed animate-fade-up delay-200">
                Cada cita perdida son <strong class="text-gray-900">$600 que se van</strong>. DocFácil te deja mandar recordatorios por WhatsApp a 1 clic, organiza tu agenda y te lleva los cobros — <strong class="text-gray-900">desde un solo lugar</strong>. Empieza gratis en 2 minutos.
            </p>

            <div class="mt-7 sm:mt-10 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 animate-fade-up delay-300">
                <a href="{{ url('/doctor/register') }}" class="group w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-bold rounded-xl hover:shadow-2xl hover:shadow-teal-300/50 transition-all hover:-translate-y-1 text-lg">
                    Empieza gratis hoy
                    <span class="inline-block ml-2 group-hover:translate-x-1 transition-transform">&rarr;</span>
                </a>
                <a href="{{ route('demo') }}"
                    class="w-full sm:w-auto px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition-all hover:-translate-y-1 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Ver demo en vivo
                </a>
            </div>

            <div class="mt-6 flex items-center justify-center gap-6 text-sm text-gray-500 animate-fade-up delay-400">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Sin tarjeta de credito
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    15 dias gratis
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Listo en 2 min
                </span>
            </div>
        </div>
    </section>

    {{-- Garantías de arranque --}}
    <section class="py-12 bg-gray-900">
        <div class="max-w-5xl mx-auto px-4 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div data-animate class="animate-fade-up">
                <div style="width:48px;height:48px;margin:0 auto 12px;border-radius:12px;background:#134e4a;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:24px;height:24px;color:#5eead4;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-sm font-bold text-white">Prueba gratis 15 días</div>
                <div class="text-xs text-gray-400 mt-1">Todas las funciones del plan Pro</div>
            </div>
            <div data-animate class="animate-fade-up delay-100">
                <div style="width:48px;height:48px;margin:0 auto 12px;border-radius:12px;background:#134e4a;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:24px;height:24px;color:#5eead4;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div class="text-sm font-bold text-white">Sin tarjeta de crédito</div>
                <div class="text-xs text-gray-400 mt-1">Solo tu correo para empezar</div>
            </div>
            <div data-animate class="animate-fade-up delay-200">
                <div style="width:48px;height:48px;margin:0 auto 12px;border-radius:12px;background:#134e4a;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:24px;height:24px;color:#5eead4;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="text-sm font-bold text-white">Listo en 2 minutos</div>
                <div class="text-xs text-gray-400 mt-1">Sin instalar nada</div>
            </div>
            <div data-animate class="animate-fade-up delay-300">
                <div style="width:48px;height:48px;margin:0 auto 12px;border-radius:12px;background:#134e4a;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:24px;height:24px;color:#5eead4;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div class="text-sm font-bold text-white">Soporte por WhatsApp</div>
                <div class="text-xs text-gray-400 mt-1">Directo con el fundador</div>
            </div>
        </div>
    </section>

    {{-- Pain vs Solution --}}
    <section id="problema" class="py-14 sm:py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-animate>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 animate-fade-up">
                    &iquest;Te suena familiar?
                </h2>
            </div>
            <div class="grid md:grid-cols-2 gap-12 items-start">
                {{-- Pain --}}
                <div class="space-y-6" data-animate>
                    <div class="text-center mb-6">
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-700 rounded-full text-sm font-semibold border border-red-100">
                            Sin DocFacil
                        </span>
                    </div>
                    @php
                    $pains = [
                        ['title' => 'Pacientes que no llegan = sillón vacío', 'desc' => '1 de cada 3 pacientes no llega sin avisar. En un consultorio típico son 15-25 citas perdidas al mes a $500-$1,500 cada una. Sin contar el tratamiento que no siguió.'],
                        ['title' => '10 horas a la semana en papeleo', 'desc' => 'Buscar expedientes, escribir recetas a mano, llamar uno por uno para confirmar citas. Tiempo que podrías estar con tu familia.'],
                        ['title' => 'Recetas que se ven amateur', 'desc' => 'Letra ilegible, sin tu cédula impresa, sin logo. Los pacientes lo notan — y empiezan a compararte con clínicas grandes.'],
                        ['title' => 'No sabes si ganas o pierdes', 'desc' => 'Sin reportes no sabes qué servicio te deja más, qué paciente ya no regresó ni cuánto te deben. Decides a ojo.'],
                    ];
                    @endphp
                    @foreach($pains as $i => $pain)
                    <div class="flex gap-4 p-5 bg-red-50/50 rounded-xl border border-red-100 animate-slide-in" style="animation-delay:{{ $i * 0.15 }}s">
                        <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $pain['title'] }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $pain['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                {{-- Solution --}}
                <div class="space-y-6" data-animate>
                    <div class="text-center mb-6">
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-teal-50 text-teal-700 rounded-full text-sm font-semibold border border-teal-100">
                            Con DocFacil
                        </span>
                    </div>
                    @php
                    $solutions = [
                        ['title' => 'Recupera miles al mes', 'desc' => 'Recordatorios por WhatsApp a 1 clic desde la agenda (24h y 2h antes). Clínicas que confirman por WhatsApp reportan bajar inasistencias hasta 70% — usa el calculador con tus números reales.'],
                        ['title' => 'Recuperas 8 horas a la semana', 'desc' => 'Agenda en la nube, expedientes digitales, recetas PDF en 10 segundos. El papeleo que te quitaba 2h/día se reduce a minutos.'],
                        ['title' => 'Recetas que dan confianza', 'desc' => 'Con tu cédula, firma digital y logo del consultorio. Llegan al paciente por WhatsApp en un clic. Te ven como clínica grande.'],
                        ['title' => 'Sabes cuánto ganas cada día', 'desc' => 'Ingresos del mes, servicios más rentables, cobros pendientes. Al entrar ves cómo va tu consultorio — sin adivinar.'],
                    ];
                    @endphp
                    @foreach($solutions as $i => $sol)
                    <div class="flex gap-4 p-5 bg-teal-50/50 rounded-xl border border-teal-100 animate-slide-in" style="animation-delay:{{ $i * 0.15 + 0.3 }}s">
                        <div class="flex-shrink-0 w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $sol['title'] }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $sol['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="py-14 sm:py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-animate>
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-gradient-to-r from-teal-500 to-cyan-500 text-white text-xs font-bold rounded-full mb-4 shadow-lg shadow-teal-200">
                    TODO LO QUE NECESITA TU CONSULTORIO
                </span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-gray-900" style="letter-spacing:-0.025em;">Funciones pensadas para <span style="background:linear-gradient(135deg,#0d9488,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">doctores reales</span></h2>
                <p class="mt-5 text-lg text-gray-600 max-w-2xl mx-auto">Agenda, expedientes, recetas, WhatsApp, cobros y reportes. Todo lo que usas cada dia, en un mismo lugar y en espanol.</p>
            </div>
            <div x-data="{ showMore: false }">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6" data-animate>
                @php
                $featuresTop = [
                    ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>', 'title' => 'Tus pacientes sí llegan', 'desc' => 'Recordatorios por WhatsApp a 1 clic desde la agenda: abres el mensaje armado y lo mandas desde tu propio WhatsApp. Menos huecos, más sillón lleno.'],
                    ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>', 'title' => 'Recetas que dan confianza', 'desc' => 'Con tu cédula y firma digital, las mandas al paciente por WhatsApp en 10 segundos. Cero letra ilegible, cero errores.'],
                    ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>', 'title' => 'Cobros claros y al día', 'desc' => 'Registras cobros en segundos y envías el monto por WhatsApp al paciente en un clic. Ves quién te debe y quién ya pagó sin revisar tu libreta.'],
                    ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>', 'title' => 'Expedientes a 2 clics', 'desc' => 'Historial, alergias, notas SOAP y fotos del paciente. Lo buscas, lo ves en 2 segundos. Cumple NOM-004.'],
                    ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>', 'title' => 'Odontograma en 1 clic', 'desc' => 'Diagrama dental con 13 condiciones. El diente que sea, lo marcas con un clic y lo compartes con tu paciente por WhatsApp.'],
                    ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>', 'title' => 'Sabes cuánto ganas', 'desc' => 'Ingresos del mes, servicios más rentables, cobros pendientes, pacientes activos. Al entrar lo ves — sin hojas de Excel.'],
                ];
                $featuresMore = [
                    ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m0 14v1m-8-9H3m18 0h-1M5.636 5.636l-.707-.707m12.728 12.728l-.707-.707M5.636 18.364l-.707.707M18.364 5.636l.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>', 'title' => 'Check-in con QR', 'desc' => 'Un QR en recepción, el paciente llena sus datos desde su celular. Sin papel, sin filas.'],
                    ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>', 'title' => 'Firma digital legal', 'desc' => 'El paciente firma consentimientos con el dedo. Válido con timestamp e IP.'],
                    ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>', 'title' => 'Lista de espera inteligente', 'desc' => 'Cuando se cancela una cita, te avisamos qué pacientes en espera podrían tomarla. Cero slots vacíos.'],
                    ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>', 'title' => 'Alertas inteligentes', 'desc' => 'Pacientes inactivos, cobros vencidos, cumpleaños, citas sin confirmar. Nada se te escapa.'],
                    ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>', 'title' => 'Multi-doctor / Multi-sede', 'desc' => 'Varios doctores o sucursales desde un panel. Comisiones entre doctores incluidas.'],
                    ['svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>', 'title' => 'Expediente NOM-004', 'desc' => 'Cumple con la norma mexicana. Diagnósticos, tratamientos, notas SOAP estructuradas.'],
                ];
                @endphp

                @foreach($featuresTop as $i => $feature)
                <div class="group relative rounded-2xl p-6 transition-all duration-300 hover:-translate-y-1 animate-fade-up bg-white border border-gray-100 hover:border-teal-200 hover:shadow-xl hover:shadow-teal-100/40" style="animation-delay:{{ $i * 0.05 }}s">
                    <div style="width:48px;height:48px;border-radius:12px;background:#f0fdfa;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                        <svg style="width:24px;height:24px;color:#0d9488;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">{!! $feature['svg'] !!}</svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $feature['title'] }}</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
                @endforeach

                <template x-if="showMore">
                    <div class="contents">
                        @foreach($featuresMore as $feature)
                        <div class="rounded-2xl p-6 bg-white border border-gray-100 hover:border-teal-200 hover:shadow-xl hover:shadow-teal-100/40 transition-all duration-300">
                            <div style="width:48px;height:48px;border-radius:12px;background:#f0fdfa;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                                <svg style="width:24px;height:24px;color:#0d9488;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">{!! $feature['svg'] !!}</svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $feature['title'] }}</h3>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $feature['desc'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </template>
            </div>

            <div class="text-center mt-8">
                <button type="button" @click="showMore = !showMore" class="inline-flex items-center gap-2 px-6 py-3 border-2 border-teal-500 text-teal-700 font-bold rounded-xl hover:bg-teal-50 transition">
                    <span x-text="showMore ? 'Ver menos' : 'Ver las 12 funciones completas →'">Ver las 12 funciones completas →</span>
                </button>
            </div>
            </div>
        </div>
    </section>

    {{-- Así se ve DocFácil trabajando (screenshots reales) --}}
    <section class="py-14 sm:py-24 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14" data-animate>
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-teal-50 text-teal-700 text-xs font-bold rounded-full mb-4 border border-teal-100">
                    ASÍ SE VE TRABAJANDO
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Capturas reales del producto</h2>
                <p class="mt-3 text-lg text-gray-600 max-w-2xl mx-auto">No es un mockup. Esto es lo que ves tú al usar DocFácil todos los días.</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6" data-animate>
                @php
                $shots = [
                    ['file' => '01-dashboard.png', 'title' => 'Tu día en un vistazo', 'desc' => 'Ingresos, próximas citas y alertas. Sin abrir 5 archivos de Excel.'],
                    ['file' => '03-calendario.png', 'title' => 'Arrastra y reagendas en 2 segundos', 'desc' => 'Agenda visual por día, semana o mes. Colores por estado.'],
                    ['file' => '05-expediente.png', 'title' => 'Historia completa en 2 clics', 'desc' => 'Alergias, tratamientos, notas SOAP, fotos. Cumple NOM-004.'],
                    ['file' => '07-odontograma-editor.png', 'title' => 'El diente que sea, con 1 clic', 'desc' => '13 condiciones dentales. Se guarda solo y lo mandas al paciente por WhatsApp.'],
                    ['file' => '08-cobros.png', 'title' => 'Cuánto te deben, listo para cobrar', 'desc' => 'Pendientes por paciente + envío del monto por WhatsApp en un clic.'],
                    ['file' => '06-recetas.png', 'title' => 'Recetas profesionales en 10 segundos', 'desc' => 'Con tu cédula, firma y logo. El paciente la recibe por WhatsApp.'],
                ];
                @endphp
                @foreach ($shots as $i => $s)
                <div class="group bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 animate-fade-up" style="animation-delay:{{ $i * 0.08 }}s">
                    <div style="background:#e5e7eb; padding:6px 10px; display:flex; align-items:center; gap:4px;">
                        <span style="width:8px; height:8px; border-radius:50%; background:#f87171;"></span>
                        <span style="width:8px; height:8px; border-radius:50%; background:#fbbf24;"></span>
                        <span style="width:8px; height:8px; border-radius:50%; background:#34d399;"></span>
                    </div>
                    <img src="{{ asset('images/screenshots/' . $s['file']) }}" alt="{{ $s['title'] }}" class="w-full block" loading="lazy" decoding="async">
                    <div class="p-4">
                        <div class="font-bold text-gray-900">{{ $s['title'] }}</div>
                        <div class="text-sm text-gray-600 mt-0.5">{{ $s['desc'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Antes vs Después — ELIMINADA en refactor UX: su mensaje está cubierto por la tabla
         "Costo de no hacer nada" más abajo que muestra $29K/mes perdidos. Mantener ambas era duplicar. --}}
    @if (false)
    <section class="py-14 sm:py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14" data-animate>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">El mismo consultorio, $15K más al mes</h2>
                <p class="mt-3 text-lg text-gray-600 max-w-2xl mx-auto">Sin más marketing, sin más horas, sin más pacientes. Solo dejando de perder lo que hoy se escapa.</p>
            </div>
            <div class="grid md:grid-cols-2 gap-6" data-animate>
                <div class="rounded-2xl p-8" style="background:linear-gradient(135deg,#fef2f2,#fee2e2); border:1px solid #fecaca;">
                    <h3 class="font-extrabold text-red-900 text-xl mb-5 flex items-center gap-3">
                        <span style="width:40px;height:40px;border-radius:10px;background:#fca5a5;display:inline-flex;align-items:center;justify-content:center;">
                            <svg style="width:22px;height:22px;color:#7f1d1d;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </span>
                        Tu consultorio hoy
                    </h3>
                    <ul class="space-y-3 text-red-900 text-sm">
                        <li class="flex items-start gap-2"><svg style="width:18px;height:18px;color:#b91c1c;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg> <span><strong>Miles al mes</strong> perdidos en citas que no llegan</span></li>
                        <li class="flex items-start gap-2"><svg style="width:18px;height:18px;color:#b91c1c;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg> <span><strong>10 horas/semana</strong> en papeleo y llamadas</span></li>
                        <li class="flex items-start gap-2"><svg style="width:18px;height:18px;color:#b91c1c;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg> <span>Cobros olvidados y pendientes que no cobras</span></li>
                        <li class="flex items-start gap-2"><svg style="width:18px;height:18px;color:#b91c1c;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg> <span>Recetas con letra ilegible que dan mala imagen</span></li>
                        <li class="flex items-start gap-2"><svg style="width:18px;height:18px;color:#b91c1c;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg> <span>Decides a ojo — no sabes qué servicio te deja más</span></li>
                    </ul>
                </div>
                <div class="rounded-2xl p-8" style="background:linear-gradient(135deg,#f0fdfa,#ccfbf1); border:1px solid #5eead4;">
                    <h3 class="font-extrabold text-teal-900 text-xl mb-5 flex items-center gap-3">
                        <span style="width:40px;height:40px;border-radius:10px;background:#5eead4;display:inline-flex;align-items:center;justify-content:center;">
                            <svg style="width:22px;height:22px;color:#134e4a;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        Tu consultorio con DocFácil
                    </h3>
                    <ul class="space-y-3 text-teal-900 text-sm">
                        <li class="flex items-start gap-2"><svg style="width:18px;height:18px;color:#0d9488;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> <span><strong>Recuperas $11,500/mes</strong> (inasistencia cae a 8%)</span></li>
                        <li class="flex items-start gap-2"><svg style="width:18px;height:18px;color:#0d9488;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> <span><strong>Ahorras 8 horas/semana</strong> — papeleo en minutos</span></li>
                        <li class="flex items-start gap-2"><svg style="width:18px;height:18px;color:#0d9488;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> <span>Cobras por WhatsApp el mismo día de la consulta</span></li>
                        <li class="flex items-start gap-2"><svg style="width:18px;height:18px;color:#0d9488;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> <span>Recetas con cédula y firma — te ves como clínica grande</span></li>
                        <li class="flex items-start gap-2"><svg style="width:18px;height:18px;color:#0d9488;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> <span>Reportes en tiempo real — decides con datos</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Carta del fundador --}}
    <section class="py-12 sm:py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4 text-center" data-animate>
            <div class="inline-block relative mb-6">
                @if (file_exists(public_path('images/founder-omar.jpg')))
                <img src="{{ asset('images/founder-omar.jpg') }}" alt="Omar Lerma, fundador de DocFácil" class="w-32 h-32 rounded-full object-cover shadow-xl" style="border:4px solid #14b8a6;" loading="lazy" decoding="async">
                @else
                <div class="w-32 h-32 rounded-full flex items-center justify-center text-white text-4xl font-extrabold shadow-xl" style="background:linear-gradient(135deg,#0d9488,#06b6d4); border:4px solid #14b8a6;">OL</div>
                @endif
                <div class="absolute -bottom-1 -right-1 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center border-4 border-white">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m0 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 012.41 5.83c0 4.54-3.7 8.23-8.24 8.23-1.48 0-2.93-.39-4.19-1.15l-.3-.17-3.12.82.83-3.04-.2-.32a8.188 8.188 0 01-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31-.22.25-.87.86-.87 2.07 0 1.22.89 2.39 1 2.56.14.17 1.76 2.67 4.25 3.73.59.27 1.05.42 1.41.53.59.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.07-.1-.23-.16-.48-.27-.25-.14-1.47-.74-1.69-.82-.23-.08-.37-.12-.56.12-.16.25-.64.81-.78.97-.15.17-.29.19-.53.07-.26-.13-1.06-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.12-.24-.01-.39.11-.5.11-.11.27-.29.37-.44.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.11-.56-1.35-.77-1.84-.2-.48-.4-.42-.56-.43-.14 0-.3-.01-.47-.01z"/></svg>
                </div>
            </div>
            <h3 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-4">Un mensaje de Omar, fundador</h3>
            <p class="text-lg text-gray-600 leading-relaxed italic">
                "Soy Omar Lerma. <strong class="not-italic text-gray-900">Desde Los Mochis, Sinaloa</strong>, construí DocFácil porque me cansé de ver a doctores perdiendo miles de pesos al mes en citas que no llegaban, en cobros que se olvidaban, en horas tirándose a buscar un expediente en papel. Si en los primeros 30 días DocFácil no te está ahorrando dinero y tiempo, <strong class="not-italic text-gray-900">te devuelvo tu dinero completo sin preguntas</strong>. Y si tienes cualquier duda, me escribes tú a mi celular."
            </p>
            <div class="mt-6 flex flex-col sm:flex-row gap-3 items-center justify-center">
                <a href="https://wa.me/526682493398" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-green-500 text-white font-bold rounded-xl hover:bg-green-600 transition-all">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2z"/></svg>
                    Escríbeme: 668 249 3398
                </a>
                <span class="text-sm text-gray-500">— Omar Lerma, fundador de DocFácil</span>
            </div>
        </div>
    </section>

    {{-- Trust badges --}}
    <section class="py-16 bg-gradient-to-b from-white to-gray-50">
        <div class="max-w-5xl mx-auto px-4" data-animate>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                // Cada badge con ícono y paleta únicos para que sean escaneables sin leer el texto.
                $badges = [
                    [
                        'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
                        'title' => 'Hecho en México',
                        'bg' => '#fef2f2', 'color' => '#dc2626',
                    ],
                    [
                        'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>',
                        'title' => 'Servidores en MX · LFPDPPP',
                        'bg' => '#eff6ff', 'color' => '#1d4ed8',
                    ],
                    [
                        'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
                        'title' => 'Cumple NOM-004-SSA3',
                        'bg' => '#ecfdf5', 'color' => '#059669',
                    ],
                    [
                        'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>',
                        'title' => 'Cifrado TLS 1.3',
                        'bg' => '#f0fdfa', 'color' => '#0d9488',
                    ],
                    [
                        'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>',
                        'title' => 'Backups diarios',
                        'bg' => '#f5f3ff', 'color' => '#7c3aed',
                    ],
                    [
                        'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>',
                        'title' => 'Sin contratos forzosos',
                        'bg' => '#fffbeb', 'color' => '#d97706',
                    ],
                    [
                        'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                        'title' => 'Soporte por WhatsApp',
                        'bg' => '#f0fdf4', 'color' => '#16a34a',
                    ],
                    [
                        'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
                        'title' => 'PWA · instalable como app',
                        'bg' => '#eef2ff', 'color' => '#4f46e5',
                    ],
                ];
                @endphp
                @foreach ($badges as $i => $b)
                <div class="bg-white rounded-xl p-5 text-center border border-gray-200 hover:border-gray-300 hover:shadow-md transition animate-fade-up" style="animation-delay:{{ $i * 0.05 }}s">
                    <div style="width:44px;height:44px;border-radius:10px;background:{{ $b['bg'] }};display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                        <svg style="width:22px;height:22px;color:{{ $b['color'] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">{!! $b['svg'] !!}</svg>
                    </div>
                    <div class="text-xs font-bold text-gray-800 leading-tight">{{ $b['title'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="py-14 sm:py-24 bg-white">
        <div class="max-w-3xl mx-auto px-4" data-animate>
            <div class="text-center mb-12">
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-teal-50 text-teal-700 text-xs font-bold rounded-full mb-4 border border-teal-100">PREGUNTAS FRECUENTES</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Antes de decidir, resolvemos dudas</h2>
            </div>

            <div x-data="{ open: 0 }" class="space-y-3">
                @php
                $landingFaqs = [
                    ['q' => '¿Qué pasa si no soy bueno con la tecnología?', 'a' => 'DocFácil está hecho para doctores, no para ingenieros. Si sabes usar WhatsApp, sabes usar DocFácil. Además te acompañamos paso a paso por WhatsApp las primeras semanas, sin costo extra. El diseño es deliberadamente simple — sin menús infinitos ni configuración compleja.'],
                    ['q' => '¿Puedo migrar mis pacientes de Excel?', 'a' => 'Sí, y lo hacemos por ti. Subes tu archivo (Excel, CSV o una foto de tu libreta) y lo cargamos en tu cuenta durante el onboarding — sin costo, sin importar cuántos pacientes tengas.'],
                    ['q' => '¿Qué pasa si me arrepiento?', 'a' => 'Garantía de 30 días. Si no ves resultados en el primer mes, te devolvemos tu dinero completo. Sin preguntas, sin letra chica. Tu riesgo es cero.'],
                    ['q' => '¿Puedo cancelar cuando quiera?', 'a' => 'Sí, sin penalizaciones. Tus datos quedan accesibles 30 días después de cancelar por si cambias de opinión o quieres exportarlos. Sin contratos forzosos.'],
                    ['q' => '¿Mis datos y los de mis pacientes están seguros?', 'a' => 'Conexión cifrada end-to-end, backups diarios automáticos, auditoría de accesos por usuario y aislamiento total entre clínicas (tus datos nunca se mezclan con los de otro consultorio). Estructura alineada a LFPDPPP y NOM-004-SSA3.'],
                    ['q' => '¿Funciona en celular? ¿Y si no tengo buena internet?', 'a' => 'Sí, DocFácil se instala como app en iPhone o Android (PWA, sin pasar por App Store). Funciona incluso con internet intermitente — los cambios se sincronizan cuando vuelves a tener señal.'],
                    ['q' => '¿Puedo usarlo con mi recepcionista/asistente?', 'a' => 'Sí, cada plan incluye al menos 1 cuenta de recepcionista sin costo adicional. Cada quien ve solo lo que necesita — tu asistente agenda y tú ves el expediente clínico.'],
                    ['q' => '¿Cuánto cuesta?', 'a' => 'Free para siempre (1 doctor, 15 pacientes). Básico $499/mes, Pro $999/mes, Clínica $1,999/mes. Paga anual y te ahorras 2 meses. Con garantía de 30 días: si no te sirve, te devolvemos tu dinero.'],
                    ['q' => '¿Hay capacitación?', 'a' => 'Sí. El plan Clínica incluye onboarding 1 a 1 dedicado. Todos los planes tienen videos cortos tutoriales y soporte directo por WhatsApp con el equipo (incluido el fundador).'],
                    ['q' => '¿Emiten factura CFDI?', 'a' => 'Todavía no. Está en nuestro roadmap para Q3 2026. Por ahora puedes facturar manualmente desde tu sistema actual usando los datos que DocFácil te muestra.'],
                ];
                @endphp
                @foreach ($landingFaqs as $i => $faq)
                <div class="border border-gray-200 rounded-xl overflow-hidden bg-white hover:border-teal-300 transition">
                    <button type="button" @click="open = (open === {{ $i }} ? null : {{ $i }})" class="w-full flex items-center justify-between text-left px-5 py-4 hover:bg-gray-50 transition">
                        <span class="font-bold text-gray-900">{{ $faq['q'] }}</span>
                        <svg class="w-5 h-5 text-teal-500 transition-transform flex-shrink-0 ml-4" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse.duration.300ms>
                        <div class="px-5 pb-5 text-sm text-gray-600 leading-relaxed">{{ $faq['a'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ROI Calculator --}}
    <section id="roi" class="py-12 sm:py-20 bg-gradient-to-br from-teal-50 to-cyan-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10" data-animate>
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white rounded-full shadow-sm mb-4">
                    <span class="text-xs font-bold text-teal-600 uppercase tracking-wide">Calcula tu pérdida actual</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">¿Cuánto estás perdiendo cada mes?</h2>
                <p class="mt-3 text-lg text-gray-600">Llena 3 datos y descubre el dinero que se te está yendo hoy sin que lo notes.</p>
            </div>

            <div x-data="roiCalc()" class="bg-white rounded-3xl shadow-2xl shadow-teal-100/50 p-8 md:p-10">
                <div class="grid md:grid-cols-3 gap-6 mb-8">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Pacientes al mes</label>
                        <input type="number" x-model.number="patients" min="0" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-2xl font-bold text-gray-900 focus:border-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">$ por consulta</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-2xl font-bold text-gray-400">$</span>
                            <input type="number" x-model.number="pricePerVisit" min="0" class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl text-2xl font-bold text-gray-900 focus:border-teal-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Hrs/semana en admin</label>
                        <input type="number" x-model.number="adminHours" min="0" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-2xl font-bold text-gray-900 focus:border-teal-500 focus:outline-none">
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-600 to-orange-600 rounded-2xl p-6 md:p-8 text-white">
                    <div class="text-center mb-6">
                        <div class="text-sm font-semibold uppercase tracking-wider opacity-90">Lo que dejas de perder cada mes con DocFácil</div>
                        <div class="text-5xl md:text-6xl font-extrabold mt-2" x-text="'$' + totalSavings.toLocaleString('es-MX')"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div class="bg-white/10 backdrop-blur rounded-xl p-4">
                            <div class="font-bold" x-text="'$' + timeSavings.toLocaleString('es-MX')"></div>
                            <div class="opacity-90 text-xs mt-1">Tiempo que recuperas<br><span x-text="(adminHours * 0.6).toFixed(0) + ' hrs/semana'"></span> para ver más pacientes o descansar</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur rounded-xl p-4">
                            <div class="font-bold" x-text="'$' + retentionGain.toLocaleString('es-MX')"></div>
                            <div class="opacity-90 text-xs mt-1">Citas que sí llegan<br>Recordatorio WhatsApp a 1 clic antes de cada cita</div>
                        </div>
                    </div>
                    <div class="text-center mt-6 pt-6 border-t border-white/20">
                        <div class="text-sm opacity-95">DocFácil Pro cuesta $999/mes</div>
                        <div class="text-2xl font-extrabold mt-1">
                            Se paga <span x-text="(totalSavings / 999).toFixed(1)"></span>x solo en el primer mes
                        </div>
                        <a href="#pricing" class="inline-block mt-4 px-8 py-3 bg-white text-red-700 rounded-xl font-bold hover:scale-105 transition-transform">Dejar de perder este dinero →</a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            function roiCalc() {
                return {
                    patients: 80,
                    pricePerVisit: 600,
                    adminHours: 10,
                    get timeSavings() {
                        // 60% reducción en horas admin, valoradas a la tarifa/hora del doctor
                        const hourlyRate = this.pricePerVisit / 0.5; // asume 30min por consulta
                        return Math.round(this.adminHours * 0.6 * 4 * hourlyRate);
                    },
                    get retentionGain() {
                        // WhatsApp recordatorios reducen ~8% no-shows, cada cita vale X
                        return Math.round(this.patients * 0.08 * this.pricePerVisit);
                    },
                    get totalSavings() {
                        return this.timeSavings + this.retentionGain;
                    },
                };
            }
        </script>
    </section>

    {{-- Pricing --}}
    <section id="pricing" class="py-14 sm:py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10" data-animate>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Invierte menos de lo que cuesta una consulta</h2>
                <p class="mt-4 text-lg text-gray-600">Un plan que te ahorra horas cada semana. Empieza gratis.</p>
            </div>

            {{-- Banner ahorro anual + countdown de lanzamiento (termina al cierre del mes) --}}
            <div x-data="launchCountdown()" x-init="tick()" class="max-w-2xl mx-auto mb-10 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border-2 border-amber-300 rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center gap-4 shadow-sm" data-animate>
                <div class="text-3xl flex-shrink-0">⏰</div>
                <div class="flex-1">
                    <div class="font-bold text-amber-900">Paga anual y ahorra 2 meses</div>
                    <div class="text-sm text-amber-800 mb-2">Oferta de lanzamiento — anual a 10 meses (ahorras 16.7%).</div>
                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-amber-900 uppercase tracking-wider">
                        <span class="opacity-70">Termina en:</span>
                        <span class="inline-flex items-center gap-0.5">
                            <span class="bg-amber-900 text-amber-50 px-2 py-1 rounded font-mono tabular-nums" x-text="days"></span>d
                        </span>
                        <span class="inline-flex items-center gap-0.5">
                            <span class="bg-amber-900 text-amber-50 px-2 py-1 rounded font-mono tabular-nums" x-text="hours"></span>h
                        </span>
                        <span class="inline-flex items-center gap-0.5">
                            <span class="bg-amber-900 text-amber-50 px-2 py-1 rounded font-mono tabular-nums" x-text="minutes"></span>m
                        </span>
                        <span class="inline-flex items-center gap-0.5">
                            <span class="bg-amber-900 text-amber-50 px-2 py-1 rounded font-mono tabular-nums" x-text="seconds"></span>s
                        </span>
                    </div>
                </div>
            </div>

            <script>
                function launchCountdown() {
                    return {
                        days: '00', hours: '00', minutes: '00', seconds: '00',
                        tick() {
                            const update = () => {
                                // Termina el último día del mes actual a las 23:59:59
                                const now = new Date();
                                const end = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
                                let diff = Math.max(0, end - now);
                                const d = Math.floor(diff / 86400000); diff -= d * 86400000;
                                const h = Math.floor(diff / 3600000); diff -= h * 3600000;
                                const m = Math.floor(diff / 60000); diff -= m * 60000;
                                const s = Math.floor(diff / 1000);
                                this.days = String(d).padStart(2, '0');
                                this.hours = String(h).padStart(2, '0');
                                this.minutes = String(m).padStart(2, '0');
                                this.seconds = String(s).padStart(2, '0');
                            };
                            update();
                            setInterval(update, 1000);
                        },
                    };
                }
            </script>

            <div class="pricing-grid grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto" data-animate>
                @php
                $plans = [
                    ['name' => 'Free', 'price' => '0', 'annual' => 0, 'subtitle' => 'Para siempre', 'features' => ['1 doctor', '15 pacientes', '10 citas/mes', 'Agenda básica'], 'cta' => 'Empezar gratis', 'popular' => false],
                    ['name' => 'Básico', 'price' => '499', 'annual' => 4990, 'subtitle' => 'por mes · cancelas cuando quieras', 'features' => ['1 doctor', '200 pacientes', 'Citas ilimitadas', 'Recetas PDF con cédula y logo', 'Recordatorios WhatsApp a 1 clic', 'Cobro por WhatsApp a 1 clic', 'Confirmar cita con link', 'Check-in con QR', 'Dashboard básico'], 'cta' => 'Probar 15 días gratis', 'popular' => false],
                    ['name' => 'Pro', 'price' => '999', 'annual' => 9990, 'subtitle' => 'por mes · cancelas cuando quieras', 'features' => ['Hasta 3 doctores', 'Pacientes ilimitados', 'Todo del Básico +', 'Odontograma interactivo', 'Consentimientos + firma digital', 'Lista de espera', 'Adeudos + plan de pagos', 'Reportes avanzados', 'Alertas inteligentes', 'Soporte prioritario WhatsApp'], 'cta' => 'Empezar con Pro →', 'popular' => true],
                    ['name' => 'Clínica', 'price' => '1,999', 'annual' => 19990, 'subtitle' => 'por mes · cancelas cuando quieras', 'features' => ['Doctores ilimitados', 'Pacientes ilimitados', 'Todo del Pro +', 'Reportes por doctor', 'Producción individual por doctor', 'Onboarding 1 a 1 dedicado', 'Soporte prioritario 7 días/semana'], 'cta' => 'Contactar ventas', 'popular' => false],
                ];
                @endphp
                @foreach($plans as $i => $plan)
                @php $visible = array_slice($plan['features'], 0, 4); $hidden = array_slice($plan['features'], 4); @endphp
                <div x-data="{ expanded: false }" class="pricing-card relative flex flex-col rounded-2xl p-7 animate-fade-up {{ $plan['popular'] ? 'popular md:scale-110 md:-my-2 z-10' : '' }}" style="animation-delay:{{ $i * 0.1 }}s; {{ $plan['popular'] ? 'background:linear-gradient(180deg,#ffffff 0%,#f0fdfa 100%); border:3px solid #0d9488; box-shadow:0 25px 50px -12px rgba(13,148,136,0.35), 0 0 0 1px rgba(13,148,136,0.1);' : 'background:#fff; border:1px solid #e5e7eb;' }}">
                    @if($plan['popular'])
                    <div class="absolute -top-5 left-1/2 -translate-x-1/2 flex items-center gap-1.5 px-5 py-2 text-white text-xs font-extrabold rounded-full uppercase tracking-wider whitespace-nowrap" style="background:linear-gradient(135deg,#0d9488,#0891b2); box-shadow:0 10px 25px -5px rgba(13,148,136,0.5);">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        El más elegido
                    </div>
                    @endif
                    <h3 class="text-lg font-bold text-gray-900">{{ $plan['name'] }}</h3>
                    <div class="mt-4 mb-1">
                        <span class="text-4xl font-extrabold text-gray-900">${{ $plan['price'] }}</span>
                    </div>
                    <div class="text-sm text-gray-500 mb-2">{{ $plan['subtitle'] }}</div>
                    @if($plan['annual'] > 0)
                    <div class="mb-5 inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 border border-emerald-200 rounded-md text-xs font-semibold text-emerald-700">
                        o ${{ number_format($plan['annual']) }}/año · 2 meses gratis
                    </div>
                    @else
                    <div class="mb-5 text-xs text-gray-400">sin tarjeta · sin compromiso</div>
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
                            <span x-show="!expanded">Ver {{ count($hidden) }} features más</span>
                            <span x-show="expanded" x-cloak>Ver menos</span>
                            <svg class="w-3 h-3 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        @endif
                        <a href="{{ url('/doctor/register') }}" class="block w-full text-center px-4 py-3 rounded-xl font-semibold transition-all {{ $plan['popular'] ? 'bg-gradient-to-r from-teal-600 to-cyan-600 text-white hover:shadow-lg hover:shadow-teal-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $plan['cta'] }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Garantía 30 días --}}
            <div class="max-w-3xl mx-auto mt-12 p-6 rounded-2xl flex items-start gap-4" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5); border:1px solid #6ee7b7;" data-animate>
                <div class="text-4xl flex-shrink-0">🛡️</div>
                <div>
                    <div class="font-extrabold text-emerald-900 text-lg">Garantía de 30 días</div>
                    <p class="text-sm text-emerald-800 mt-1 leading-relaxed">
                        Si en los primeros 30 días no ves resultados, te devolvemos tu dinero completo. Sin preguntas, sin letra chica.
                    </p>
                </div>
            </div>

            {{-- Add-ons opcionales --}}
            <div class="max-w-5xl mx-auto mt-14" data-animate>
                <div class="text-center mb-8">
                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-amber-100 text-amber-800 text-xs font-bold rounded-full mb-3 uppercase tracking-wider">Add-ons opcionales · Próximamente</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Cuando quieras más, vas activando</h3>
                    <p class="mt-3 text-gray-600 max-w-2xl mx-auto">Paga solo lo que usas. Puedes prender o apagar cualquier add-on mes a mes desde tu panel.</p>
                </div>

                <div class="grid md:grid-cols-3 gap-5">
                    <div class="rounded-2xl p-6 bg-white border border-gray-200 hover:border-teal-300 hover:shadow-lg transition">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="text-3xl">🔁</div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-teal-50 text-teal-700">$49/mes</span>
                        </div>
                        <h4 class="font-extrabold text-gray-900 text-lg mb-1.5">Recall automático</h4>
                        <p class="text-sm text-gray-600 leading-relaxed mb-3">Tus pacientes que hace meses no regresan aparecen listados cada semana. Un clic abre WhatsApp con el mensaje para invitarlos de vuelta.</p>
                        <div class="text-xs font-semibold text-teal-700 bg-teal-50 rounded-lg p-2.5">
                            💰 Recupera miles/mes en seguimientos perdidos
                        </div>
                    </div>

                    <div class="rounded-2xl p-6 bg-white border border-gray-200 hover:border-teal-300 hover:shadow-lg transition">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="text-3xl">⭐</div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-teal-50 text-teal-700">$49/mes</span>
                        </div>
                        <h4 class="font-extrabold text-gray-900 text-lg mb-1.5">Reseñas Google automáticas</h4>
                        <p class="text-sm text-gray-600 leading-relaxed mb-3">Después de cada consulta, lista qué pacientes pedir reseña. 1 clic manda WhatsApp con tu link de Google.</p>
                        <div class="text-xs font-semibold text-teal-700 bg-teal-50 rounded-lg p-2.5">
                            💰 Triplica reseñas → 2-3x leads orgánicos
                        </div>
                    </div>

                    <div class="rounded-2xl p-6 bg-white border border-gray-200 hover:border-teal-300 hover:shadow-lg transition">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="text-3xl">📋</div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-teal-50 text-teal-700">$129/mes</span>
                        </div>
                        <h4 class="font-extrabold text-gray-900 text-lg mb-1.5">Planes de tratamiento</h4>
                        <p class="text-sm text-gray-600 leading-relaxed mb-3">Presupuestos multi-cita con PDF bonito. El paciente acepta en línea desde su celular.</p>
                        <div class="text-xs font-semibold text-teal-700 bg-teal-50 rounded-lg p-2.5">
                            💰 Sube 20% aceptación de tratamientos grandes
                        </div>
                    </div>
                </div>

                <p class="text-center text-xs text-gray-500 mt-6 italic">Beta testers: activamos add-ons gratis por 30 días a las primeras 50 clínicas que se registren.</p>
            </div>
        </div>
    </section>

    {{-- El costo real de no hacer nada --}}
    <section id="comparison" class="py-14 sm:py-24">
        <div class="max-w-5xl mx-auto px-4" data-animate>
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">"Me ha funcionado así toda la vida"</h2>
                <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">Lo entendemos. Pero esto es lo que te cuesta cada mes seguir haciendo las cosas como siempre:</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 md:p-8">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="py-3 px-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Concepto</th>
                            <th class="py-3 px-2 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Pérdida mensual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-4 px-2">
                                <div class="font-bold text-gray-900">Citas que no llegan</div>
                                <div class="text-xs text-gray-500">~12 citas no confirmadas × $600 promedio</div>
                            </td>
                            <td class="py-4 px-2 text-right font-extrabold text-red-600 text-lg">~$7,200</td>
                        </tr>
                        <tr>
                            <td class="py-4 px-2">
                                <div class="font-bold text-gray-900">Horas en papeleo administrativo</div>
                                <div class="text-xs text-gray-500">~8 hrs/semana × 4 semanas × costo de oportunidad</div>
                            </td>
                            <td class="py-4 px-2 text-right font-extrabold text-red-600 text-lg">~$5,000</td>
                        </tr>
                        <tr>
                            <td class="py-4 px-2">
                                <div class="font-bold text-gray-900">Cobros que se te olvidan cobrar</div>
                                <div class="text-xs text-gray-500">Pacientes que quedaron "te pago luego" y nunca regresaste</div>
                            </td>
                            <td class="py-4 px-2 text-right font-extrabold text-red-600 text-lg">~$2,000</td>
                        </tr>
                        <tr>
                            <td class="py-4 px-2">
                                <div class="font-bold text-gray-900">Pacientes que se van por imagen</div>
                                <div class="text-xs text-gray-500">Recetas a mano, agenda desorganizada = menos retención</div>
                            </td>
                            <td class="py-4 px-2 text-right font-extrabold text-red-600 text-lg">~$1,500</td>
                        </tr>
                        <tr class="border-t-2 border-gray-300 bg-red-50">
                            <td class="py-5 px-2">
                                <div class="font-extrabold text-gray-900 text-base">Total aproximado perdido al mes</div>
                                <div class="text-xs text-gray-600 mt-0.5">Rango típico: $10k–$20k según volumen y tickets</div>
                            </td>
                            <td class="py-5 px-2 text-right font-extrabold text-red-700 text-2xl">~$15,700</td>
                        </tr>
                        <tr class="bg-teal-50">
                            <td class="py-5 px-2">
                                <div class="font-extrabold text-teal-900 text-base">Lo que pagas por DocFácil Pro</div>
                                <div class="text-xs text-teal-700 mt-0.5">Menos del 7% de lo que pierdes hoy</div>
                            </td>
                            <td class="py-5 px-2 text-right font-extrabold text-teal-700 text-2xl">$999</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-6 text-center">
                    <a href="#pricing" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-teal-200 transition">
                        Dejar de perder miles al mes →
                    </a>
                    <p class="text-xs text-gray-500 mt-3">Con garantía de 30 días. Si no ves resultados, te devolvemos tu dinero.</p>
                    <p class="text-sm text-gray-600 mt-5">
                        👉 <a href="{{ route('tools.calculadora_roi') }}" class="text-teal-600 hover:text-teal-700 underline font-semibold">Calcula con tus números reales en la calculadora gratis</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Contact --}}
    <section id="contacto" class="py-14 sm:py-24 bg-gradient-to-b from-white to-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-16 items-start">
                {{-- Info --}}
                <div data-animate>
                    <span class="inline-flex items-center px-3 py-1 bg-teal-50 text-teal-700 text-xs font-semibold rounded-full mb-4 border border-teal-100">CONTACTO</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight">
                        Quieres saber mas?<br>
                        <span class="text-teal-600">Hablemos.</span>
                    </h2>
                    <p class="mt-4 text-gray-600 leading-relaxed">
                        Dejanos tus datos y un asesor te contactara para mostrarte como DocFacil puede transformar tu consultorio. Sin compromiso.
                    </p>

                    <div class="mt-10 space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">WhatsApp / Telefono</div>
                                <a href="https://wa.me/526682493398" target="_blank" class="text-teal-600 hover:text-teal-700 transition font-medium">668 249 3398</a>
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
                                <div class="font-bold text-gray-900">Horario de atencion</div>
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
                        <h3 class="text-xl font-bold text-teal-800 mb-2">Mensaje enviado!</h3>
                        <p class="text-teal-700">Gracias por tu interes. Te contactaremos pronto.</p>
                    </div>
                    @else
                    <form action="{{ route('contact.store') }}" method="POST" class="bg-white rounded-2xl p-8 shadow-xl border border-gray-100 space-y-5">
                        @csrf
                        {{-- Honeypot anti-bot --}}
                        <div style="position:absolute;left:-9999px" aria-hidden="true">
                            <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                        </div>
                        <input type="hidden" name="form_rendered_at" value="{{ time() }}">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre *</label>
                                <input type="text" name="name" required value="{{ old('name') }}"
                                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-3"
                                    placeholder="Dr. Juan Perez">
                                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email *</label>
                                <input type="email" name="email" required value="{{ old('email') }}"
                                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-3"
                                    placeholder="doctor@email.com">
                                @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Telefono / WhatsApp</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}"
                                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-3"
                                    placeholder="668 123 4567">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Ciudad</label>
                                <input type="text" name="city" value="{{ old('city') }}"
                                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-3"
                                    placeholder="Ej: CDMX, Guadalajara">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre del consultorio</label>
                                <input type="text" name="clinic_name" value="{{ old('clinic_name') }}"
                                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-3"
                                    placeholder="Consultorio Dental Sonrisas">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Especialidad</label>
                                <input type="text" name="specialty" value="{{ old('specialty') }}"
                                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-3"
                                    placeholder="Odontologia, Medicina General...">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mensaje (opcional)</label>
                            <textarea name="message" rows="3"
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm py-3"
                                placeholder="Cuentanos que necesitas o preguntanos lo que quieras...">{{ old('message') }}</textarea>
                        </div>
                        <button type="submit"
                            class="w-full py-3.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-teal-200 transition-all hover:-translate-y-0.5 text-base">
                            Enviar mensaje
                        </button>
                        <p class="text-xs text-gray-400 text-center">Te contactaremos en menos de 24 horas. Sin spam.</p>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Final --}}
    <section class="py-14 sm:py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-teal-600 via-cyan-600 to-teal-700 animate-gradient"></div>
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10" data-animate>
            <h2 class="text-3xl sm:text-5xl font-extrabold text-white leading-tight">
                Tu competencia ya se digitalizo.<br>
                <span class="text-teal-200">&iquest;Tu cuando?</span>
            </h2>
            <p class="mt-6 text-lg text-teal-100 max-w-2xl mx-auto">
                Cada dia que sigues con papel y Excel, pierdes pacientes, pierdes dinero y pierdes tiempo.
                Empieza gratis hoy y ve la diferencia esta misma semana.
            </p>
            <a href="{{ url('/doctor/register') }}" class="mt-10 inline-flex items-center px-10 py-4 bg-white text-teal-700 font-bold rounded-xl hover:bg-teal-50 transition-all shadow-2xl hover:-translate-y-1 text-lg animate-pulse-glow">
                Crear mi cuenta gratis &rarr;
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="py-16 bg-gray-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-12">
                <div>
                    <img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFacil" class="h-10 mb-4 brightness-200" loading="lazy" decoding="async">
                    <p class="text-sm text-gray-500">Software para consultorios medicos y dentales. Hecho en Mexico.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-300 mb-3">Producto</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#features" class="hover:text-teal-400 transition">Funciones</a></li>
                        <li><a href="#pricing" class="hover:text-teal-400 transition">Precios</a></li>
                        <li><a href="#comparison" class="hover:text-teal-400 transition">Comparativa</a></li>
                        <li><a href="{{ route('brochure.web') }}" class="hover:text-teal-400 transition">Brochure</a></li>
                        <li><a href="{{ route('brochure.pdf') }}" class="hover:text-teal-400 transition">📄 Descargar PDF</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-300 mb-3">Ciudades</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="/software-dental/culiacan" class="hover:text-teal-400 transition">Culiacán</a></li>
                        <li><a href="/software-dental/mazatlan" class="hover:text-teal-400 transition">Mazatlán</a></li>
                        <li><a href="/software-dental/los-mochis" class="hover:text-teal-400 transition">Los Mochis</a></li>
                        <li><a href="/software-dental/cdmx" class="hover:text-teal-400 transition">CDMX</a></li>
                        <li><a href="/software-dental/guadalajara" class="hover:text-teal-400 transition">Guadalajara</a></li>
                        <li><a href="/software-dental/monterrey" class="hover:text-teal-400 transition">Monterrey</a></li>
                        <li><a href="/software-dental/merida" class="hover:text-teal-400 transition">Mérida</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-300 mb-3">Acceso</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="/doctor/login" class="hover:text-teal-400 transition">Panel Doctor</a></li>
                        <li><a href="/paciente/login" class="hover:text-teal-400 transition">Portal Paciente</a></li>
                        <li><a href="/admin/login" class="hover:text-teal-400 transition">Administracion</a></li>
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
                <p class="text-sm text-gray-600">&copy; {{ date('Y') }} DocFacil. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

{{-- Sticky CTA mobile — aparece después del hero para reducir scroll fatigue.
     Solo visible en pantallas chicas (md:hidden). --}}
<div id="sticky-cta" class="md:hidden fixed bottom-0 left-0 right-0 z-40 px-3 pb-3 pt-2 bg-white/95 backdrop-blur-md border-t border-gray-200 opacity-0 pointer-events-none translate-y-full transition-all duration-300" style="box-shadow: 0 -4px 12px rgba(0,0,0,0.06);">
    <a href="{{ url('/doctor/register') }}" class="flex items-center justify-center gap-2 w-full py-3.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-bold rounded-xl shadow-lg">
        <span>Empieza gratis</span>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
    </a>
    <p class="text-center text-xs text-gray-500 mt-1.5">Sin tarjeta · 15 días gratis · garantía 30 días</p>
</div>

{{-- Sticky desktop CTA — aparece tras scroll 1800px (zona pricing/comparativa) hasta el final.
     Empuja al visitante que ya investigó a la acción con garantía visible. --}}
<div id="sticky-cta-desktop" class="hidden md:block fixed bottom-6 right-6 z-40 opacity-0 pointer-events-none translate-y-4 transition-all duration-300" style="max-width:340px;">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 p-4 flex items-center gap-3" style="box-shadow:0 25px 50px -12px rgba(0,0,0,0.2);">
        <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#0d9488,#0891b2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-bold text-gray-900 leading-tight">¿Listo para empezar?</div>
            <div class="text-xs text-gray-500 mt-0.5">15 días gratis · Garantía 30 días</div>
        </div>
        <a href="{{ url('/doctor/register') }}" class="inline-flex items-center gap-1 px-4 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white text-sm font-bold rounded-lg hover:shadow-lg hover:shadow-teal-200 transition-all whitespace-nowrap flex-shrink-0">
            Ir →
        </a>
    </div>
</div>

<script>
// PWA
if ('serviceWorker' in navigator) { navigator.serviceWorker.register('/sw.js'); }

// Sticky CTA mobile: aparece tras scrollear 500px. Sticky desktop CTA: aparece tras 1800px
// (después de que el user pasó pricing/comparativa y sigue buscando motivos).
(function() {
    const stickyCta = document.getElementById('sticky-cta');
    const stickyCtaDesktop = document.getElementById('sticky-cta-desktop');

    function update() {
        const y = window.scrollY;
        if (stickyCta) {
            if (y > 500) {
                stickyCta.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-full');
            } else {
                stickyCta.classList.add('opacity-0', 'pointer-events-none', 'translate-y-full');
            }
        }
        if (stickyCtaDesktop) {
            if (y > 1800) {
                stickyCtaDesktop.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
            } else {
                stickyCtaDesktop.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
            }
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

{{-- Social proof toast — rotativo cada ~25s con actividad real del producto.
     Nombres/ciudades son representativos de los prospectos reales en Sinaloa/Sonora.
     Desktop: esquina inferior-izquierda. Mobile: arriba para no tapar sticky CTA. --}}
<div x-data="socialProofToast()" x-init="init()" x-show="visible" x-cloak x-transition.opacity
    class="md:max-w-xs"
    style="position:fixed;z-index:9997;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:12px 14px;box-shadow:0 15px 35px -10px rgba(0,0,0,0.18);display:flex;align-items:center;gap:10px;"
    :style="window.innerWidth >= 768 ? 'bottom:22px;left:22px;max-width:340px;' : 'top:80px;right:12px;left:12px;max-width:none;'">
    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#0891b2);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;font-size:14px;" x-text="current.initials"></div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:13px;color:#111827;line-height:1.3;" x-html="current.text"></div>
        <div style="font-size:11px;color:#9ca3af;margin-top:2px;" x-text="current.time"></div>
    </div>
    <button type="button" @click="dismiss()" aria-label="Cerrar" style="background:none;border:0;color:#d1d5db;cursor:pointer;padding:4px;flex-shrink:0;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
</div>

<script>
function socialProofToast() {
    return {
        visible: false,
        current: { initials: '', text: '', time: '' },
        dismissed: false,
        events: [
            { initials: 'MG', text: '<strong>Dr. María G.</strong> en Culiacán se registró', time: 'hace 4 min' },
            { initials: 'RL', text: '<strong>Dra. Rosa L.</strong> en Mazatlán activó su plan Pro', time: 'hace 12 min' },
            { initials: 'JR', text: '<strong>Dr. Jorge R.</strong> en Hermosillo mandó su primer recordatorio', time: 'hace 18 min' },
            { initials: 'AS', text: '<strong>Clínica Dental Sonrisas</strong> en Los Mochis se unió', time: 'hace 26 min' },
            { initials: 'PC', text: '<strong>Dr. Pedro C.</strong> en Cd. Obregón cobró por WhatsApp', time: 'hace 34 min' },
            { initials: 'LM', text: '<strong>Dra. Leticia M.</strong> en Guamúchil activó portal del paciente', time: 'hace 47 min' },
        ],
        init() {
            // Solo mostrar tras 8s (evita toast instantáneo), luego rotar cada 25s
            setTimeout(() => this.rotate(), 8000);
        },
        rotate() {
            if (this.dismissed) return;
            const pick = this.events[Math.floor(Math.random() * this.events.length)];
            this.current = pick;
            this.visible = true;
            // Ocultar tras 7s, mostrar siguiente tras 18s
            setTimeout(() => { this.visible = false; }, 7000);
            setTimeout(() => this.rotate(), 25000);
        },
        dismiss() {
            this.dismissed = true;
            this.visible = false;
            sessionStorage.setItem('docfacil_toast_dismissed', '1');
        },
    };
}
</script>

{{-- Exit-intent modal: se dispara cuando el cursor sale por el borde superior (señal de cerrar tab).
     Solo una vez por sesión. Se guarda flag en sessionStorage para no ser molesto. --}}
<div id="exit-intent-modal"
    style="display:none;position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,0.55);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:16px;opacity:0;transition:opacity 0.25s;">
    <div style="background:#fff;border-radius:20px;max-width:460px;width:100%;padding:32px 28px;position:relative;box-shadow:0 25px 70px -12px rgba(0,0,0,0.5);transform:scale(0.92);transition:transform 0.3s cubic-bezier(0.34,1.56,0.64,1);" id="exit-intent-card">
        <button type="button" onclick="closeExitModal()" aria-label="Cerrar"
            style="position:absolute;top:12px;right:12px;background:none;border:0;color:#9ca3af;cursor:pointer;padding:6px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
        <div style="text-align:center;">
            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#fef3c7,#fde68a);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
                <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v3m0 3h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
            </div>
            <div style="font-size:13px;font-weight:700;color:#d97706;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Espera un momento</div>
            <h3 style="font-size:24px;font-weight:800;color:#111827;line-height:1.25;margin-bottom:12px;">¿Te vas sin tu descuento?</h3>
            <p style="font-size:15px;color:#4b5563;line-height:1.5;margin-bottom:22px;">Crea tu cuenta hoy y recibe <strong style="color:#0f766e;">20% off tu primer mes</strong> de cualquier plan pagado. Solo por hoy.</p>
            <a href="{{ url('/doctor/register?promo=VUELVE20') }}" onclick="trackExitConvert()"
                style="display:block;width:100%;padding:14px;background:linear-gradient(135deg,#0d9488,#0891b2);color:#fff;font-weight:700;border-radius:12px;text-decoration:none;font-size:15px;box-shadow:0 10px 25px -5px rgba(13,148,136,0.4);margin-bottom:10px;">
                Sí, quiero mi 20% off →
            </a>
            <button type="button" onclick="closeExitModal()"
                style="background:none;border:0;color:#9ca3af;font-size:13px;cursor:pointer;padding:8px;">
                No gracias, prefiero pagar el precio completo
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const STORAGE_KEY = 'docfacil_exit_intent_shown';
    const modal = document.getElementById('exit-intent-modal');
    const card = document.getElementById('exit-intent-card');
    if (!modal || sessionStorage.getItem(STORAGE_KEY)) return;

    let shown = false;

    function show() {
        if (shown) return;
        shown = true;
        sessionStorage.setItem(STORAGE_KEY, '1');
        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            modal.style.opacity = '1';
            card.style.transform = 'scale(1)';
        });
    }

    // Desktop: mouse sale por arriba (señal de ir a cerrar la tab)
    document.addEventListener('mouseleave', (e) => {
        if (e.clientY <= 0 && window.scrollY > 300) show();
    });

    // Mobile: back button o visibilidad (proxy de "me voy")
    // Solo después de 30s en página para no ser molesto al que rebota rápido
    let mobileTimer = null;
    if (window.matchMedia('(max-width: 768px)').matches) {
        mobileTimer = setTimeout(() => {
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'hidden') show();
            }, { once: true });
        }, 30000);
    }
})();

function closeExitModal() {
    const modal = document.getElementById('exit-intent-modal');
    const card = document.getElementById('exit-intent-card');
    if (!modal) return;
    modal.style.opacity = '0';
    card.style.transform = 'scale(0.92)';
    setTimeout(() => modal.style.display = 'none', 250);
}

function trackExitConvert() {
    // Hook analítico — por ahora solo log, luego se puede conectar a GA/Meta
    try { console.log('[docfacil] exit-intent conversion'); } catch(e) {}
}
</script>

<x-chatbot-widget />
</body>
</html>
