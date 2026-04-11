<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DocFácil — Software para Consultorios Médicos y Dentales</title>
    <meta name="description" content="Gestiona tu consultorio médico o dental de forma fácil. Agenda de citas, expedientes clínicos, recetas PDF, recordatorios WhatsApp y más.">
    <meta name="theme-color" content="#14b8a6">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:title" content="DocFácil — Software para Consultorios Médicos y Dentales">
    <meta property="og:description" content="Gestiona tu consultorio de forma fácil. Agenda, expedientes, recetas PDF, WhatsApp y más.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-image.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DocFácil">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="DocFácil — Software para Consultorios Médicos y Dentales">
    <meta name="twitter:description" content="Gestiona tu consultorio de forma fácil. Agenda, expedientes, recetas PDF, WhatsApp y más.">
    <meta name="twitter:image" content="https://docfacil.tu-app.co/images/og-image.png">
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
            { "@@type": "Offer", "price": "249", "priceCurrency": "MXN", "name": "Plan Básico" },
            { "@@type": "Offer", "price": "499", "priceCurrency": "MXN", "name": "Plan Pro" },
            { "@@type": "Offer", "price": "999", "priceCurrency": "MXN", "name": "Plan Clínica" }
        ],
        "aggregateRating": { "@@type": "AggregateRating", "ratingValue": "4.9", "reviewCount": "127" }
    }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
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
                <a href="#contacto" class="text-sm text-gray-600 hover:text-teal-600 transition font-medium">Contacto</a>
                <a href="{{ url('/doctor/login') }}" class="text-sm text-gray-600 hover:text-teal-600 transition font-medium">Iniciar sesion</a>
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
            <a href="#contacto" class="block py-2 text-gray-600">Contacto</a>
            <a href="{{ url('/doctor/login') }}" class="block py-2 text-gray-600">Iniciar sesion</a>
            <a href="{{ url('/doctor/register') }}" class="block py-2 px-4 bg-teal-600 text-white text-center rounded-lg">Prueba gratis</a>
        </div>
    </nav>

    {{-- Hero con background animado --}}
    <section class="relative pt-32 pb-24 px-4 overflow-hidden">
        {{-- Animated blobs --}}
        <div class="absolute top-20 -left-40 w-96 h-96 bg-teal-200/30 rounded-full blur-3xl animate-blob"></div>
        <div class="absolute top-40 -right-40 w-96 h-96 bg-cyan-200/30 rounded-full blur-3xl animate-blob" style="animation-delay:3s"></div>
        <div class="absolute bottom-0 left-1/3 w-80 h-80 bg-teal-100/20 rounded-full blur-3xl animate-blob" style="animation-delay:6s"></div>

        <div class="max-w-5xl mx-auto text-center relative z-10">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-teal-50 text-teal-700 text-sm font-semibold rounded-full mb-8 animate-fade-up border border-teal-100">
                <span class="w-2 h-2 bg-teal-500 rounded-full animate-pulse"></span>
                +500 consultorios ya usan DocFacil
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-7xl font-extrabold tracking-tight leading-[1.1] animate-fade-up delay-100">
                Deja de perder tiempo<br>
                <span class="bg-gradient-to-r from-teal-600 via-cyan-500 to-teal-600 bg-clip-text text-transparent animate-gradient">
                    con el papeleo
                </span>
            </h1>

            <p class="mt-8 text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed animate-fade-up delay-200">
                Mientras tu sigues con libretas, hojas de Excel y notas perdidas,
                <strong class="text-gray-900">tus pacientes esperan un consultorio moderno</strong>.
                Automatiza tu consultorio en minutos.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 animate-fade-up delay-300">
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

    {{-- Stats counter --}}
    <section class="py-12 bg-gray-900">
        <div class="max-w-5xl mx-auto px-4 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div data-animate class="animate-fade-up">
                <div class="text-3xl sm:text-4xl font-extrabold text-white" data-count="500">500+</div>
                <div class="text-sm text-gray-400 mt-1">Consultorios activos</div>
            </div>
            <div data-animate class="animate-fade-up delay-100">
                <div class="text-3xl sm:text-4xl font-extrabold text-white" data-count="15000">15,000+</div>
                <div class="text-sm text-gray-400 mt-1">Citas gestionadas</div>
            </div>
            <div data-animate class="animate-fade-up delay-200">
                <div class="text-3xl sm:text-4xl font-extrabold text-white" data-count="40">40%</div>
                <div class="text-sm text-gray-400 mt-1">Menos inasistencias</div>
            </div>
            <div data-animate class="animate-fade-up delay-300">
                <div class="text-3xl sm:text-4xl font-extrabold text-teal-400">4.9/5</div>
                <div class="text-sm text-gray-400 mt-1">Satisfaccion de usuarios</div>
            </div>
        </div>
    </section>

    {{-- Pain vs Solution --}}
    <section id="problema" class="py-24">
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
                        ['title' => 'Agenda en papel o Excel', 'desc' => 'Pierdes citas, no puedes buscar rapido, se te olvidan los horarios y tus pacientes se van.'],
                        ['title' => 'Pacientes que no llegan', 'desc' => 'No les recuerdas y el 30% no se presenta. Pierdes dinero y tiempo cada dia.'],
                        ['title' => 'Recetas a mano', 'desc' => 'Letra ilegible, errores de dosis, sin registro digital. Tus pacientes merecen algo mejor.'],
                        ['title' => 'No sabes cuanto ganas', 'desc' => 'Sin reportes claros. No sabes que servicio te deja mas, ni cuantos pacientes perdiste.'],
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
                        ['title' => 'Agenda inteligente en la nube', 'desc' => 'Calendario visual, horarios por doctor, desde tu celular o computadora. Nunca pierdas una cita.'],
                        ['title' => 'WhatsApp automatico', 'desc' => 'Recordatorio 24hrs antes con un clic o automatico. Reduce inasistencias un 40% desde el primer mes.'],
                        ['title' => 'Recetas PDF profesionales', 'desc' => 'Con tu nombre, cedula, logo del consultorio. Descargables e imprimibles en segundos.'],
                        ['title' => 'Reportes en tiempo real', 'desc' => 'Ingresos del mes, servicios mas solicitados, citas perdidas. Toma decisiones con datos, no con intuicion.'],
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
    <section id="features" class="py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-animate>
                <span class="inline-flex items-center px-3 py-1 bg-teal-50 text-teal-700 text-xs font-semibold rounded-full mb-4">12+ MODULOS INCLUIDOS</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Todo lo que tu consultorio necesita</h2>
                <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">Cada funcion fue disenada pensando en el dia a dia real de un doctor o dentista</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6" data-animate>
                @php
                $features = [
                    ['icon' => '📅', 'title' => 'Agenda de citas', 'desc' => 'Calendario visual con horarios por doctor. Configura duracion por servicio. Citas de hoy en un clic.'],
                    ['icon' => '👥', 'title' => 'Perfil 360 del paciente', 'desc' => 'Historial, recetas, pagos, citas y odontograma en una sola pantalla. Vista completa de cada paciente.'],
                    ['icon' => '📋', 'title' => 'Expediente clinico', 'desc' => 'Diagnosticos, tratamientos, signos vitales, radiografias. Historial completo con auditoria de cambios.'],
                    ['icon' => '📱', 'title' => 'WhatsApp automatico', 'desc' => 'Recordatorio 24hrs antes. Manual con un clic. Reduce inasistencias hasta un 40%.'],
                    ['icon' => '📝', 'title' => 'Recetas PDF', 'desc' => 'Genera recetas profesionales con cedula, medicamentos, dosis y firma. Descarga o envia por WhatsApp.'],
                    ['icon' => '💳', 'title' => 'Cobros y pagos', 'desc' => 'Registra pagos, metodos, pendientes. Alertas de cobros vencidos. Reportes de ingresos en tiempo real.'],
                    ['icon' => '🦷', 'title' => 'Odontograma interactivo', 'desc' => 'Diagrama dental con 13 condiciones. Click para marcar. Se muestra solo si eres dentista.'],
                    ['icon' => '🔍', 'title' => 'Buscador global Ctrl+K', 'desc' => 'Busca pacientes, servicios o citas al instante. Acceso rapido desde cualquier pantalla.'],
                    ['icon' => '✍️', 'title' => 'Firma digital', 'desc' => 'El paciente firma consentimientos con el dedo en tablet o celular. Guardado seguro con IP y fecha.'],
                    ['icon' => '🔔', 'title' => 'Notificaciones', 'desc' => 'Alertas de citas, pagos pendientes, pacientes inactivos. Todo en tu panel sin buscar.'],
                    ['icon' => '📊', 'title' => 'Reportes y graficas', 'desc' => 'Ingresos diarios/mensuales, servicios top, citas perdidas. Dashboard con datos en tiempo real.'],
                    ['icon' => '🩺', 'title' => 'Flujo de consulta guiado', 'desc' => 'Wizard paso a paso: signos vitales, diagnostico, receta, cobro y siguiente cita. Todo en 2 minutos.'],
                ];
                @endphp
                @foreach($features as $i => $feature)
                <div class="group bg-white rounded-2xl p-6 border border-gray-100 hover:border-teal-200 hover:shadow-xl hover:shadow-teal-100/50 transition-all duration-300 hover:-translate-y-2 animate-fade-up" style="animation-delay:{{ $i * 0.08 }}s">
                    <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">{{ $feature['icon'] }}</div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $feature['title'] }}</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="py-24">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-16" data-animate>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Lo que dicen nuestros doctores</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-8" data-animate>
                @php
                $testimonials = [
                    ['name' => 'Dra. Maria Fernandez', 'role' => 'Odontologa, CDMX', 'text' => 'Antes perdia 2 horas al dia organizando citas en mi libreta. Con DocFacil lo hago en 5 minutos. Mis pacientes reciben recordatorio por WhatsApp y ya casi no tengo inasistencias.', 'stars' => 5],
                    ['name' => 'Dr. Carlos Mendoza', 'role' => 'Medicina General, Guadalajara', 'text' => 'Las recetas PDF le dan un toque profesional a mi consultorio. Mis pacientes las reciben por WhatsApp y no se pierden. Ademas el expediente clinico es muy completo.', 'stars' => 5],
                    ['name' => 'Dra. Ana Torres', 'role' => 'Dentista, Monterrey', 'text' => 'El odontograma es increible, mis pacientes ven exactamente que dientes necesitan tratamiento. Antes todo era en papel. DocFacil me ahorra al menos 1 hora diaria.', 'stars' => 5],
                ];
                @endphp
                @foreach($testimonials as $i => $test)
                <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 animate-fade-up" style="animation-delay:{{ $i * 0.15 }}s">
                    <div class="flex gap-1 mb-4">
                        @for($s = 0; $s < $test['stars']; $s++)
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-6 italic">"{{ $test['text'] }}"</p>
                    <div>
                        <div class="font-bold text-gray-900">{{ $test['name'] }}</div>
                        <div class="text-sm text-gray-500">{{ $test['role'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ROI Calculator --}}
    <section id="roi" class="py-20 bg-gradient-to-br from-teal-50 to-cyan-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10" data-animate>
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white rounded-full shadow-sm mb-4">
                    <span class="text-xs font-bold text-teal-600 uppercase tracking-wide">Calculadora de ROI</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">¿Cuánto dinero te hará ganar DocFácil?</h2>
                <p class="mt-3 text-lg text-gray-600">Llena 3 datos y descubre tu ahorro mensual real.</p>
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

                <div class="bg-gradient-to-br from-teal-600 to-cyan-600 rounded-2xl p-6 md:p-8 text-white">
                    <div class="text-center mb-6">
                        <div class="text-sm font-semibold uppercase tracking-wider opacity-80">Con DocFácil ganas/ahorras al mes</div>
                        <div class="text-5xl md:text-6xl font-extrabold mt-2" x-text="'$' + totalSavings.toLocaleString('es-MX')"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                        <div class="bg-white/10 backdrop-blur rounded-xl p-4">
                            <div class="font-bold" x-text="'$' + timeSavings.toLocaleString('es-MX')"></div>
                            <div class="opacity-80 text-xs mt-1">Ahorro de tiempo<br><span x-text="(adminHours * 0.6).toFixed(0) + ' hrs/semana'"></span> liberadas</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur rounded-xl p-4">
                            <div class="font-bold" x-text="'$' + retentionGain.toLocaleString('es-MX')"></div>
                            <div class="opacity-80 text-xs mt-1">Retención de pacientes<br>WhatsApp reduce no-shows</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur rounded-xl p-4">
                            <div class="font-bold" x-text="'$' + aiGain.toLocaleString('es-MX')"></div>
                            <div class="opacity-80 text-xs mt-1">IA + dictado<br>Atiendes más pacientes</div>
                        </div>
                    </div>
                    <div class="text-center mt-6 pt-6 border-t border-white/20">
                        <div class="text-sm opacity-90">DocFácil Pro cuesta $499/mes</div>
                        <div class="text-2xl font-extrabold mt-1">
                            ROI: paga DocFácil <span x-text="(totalSavings / 499).toFixed(1)"></span>x
                        </div>
                        <a href="#pricing" class="inline-block mt-4 px-8 py-3 bg-white text-teal-700 rounded-xl font-bold hover:scale-105 transition-transform">Empezar gratis →</a>
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
                    get aiGain() {
                        // Dictado inteligente ahorra ~5 min por consulta = 1 paciente extra por día
                        return Math.round(20 * this.pricePerVisit * 0.15);
                    },
                    get totalSavings() {
                        return this.timeSavings + this.retentionGain + this.aiGain;
                    },
                };
            }
        </script>
    </section>

    {{-- Pricing --}}
    <section id="pricing" class="py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-animate>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Invierte menos de lo que cuesta una consulta</h2>
                <p class="mt-4 text-lg text-gray-600">Un plan que te ahorra horas cada semana. Empieza gratis.</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto" data-animate>
                @php
                $plans = [
                    ['name' => 'Free', 'price' => '0', 'subtitle' => 'Para siempre', 'features' => ['1 doctor', '30 pacientes', '20 citas/mes', 'Agenda basica'], 'cta' => 'Empezar gratis', 'popular' => false],
                    ['name' => 'Basico', 'price' => '249', 'subtitle' => 'por mes', 'features' => ['1 doctor', '200 pacientes', 'Citas ilimitadas', 'Dictado por voz', 'Resumen IA del paciente', 'Sugerencias de Dx con IA'], 'cta' => 'Probar 14 dias', 'popular' => false],
                    ['name' => 'Pro', 'price' => '499', 'subtitle' => 'por mes', 'features' => ['3 doctores', 'Pacientes ilimitados', 'Dictado inteligente con IA', 'Consentimientos con IA', 'Analisis IA del consultorio', 'Cobro por WhatsApp', 'Check-in con QR'], 'cta' => 'Probar 14 dias', 'popular' => true],
                    ['name' => 'Clinica', 'price' => '999', 'subtitle' => 'por mes', 'features' => ['Doctores ilimitados', 'Multi-sucursal', 'Todo del Pro', 'Soporte prioritario', 'Onboarding 1 a 1'], 'cta' => 'Contactar ventas', 'popular' => false],
                ];
                @endphp
                @foreach($plans as $i => $plan)
                <div class="relative bg-white rounded-2xl p-7 transition-all duration-300 hover:-translate-y-2 animate-fade-up {{ $plan['popular'] ? 'border-2 border-teal-500 shadow-xl shadow-teal-100/50 scale-105' : 'border border-gray-200 hover:shadow-lg' }}" style="animation-delay:{{ $i * 0.1 }}s">
                    @if($plan['popular'])
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white text-xs font-bold rounded-full shadow-lg">
                        Mas popular
                    </div>
                    @endif
                    <h3 class="text-lg font-bold text-gray-900">{{ $plan['name'] }}</h3>
                    <div class="mt-4 mb-1">
                        <span class="text-4xl font-extrabold text-gray-900">${{ $plan['price'] }}</span>
                    </div>
                    <div class="text-sm text-gray-500 mb-6">{{ $plan['subtitle'] }}</div>
                    <ul class="space-y-3 mb-8">
                        @foreach($plan['features'] as $feature)
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-teal-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ url('/doctor/register') }}" class="block w-full text-center px-4 py-3 rounded-xl font-semibold transition-all {{ $plan['popular'] ? 'bg-gradient-to-r from-teal-600 to-cyan-600 text-white hover:shadow-lg hover:shadow-teal-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ $plan['cta'] }}
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Comparison --}}
    <section id="comparison" class="py-24">
        <div class="max-w-5xl mx-auto px-4" data-animate>
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">DocFacil vs la competencia</h2>
                <p class="mt-4 text-lg text-gray-600">Mismas funciones (o mas), una fraccion del precio</p>
            </div>
            <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-sm">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-4 px-5 text-left text-gray-500 font-medium">Funcion</th>
                            <th class="py-4 px-4 text-center"><img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFacil" class="h-7 mx-auto"></th>
                            <th class="py-4 px-4 text-center text-gray-400 font-medium">Dentrix</th>
                            <th class="py-4 px-4 text-center text-gray-400 font-medium">Eaglesoft</th>
                            <th class="py-4 px-4 text-center text-gray-400 font-medium">DentalIntel</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @php
                        $comparisons = [
                            ['Agenda de citas', true, true, true, true],
                            ['Expediente clinico', true, true, true, false],
                            ['Odontograma interactivo', true, true, true, false],
                            ['Recetas PDF', true, false, false, false],
                            ['Recordatorios WhatsApp', true, false, false, false],
                            ['Consentimiento digital', true, false, true, false],
                            ['Portal del paciente', true, false, false, true],
                            ['100% web (sin instalar)', true, false, false, true],
                            ['Soporte en espanol', true, false, false, false],
                            ['Precio desde', '$0/mes', 'USD $400', 'USD $350', 'USD $299'],
                        ];
                        @endphp
                        @foreach($comparisons as $row)
                        <tr class="hover:bg-teal-50/30 transition">
                            <td class="py-3.5 px-5 font-medium text-gray-900">{{ $row[0] }}</td>
                            @for($i = 1; $i <= 4; $i++)
                            <td class="py-3.5 px-4 text-center">
                                @if(is_bool($row[$i]))
                                    @if($row[$i])
                                    <span class="inline-flex w-6 h-6 bg-teal-100 rounded-full items-center justify-center"><svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></span>
                                    @else
                                    <span class="inline-flex w-6 h-6 bg-gray-100 rounded-full items-center justify-center"><svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></span>
                                    @endif
                                @else
                                    <span class="{{ $i === 1 ? 'font-bold text-teal-600' : 'text-gray-400' }}">{{ $row[$i] }}</span>
                                @endif
                            </td>
                            @endfor
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Contact --}}
    <section id="contacto" class="py-24 bg-gradient-to-b from-white to-gray-50">
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
    <section class="py-24 relative overflow-hidden">
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
                    <img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFacil" class="h-10 mb-4 brightness-200">
                    <p class="text-sm text-gray-500">Software para consultorios medicos y dentales. Hecho en Mexico.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-300 mb-3">Producto</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#features" class="hover:text-teal-400 transition">Funciones</a></li>
                        <li><a href="#pricing" class="hover:text-teal-400 transition">Precios</a></li>
                        <li><a href="#comparison" class="hover:text-teal-400 transition">Comparativa</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-300 mb-3">Ciudades</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="/software-dental/cdmx" class="hover:text-teal-400 transition">CDMX</a></li>
                        <li><a href="/software-dental/guadalajara" class="hover:text-teal-400 transition">Guadalajara</a></li>
                        <li><a href="/software-dental/monterrey" class="hover:text-teal-400 transition">Monterrey</a></li>
                        <li><a href="/software-dental/merida" class="hover:text-teal-400 transition">Merida</a></li>
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
                    <a href="/privacidad" class="text-gray-400 hover:text-teal-400 transition">Aviso de Privacidad</a>
                    <span class="text-gray-700">&middot;</span>
                    <a href="/terminos" class="text-gray-400 hover:text-teal-400 transition">Términos y Condiciones</a>
                </div>
                <p class="text-sm text-gray-600">&copy; {{ date('Y') }} DocFacil. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

