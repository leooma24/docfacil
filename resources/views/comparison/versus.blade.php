<!DOCTYPE html>
<html lang="es" style="scroll-behavior:smooth;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DocFácil vs {{ $competitor['name'] }} — Comparativa para Consultorios Dentales en México</title>
    <meta name="description" content="¿Compara DocFácil con {{ $competitor['name'] }}? Lo que DocFácil hace hoy para consultorios dentales en México, con precios en pesos. 15 días gratis, sin tarjeta.">
    <meta name="theme-color" content="#14b8a6">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">

    {{-- OpenGraph --}}
    <meta property="og:title" content="DocFácil vs {{ $competitor['name'] }} — Comparativa">
    <meta property="og:description" content="Lo que DocFácil hace hoy, con precios en pesos, para quien lo compara con {{ $competitor['name'] }}. Para consultorios dentales en México.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ url("/vs/{$slug}") }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DocFácil">
    <meta property="og:locale" content="es_MX">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="DocFácil vs {{ $competitor['name'] }} — Comparativa">
    <meta name="twitter:description" content="Lo que DocFácil hace hoy para consultorios dentales en México.">
    <meta name="twitter:image" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">

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
        "description": "Lo que DocFácil hace hoy para consultorios dentales en México, para quien lo compara con {{ $competitor['name'] }}. Para precios y funciones de {{ $competitor['name'] }}, consulte su sitio oficial.",
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
                "name": "¿Qué hace DocFácil para un consultorio dental?",
                "acceptedAnswer": { "@@type": "Answer", "text": "Agenda, recordatorios por WhatsApp a 1 clic (DocFácil abre su WhatsApp con el mensaje escrito y usted le da enviar), odontograma FDI, expediente pensado para la NOM-004 (notas y recetas que se bloquean a las 24 horas, diagnósticos con CIE-10), recetas PDF con cédula, presupuestos que el paciente acepta en línea y cobros. Hecho en México para consultorios mexicanos." }
            },
            {
                "@@type": "Question",
                "name": "¿Cuánto cuesta DocFácil?",
                "acceptedAnswer": { "@@type": "Answer", "text": "Plan Free de por vida (1 doctor, 15 pacientes, 10 citas al mes). Básico $499 MXN al mes, Pro $999 y Clínica $1,999. Si paga el año, le sale en 10 meses. 15 días con todo, sin tarjeta, y garantía de 30 días sobre su primer pago." }
            },
            {
                "@@type": "Question",
                "name": "¿Cómo comparo DocFácil con {{ $competitor['name'] }}?",
                "acceptedAnswer": { "@@type": "Answer", "text": "Para comparar precios y funciones actuales de {{ $competitor['name'] }}, consulte su sitio oficial. De DocFácil puede probar todo 15 días gratis, sin tarjeta, con sus propios pacientes." }
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
                COMPARATIVA
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                DocFácil <span class="text-gray-400">vs</span> {{ $competitor['name'] }}
            </h1>
            <p class="mt-6 text-lg text-gray-600 leading-relaxed max-w-2xl mx-auto">
                Si está comparando opciones para su consultorio dental, aquí está lo que DocFácil hace hoy, con precios en pesos.
                Para comparar precios y funciones actuales de {{ $competitor['name'] }}, consulte su sitio oficial.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="vs_hero" data-track-competitor="{{ $slug }}"
                   class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-bold rounded-xl hover:shadow-lg transition">
                    Probar DocFácil 15 días gratis →
                </a>
                <a href="#tabla-comparativa" class="w-full sm:w-auto px-8 py-3.5 bg-gray-100 text-gray-800 font-semibold rounded-xl hover:bg-gray-200 transition">
                    Ver qué revisar ↓
                </a>
            </div>
        </div>
    </section>

    {{-- Resumen --}}
    <section class="py-12 bg-white">
        <div class="max-w-3xl mx-auto px-4">
            <div class="rounded-2xl border-2 border-amber-200 bg-amber-50/60 p-6 sm:p-8">
                <div class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-3">En resumen</div>
                <p class="text-gray-800 leading-relaxed">
                    <strong>DocFácil</strong> está hecho en México para consultorios dentales mexicanos: expediente pensado para la NOM-004 (notas y recetas que se bloquean a las 24 horas), recetas PDF con cédula, odontograma FDI, presupuestos, cobros y recordatorios por WhatsApp a 1 clic. Plan Free de por vida y Básico desde $499 MXN al mes.
                    <br><br>
                    Para comparar precios y funciones actuales de <strong>{{ $competitor['name'] }}</strong>, consulte su sitio oficial. Abajo le dejamos las preguntas que conviene hacerle a cualquier sistema.
                </p>
            </div>
        </div>
    </section>

    {{-- Tabla: qué revisar y qué hace DocFácil --}}
    <section id="tabla-comparativa" class="py-14 sm:py-20 bg-gray-50">
        <div class="max-w-5xl mx-auto px-4">
            <h2 class="text-2xl sm:text-4xl font-extrabold text-center mb-3">Qué revisar al comparar</h2>
            <p class="text-center text-gray-600 mb-10">Lo que DocFácil hace hoy, punto por punto. Haga las mismas preguntas a {{ $competitor['name'] }}.</p>

            <div class="overflow-x-auto bg-white rounded-2xl shadow-sm border border-gray-200">
                <table class="w-full text-sm sm:text-base">
                    <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50">
                            <th class="text-left py-4 px-4 sm:px-6 font-bold text-gray-700">Qué revisar</th>
                            <th class="text-left py-4 px-4 sm:px-6 font-bold text-teal-700">DocFácil</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $filas = [
                                ['Hecho en', 'México, para consultorios dentales mexicanos'],
                                ['Pensado para la NOM-004', 'Notas y recetas que se bloquean a las 24 horas, historial de cambios y diagnósticos con CIE-10'],
                                ['Aviso de privacidad y datos de sus pacientes', 'Aviso de privacidad publicado; DocFácil es encargado de los datos de sus pacientes'],
                                ['Recordatorios por WhatsApp', 'A 1 clic: DocFácil abre su WhatsApp con el mensaje escrito y usted le da enviar'],
                                ['Odontograma FDI', 'Interactivo, con 13 condiciones; lo que falta tratar se vuelve presupuesto'],
                                ['Recetas', 'PDF con su cédula y espacio para su firma; el paciente las ve en su portal'],
                                ['Presupuestos y cobros', 'El paciente acepta el presupuesto en línea; cobros, abonos y saldo por WhatsApp a 1 clic'],
                                ['Precio', 'Free de por vida · Básico $499 MXN al mes · Pro $999 · Clínica $1,999'],
                                ['Cómo paga su plan', 'Con tarjeta, o por SPEI'],
                                ['Soporte', 'Por WhatsApp con el fundador, en español'],
                                ['Garantía', '30 días de garantía sobre su primer pago'],
                            ];
                        @endphp
                        @foreach($filas as [$criterio, $docfacil])
                        <tr>
                            <td class="py-3 px-4 sm:px-6 font-semibold text-gray-700">{{ $criterio }}</td>
                            <td class="py-3 px-4 sm:px-6 text-emerald-700 font-semibold">{{ $docfacil }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-4 text-center text-sm text-gray-500">Para comparar precios y funciones actuales de {{ $competitor['name'] }}, consulte su sitio oficial.</p>
        </div>
    </section>

    {{-- Fortalezas y limitaciones de DocFácil --}}
    <section class="py-14 sm:py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4">
            <h2 class="text-2xl sm:text-4xl font-extrabold text-center mb-10">Lo que DocFácil sí hace y lo que no</h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="rounded-2xl p-6 border-2 border-teal-200 bg-teal-50/40">
                    <h3 class="text-xl font-extrabold text-teal-900 mb-4">Lo que sí hace</h3>
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span><span><strong>WhatsApp a 1 clic</strong>: abre su propio WhatsApp con el mensaje ya escrito, sin pagar mensajes a Meta</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span><span><strong>Hecho en México para consultorios mexicanos</strong>: pensado para la NOM-004, en español de México, precios en pesos</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span><span><strong>Soporte con el fundador</strong>: Omar Lerma responde por WhatsApp</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span><span><strong>Plan Free de por vida</strong>: 1 doctor, 15 pacientes y 10 citas al mes, sin tarjeta</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span><span><strong>Garantía</strong>: 30 días de garantía sobre su primer pago</span></li>
                        <li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span><span><strong>Se instala como app</strong> en el celular (iPhone o Android) desde el navegador</span></li>
                    </ul>
                </div>

                <div class="rounded-2xl p-6 border-2 border-gray-200 bg-gray-50/40">
                    <h3 class="text-xl font-extrabold text-gray-900 mb-4">Lo que no hace (todavía)</h3>
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start gap-2"><span class="text-gray-400 mt-0.5">·</span><span>Es nuevo (2026): todavía no tiene casos publicados de otros consultorios</span></li>
                        <li class="flex items-start gap-2"><span class="text-gray-400 mt-0.5">·</span><span>No emite factura CFDI</span></li>
                        <li class="flex items-start gap-2"><span class="text-gray-400 mt-0.5">·</span><span>No manda WhatsApp solo: usted le da enviar a cada mensaje</span></li>
                        <li class="flex items-start gap-2"><span class="text-gray-400 mt-0.5">·</span><span>No cobra al paciente en línea: usted registra el cobro</span></li>
                        <li class="flex items-start gap-2"><span class="text-gray-400 mt-0.5">·</span><span>Hecho para consultorios dentales</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Para quién es --}}
    <section class="py-14 sm:py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-2xl sm:text-4xl font-extrabold mb-10">¿Es DocFácil para su consultorio?</h2>
            <div class="grid md:grid-cols-2 gap-6 text-left">
                <div class="rounded-2xl bg-gradient-to-br from-teal-50 to-cyan-50 border border-teal-200 p-6">
                    <div class="text-xs font-bold text-teal-700 uppercase tracking-wider mb-2">Le conviene DocFácil si...</div>
                    <ul class="space-y-2.5 text-sm text-gray-700">
                        <li class="flex items-start gap-2"><span class="text-teal-600">→</span> Su consultorio dental está en México (1 a 3 sillones)</li>
                        <li class="flex items-start gap-2"><span class="text-teal-600">→</span> Quiere mandar recordatorios por WhatsApp desde su propio número</li>
                        <li class="flex items-start gap-2"><span class="text-teal-600">→</span> Quiere un expediente pensado para la NOM-004 (notas que se bloquean, recetas con cédula)</li>
                        <li class="flex items-start gap-2"><span class="text-teal-600">→</span> Prefiere pagar en pesos</li>
                        <li class="flex items-start gap-2"><span class="text-teal-600">→</span> Quiere hablar directo con quien hace el sistema</li>
                    </ul>
                </div>
                <div class="rounded-2xl bg-white border border-gray-200 p-6">
                    <div class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Antes de decidir</div>
                    <p class="text-sm text-gray-700 leading-relaxed">Pruebe DocFácil 15 días con sus propios pacientes, sin tarjeta. Para comparar precios y funciones actuales de {{ $competitor['name'] }}, consulte su sitio oficial y pida el precio en pesos por escrito.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Migración --}}
    <section class="py-14 bg-white">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-2xl sm:text-3xl font-extrabold mb-4">¿Ya usa {{ $competitor['name'] }}? Le ayudamos a pasar sus pacientes</h2>
            <p class="text-gray-600 leading-relaxed mb-6">
                Si quiere probar DocFácil, mándenos por WhatsApp su Excel o CSV con sus pacientes y lo subimos a su cuenta al empezar, sin costo.
            </p>
            <a href="https://wa.me/526682493398?text={{ urlencode("Hola Omar, uso {$competitor['name']} y quiero probar DocFácil. ¿Me puede ayudar a pasar mis pacientes?") }}"
               target="_blank"
               data-track="whatsapp_clicked" data-track-location="vs_migration" data-track-competitor="{{ $slug }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-green-500 text-white font-bold rounded-xl hover:bg-green-600 transition">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2z"/></svg>
                Hablar con Omar
            </a>
        </div>
    </section>

    {{-- CTA final --}}
    <section class="py-16 sm:py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-teal-600 via-cyan-600 to-teal-700"></div>
        <div class="max-w-3xl mx-auto px-4 text-center relative z-10">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white">Pruébelo usted mismo</h2>
            <p class="mt-4 text-lg text-teal-100">15 días con todo, sin tarjeta. 30 días de garantía sobre su primer pago.</p>
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
            <p class="text-xs text-gray-600">&copy; {{ date('Y') }} DocFácil. Comparativa con fines informativos. Las marcas mencionadas pertenecen a sus respectivos dueños.</p>
        </div>
    </footer>
</body>
</html>
