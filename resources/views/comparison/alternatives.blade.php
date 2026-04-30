<!DOCTYPE html>
<html lang="es" style="scroll-behavior:smooth;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alternativas a {{ $competitor['name'] }} en México 2026 — Mejores Opciones para Consultorios Dentales</title>
    <meta name="description" content="¿Buscas alternativas a {{ $competitor['name'] }}? Comparamos las mejores opciones de software dental para consultorios mexicanos: precio, NOM-004, WhatsApp, soporte en español MX.">
    <meta name="theme-color" content="#14b8a6">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">

    <meta property="og:title" content="Alternativas a {{ $competitor['name'] }} en México · Mejores Opciones">
    <meta property="og:description" content="Comparamos las mejores alternativas a {{ $competitor['name'] }} para consultorios dentales en México.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-image.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ url("/alternativas-a-{$slug}") }}">
    <meta property="og:type" content="article">
    <meta property="og:locale" content="es_MX">

    <link rel="canonical" href="{{ url("/alternativas-a-{$slug}") }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>

    {{-- Schema.org Article + ItemList (lista de alternativas) --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Article",
        "headline": "Alternativas a {{ $competitor['name'] }} en México 2026",
        "datePublished": "2026-04-29",
        "dateModified": "{{ now()->toDateString() }}",
        "author": { "@@type": "Organization", "name": "DocFácil" },
        "publisher": {
            "@@type": "Organization",
            "name": "DocFácil",
            "logo": { "@@type": "ImageObject", "url": "{{ asset('images/logo_doc_facil.png') }}" }
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "ItemList",
        "name": "Alternativas a {{ $competitor['name'] }} para Consultorios Dentales en México",
        "itemListElement": [
            {
                "@@type": "ListItem",
                "position": 1,
                "item": {
                    "@@type": "SoftwareApplication",
                    "name": "DocFácil",
                    "applicationCategory": "HealthApplication",
                    "operatingSystem": "Web",
                    "offers": { "@@type": "Offer", "price": "499", "priceCurrency": "MXN" }
                }
            }
            @foreach($others as $i => $o)
            ,{
                "@@type": "ListItem",
                "position": {{ $i + 2 }},
                "item": {
                    "@@type": "SoftwareApplication",
                    "name": @json($o['name']),
                    "applicationCategory": "HealthApplication"
                }
            }
            @endforeach
        ]
    }
    </script>

    @include('partials.analytics')
</head>
<body class="bg-white text-gray-900 antialiased">
    {{-- Navbar --}}
    <nav class="sticky top-0 w-full bg-white/90 backdrop-blur-lg border-b border-gray-100 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16 sm:h-20">
            <a href="{{ url('/') }}"><img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" class="h-12 sm:h-14"></a>
            <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="alt_navbar" data-track-competitor="{{ $slug }}"
               class="px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-xl hover:bg-teal-700 transition shadow">
                Probar gratis
            </a>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="py-16 sm:py-20 px-4 bg-gradient-to-b from-teal-50/40 to-white">
        <div class="max-w-3xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-teal-50 border border-teal-200 text-teal-700 text-xs font-bold rounded-full mb-5">
                ACTUALIZADO {{ now()->translatedFormat('F Y') }}
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold leading-tight">
                Las mejores alternativas a <span class="text-teal-600">{{ $competitor['name'] }}</span><br>
                <span class="text-gray-600">para consultorios dentales en México</span>
            </h1>
            <p class="mt-6 text-lg text-gray-600 leading-relaxed">
                Si {{ $competitor['name'] }} no te termina de convencer ({{ \Illuminate\Support\Str::lower($competitor['weaknesses'][0] ?? 'pricing en USD') }}, etc.), aquí están las opciones que usan dentistas mexicanos hoy.
            </p>
        </div>
    </section>

    {{-- Por qué buscar alternativas --}}
    <section class="py-12 bg-white">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-2xl font-extrabold mb-6">¿Por qué buscar alternativas a {{ $competitor['name'] }}?</h2>
            <ul class="space-y-3 text-gray-700">
                @foreach($competitor['weaknesses'] as $w)
                <li class="flex items-start gap-2"><span class="text-amber-600 mt-1">⚠️</span><span>{{ $w }}</span></li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Criterios para elegir --}}
    <section class="py-12 bg-gray-50">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-2xl font-extrabold mb-6">Qué evaluar al elegir alternativa</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="bg-white p-5 rounded-xl border border-gray-100">
                    <div class="text-2xl mb-2">🇲🇽</div>
                    <div class="font-bold mb-1">Cumplimiento mexicano</div>
                    <p class="text-sm text-gray-600">NOM-004 (expediente), LFPDPPP (datos), SPEI, CFDI.</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-100">
                    <div class="text-2xl mb-2">💰</div>
                    <div class="font-bold mb-1">Precio en pesos</div>
                    <p class="text-sm text-gray-600">USD significa costo variable según tipo de cambio.</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-100">
                    <div class="text-2xl mb-2">📱</div>
                    <div class="font-bold mb-1">WhatsApp nativo</div>
                    <p class="text-sm text-gray-600">El canal #1 de comunicación con pacientes en MX.</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-100">
                    <div class="text-2xl mb-2">🤝</div>
                    <div class="font-bold mb-1">Soporte en español MX</div>
                    <p class="text-sm text-gray-600">Mismo huso horario, mismo lenguaje, mismo contexto.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- LISTA DE ALTERNATIVAS — DocFácil primero --}}
    <section class="py-14 sm:py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4">
            <h2 class="text-2xl sm:text-4xl font-extrabold text-center mb-12">{{ count($others) + 1 }} alternativas a {{ $competitor['name'] }}</h2>

            {{-- 1. DocFácil --}}
            <div class="rounded-2xl border-2 border-teal-500 bg-gradient-to-br from-teal-50/40 to-white p-6 sm:p-8 mb-6 relative">
                <div class="absolute -top-3 left-6 px-3 py-1 bg-teal-600 text-white text-xs font-bold rounded-full uppercase tracking-wider">
                    1 · Recomendado para México
                </div>
                <div class="flex items-start justify-between gap-4 flex-wrap mt-2">
                    <div>
                        <h3 class="text-2xl font-extrabold text-gray-900">DocFácil</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Software dental hecho 100% para México</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-extrabold text-teal-600">$499 MXN<span class="text-sm font-normal text-gray-500">/mes</span></div>
                        <div class="text-xs text-gray-500">+ Plan Free de por vida</div>
                    </div>
                </div>
                <p class="mt-4 text-gray-700 leading-relaxed">
                    DocFácil es el único de esta lista hecho 100% para México. WhatsApp 1-clic nativo (sin API cara de Meta),
                    NOM-004 estructurado, LFPDPPP de fábrica, integración SPEI, soporte directo del fundador en su WhatsApp personal.
                    Plan Free real (1 doctor + 15 pacientes), garantía 30 días.
                </p>
                <div class="mt-5 grid sm:grid-cols-2 gap-2 text-sm">
                    <div class="flex items-center gap-2"><span class="text-emerald-600">✓</span> WhatsApp 1-clic nativo</div>
                    <div class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Cumple NOM-004 + LFPDPPP</div>
                    <div class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Servidores en MX</div>
                    <div class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Soporte por WhatsApp del fundador</div>
                    <div class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Odontograma FDI con 13 condiciones</div>
                    <div class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Recetas PDF con cédula</div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="alt_docfacil_card" data-track-competitor="{{ $slug }}"
                       class="px-5 py-2.5 bg-teal-600 text-white font-bold rounded-xl hover:bg-teal-700 transition text-sm">
                        Probar 15 días gratis →
                    </a>
                    <a href="{{ url('/dentistas') }}" class="px-5 py-2.5 bg-white border border-gray-200 hover:border-teal-300 text-gray-700 font-semibold rounded-xl transition text-sm">
                        Ver landing completa
                    </a>
                </div>
            </div>

            {{-- 2..N. Otros competidores --}}
            @foreach($others as $i => $o)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 mb-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="text-xl font-extrabold text-gray-900">{{ $i + 2 }}. {{ $o['name'] }}</h3>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $o['tagline'] }} · {{ $o['origin'] }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-gray-700">{{ $o['pricing'] }}</div>
                    </div>
                </div>
                <div class="mt-4 grid sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Fortalezas</div>
                        <ul class="space-y-1">
                            @foreach(array_slice($o['strengths'], 0, 3) as $s)
                            <li class="text-gray-700">· {{ $s }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Limitaciones para MX</div>
                        <ul class="space-y-1">
                            @foreach(array_slice($o['weaknesses'], 0, 3) as $w)
                            <li class="text-gray-700">· {{ $w }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 text-sm">
                    <strong class="text-gray-700">Mejor para:</strong> <span class="text-gray-600">{{ $o['best_for'] }}</span>
                </div>
                <div class="mt-3">
                    <a href="{{ url("/vs/{$o['slug']}") }}" class="inline-flex items-center gap-1 text-sm text-teal-700 hover:text-teal-800 font-semibold">
                        Ver comparativa DocFácil vs {{ $o['name'] }} →
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Recomendación por uso --}}
    <section class="py-14 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-center mb-10">¿Cuál elegir?</h2>
            <div class="grid md:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-xl border border-teal-200 shadow-sm">
                    <div class="text-xs font-bold text-teal-700 uppercase tracking-wider mb-2">Si tu consultorio está en México</div>
                    <p class="text-sm text-gray-700 leading-relaxed"><strong>DocFácil</strong>. Es el único hecho 100% para el contexto mexicano.</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-200">
                    <div class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Si operas multi-país LATAM</div>
                    <p class="text-sm text-gray-700 leading-relaxed">Considera Dentalink — tiene presencia en varios países.</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-200">
                    <div class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Si solo atiendes IMSS/ISSSTE</div>
                    <p class="text-sm text-gray-700 leading-relaxed">Probablemente no necesitas software comercial — usa los sistemas internos.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA final --}}
    <section class="py-16 sm:py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-teal-600 via-cyan-600 to-teal-700"></div>
        <div class="max-w-3xl mx-auto px-4 text-center relative z-10">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white">Empieza con DocFácil hoy</h2>
            <p class="mt-4 text-lg text-teal-100">15 días gratis con todas las funciones del Pro. Sin tarjeta. Garantía 30 días si decides quedarte.</p>
            <a href="{{ url('/doctor/register') }}"
               data-track="cta_clicked" data-track-location="alt_final_cta" data-track-competitor="{{ $slug }}"
               class="mt-8 inline-flex items-center px-10 py-4 bg-white text-teal-700 font-bold rounded-xl hover:bg-teal-50 transition shadow-2xl text-lg">
                Crear mi cuenta gratis →
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="py-10 bg-gray-950 text-gray-400 text-center">
        <div class="max-w-7xl mx-auto px-4">
            <img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" class="h-10 mx-auto mb-4 brightness-200">
            <div class="flex items-center justify-center gap-4 text-sm mb-2">
                <a href="{{ url('/') }}" class="hover:text-teal-400 transition">Inicio</a>
                <span class="text-gray-700">·</span>
                <a href="{{ url('/dentistas') }}#pricing" class="hover:text-teal-400 transition">Precios</a>
                <span class="text-gray-700">·</span>
                <a href="{{ url('/doctor/login') }}" class="hover:text-teal-400 transition">Iniciar sesión</a>
            </div>
            <p class="text-xs text-gray-600">&copy; {{ date('Y') }} DocFácil. Comparativa con fines informativos. Las marcas mencionadas pertenecen a sus respectivos dueños.</p>
        </div>
    </footer>
</body>
</html>
