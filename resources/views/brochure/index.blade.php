<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Brochure DocFácil — Software para Consultorios</title>
    <meta name="description" content="Brochure completo de DocFácil: features, testimonios, precios y cómo empezar. Software para consultorios médicos y dentales en México.">
    <meta name="theme-color" content="#14b8a6">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="canonical" href="{{ url('/brochure') }}">

    {{-- OpenGraph --}}
    <meta property="og:title" content="Brochure DocFácil — Software para Consultorios Médicos y Dentales">
    <meta property="og:description" content="Conoce DocFácil: agenda, expedientes, recetas PDF, recordatorios WhatsApp y más. Descarga el brochure completo.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-image.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ url('/brochure') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_MX">
    <meta property="og:site_name" content="DocFácil">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @keyframes fadeUp { from{opacity:0; transform:translateY(20px)} to{opacity:1; transform:translateY(0)} }
        .animate-fade-up { animation: fadeUp 0.7s ease forwards; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

{{-- Navbar simple --}}
<nav class="sticky top-0 bg-white/90 backdrop-blur-lg border-b border-gray-100 z-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
        <a href="/" class="flex items-center gap-2">
            <img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" class="h-10">
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('brochure.pdf') }}" class="hidden sm:inline-flex items-center gap-1.5 text-sm font-semibold text-teal-700 border border-teal-200 hover:bg-teal-50 rounded-lg px-3 py-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3"/></svg>
                Descargar PDF
            </a>
            <a href="{{ $registerUrl }}" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition">
                Prueba gratis
            </a>
        </div>
    </div>
</nav>

{{-- Portada --}}
<section class="relative overflow-hidden bg-gradient-to-br from-teal-600 via-teal-500 to-cyan-500 text-white">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.1),transparent_50%)]"></div>
    <div class="max-w-5xl mx-auto px-6 py-24 sm:py-32 text-center relative z-10">
        <span class="inline-block bg-white/20 text-white text-xs font-semibold px-4 py-1.5 rounded-full mb-6 tracking-wider">BROCHURE · VERSIÓN 2026</span>
        <h1 class="text-5xl sm:text-7xl font-extrabold mb-4 tracking-tight leading-none">DocFácil</h1>
        <div class="w-16 h-1 bg-white mx-auto rounded-full mb-6"></div>
        <p class="text-lg sm:text-2xl opacity-95 max-w-2xl mx-auto leading-relaxed">
            Software para consultorios médicos y dentales.<br>
            <strong>Tu consultorio, organizado y al día.</strong>
        </p>
        <div class="mt-10 inline-block bg-white text-gray-900 rounded-xl px-6 py-4 shadow-2xl">
            <div class="text-teal-600 font-bold text-lg">500+ consultorios · 15K+ citas · 4.9/5</div>
            <div class="text-gray-500 text-xs mt-1">Más de 500 consultorios en México confían en DocFácil</div>
        </div>
    </div>
</section>

{{-- Sección: Para quién es --}}
<section class="py-20 px-6 bg-white">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-4xl sm:text-5xl font-extrabold text-teal-700 mb-3 tracking-tight">Para doctores que aún dependen del papel</h2>
        <p class="text-gray-600 text-lg mb-10">DocFácil está diseñado para consultorios pequeños y medianos en México que quieren digitalizarse sin complicarse.</p>

        <div class="grid sm:grid-cols-3 gap-5">
            <div class="bg-teal-50 border-l-4 border-teal-500 p-5 rounded-xl">
                <div class="text-3xl mb-2">🦷</div>
                <h3 class="font-bold text-teal-800 mb-1">Consultorios dentales (1-3 doctores)</h3>
                <p class="text-sm text-gray-700">Odontólogos, ortodoncistas, endodoncistas. 30-200 pacientes/mes. Listos para dejar el papel o Excel.</p>
            </div>
            <div class="bg-teal-50 border-l-4 border-teal-500 p-5 rounded-xl">
                <div class="text-3xl mb-2">🩺</div>
                <h3 class="font-bold text-teal-800 mb-1">Consultorios médicos</h3>
                <p class="text-sm text-gray-700">Médicos generales y especialistas que facturan $20K-200K/mes y pierden tiempo en admin.</p>
            </div>
            <div class="bg-teal-50 border-l-4 border-teal-500 p-5 rounded-xl">
                <div class="text-3xl mb-2">🏥</div>
                <h3 class="font-bold text-teal-800 mb-1">Clínicas pequeñas</h3>
                <p class="text-sm text-gray-700">3-10 doctores con agenda compartida, comisiones entre doctores y reportes por profesional.</p>
            </div>
        </div>

        <h3 class="text-2xl font-bold text-gray-900 mt-16 mb-5">4 dolores del día a día</h3>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg"><strong class="text-red-800 block mb-1">📋 Agenda caótica</strong><span class="text-sm text-gray-700">Papel/Excel: pierdes citas, no buscas rápido, cada cambio pesa.</span></div>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg"><strong class="text-red-800 block mb-1">📞 Pacientes no llegan</strong><span class="text-sm text-gray-700">30% no se presenta. Consultas perdidas que no regresan.</span></div>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg"><strong class="text-red-800 block mb-1">✍ Recetas a mano</strong><span class="text-sm text-gray-700">Letra ilegible, sin copia, sin respaldo. Riesgo legal.</span></div>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg"><strong class="text-red-800 block mb-1">💸 No sabes si ganas</strong><span class="text-sm text-gray-700">Sin reportes ni control de cobros. Decisiones a ojo.</span></div>
        </div>
    </div>