{{-- Chatbot FAQ --}}
<div id="chatbot" class="fixed bottom-6 right-6 z-50">
    <div id="chat-window" class="hidden mb-4 w-80 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-teal-600 to-cyan-600 text-white px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                <span class="font-semibold text-sm">DocFacil — Ayuda</span>
            </div>
            <button onclick="toggleChat()" class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <div id="chat-messages" class="p-4 h-72 overflow-y-auto space-y-3 text-sm">
            <div class="bg-teal-50 text-teal-800 p-3 rounded-xl rounded-tl-none">
                Hola! Soy el asistente de DocFacil. En que puedo ayudarte?
            </div>
            <div class="space-y-2" id="faq-buttons">
                <button onclick="askFaq(0)" class="block w-full text-left px-3 py-2.5 bg-gray-50 hover:bg-teal-50 rounded-xl text-xs transition border border-gray-100 hover:border-teal-200">Que es DocFacil?</button>
                <button onclick="askFaq(1)" class="block w-full text-left px-3 py-2.5 bg-gray-50 hover:bg-teal-50 rounded-xl text-xs transition border border-gray-100 hover:border-teal-200">Cuanto cuesta?</button>
                <button onclick="askFaq(2)" class="block w-full text-left px-3 py-2.5 bg-gray-50 hover:bg-teal-50 rounded-xl text-xs transition border border-gray-100 hover:border-teal-200">Para que especialidades sirve?</button>
                <button onclick="askFaq(4)" class="block w-full text-left px-3 py-2.5 bg-gray-50 hover:bg-teal-50 rounded-xl text-xs transition border border-gray-100 hover:border-teal-200">Que es el flujo de consulta?</button>
                <button onclick="askFaq(5)" class="block w-full text-left px-3 py-2.5 bg-gray-50 hover:bg-teal-50 rounded-xl text-xs transition border border-gray-100 hover:border-teal-200">Los pacientes pueden firmar digital?</button>
                <button onclick="askFaq(6)" class="block w-full text-left px-3 py-2.5 bg-gray-50 hover:bg-teal-50 rounded-xl text-xs transition border border-gray-100 hover:border-teal-200">Mis datos estan seguros?</button>
                <button onclick="askFaq(8)" class="block w-full text-left px-3 py-2.5 bg-gray-50 hover:bg-teal-50 rounded-xl text-xs transition border border-gray-100 hover:border-teal-200">Puedo probarlo sin registrarme?</button>
            </div>
        </div>
    </div>
    <button onclick="toggleChat()" class="w-14 h-14 bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-full shadow-lg hover:shadow-xl hover:shadow-teal-300/50 transition-all hover:-translate-y-1 flex items-center justify-center">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
    </button>
