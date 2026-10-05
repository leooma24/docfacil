<!DOCTYPE html>
<html lang="es" style="scroll-behavior:smooth;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Software para Consultorios Dentales en {{ $city }} — DocFácil</title>
    <meta name="description" content="Software para dentistas en {{ $city }}, {{ $state }}. Recordatorios por WhatsApp a 1 clic, odontograma digital FDI, expediente pensado para la NOM-004 (notas que se bloquean) y recetas PDF con cédula. 15 días gratis, sin tarjeta. Desde $499/mes.">
    <meta name="theme-color" content="#14b8a6">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="DocFácil">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">

    {{-- OpenGraph --}}
    <meta property="og:title" content="Software para Consultorios Dentales en {{ $city }} — DocFácil">
    <meta property="og:description" content="Agenda, recordatorios por WhatsApp a 1 clic, odontograma, expediente, recetas PDF con cédula, presupuestos y cobros para dentistas en {{ $city }}. 15 días gratis.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="DocFácil — Software para dentistas en {{ $city }}">
    <meta property="og:url" content="{{ url("/software-dental/{$slug}") }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DocFácil">
    <meta property="og:locale" content="es_MX">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Software para Consultorios Dentales en {{ $city }} — DocFácil">
    <meta name="twitter:description" content="Recordatorios por WhatsApp a 1 clic, odontograma digital y recetas PDF para dentistas en {{ $city }}. Pruébelo 15 días gratis.">
    <meta name="twitter:image" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">

    <link rel="canonical" href="{{ url("/software-dental/{$slug}") }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
        section[id] { scroll-margin-top: 80px; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .animate-fade-up { animation: fadeUp 0.6s ease forwards; }
    </style>

    {{-- Schema.org SoftwareApplication --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "DocFácil — Software para dentistas en {{ $city }}",
        "description": "Software dental para consultorios en {{ $city }}, {{ $state }}. Agenda con recordatorios por WhatsApp a 1 clic, odontograma digital FDI, expediente pensado para la NOM-004 (notas que se bloquean a las 24 horas), recetas PDF con cédula, presupuestos y cobros.",
        "applicationCategory": "HealthApplication",
        "operatingSystem": "Web",
        "url": "{{ url("/software-dental/{$slug}") }}",
        "offers": [
            { "@@type": "Offer", "price": "0",    "priceCurrency": "MXN", "name": "Plan Free" },
            { "@@type": "Offer", "price": "499",  "priceCurrency": "MXN", "name": "Plan Básico" },
            { "@@type": "Offer", "price": "999",  "priceCurrency": "MXN", "name": "Plan Pro" },
            { "@@type": "Offer", "price": "1999", "priceCurrency": "MXN", "name": "Plan Clínica" }
        ],
        "areaServed": {
            "@@type": "City",
            "name": "{{ $city }}",
            "containedInPlace": { "@@type": "AdministrativeArea", "name": "{{ $state }}" }
        }
    }
    </script>

    {{-- Schema.org FAQ --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "FAQPage",
        "mainEntity": [
            { "@@type": "Question", "name": "¿Cuánto cuesta DocFácil para un consultorio en {{ $city }}?",
              "acceptedAnswer": { "@@type": "Answer", "text": "Plan Free de por vida (1 doctor, 15 pacientes, 10 citas al mes). Plan Básico $499 al mes con odontograma, recordatorios por WhatsApp a 1 clic, recetas PDF, presupuestos y cobros. Si paga el año, le sale en 10 meses. Garantía de 30 días sobre su primer pago." } },
            { "@@type": "Question", "name": "¿Funciona para consultorios pequeños en {{ $city }}?",
              "acceptedAnswer": { "@@type": "Answer", "text": "Sí. Está hecho para consultorios de 1 a 3 sillones. El plan Free sirve para consultorios pequeños que apenas empiezan." } },
            { "@@type": "Question", "name": "¿DocFácil me ayuda con la NOM-004 y con la protección de datos?",
              "acceptedAnswer": { "@@type": "Answer", "text": "Está pensado para ayudarle con la NOM-004: las notas clínicas y las recetas se bloquean 24 horas después de creadas, queda historial de cambios, los diagnósticos usan el catálogo CIE-10 y la receta lleva su cédula. Para los datos: conexión cifrada (HTTPS), respaldo diario y cada consultorio aislado de los demás. Los servidores están en Estados Unidos." } },
            { "@@type": "Question", "name": "¿Necesito instalar algo?",
              "acceptedAnswer": { "@@type": "Answer", "text": "No. DocFácil funciona en cualquier navegador y se puede instalar como app (PWA) en celular. No requiere instalación local." } }
        ]
    }
    </script>

    @include('partials.analytics')
</head>
<body class="bg-white text-gray-900 antialiased overflow-x-hidden">

    {{-- Navbar --}}
    <nav class="sticky top-0 w-full bg-white/90 backdrop-blur-lg border-b border-gray-100 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16 sm:h-20">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" class="h-12 sm:h-14">
            </a>
            <div class="flex items-center gap-3 sm:gap-5">
                <a href="{{ url('/') }}#pricing" class="hidden sm:inline text-sm text-gray-600 hover:text-teal-600 font-medium">Precios</a>
                <a href="{{ url('/doctor/login') }}" class="hidden sm:inline text-sm text-gray-500 hover:text-teal-600 font-medium">Iniciar sesión</a>
                <a href="{{ url('/doctor/register?city=' . urlencode($city) . '&state=' . urlencode($state)) }}" data-track="cta_clicked" data-track-location="city_navbar" data-track-city="{{ $slug }}"
                   class="px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-xl hover:bg-teal-700 transition shadow hover:shadow-lg hover:shadow-teal-200">
                    Probar gratis
                </a>
            </div>
        </div>
    </nav>

    {{-- HERO local --}}
    <section class="relative pt-12 pb-16 sm:pt-20 sm:pb-24 px-4 overflow-hidden">
        <div class="absolute top-10 -left-32 w-80 h-80 bg-teal-100 rounded-full blur-3xl opacity-60"></div>
        <div class="absolute top-32 -right-32 w-80 h-80 bg-cyan-100 rounded-full blur-3xl opacity-60"></div>

        <div class="max-w-5xl mx-auto text-center relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-teal-50 to-cyan-50 text-teal-700 text-xs sm:text-sm font-bold rounded-full mb-5 border border-teal-200 shadow-sm animate-fade-up">
                <span class="w-2 h-2 bg-teal-500 rounded-full animate-pulse"></span>
                Software dental · {{ $city }}, {{ $state }}
            </div>

            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.1] animate-fade-up" style="animation-delay:0.1s;">
                El software para consultorios<br>
                dentales en
                <span class="bg-gradient-to-r from-teal-600 via-cyan-500 to-teal-700 bg-clip-text text-transparent">{{ $city }}</span>
            </h1>

            <p class="mt-6 text-base sm:text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed animate-fade-up" style="animation-delay:0.2s;">
                Recordatorios por WhatsApp <strong class="text-gray-900">a 1 clic</strong>, odontograma digital FDI,
                expediente pensado para la NOM-004 (notas que se bloquean), recetas PDF con cédula, presupuestos y cobros.
                <strong class="text-gray-900">Hecho en México</strong> para consultorios dentales mexicanos.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 animate-fade-up" style="animation-delay:0.3s;">
                <a href="{{ url('/doctor/register?city=' . urlencode($city) . '&state=' . urlencode($state)) }}"
                   data-track="cta_clicked" data-track-location="city_hero" data-track-city="{{ $slug }}"
                   class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-bold rounded-xl hover:shadow-2xl hover:shadow-teal-300/50 transition-all hover:-translate-y-1 text-lg">
                    Probar 15 días gratis →
                </a>
                <a href="{{ url('/dentistas') }}#pricing"
                   data-track="cta_clicked" data-track-location="city_hero" data-track-text="ver_precios"
                   class="w-full sm:w-auto px-8 py-4 bg-gray-100 text-gray-800 font-semibold rounded-xl hover:bg-gray-200 transition">
                    Ver precios y planes
                </a>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm text-gray-500 animate-fade-up" style="animation-delay:0.4s;">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Sin tarjeta
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Garantía 30 días
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Cancele cuando quiera, sin penalización
                </span>
            </div>

        </div>
    </section>

    {{-- Mensaje local: por qué esta ciudad --}}
    <section class="py-12 bg-gradient-to-b from-white to-gray-50">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">
                Hecho para dentistas en {{ $city }}
            </h2>
            <p class="mt-4 text-base sm:text-lg text-gray-600 leading-relaxed">
                Cada cita que no llega es un hueco en su agenda. Con DocFácil, usted
                abre el recordatorio ya escrito en <strong class="text-gray-900">su propio WhatsApp</strong> y solo le da enviar.
                El paciente confirma su cita con un link, y en el plan Pro, si alguien cancela, la lista de espera le ayuda a llenar ese hueco.
            </p>
        </div>
    </section>

    {{-- Features grid (mismo lenguaje que la principal) --}}
    <section class="py-14 sm:py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl sm:text-4xl font-extrabold text-gray-900">Lo que su consultorio en {{ $city }} necesita</h2>
                <p class="mt-3 text-gray-600 max-w-2xl mx-auto">Lo que se usa todos los días en un consultorio dental, sin complicaciones.</p>
            </div>
            @php
                $features = [
                    ['icon' => '📱', 'title' => 'Recordatorios por WhatsApp a 1 clic', 'desc' => 'DocFácil abre su WhatsApp con el mensaje ya escrito y usted le da enviar. Sin pagar mensajes a Meta.'],
                    ['icon' => '🦷', 'title' => 'Odontograma digital FDI', 'desc' => '13 condiciones (caries, corona, implante, sellante…). Lo que falta tratar se vuelve presupuesto.'],
                    ['icon' => '📋', 'title' => 'Expediente pensado para la NOM-004', 'desc' => 'Notas de cada consulta, alergias y antecedentes. Diagnósticos con CIE-10 y notas que se bloquean a las 24 horas.'],
                    ['icon' => '💊', 'title' => 'Recetas PDF con cédula', 'desc' => 'Su nombre, especialidad, cédula y espacio para su firma. El paciente las ve en su portal.'],
                    ['icon' => '💰', 'title' => 'Presupuestos y cobros', 'desc' => 'El paciente acepta el presupuesto en línea. Usted registra cobros y abonos, y manda el saldo por WhatsApp a 1 clic.'],
                    ['icon' => '📊', 'title' => 'Corte del mes', 'desc' => 'Ingresos, gastos y corte del mes en una pantalla. Sin hojas de Excel.'],
                ];
            @endphp
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($features as $f)
                <div class="rounded-2xl p-6 bg-white border border-gray-100 hover:border-teal-200 hover:shadow-lg hover:shadow-teal-100/40 hover:-translate-y-1 transition-all">
                    <div class="text-3xl mb-3">{{ $f['icon'] }}</div>
                    <h3 class="font-bold text-gray-900 mb-1.5">{{ $f['title'] }}</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $f['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Pricing summary (link a la principal para detalles) --}}
    <section class="py-14 sm:py-20 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-2xl sm:text-4xl font-extrabold text-gray-900">Precios para consultorios en {{ $city }}</h2>
            <p class="mt-3 text-lg text-gray-600">Plan gratis de por vida. Plan pagado desde $499/mes.</p>

            <div class="mt-10 grid sm:grid-cols-2 gap-4 max-w-2xl mx-auto">
                <div class="rounded-2xl p-6 bg-white border-2 border-gray-200">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Plan Free</div>
                    <div class="mt-2 text-3xl font-extrabold text-gray-900">$0<span class="text-base font-normal text-gray-500">/mes</span></div>
                    <div class="mt-2 text-sm text-gray-600">1 doctor · 15 pacientes · 10 citas al mes</div>
                </div>
                <div class="rounded-2xl p-6 bg-gradient-to-br from-teal-50 to-cyan-50 border-2 border-teal-500 relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-teal-600 text-white text-xs font-bold rounded-full uppercase tracking-wider">Para empezar</div>
                    <div class="text-xs font-bold text-teal-700 uppercase tracking-wide">Plan Básico</div>
                    <div class="mt-2 text-3xl font-extrabold text-gray-900">$499<span class="text-base font-normal text-gray-500">/mes</span></div>
                    <div class="mt-2 text-sm text-gray-700">Odontograma + WhatsApp a 1 clic + recetas + presupuestos + cobros</div>
                </div>
            </div>

            <a href="{{ url('/dentistas') }}#pricing"
               data-track="cta_clicked" data-track-location="city_pricing" data-track-city="{{ $slug }}"
               class="inline-block mt-8 text-teal-600 hover:text-teal-700 font-semibold underline">
                Ver los 4 planes y comparativa →
            </a>
        </div>
    </section>

    {{-- FAQ local --}}
    <section class="py-14 sm:py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-2xl sm:text-4xl font-extrabold text-gray-900 text-center mb-10">Preguntas frecuentes — {{ $city }}</h2>
            <div class="space-y-3" x-data="{ open: 0 }">
                @php
                $faqs = [
                    [
                        'q' => "¿DocFácil funciona en {$city}?",
                        'a' => "Sí. DocFácil funciona en cualquier ciudad de México con internet. El soporte es en español, directo por WhatsApp con el fundador.",
                    ],
                    [
                        'q' => "¿Cuánto cuesta DocFácil para un consultorio en {$city}?",
                        'a' => "Plan Free de por vida (1 doctor, 15 pacientes, 10 citas al mes). Plan Básico $499 al mes con odontograma, recordatorios por WhatsApp a 1 clic, recetas PDF, presupuestos y cobros. Pro $999 al mes, hasta 3 doctores. Clínica $1,999 al mes. Si paga el año, le sale en 10 meses. Garantía de 30 días sobre su primer pago.",
                    ],
                    [
                        'q' => "¿DocFácil me ayuda con la NOM-004 y con la protección de datos?",
                        'a' => "Está pensado para ayudarle con la NOM-004: las notas clínicas y las recetas se bloquean 24 horas después de creadas, queda historial de cambios, los diagnósticos usan el catálogo CIE-10 y la receta lleva su cédula. Para los datos: conexión cifrada (HTTPS), respaldo diario y cada consultorio aislado de los demás. Los servidores están en Estados Unidos.",
                    ],
                    [
                        'q' => "¿Tengo que firmar un contrato?",
                        'a' => "No. Cancele cuando quiera, sin penalización.",
                    ],
                    [
                        'q' => "¿Y si no se me da la tecnología?",
                        'a' => "DocFácil está hecho para dentistas, no para ingenieros. Si usa WhatsApp, puede usar DocFácil. Le acompañamos paso a paso por WhatsApp las primeras semanas, sin costo extra.",
                    ],
                    [
                        'q' => "¿Puedo migrar mis pacientes desde Excel o papel?",
                        'a' => "Sí. Nos manda su Excel o CSV por WhatsApp y lo subimos a su cuenta al empezar, sin costo.",
                    ],
                ];
                @endphp
                @foreach($faqs as $i => $faq)
                <div class="border border-gray-200 rounded-xl bg-white hover:border-teal-300 transition">
                    <button type="button" @click="open = (open === {{ $i }} ? null : {{ $i }})"
                            class="w-full flex items-center justify-between text-left px-5 py-4 hover:bg-gray-50 transition">
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

    {{-- CTA final --}}
    <section class="py-16 sm:py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-teal-600 via-cyan-600 to-teal-700"></div>
        <div class="max-w-3xl mx-auto px-4 text-center relative z-10">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white leading-tight">
                Empiece hoy en {{ $city }}
            </h2>
            <p class="mt-4 text-lg text-teal-100">
                15 días con todo, sin tarjeta. Después, plan Free de por vida o el plan que elija.
            </p>
            <a href="{{ url('/doctor/register?city=' . urlencode($city) . '&state=' . urlencode($state)) }}"
               data-track="cta_clicked" data-track-location="city_final_cta" data-track-city="{{ $slug }}"
               class="mt-8 inline-flex items-center px-10 py-4 bg-white text-teal-700 font-bold rounded-xl hover:bg-teal-50 transition-all shadow-2xl hover:-translate-y-1 text-lg">
                Crear mi cuenta gratis →
            </a>
        </div>
    </section>

    {{-- Footer con todas las ciudades para internal linking --}}
    <footer class="py-14 bg-gray-950 text-gray-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8 mb-10">
                <div>
                    <img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" class="h-10 mb-4 brightness-200">
                    <p class="text-sm">Software para consultorios dentales en {{ $city }}, {{ $state }} y todo México. Hecho en México.</p>
                </div>
                <div class="md:col-span-2">
                    <h4 class="font-semibold text-gray-300 mb-3 text-sm">Otras ciudades</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-1 text-sm">
                        @foreach(collect($all_cities)->where('slug', '!=', $slug)->take(24) as $c)
                            <a href="{{ url("/software-dental/{$c['slug']}") }}" class="text-gray-500 hover:text-teal-400 transition py-1">{{ $c['name'] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-6 text-center space-y-2">
                <div class="flex items-center justify-center gap-4 text-sm">
                    <a href="{{ url('/') }}" class="hover:text-teal-400 transition">Inicio</a>
                    <span class="text-gray-700">·</span>
                    <a href="{{ url('/dentistas') }}#pricing" class="hover:text-teal-400 transition">Precios</a>
                    <span class="text-gray-700">·</span>
                    <a href="{{ url('/doctor/login') }}" class="hover:text-teal-400 transition">Iniciar sesión</a>
                    <span class="text-gray-700">·</span>
                    <a href="/privacidad" class="hover:text-teal-400 transition">Privacidad</a>
                </div>
                <p class="text-xs text-gray-600">&copy; {{ date('Y') }} DocFácil. Software para consultorios en {{ $city }}, {{ $state }}.</p>
            </div>
        </div>
    </footer>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