</section>

{{-- Sección: Features --}}
<section class="py-20 px-6 bg-gray-50">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-4xl sm:text-5xl font-extrabold text-teal-700 mb-3 tracking-tight">Todo lo que necesitas, integrado</h2>
        <p class="text-gray-600 text-lg mb-10">No contrates 5 apps distintas. DocFácil cubre todo el flujo del consultorio.</p>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($pages['features'] as $f)
            <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-lg hover:border-teal-300 transition">
                <div class="text-3xl mb-3">{{ $f['icon'] }}</div>
                <h3 class="font-bold text-teal-700 mb-2">{{ $f['title'] }}</h3>
                <p class="text-sm text-gray-600 leading-relaxed">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Sección: Testimonios + Caso --}}
<section class="py-20 px-6 bg-white">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-4xl sm:text-5xl font-extrabold text-teal-700 mb-3 tracking-tight">Lo que dicen los doctores</h2>
        <p class="text-gray-600 text-lg mb-10">500+ consultorios en México. Estos son algunos de sus resultados.</p>

        <div class="grid sm:grid-cols-3 gap-5 mb-12">
            @foreach ($pages['testimonials'] as $t)
            <div class="bg-gray-50 border-l-4 border-teal-500 p-5 rounded-xl">
                <blockquote class="italic text-gray-800 mb-3 leading-relaxed">"{{ $t['quote'] }}"</blockquote>
                <div class="text-sm text-gray-600"><strong class="text-teal-700">{{ $t['name'] }}</strong> · {{ $t['specialty'] }} · {{ $t['city'] }}</div>
            </div>
            @endforeach
        </div>

        <div class="bg-gradient-to-br from-teal-600 to-cyan-500 rounded-2xl p-8 text-white">
            <h3 class="text-2xl font-bold mb-2">Caso: Dra. Fernández (CDMX)</h3>
            <p class="opacity-95 mb-6">Consultorio dental individual. Atendía 80 pacientes/mes con 30% de inasistencia. Después de DocFácil con recordatorios WhatsApp:</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="text-center"><div class="text-4xl font-extrabold">8%</div><div class="text-xs opacity-90 mt-1">inasistencia final (antes 30%)</div></div>
                <div class="text-center"><div class="text-4xl font-extrabold">+22</div><div class="text-xs opacity-90 mt-1">citas atendidas al mes</div></div>
                <div class="text-center"><div class="text-4xl font-extrabold">$17K</div><div class="text-xs opacity-90 mt-1">ingreso adicional/mes</div></div>
                <div class="text-center"><div class="text-4xl font-extrabold">2h</div><div class="text-xs opacity-90 mt-1">ahorradas al día</div></div>
            </div>
            <p class="italic mt-6 opacity-95 text-sm">"Lo que pago por DocFácil lo recupero en 2 días de consulta extra al mes. Es la mejor inversión que he hecho."</p>
        </div>
    </div>
</section>