</div>

<script>
// PWA
if ('serviceWorker' in navigator) { navigator.serviceWorker.register('/sw.js'); }

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

// Chatbot
const faqs = [
    { q: 'Que es DocFacil?', a: 'DocFacil es un software 100% en linea para gestionar consultorios medicos y dentales. Incluye agenda, expedientes, recetas PDF, cobros, odontograma, firma digital, notificaciones y mas. Todo desde tu navegador.' },
    { q: 'Cuanto cuesta?', a: 'Plan gratuito para siempre (1 doctor, 30 pacientes). Planes desde $249/mes con IA integrada (dictado, resumen de paciente, sugerencias de diagnostico). 14 dias de prueba gratis con TODAS las funciones. Sin tarjeta de credito.' },
    { q: 'Para que especialidades funciona?', a: 'Para todas! Odontologia, medicina general, pediatria, dermatologia, ginecologia, cardiologia y mas. El sistema se adapta a tu especialidad: los dentistas ven odontograma, los pediatras ven curvas de crecimiento, etc.' },
    { q: 'Como funcionan los recordatorios?', a: 'Enviamos recordatorios automaticos por WhatsApp 24hrs antes de cada cita. Tambien puedes enviar manualmente con un clic. Reduce inasistencias hasta un 40% desde el primer mes.' },
    { q: 'Que es el flujo de consulta?', a: 'Un wizard paso a paso que guia al doctor: signos vitales, diagnostico, receta, cobro y siguiente cita. Todo en una sola pantalla, en 2 minutos. Se crea el expediente, la receta y el pago automaticamente.' },
    { q: 'Los pacientes pueden firmar digital?', a: 'Si! El paciente firma consentimientos informados con el dedo en tu tablet o celular. Se guarda con fecha, hora e IP. Genera PDF legal descargable.' },
    { q: 'Mis datos estan seguros?', a: 'Encriptacion SSL, backups automaticos, historial de cambios (quien edito que y cuando), y cumplimos con normas de proteccion de datos de salud. Tu informacion nunca se comparte.' },
    { q: 'Puedo tener varios doctores?', a: 'Si! Invita doctores a tu consultorio por email. Cada uno con su agenda, sus pacientes y su perfil. Desde el plan Pro ($499/mes) hasta 3 doctores, o plan Clinica para doctores ilimitados.' },
    { q: 'Puedo probarlo sin registrarme?', a: 'Si! Haz clic en "Ver demo en vivo" y explora el sistema con datos reales. Sin registro, sin compromiso. Cuando estes listo, crea tu cuenta gratis.' },
];

function toggleChat() { document.getElementById('chat-window').classList.toggle('hidden'); }

function askFaq(index) {
    const msgs = document.getElementById('chat-messages');
    const faq = faqs[index];
    msgs.innerHTML += `<div class="bg-gray-100 text-gray-800 p-3 rounded-xl rounded-tr-none ml-8">${faq.q}</div>`;
    setTimeout(() => {
        msgs.innerHTML += `<div class="bg-teal-50 text-teal-800 p-3 rounded-xl rounded-tl-none">${faq.a}</div>`;
        msgs.scrollTop = msgs.scrollHeight;
    }, 400);
    msgs.scrollTop = msgs.scrollHeight;
}
</script>
</body>
</html>
