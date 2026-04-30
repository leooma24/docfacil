<!DOCTYPE html>
<html lang="es" style="scroll-behavior:smooth;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DocFácil vs {{ $competitor['name'] }} — Comparativa para Consultorios Dentales en México</title>
    <meta name="description" content="DocFácil vs {{ $competitor['name'] }}: comparativa honesta de funciones, precio, soporte y diferencias para consultorios dentales en México. Pros y contras de cada uno.">
    <meta name="theme-color" content="#14b8a6">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">

    {{-- OpenGraph --}}
    <meta property="og:title" content="DocFácil vs {{ $competitor['name'] }} — Comparativa Honesta">
    <meta property="og:description" content="Comparativa de DocFácil contra {{ $competitor['name'] }}: precio, funciones, NOM-004, soporte. Para consultorios dentales en México.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-image.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ url("/vs/{$slug}") }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DocFácil">
    <meta property="og:locale" content="es_MX">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="DocFácil vs {{ $competitor['name'] }} — Comparativa">
    <meta name="twitter:description" content="Comparativa honesta para consultorios dentales en México.">
    <meta name="twitter:image" content="https://docfacil.tu-app.co/images/og-image.png">

    <link rel="canonical" href="{{ url("/vs/{$slug}") }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        section[id] { scroll-margin-top: 80px; }
    </style>

    {{-- Schema.org Article + FAQPage para extracción por IA --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Article",
        "headline": "DocFácil vs {{ $competitor['name'] }}: Comparativa para Consultorios Dentales en México",
        "description": "Comparativa honesta entre DocFácil y {{ $competitor['name'] }}: funciones, precio, soporte, cumplimiento NOM-004 y casos de uso ideales.",
        "datePublished": "2026-04-29",
        "dateModified": "{{ now()->toDateString() }}",
        "author": { "@@type": "Organization", "name": "DocFácil" },
        "publisher": {
            "@@type": "Organization",
            "name": "DocFácil",
            "logo": { "@@type": "ImageObject", "url": "{{ asset('images/logo_doc_facil.png') }}" }
        },
        "mainEntityOfPage": "{{ url("/vs/{$slug}") }}"
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "FAQPage",
        "mainEntity": [
            {
                "@@type": "Question",
                "name": "¿Cuál es la diferencia principal entre DocFácil y {{ $competitor['name'] }}?",
                "acceptedAnswer": { "@@type": "Answer", "text": "DocFácil está hecho 100% para consultorios dentales en México: cumple NOM-004 y LFPDPPP, integra SPEI, tiene WhatsApp 1-clic nativo y precio en pesos. {{ $competitor['name'] }} está orientado a {{ $competitor['origin'] }} y no aborda el contexto regulatorio mexicano de la misma forma." }
            },
            {
                "@@type": "Question",
                "name": "¿{{ $competitor['name'] }} cumple con NOM-004 y LFPDPPP en México?",
                "acceptedAnswer": { "@@type": "Answer", "text": "{{ $competitor['name'] }} no fue diseñado para el marco regulatorio mexicano. DocFácil sí: estructura de expediente alineada a NOM-004-SSA3-2012, servidores en México, cumplimiento LFPDPPP." }
            },
            {
                "@@type": "Question",
                "name": "¿Cuál es más barato, DocFácil o {{ $competitor['name'] }}?",
                "acceptedAnswer": { "@@type": "Answer", "text": "DocFácil tiene plan Free de por vida y plan pagado desde $499 MXN/mes en pesos sin sobresaltos cambiarios. {{ $competitor['name'] }}: {{ $competitor['pricing'] }}." }
            },
            {
                "@@type": "Question",
                "name": "¿Para quién es mejor DocFácil que {{ $competitor['name'] }}?",
                "acceptedAnswer": { "@@type": "Answer", "text": "DocFácil es la mejor opción para consultorios dentales mexicanos de 1 a 3 sillones, dentistas independientes, y clínicas que quieren WhatsApp 1-clic, NOM-004 y soporte directo del fundador en español MX. {{ $competitor['name'] }} puede ser mejor para: {{ $competitor['best_for'] }}" }
            }
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
            <div class="flex items-center gap-3 sm:gap-5">
                <a href="{{ url('/dentistas') }}#features" class="hidden sm:inline text-sm text-gray-600 hover:text-teal-600 font-medium">Funciones</a>
                <a href="{{ url('/dentistas') }}#pricing" class="hidden sm:inline text-sm text-gray-600 hover:text-teal-600 font-medium">Precios</a>
                <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="vs_navbar" data-track-competitor="{{ $slug }}"
                   class="px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-xl hover:bg-teal-700 transition shadow">
                    Probar gratis
                </a>
            </div>
        </div>
    </nav>

    {{-- HERO --}}
    <section class="py-16 sm:py-24 px-4 bg-gradient-to-b from-teal-50/40 to-white">
        <div class="max-w-4xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-teal-50 border border-teal-200 text-teal-700 text-xs font-bold rounded-full mb-5">
                COMPARATIVA HONESTA
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                DocFácil <span class="text-gray-400">vs</span> {{ $competitor['name'] }}
            </h1>
            <p class="mt-6 text-lg text-gray-600 leading-relaxed max-w-2xl mx-auto">
                Comparativa real para consultorios dentales en México. Sin marketing inflado — incluimos lo que {{ $competitor['name'] }} hace bien y dónde DocFácil tiene la ventaja.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="vs_hero" data-track-competitor="{{ $slug }}"
                   class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-bold rounded-xl hover:shadow-lg transition">
                    Probar DocFácil 15 días gratis →
                </a>
                <a href="#tabla-comparativa" class="w-full sm:w-auto px-8 py-3.5 bg-gray-100 text-gray-800 font-semibold rounded-xl hover:bg-gray-200 transition">
                    Ver comparativa completa ↓
                </a>
            </div>
        </div>
    </section>

    {{-- TL;DR --}}
    <section class="py-12 bg-white">
        <div class="max-w-3xl mx-auto px-4">
            <div class="rounded-2xl border-2 border-amber-200 bg-amber-50/60 p-6 sm:p-8">
                <div class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-3">TL;DR · Resumen ejecutivo</div>
                <p class="text-gray-800 leading-relaxed">
                    <strong>DocFácil</strong> está hecho 100% para consultorios dentales en México: cumple NOM-004 y LFPDPPP, integra SPEI, tiene WhatsApp 1-clic nativo, soporte directo del fundador y precio en pesos. Plan Free de por vida + Básico desde $499 MXN/mes.
                    <br><br>
                    <strong>{{ $competitor['name'] }}</strong> es {{ $competitor['tagline'] }} ({{ $competitor['origin'] }}). {{ $competitor['pricing'] }}. Es opción válida para {{ \Illuminate\Support\Str::lower($competitor['best_for']) }}, pero no fue diseñado para el contexto regulatorio mexicano.
                </p>
            </div>
        </div>
    </section>

    {{-- Tabla comparativa principal --}}
    <section id="tabla-comparativa" class="py-14 sm:py-20 bg-gray-50">
        <div class="max-w-5xl mx-auto px-4">
            <h2 class="text-2xl sm:text-4xl font-extrabold text-center mb-10">Comparativa lado a lado</h2>

            <div class="overflow-x-auto bg-white rounded-2xl shadow-sm border border-gray-200">
                <table class="w-full text-sm sm:text-base">
                    <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50">
                            <th class="text-left py-4 px-4 sm:px-6 font-bold text-gray-700">Criterio</th>
                            <th class="text-left py-4 px-4 sm:px-6 font-bold text-teal-700">DocFácil</th>
                            <th class="text-left py-4 px-4 sm:px-6 font-bold text-gray-700">{{ $competitor['name'] }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-3 px-4 sm:px-6 font-semibold text-gray-700">País de origen</td>
                            <td class="py-3 px-4 sm:px-6">🇲🇽 México</td>
                            <td class="py-3 px-4 sm:px-6">{{ $competitor['origin'] }}</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 sm:px-6 font-semibold text-gray-700">Cumple NOM-004 (México)</td>
                            <td class="py-3 px-4 sm:px-6 text-emerald-700 font-semibold">✓ Estructura nativa</td>
                            <td class="py-3 px-4 sm:px-6 text-amber-700">No aplicable / requiere customización</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 sm:px-6 font-semibold text-gray-700">Cumple LFPDPPP</td>
                            <td class="py-3 px-4 sm:px-6 text-emerald-700 font-semibold">✓ Servidores en MX, TLS 1.3</td>
                            <td class="py-3 px-4 sm:px-6 text-amber-700">Servidores fuera de MX</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 sm:px-6 font-semibold text-gray-700">WhatsApp recordatorios</td>
                            <td class="py-3 px-4 sm:px-6 text-emerald-700 font-semibold">✓ 1-clic nativo, sin API cara</td>
                            <td class="py-3 px-4 sm:px-6 text-amber-700">Vía integración o copy-paste</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 sm:px-6 font-semibold text-gray-700">Pago SPEI</td>
                            <td class="py-3 px-4 sm:px-6 text-emerald-700 font-semibold">✓ Soporte directo</td>
                            <td class="py-3 px-4 sm:px-6 text-amber-700">No soportado</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 sm:px-6 font-semibold text-gray-700">Odontograma FDI interactivo</td>
                            <td class="py-3 px-4 sm:px-6 text-emerald-700 font-semibold">✓ 13 condiciones</td>
                            <td class="py-3 px-4 sm:px-6">Disponible</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 sm:px-6 font-semibold text-gray-700">Recetas con cédula profesional</td>
                            <td class="py-3 px-4 sm:px-6 text-emerald-700 font-semibold">✓ PDF + firma digital</td>
                            <td class="py-3 px-4 sm:px-6 text-amber-700">Genérica (no formato MX)</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 sm:px-6 font-semibold text-gray-700">Precio inicial</td>
                            <td class="py-3 px-4 sm:px-6 text-emerald-700 font-semibold">Free de por vida · Básico $499 MXN/mes</td>
                            <td class="py-3 px-4 sm:px-6">{{ $competitor['pricing'] }}</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 sm:px-6 font-semibold text-gray-700">Soporte</td>
                            <td class="py-3 px-4 sm:px-6 text-emerald-700 font-semibold">WhatsApp directo del fundador (Omar Lerma)</td>
                            <td class="py-3 px-4 sm:px-6">Tickets / email zona horaria distinta</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 sm:px-6 font-semibold text-gray-700">Idioma soporte</td>
                            <td class="py-3 px-4 sm:px-6 text-emerald-700 font-semibold">Español MX nativo</td>
                            <td class="py-3 px-4 sm:px-6">Español genérico / inglés</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 sm:px-6 font-semibold text-gray-700">Garantía</td>
                            <td class="py-3 px-4 sm:px-6 text-emerald-700 font-semibold">30 días devolución</td>
                            <td class="py-3 px-4 sm:px-6">Variable según contrato</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Strengths/Weaknesses honestas --}}
    <section class="py-14 sm:py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4">
            <h2 class="text-2xl sm:text-4xl font-extrabold text-center mb-10">Pros y contras de cada uno</h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="rounded-2xl p-6 border-2 border-teal-200 bg-teal-50/40">
                    <h3 class="text-xl font-extrabold text-teal-900 mb-4">DocFácil — fortalezas</h3>
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span><span><strong>WhatsApp 1-clic nativo</strong> — abre tu propio WhatsApp con mensaje pre-armado, sin API cara de Meta</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span><span><strong>Hecho 100% para México</strong> — NOM-004, LFPDPPP, SPEI, español MX, soporte MX</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span><span><strong>Founder-led</strong> — Omar Lerma responde directo en su WhatsApp personal</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span><span><strong>Plan Free de por vida</strong> — 1 doctor + 15 pacientes sin tarjeta</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span><span><strong>Precio en pesos</strong> — desde $499 MXN/mes, sin riesgo cambiario</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span><span><strong>Garantía 30 días</strong> — devolución completa sin preguntas</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span><span><strong>PWA</strong> — se instala como app en iPhone/Android sin pasar por App Store</span></li>
                    </ul>
                    <h4 class="text-sm font-bold text-gray-900 mt-6 mb-2">Limitaciones honestas</h4>
                    <ul class="space-y-2 text-xs text-gray-600">
                        <li>· Apenas arrancando (abril 2026) — pocos testimoniales públicos aún</li>
                        <li>· CFDI directo está en roadmap Q3 2026 (mientras tanto facturas con tu sistema fiscal actual)</li>
                        <li>· Foco exclusivo dental — no es para médicos generales</li>
                    </ul>
                </div>

                <div class="rounded-2xl p-6 border-2 border-gray-200 bg-gray-50/40">
                    <h3 class="text-xl font-extrabold text-gray-900 mb-4">{{ $competitor['name'] }} — fortalezas</h3>
                    <ul class="space-y-3 text-sm text-gray-700">
                        @foreach($competitor['strengths'] as $s)
                        <li class="flex items-start gap-2"><span class="text-blue-600 mt-0.5">✓</span><span>{{ $s }}</span></li>
                        @endforeach
                    </ul>
                    <h4 class="text-sm font-bold text-gray-900 mt-6 mb-2">Limitaciones para el mercado mexicano</h4>
                    <ul class="space-y-2 text-xs text-gray-600">
                        @foreach($competitor['weaknesses'] as $w)
                        <li>· {{ $w }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Para quién es cada uno --}}
    <section class="py-14 sm:py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-2xl sm:text-4xl font-extrabold mb-10">¿Para quién es mejor cada opción?</h2>
            <div class="grid md:grid-cols-2 gap-6 text-left">
                <div class="rounded-2xl bg-gradient-to-br from-teal-50 to-cyan-50 border border-teal-200 p-6">
                    <div class="text-xs font-bold text-teal-700 uppercase tracking-wider mb-2">Elige DocFácil si...</div>
                    <ul class="space-y-2.5 text-sm text-gray-700">
                        <li class="flex items-start gap-2"><span class="text-teal-600">→</span> Tu consultorio está en México (1-3 sillones)</li>
                        <li class="flex items-start gap-2"><span class="text-teal-600">→</span> Quieres recordatorios WhatsApp sin pagar API a Meta</li>
                        <li class="flex items-start gap-2"><span class="text-teal-600">→</span> Necesitas cumplimiento NOM-004 y LFPDPPP de fábrica</li>
                        <li class="flex items-start gap-2"><span class="text-teal-600">→</span> Prefieres precio en pesos sin sobresaltos cambiarios</li>
                        <li class="flex items-start gap-2"><span class="text-teal-600">→</span> Valoras hablar directo con el fundador del software</li>
                    </ul>
                </div>
                <div class="rounded-2xl bg-white border border-gray-200 p-6">
                    <div class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Elige {{ $competitor['name'] }} si...</div>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $competitor['best_for'] }}</p>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">{{ $competitor['name'] }} NO es ideal si...</div>
                        <p class="text-xs text-gray-600 leading-relaxed">{{ $competitor['not_for'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Migración --}}
    <section class="py-14 bg-white">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-2xl sm:text-3xl font-extrabold mb-4">¿Vienes de {{ $competitor['name'] }}? Te ayudo a migrar</h2>
            <p class="text-gray-600 leading-relaxed mb-6">
                Si ya estás usando {{ $competitor['name'] }} y quieres probar DocFácil, mándame tu Excel/CSV con tus pacientes a mi WhatsApp directo y yo lo subo a tu cuenta sin costo durante el onboarding. Sin importar cuántos pacientes tengas.
            </p>
            <a href="https://wa.me/526682493398?text={{ urlencode("Hola Omar, vengo de {$competitor['name']} y quiero probar DocFácil. ¿Me puedes ayudar con la migración?") }}"
               target="_blank"
               data-track="whatsapp_clicked" data-track-location="vs_migration" data-track-competitor="{{ $slug }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-green-500 text-white font-bold rounded-xl hover:bg-green-600 transition">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2z"/></svg>
                Hablar con Omar para migrar
            </a>
        </div>
    </section>

    {{-- CTA final --}}
    <section class="py-16 sm:py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-teal-600 via-cyan-600 to-teal-700"></div>
        <div class="max-w-3xl mx-auto px-4 text-center relative z-10">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white">Pruébalo tú mismo</h2>
            <p class="mt-4 text-lg text-teal-100">15 días gratis con todas las funciones del Pro. Sin tarjeta. Garantía 30 días si decides quedarte.</p>
            <a href="{{ url('/doctor/register') }}"
               data-track="cta_clicked" data-track-location="vs_final_cta" data-track-competitor="{{ $slug }}"
               class="mt-8 inline-flex items-center px-10 py-4 bg-white text-teal-700 font-bold rounded-xl hover:bg-teal-50 transition shadow-2xl text-lg">
                Crear mi cuenta gratis →
            </a>
        </div>
    </section>

    {{-- Otras comparativas --}}
    <section class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h3 class="text-lg font-bold text-gray-700 mb-4">Otras comparativas</h3>
            <div class="flex flex-wrap items-center justify-center gap-3">
                @foreach(collect($all_competitors)->where('slug', '!=', $slug) as $c)
                    <a href="{{ url("/vs/{$c['slug']}") }}" class="px-4 py-2 bg-white border border-gray-200 hover:border-teal-300 rounded-lg text-sm font-medium text-gray-700 hover:text-teal-700 transition">
                        DocFácil vs {{ $c['name'] }}
                    </a>
                @endforeach
                <a href="{{ url("/alternativas-a-{$slug}") }}" class="px-4 py-2 bg-teal-50 border border-teal-200 hover:border-teal-400 rounded-lg text-sm font-medium text-teal-700 transition">
                    Alternativas a {{ $competitor['name'] }} →
                </a>
            </div>
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
            <p class="text-xs text-gray-600">&copy; {{ date('Y') }} DocFácil. Comparativa con fines informativos. {{ $competitor['name'] }} es marca registrada de su respectivo dueño.</p>
        </div>
    </footer>
</body>
</html>