{{-- Sección: Precios --}}
<section class="py-20 px-6 bg-gray-50">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-4xl sm:text-5xl font-extrabold text-teal-700 mb-3 tracking-tight">Precios pensados para cada consultorio</h2>
        <p class="text-gray-600 text-lg mb-10">Sin contratos. Sin tarjeta para probar. Cancela cuando quieras.</p>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($pages['plans'] as $p)
            <div class="relative bg-white rounded-2xl p-6 border-2 {{ !empty($p['popular']) ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}">
                @if (!empty($p['popular']))
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full">POPULAR</span>
                @endif
                <h4 class="text-xl font-bold text-gray-900">{{ $p['name'] }}</h4>
                <div class="my-4">
                    <span class="text-4xl font-extrabold {{ !empty($p['popular']) ? 'text-orange-600' : 'text-teal-600' }}">${{ number_format($p['price']) }}</span>
                    <span class="text-gray-500 text-sm">/mes</span>
                </div>
                <p class="text-xs text-gray-500 mb-4 min-h-[40px]">{{ $p['ideal'] }}</p>
                <ul class="space-y-2">
                    @foreach ($p['features'] as $feat)
                    <li class="text-sm flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> {{ $feat }}</li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
        <p class="text-center text-sm text-gray-500 mt-6">14 días gratis con todas las funciones del plan Pro. Sin tarjeta.</p>
    </div>
</section>

{{-- Sección: Cómo empezar --}}
<section class="py-20 px-6 bg-white">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-4xl sm:text-5xl font-extrabold text-teal-700 mb-3 tracking-tight">Empieza en 3 pasos</h2>
        <p class="text-gray-600 text-lg mb-10">Sin instalaciones. Sin tarjeta. Sin perder un solo paciente.</p>

        <div class="grid sm:grid-cols-3 gap-5 mb-12">
            <div class="bg-gray-50 rounded-xl p-6 text-center">
                <div class="text-5xl font-extrabold text-teal-500 mb-3">1</div>
                <h4 class="font-bold text-gray-900 mb-2">Regístrate</h4>
                <p class="text-sm text-gray-600">Crea tu cuenta en 2 minutos. Sin tarjeta. 14 días gratis con plan Pro.</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-6 text-center">
                <div class="text-5xl font-extrabold text-teal-500 mb-3">2</div>
                <h4 class="font-bold text-gray-900 mb-2">Importa pacientes</h4>
                <p class="text-sm text-gray-600">Sube tu Excel o carga manualmente. Te ayudamos si son más de 200.</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-6 text-center">
                <div class="text-5xl font-extrabold text-teal-500 mb-3">3</div>
                <h4 class="font-bold text-gray-900 mb-2">Úsalo en consulta</h4>
                <p class="text-sm text-gray-600">Abre DocFácil en celular, tablet o PC. Desde el día 1 ya estás digital.</p>
            </div>
        </div>

        {{-- CTA final --}}
        <div class="bg-gradient-to-br from-teal-600 to-cyan-500 rounded-3xl p-8 sm:p-12 text-white">
            <div class="grid sm:grid-cols-2 gap-8 items-center">
                <div>
                    <h3 class="text-3xl font-extrabold mb-3">Empieza gratis ahora</h3>
                    <p class="opacity-95 mb-6">Escanea el QR o habla directamente con Omar, el fundador de DocFácil.</p>
                    <div class="space-y-2 text-sm">
                        <div><strong>Omar Lerma · Fundador</strong></div>
                        <div>📱 <a href="{{ $whatsappLink }}" class="underline">668 249 3398</a> (WhatsApp)</div>
                        <div>✉ <a href="mailto:contacto@docfacil.com" class="underline">contacto@docfacil.com</a></div>
                        <div>🌐 <a href="{{ url('/') }}" class="underline">docfacil.tu-app.co</a></div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <a href="{{ $registerUrl }}" class="inline-flex items-center px-5 py-3 bg-white text-teal-700 font-bold rounded-xl hover:scale-105 transition">Crear cuenta gratis</a>
                        <a href="{{ route('brochure.pdf') }}" class="inline-flex items-center px-5 py-3 border-2 border-white text-white font-semibold rounded-xl hover:bg-white/10 transition">Descargar PDF</a>
                    </div>
                </div>
                <div class="text-center">
                    <img src="{{ $qrDataUri }}" alt="QR registro" class="inline-block w-48 h-48 bg-white p-3 rounded-2xl">
                    <p class="text-xs opacity-90 mt-3">Escanea para registrarte</p>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="py-8 px-6 bg-gray-900 text-gray-400 text-sm text-center">
    DocFácil © {{ date('Y') }} · Hecho en México con cariño para doctores mexicanos ·
    <a href="{{ url('/') }}" class="text-teal-400 hover:text-teal-300">docfacil.tu-app.co</a>
</footer>

</body>
</html>
