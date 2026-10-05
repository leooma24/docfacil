<!DOCTYPE html>
<html lang="es" style="scroll-behavior:smooth;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alternativa a {{ $competitor['name'] }} en México — DocFácil para Consultorios Dentales</title>
    <meta name="description" content="¿Busca una alternativa a {{ $competitor['name'] }}? DocFácil: software dental hecho en México, precios en pesos, expediente pensado para la NOM-004 y WhatsApp a 1 clic. 15 días gratis.">
    <meta name="theme-color" content="#14b8a6">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">

    <meta property="og:title" content="Alternativa a {{ $competitor['name'] }} en México · DocFácil">
    <meta property="og:description" content="DocFácil como alternativa a {{ $competitor['name'] }} para consultorios dentales en México.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">
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
        "headline": "DocFácil como alternativa a {{ $competitor['name'] }} en México",
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
        "name": "DocFácil como alternativa a {{ $competitor['name'] }} para consultorios dentales en México",
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
                Una alternativa a <span class="text-teal-600">{{ $competitor['name'] }}</span><br>
                <span class="text-gray-600">para consultorios dentales en México</span>
            </h1>
            <p class="mt-6 text-lg text-gray-600 leading-relaxed">
                Si está buscando otra opción, aquí está lo que DocFácil hace hoy y qué conviene revisar en cualquier sistema.
                Para comparar precios y funciones actuales de {{ $competitor['name'] }}, consulte su sitio oficial.
            </p>
        </div>
    </section>

    {{-- Criterios para elegir --}}
    <section class="py-12 bg-gray-50">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-2xl font-extrabold mb-6">Qué revisar al elegir</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="bg-white p-5 rounded-xl border border-gray-100">
                    <div class="text-2xl mb-2">🇲🇽</div>
                    <div class="font-bold mb-1">Reglas mexicanas</div>
                    <p class="text-sm text-gray-600">Que el expediente le ayude con la NOM-004 y que haya aviso de privacidad para los datos de sus pacientes.</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-100">
                    <div class="text-2xl mb-2">💰</div>
                    <div class="font-bold mb-1">Precio en pesos</div>
                    <p class="text-sm text-gray-600">Un precio en pesos no cambia con el tipo de cambio. Pídalo por escrito.</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-100">
                    <div class="text-2xl mb-2">📱</div>
                    <div class="font-bold mb-1">WhatsApp</div>
                    <p class="text-sm text-gray-600">Sus pacientes ya usan WhatsApp. Revise si el sistema le deja el mensaje listo para mandar.</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-100">
                    <div class="text-2xl mb-2">🤝</div>
                    <div class="font-bold mb-1">Soporte en español</div>
                    <p class="text-sm text-gray-600">Que le contesten en su horario y en su idioma cuando algo no sale.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- DocFácil --}}
    <section class="py-14 sm:py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4">
            <h2 class="text-2xl sm:text-4xl font-extrabold text-center mb-12">DocFácil como alternativa a {{ $competitor['name'] }}</h2>

            <div class="rounded-2xl border-2 border-teal-500 bg-gradient-to-br from-teal-50/40 to-white p-6 sm:p-8 mb-6 relative">
                <div class="absolute -top-3 left-6 px-3 py-1 bg-teal-600 text-white text-xs font-bold rounded-full uppercase tracking-wider">
                    Hecho en México
                </div>
                <div class="flex items-start justify-between gap-4 flex-wrap mt-2">
                    <div>
                        <h3 class="text-2xl font-extrabold text-gray-900">DocFácil</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Hecho en México para consultorios mexicanos</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-extrabold text-teal-600">$499 MXN<span class="text-sm font-normal text-gray-500">/mes</span></div>
                        <div class="text-xs text-gray-500">+ Plan Free de por vida</div>
                    </div>
                </div>
                <p class="mt-4 text-gray-700 leading-relaxed">
                    Recordatorios por WhatsApp a 1 clic: DocFácil abre su WhatsApp con el mensaje ya escrito y usted le da enviar.
                    Expediente pensado para la NOM-004 (notas y recetas que se bloquean a las 24 horas), recetas PDF con cédula,
                    odontograma FDI, presupuestos que el paciente acepta en línea y cobros. Soporte por WhatsApp con el fundador.
                    Plan Free de por vida (1 doctor, 15 pacientes, 10 citas al mes) y 30 días de garantía sobre su primer pago.
                </p>
                <div class="mt-5 grid sm:grid-cols-2 gap-2 text-sm">
                    <div class="flex items-center gap-2"><span class="text-emerald-600">✓</span> WhatsApp a 1 clic desde su número</div>
                    <div class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Pensado para la NOM-004 (notas que se bloquean)</div>
                    <div class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Datos aislados por consultorio</div>
                    <div class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Soporte por WhatsApp del fundador</div>
                    <div class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Odontograma FDI con 13 condiciones</div>
                    <div class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Recetas PDF con cédula</div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ url('/doctor/register') }}" data-track="cta_clicked" data-track-location="alt_docfacil_card" data-track-competitor="{{ $slug }}"
                       class="px-5 py-2.5 bg-teal-600 text-white font-bold rounded-xl hover:bg-teal-700 transition text-sm">
                        Probar 15 días gratis →
                    </a>
                    <a href="{{ url("/vs/{$slug}") }}" class="px-5 py-2.5 bg-white border border-gray-200 hover:border-teal-300 text-gray-700 font-semibold rounded-xl transition text-sm">
                        DocFácil vs {{ $competitor['name'] }}
                    </a>
                </div>
            </div>

            <p class="text-center text-sm text-gray-500">Para comparar precios y funciones actuales de {{ $competitor['name'] }}, consulte su sitio oficial.</p>

            @if(count($others))
            <div class="mt-10 text-center">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Otras comparativas</h3>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    @foreach($others as $o)
                        <a href="{{ url("/vs/{$o['slug']}") }}" class="px-4 py-2 bg-white border border-gray-200 hover:border-teal-300 rounded-lg text-sm font-medium text-gray-700 hover:text-teal-700 transition">
                            DocFácil vs {{ $o['name'] }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </section>

    {{-- Cómo decidir --}}
    <section class="py-14 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-center mb-10">¿Cómo decidir?</h2>
            <div class="grid md:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-xl border border-teal-200 shadow-sm">
                    <div class="text-xs font-bold text-teal-700 uppercase tracking-wider mb-2">Pruebe con sus pacientes</div>
                    <p class="text-sm text-gray-700 leading-relaxed">DocFácil tiene 15 días con todo, sin tarjeta. Cargue una semana de agenda y vea si le acomoda.</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-200">
                    <div class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Pida el precio por escrito</div>
                    <p class="text-sm text-gray-700 leading-relaxed">En pesos, con lo que incluye cada plan y cómo se cancela.</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-gray-200">
                    <div class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Revise el expediente</div>
                    <p class="text-sm text-gray-700 leading-relaxed">Si las notas se bloquean y si la receta lleva su cédula.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA final --}}
    <section class="py-16 sm:py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-teal-600 via-cyan-600 to-teal-700"></div>
        <div class="max-w-3xl mx-auto px-4 text-center relative z-10">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white">Empiece con DocFácil hoy</h2>
            <p class="mt-4 text-lg text-teal-100">15 días con todo, sin tarjeta. 30 días de garantía sobre su primer pago.</p>
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
