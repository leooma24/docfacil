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

        /* Browser chrome mockup */
        .browser-chrome { background: #e5e7eb; padding: 8px 12px; border-radius: 12px 12px 0 0; display: flex; align-items: center; gap: 6px; }
        .browser-chrome .dot { width: 10px; height: 10px; border-radius: 50%; }
        .browser-chrome .url { flex: 1; background: white; border-radius: 6px; padding: 4px 12px; font-size: 11px; color: #6b7280; margin-left: 8px; text-align: center; }

        /* Soft grid bg */
        .bg-grid {
            background-image: linear-gradient(rgba(20,184,166,0.06) 1px, transparent 1px), linear-gradient(90deg, rgba(20,184,166,0.06) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* Animated gradient glow */
        .glow::before {
            content: ''; position: absolute; inset: -40px;
            background: radial-gradient(circle at 30% 40%, rgba(20,184,166,0.25), transparent 60%),
                        radial-gradient(circle at 70% 60%, rgba(6,182,212,0.25), transparent 60%);
            z-index: 0; filter: blur(40px);
        }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased">

{{-- Navbar --}}
<nav class="sticky top-0 bg-white/90 backdrop-blur-lg border-b border-gray-100 z-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
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

{{-- HERO --}}
<section class="relative overflow-hidden bg-gradient-to-br from-teal-600 via-teal-500 to-cyan-500 text-white">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.12),transparent_50%)]"></div>
    <div class="absolute inset-0 bg-grid opacity-20"></div>
    <div class="max-w-6xl mx-auto px-6 pt-20 pb-12 sm:pt-24 text-center relative z-10">
        <span class="inline-block bg-white/20 backdrop-blur-sm text-white text-xs font-semibold px-4 py-1.5 rounded-full mb-6 tracking-wider ring-1 ring-white/30">BROCHURE · EDICIÓN 2026</span>
        <h1 class="text-5xl sm:text-7xl font-extrabold mb-4 tracking-tight leading-none">DocFácil</h1>
        <div class="w-16 h-1 bg-white/70 mx-auto rounded-full mb-6"></div>
        <p class="text-lg sm:text-2xl opacity-95 max-w-3xl mx-auto leading-relaxed">
            Software para consultorios médicos y dentales.<br>
            <strong>Tu consultorio, organizado y al día.</strong>
        </p>

        <div class="flex flex-wrap justify-center gap-3 mt-8">
            <a href="{{ $registerUrl }}" class="inline-flex items-center px-6 py-3 bg-white text-teal-700 font-bold rounded-xl hover:scale-105 shadow-xl transition">Empieza gratis</a>
            <a href="{{ route('brochure.pdf') }}" class="inline-flex items-center px-6 py-3 border-2 border-white/80 text-white font-semibold rounded-xl hover:bg-white/10 transition">Descargar brochure PDF</a>
        </div>

        {{-- Hero screenshot with browser mockup --}}
        <div class="mt-16 max-w-5xl mx-auto relative">
            <div class="absolute -inset-6 bg-white/10 rounded-3xl blur-2xl"></div>
            <div class="relative bg-white rounded-xl shadow-2xl ring-1 ring-black/10 overflow-hidden">
                <div class="browser-chrome">
                    <span class="dot bg-red-400"></span>
                    <span class="dot bg-yellow-400"></span>
                    <span class="dot bg-green-400"></span>
                    <div class="url">docfacil.tu-app.co/doctor</div>
                </div>
                <img src="{{ $screens['dashboard'] }}" alt="Panel principal DocFácil" class="w-full block">
            </div>
        </div>

        {{-- Stats strip --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-4xl mx-auto mt-12 text-left">
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 ring-1 ring-white/20">
                <div class="text-3xl font-extrabold">500+</div>
                <div class="text-xs opacity-90 mt-1">consultorios activos</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 ring-1 ring-white/20">
                <div class="text-3xl font-extrabold">15K+</div>
                <div class="text-xs opacity-90 mt-1">citas gestionadas</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 ring-1 ring-white/20">
                <div class="text-3xl font-extrabold">40%</div>
                <div class="text-xs opacity-90 mt-1">menos inasistencias</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 ring-1 ring-white/20">
                <div class="text-3xl font-extrabold">4.9/5</div>
                <div class="text-xs opacity-90 mt-1">satisfacción</div>
            </div>
        </div>
    </div>
    <div class="h-16 bg-gradient-to-b from-transparent to-white/0"></div>
</section>

{{-- ICP --}}
<section class="py-20 px-6 bg-white">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <span class="inline-block text-xs font-bold text-teal-600 tracking-wider uppercase mb-3">Para quién es</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Para doctores que aún<br>dependen del papel</h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">Diseñado para consultorios pequeños y medianos en México que quieren digitalizarse sin complicarse.</p>
        </div>

        <div class="grid sm:grid-cols-3 gap-6">
            <div class="group relative bg-gradient-to-br from-teal-50 to-cyan-50 border border-teal-100 p-7 rounded-2xl hover:shadow-xl hover:-translate-y-1 transition">
                <div class="absolute top-5 right-5 text-4xl opacity-30 group-hover:opacity-100 transition">🦷</div>
                <span class="inline-block bg-teal-600 text-white text-xs font-bold px-2.5 py-1 rounded-md mb-3">DENTAL</span>
                <h3 class="font-bold text-lg text-gray-900 mb-2">Consultorios dentales (1–3 doctores)</h3>
                <p class="text-sm text-gray-700 leading-relaxed">Odontólogos generales, ortodoncistas, endodoncistas. Atienden 30–200 pacientes al mes y están listos para dejar el papel o Excel.</p>
            </div>
            <div class="group relative bg-gradient-to-br from-cyan-50 to-blue-50 border border-cyan-100 p-7 rounded-2xl hover:shadow-xl hover:-translate-y-1 transition">
                <div class="absolute top-5 right-5 text-4xl opacity-30 group-hover:opacity-100 transition">🩺</div>
                <span class="inline-block bg-cyan-600 text-white text-xs font-bold px-2.5 py-1 rounded-md mb-3">MÉDICO</span>
                <h3 class="font-bold text-lg text-gray-900 mb-2">Médicos generales y especialistas</h3>
                <p class="text-sm text-gray-700 leading-relaxed">Pediatras, ginecólogos, dermatólogos, internistas. Facturan $20K–$200K al mes y pierden tiempo en tareas administrativas.</p>
            </div>
            <div class="group relative bg-gradient-to-br from-violet-50 to-fuchsia-50 border border-violet-100 p-7 rounded-2xl hover:shadow-xl hover:-translate-y-1 transition">
                <div class="absolute top-5 right-5 text-4xl opacity-30 group-hover:opacity-100 transition">🏥</div>
                <span class="inline-block bg-violet-600 text-white text-xs font-bold px-2.5 py-1 rounded-md mb-3">CLÍNICA</span>
                <h3 class="font-bold text-lg text-gray-900 mb-2">Clínicas pequeñas multi-doctor</h3>
                <p class="text-sm text-gray-700 leading-relaxed">3–10 doctores con agenda compartida, comisiones entre profesionales y reportes por doctor o sucursal.</p>
            </div>
        </div>
    </div>
</section>

{{-- Dolores --}}
<section class="py-20 px-6 bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <span class="inline-block text-xs font-bold text-red-500 tracking-wider uppercase mb-3">Dolores que vivimos</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Si te identificas con 2 o más,<br>DocFácil es para ti</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white border border-red-100 p-5 rounded-2xl shadow-sm">
                <div class="text-3xl mb-3">📋</div>
                <strong class="block text-red-700 mb-1.5">Agenda caótica</strong>
                <span class="text-sm text-gray-600 leading-relaxed">Papel y Excel: pierdes citas, no buscas rápido, cada cambio pesa.</span>
            </div>
            <div class="bg-white border border-red-100 p-5 rounded-2xl shadow-sm">
                <div class="text-3xl mb-3">📞</div>
                <strong class="block text-red-700 mb-1.5">Pacientes no llegan</strong>
                <span class="text-sm text-gray-600 leading-relaxed">30% no se presenta. Consultas perdidas que no regresan.</span>
            </div>
            <div class="bg-white border border-red-100 p-5 rounded-2xl shadow-sm">
                <div class="text-3xl mb-3">✍</div>
                <strong class="block text-red-700 mb-1.5">Recetas a mano</strong>
                <span class="text-sm text-gray-600 leading-relaxed">Letra ilegible, sin copia, sin respaldo. Riesgo legal.</span>
            </div>
            <div class="bg-white border border-red-100 p-5 rounded-2xl shadow-sm">
                <div class="text-3xl mb-3">💸</div>
                <strong class="block text-red-700 mb-1.5">No sabes si ganas</strong>
                <span class="text-sm text-gray-600 leading-relaxed">Sin reportes ni control de cobros. Decisiones a ojo.</span>
            </div>
        </div>
    </div>
</section>

{{-- FEATURES con screenshots alternados --}}
<section class="py-24 px-6 bg-white">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
            <span class="inline-block text-xs font-bold text-teal-600 tracking-wider uppercase mb-3">Funciones clave</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Todo el flujo del consultorio,<br>integrado</h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">No contrates 5 apps distintas. DocFácil cubre todo en una sola plataforma.</p>
        </div>

        {{-- Feature 1: Agenda --}}
        <div class="grid md:grid-cols-2 gap-10 items-center mb-24">
            <div class="md:order-1 order-2">
                <span class="inline-block bg-teal-100 text-teal-800 text-xs font-bold px-3 py-1 rounded-full mb-4">01 · AGENDA</span>
                <h3 class="text-3xl font-extrabold text-gray-900 mb-4 tracking-tight">Calendario inteligente + recordatorios WhatsApp automáticos</h3>
                <p class="text-gray-600 mb-5 leading-relaxed">Vista diaria, semanal, mensual. Arrastra citas para reagendar, colores por estado. Los pacientes reciben recordatorios 24h y 2h antes — hasta 40% menos inasistencias.</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Multi-doctor con colores por profesional</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Acceso desde PC, tablet o celular</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Recordatorios WhatsApp automáticos incluidos</li>
                </ul>
            </div>
            <div class="md:order-2 order-1 relative">
                <div class="absolute -inset-8 bg-gradient-to-br from-teal-200 to-cyan-200 rounded-3xl blur-2xl opacity-50"></div>
                <div class="relative bg-white rounded-xl shadow-2xl ring-1 ring-black/5 overflow-hidden">
                    <div class="browser-chrome"><span class="dot bg-red-400"></span><span class="dot bg-yellow-400"></span><span class="dot bg-green-400"></span><div class="url">/doctor/calendario</div></div>
                    <img src="{{ $screens['calendario'] }}" alt="Calendario DocFácil" class="w-full block">
                </div>
            </div>
        </div>

        {{-- Feature 2: Expediente --}}
        <div class="grid md:grid-cols-2 gap-10 items-center mb-24">
            <div class="relative">
                <div class="absolute -inset-8 bg-gradient-to-br from-cyan-200 to-blue-200 rounded-3xl blur-2xl opacity-50"></div>
                <div class="relative bg-white rounded-xl shadow-2xl ring-1 ring-black/5 overflow-hidden">
                    <div class="browser-chrome"><span class="dot bg-red-400"></span><span class="dot bg-yellow-400"></span><span class="dot bg-green-400"></span><div class="url">/doctor/expediente-clinico</div></div>
                    <img src="{{ $screens['expediente'] }}" alt="Expediente clínico" class="w-full block">
                </div>
            </div>
            <div>
                <span class="inline-block bg-cyan-100 text-cyan-800 text-xs font-bold px-3 py-1 rounded-full mb-4">02 · EXPEDIENTE</span>
                <h3 class="text-3xl font-extrabold text-gray-900 mb-4 tracking-tight">Expediente clínico digital completo</h3>
                <p class="text-gray-600 mb-5 leading-relaxed">Historial completo por paciente: diagnósticos, tratamientos, alergias, fotos clínicas, notas SOAP. Búsqueda instantánea y cumplimiento con NOM-004-SSA3.</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Todo organizado por paciente y consulta</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Fotos antes/después sin límite</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Búsqueda global por nombre, diagnóstico o medicamento</li>
                </ul>
            </div>
        </div>

        {{-- Feature 3: Recetas --}}
        <div class="grid md:grid-cols-2 gap-10 items-center mb-24">
            <div class="md:order-1 order-2">
                <span class="inline-block bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full mb-4">03 · RECETAS</span>
                <h3 class="text-3xl font-extrabold text-gray-900 mb-4 tracking-tight">Recetas PDF profesionales y legibles</h3>
                <p class="text-gray-600 mb-5 leading-relaxed">Generadas con logo, cédula profesional y firma digital. Se descargan en un clic y se envían al paciente por WhatsApp o email — sin papel, sin letra ilegible.</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Plantilla personalizada por doctor</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Historial de recetas por paciente</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Firma digital con validez legal</li>
                </ul>
            </div>
            <div class="md:order-2 order-1 relative">
                <div class="absolute -inset-8 bg-gradient-to-br from-emerald-200 to-teal-200 rounded-3xl blur-2xl opacity-50"></div>
                <div class="relative bg-white rounded-xl shadow-2xl ring-1 ring-black/5 overflow-hidden">
                    <div class="browser-chrome"><span class="dot bg-red-400"></span><span class="dot bg-yellow-400"></span><span class="dot bg-green-400"></span><div class="url">/doctor/recetas</div></div>
                    <img src="{{ $screens['recetas'] }}" alt="Recetas PDF" class="w-full block">
                </div>
            </div>
        </div>

        {{-- Feature 4: Odontograma --}}
        <div class="grid md:grid-cols-2 gap-10 items-center mb-24">
            <div class="relative">
                <div class="absolute -inset-8 bg-gradient-to-br from-violet-200 to-fuchsia-200 rounded-3xl blur-2xl opacity-50"></div>
                <div class="relative bg-white rounded-xl shadow-2xl ring-1 ring-black/5 overflow-hidden">
                    <div class="browser-chrome"><span class="dot bg-red-400"></span><span class="dot bg-yellow-400"></span><span class="dot bg-green-400"></span><div class="url">/doctor/odontogramas</div></div>
                    <img src="{{ $screens['odontograma'] }}" alt="Odontograma interactivo" class="w-full block">
                </div>
            </div>
            <div>
                <span class="inline-block bg-violet-100 text-violet-800 text-xs font-bold px-3 py-1 rounded-full mb-4">04 · ODONTOGRAMA</span>
                <h3 class="text-3xl font-extrabold text-gray-900 mb-4 tracking-tight">Odontograma interactivo (dental)</h3>
                <p class="text-gray-600 mb-5 leading-relaxed">Diagrama dental con 13 condiciones. Haces clic en el diente, eliges el estado, se guarda automático. Compartible con el paciente por WhatsApp.</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Historial visual de cada pieza dental</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Colores por tipo de tratamiento</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Exportable como PDF profesional</li>
                </ul>
            </div>
        </div>

        {{-- Feature 5: Cobros --}}
        <div class="grid md:grid-cols-2 gap-10 items-center mb-24">
            <div class="md:order-1 order-2">
                <span class="inline-block bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-full mb-4">05 · COBROS</span>
                <h3 class="text-3xl font-extrabold text-gray-900 mb-4 tracking-tight">Cobros, pagos y reportes de ingresos</h3>
                <p class="text-gray-600 mb-5 leading-relaxed">Registro de cada pago con método (efectivo, transferencia, tarjeta). Control automático de pendientes por paciente. Envía el link de cobro por WhatsApp en un clic.</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Reporte de ingresos del mes en tiempo real</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Alertas de cobros vencidos</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Pagos parciales, abonos y recordatorios</li>
                </ul>
            </div>
            <div class="md:order-2 order-1 relative">
                <div class="absolute -inset-8 bg-gradient-to-br from-amber-200 to-orange-200 rounded-3xl blur-2xl opacity-50"></div>
                <div class="relative bg-white rounded-xl shadow-2xl ring-1 ring-black/5 overflow-hidden">
                    <div class="browser-chrome"><span class="dot bg-red-400"></span><span class="dot bg-yellow-400"></span><span class="dot bg-green-400"></span><div class="url">/doctor/cobros</div></div>
                    <img src="{{ $screens['cobros'] }}" alt="Cobros e ingresos" class="w-full block">
                </div>
            </div>
        </div>

        {{-- Feature 6: Pacientes --}}
        <div class="grid md:grid-cols-2 gap-10 items-center">
            <div class="relative">
                <div class="absolute -inset-8 bg-gradient-to-br from-sky-200 to-teal-200 rounded-3xl blur-2xl opacity-50"></div>
                <div class="relative bg-white rounded-xl shadow-2xl ring-1 ring-black/5 overflow-hidden">
                    <div class="browser-chrome"><span class="dot bg-red-400"></span><span class="dot bg-yellow-400"></span><span class="dot bg-green-400"></span><div class="url">/doctor/pacientes</div></div>
                    <img src="{{ $screens['pacientes'] }}" alt="Lista de pacientes" class="w-full block">
                </div>
            </div>
            <div>
                <span class="inline-block bg-sky-100 text-sky-800 text-xs font-bold px-3 py-1 rounded-full mb-4">06 · PACIENTES</span>
                <h3 class="text-3xl font-extrabold text-gray-900 mb-4 tracking-tight">Pacientes centralizados y buscables</h3>
                <p class="text-gray-600 mb-5 leading-relaxed">Tabla con teléfono, email, último motivo, próxima cita. Un clic para ver el expediente completo, mandar WhatsApp o agendar siguiente consulta.</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Búsqueda instantánea por nombre o teléfono</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Alertas de inactivos, cumpleaños, recetas vencidas</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Importación masiva desde Excel</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- +6 features compactas --}}
<section class="py-20 px-6 bg-gray-50 border-y border-gray-100">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-10">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Y 6 funciones más incluidas</h3>
            <p class="text-gray-600">Todas disponibles desde el plan Pro.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $extras = [
                    ['icon' => '📱', 'title' => 'Check-in con QR', 'desc' => 'El paciente escanea al llegar, firma consentimiento en tablet.'],
                    ['icon' => '✍', 'title' => 'Firma digital legal', 'desc' => 'Consentimientos con timestamp y respaldo legal.'],
                    ['icon' => '👥', 'title' => 'Portal del paciente', 'desc' => 'Tus pacientes ven sus citas, recetas e historial.'],
                    ['icon' => '📊', 'title' => 'Dashboard con gráficas', 'desc' => 'Ingresos, citas por doctor, pacientes activos.'],
                    ['icon' => '🔔', 'title' => 'Alertas inteligentes', 'desc' => 'Inactivos, cumpleaños, cobros atrasados.'],
                    ['icon' => '🏥', 'title' => 'Multi-doctor / Multi-sede', 'desc' => 'Con comisiones automáticas entre doctores.'],
                ];
            @endphp
            @foreach ($extras as $e)
            <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-md hover:border-teal-300 transition">
                <div class="text-2xl mb-2">{{ $e['icon'] }}</div>
                <h4 class="font-bold text-gray-900 mb-1">{{ $e['title'] }}</h4>
                <p class="text-sm text-gray-600 leading-relaxed">{{ $e['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CASO DRA. FERNÁNDEZ --}}
<section class="py-24 px-6 bg-white">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <span class="inline-block text-xs font-bold text-teal-600 tracking-wider uppercase mb-3">Caso de éxito</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Dra. Fernández — CDMX</h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">Consultorio dental individual. 80 pacientes/mes con 30% de inasistencia. Esto cambió con DocFácil:</p>
        </div>

        {{-- Stats card --}}
        <div class="bg-gradient-to-br from-teal-600 via-teal-500 to-cyan-500 rounded-3xl p-8 sm:p-12 text-white mb-10 shadow-2xl">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-5xl sm:text-6xl font-extrabold">8%</div>
                    <div class="text-sm opacity-90 mt-2">inasistencia final<br><span class="text-xs opacity-75">(antes 30%)</span></div>
                </div>
                <div class="text-center">
                    <div class="text-5xl sm:text-6xl font-extrabold">+22</div>
                    <div class="text-sm opacity-90 mt-2">citas atendidas<br><span class="text-xs opacity-75">al mes</span></div>
                </div>
                <div class="text-center">
                    <div class="text-5xl sm:text-6xl font-extrabold">$17K</div>
                    <div class="text-sm opacity-90 mt-2">ingreso adicional<br><span class="text-xs opacity-75">al mes</span></div>
                </div>
                <div class="text-center">
                    <div class="text-5xl sm:text-6xl font-extrabold">2h</div>
                    <div class="text-sm opacity-90 mt-2">ahorradas<br><span class="text-xs opacity-75">al día</span></div>
                </div>
            </div>
        </div>

        {{-- Barras antes/después --}}
        <div class="max-w-2xl mx-auto">
            <h4 class="font-bold text-gray-900 mb-4 text-center">Inasistencia mensual: antes vs. después</h4>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-sm mb-1"><span class="font-semibold text-gray-700">Antes de DocFácil</span><span class="font-bold text-red-600">30%</span></div>
                    <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden"><div class="bg-gradient-to-r from-red-400 to-red-600 h-4 rounded-full" style="width:95%"></div></div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1"><span class="font-semibold text-gray-700">Con DocFácil</span><span class="font-bold text-emerald-600">8%</span></div>
                    <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden"><div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-4 rounded-full" style="width:25%"></div></div>
                </div>
            </div>
            <blockquote class="mt-8 italic text-lg text-gray-700 text-center border-l-4 border-teal-500 pl-4">
                "Lo que pago por DocFácil lo recupero en 2 días de consulta extra al mes. Es la mejor inversión que he hecho."<br>
                <span class="not-italic text-sm text-gray-500 mt-2 block">— Dra. María Fernández, Odontóloga</span>
            </blockquote>
        </div>
    </div>
</section>

{{-- TESTIMONIOS --}}
<section class="py-20 px-6 bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <span class="inline-block text-xs font-bold text-teal-600 tracking-wider uppercase mb-3">Testimonios</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Lo que dicen los doctores</h2>
        </div>
        <div class="grid sm:grid-cols-3 gap-6">
            @php $colors = ['from-teal-500 to-cyan-500', 'from-cyan-500 to-blue-500', 'from-violet-500 to-fuchsia-500']; @endphp
            @foreach ($pages['testimonials'] as $i => $t)
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-lg transition">
                <div class="text-4xl text-teal-200 leading-none mb-2">"</div>
                <blockquote class="italic text-gray-800 mb-6 leading-relaxed">{{ $t['quote'] }}</blockquote>
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <div class="w-11 h-11 rounded-full bg-gradient-to-br {{ $colors[$i % 3] }} text-white font-bold flex items-center justify-center text-sm">
                        {{ strtoupper(substr(explode(' ', $t['name'])[1] ?? $t['name'], 0, 1)) }}{{ strtoupper(substr(explode(' ', $t['name'])[2] ?? '', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-gray-900 text-sm">{{ $t['name'] }}</div>
                        <div class="text-xs text-gray-500">{{ $t['specialty'] }} · {{ $t['city'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ECOSISTEMA --}}
<section class="py-24 px-6 bg-white">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14">
            <span class="inline-block text-xs font-bold text-teal-600 tracking-wider uppercase mb-3">Ecosistema integrado</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Todo viene listo, sin instalar nada</h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">Sin configuraciones técnicas. Sin contratar 5 proveedores. Todo funciona desde el primer día.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="group bg-gradient-to-br from-green-50 to-emerald-50 border border-green-100 p-6 rounded-2xl hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-green-500 text-white flex items-center justify-center text-xl font-bold mb-4">💬</div>
                <h4 class="font-bold text-gray-900 mb-1">WhatsApp Business</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Envía recordatorios, recetas y links de cobro directo al chat del paciente desde tu número.</p>
            </div>
            <div class="group bg-gradient-to-br from-blue-50 to-cyan-50 border border-blue-100 p-6 rounded-2xl hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-blue-500 text-white flex items-center justify-center text-xl font-bold mb-4">✉</div>
                <h4 class="font-bold text-gray-900 mb-1">Correo automático</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Confirmaciones de cita, recibos de pago y seguimientos post-consulta automáticos.</p>
            </div>
            <div class="group bg-gradient-to-br from-violet-50 to-purple-50 border border-violet-100 p-6 rounded-2xl hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-violet-500 text-white flex items-center justify-center text-xl font-bold mb-4">💳</div>
                <h4 class="font-bold text-gray-900 mb-1">Pagos en línea</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Tu paciente paga con tarjeta o transferencia desde el link que le envías por WhatsApp.</p>
            </div>
            <div class="group bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100 p-6 rounded-2xl hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl font-bold mb-4">📱</div>
                <h4 class="font-bold text-gray-900 mb-1">App PWA instalable</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Se instala en celular o tablet como app nativa. Funciona con internet intermitente.</p>
            </div>
            <div class="group bg-gradient-to-br from-sky-50 to-cyan-50 border border-sky-100 p-6 rounded-2xl hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-sky-500 text-white flex items-center justify-center text-xl font-bold mb-4">☁</div>
                <h4 class="font-bold text-gray-900 mb-1">Respaldo en la nube</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Backups diarios automáticos. Sin USBs ni archivos perdidos.</p>
            </div>
            <div class="group bg-gradient-to-br from-teal-50 to-emerald-50 border border-teal-100 p-6 rounded-2xl hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-teal-500 text-white flex items-center justify-center text-xl font-bold mb-4">👥</div>
                <h4 class="font-bold text-gray-900 mb-1">Portal del paciente</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Tus pacientes ven citas, recetas e historial. Reduce llamadas rutinarias.</p>
            </div>
        </div>
    </div>
</section>

{{-- SEGURIDAD --}}
<section class="py-20 px-6 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <span class="inline-block text-xs font-bold text-teal-300 tracking-wider uppercase mb-3">Seguridad y cumplimiento</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold mb-4 tracking-tight">Tus datos y los de tus pacientes,<br>protegidos</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-2xl">
                <div class="text-2xl mb-3">🔒</div>
                <h4 class="font-bold mb-1">Cifrado TLS 1.3</h4>
                <p class="text-sm text-gray-300 leading-relaxed">Todas las conexiones y datos en tránsito viajan cifrados extremo a extremo.</p>
            </div>
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-2xl">
                <div class="text-2xl mb-3">🇲🇽</div>
                <h4 class="font-bold mb-1">Datos en servidores mexicanos</h4>
                <p class="text-sm text-gray-300 leading-relaxed">Cumplimiento total con LFPDPPP (Ley Federal de Protección de Datos).</p>
            </div>
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-2xl">
                <div class="text-2xl mb-3">📋</div>
                <h4 class="font-bold mb-1">NOM-004-SSA3</h4>
                <p class="text-sm text-gray-300 leading-relaxed">Expediente clínico estructurado conforme a la norma oficial mexicana.</p>
            </div>
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-2xl">
                <div class="text-2xl mb-3">💾</div>
                <h4 class="font-bold mb-1">Backups diarios automáticos</h4>
                <p class="text-sm text-gray-300 leading-relaxed">Restauración punto-en-el-tiempo hasta 30 días atrás.</p>
            </div>
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-2xl">
                <div class="text-2xl mb-3">🔐</div>
                <h4 class="font-bold mb-1">Roles y permisos</h4>
                <p class="text-sm text-gray-300 leading-relaxed">Cada usuario ve solo lo que necesita. Auditoría de accesos.</p>
            </div>
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-2xl">
                <div class="text-2xl mb-3">📤</div>
                <h4 class="font-bold mb-1">Exportación libre</h4>
                <p class="text-sm text-gray-300 leading-relaxed">Descarga todos tus datos cuando quieras, en CSV o PDF. Tú eres dueño.</p>
            </div>
        </div>
    </div>
</section>

{{-- PRECIOS --}}
<section class="py-24 px-6 bg-white">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <span class="inline-block text-xs font-bold text-teal-600 tracking-wider uppercase mb-3">Precios</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Un plan para cada consultorio</h2>
            <p class="text-gray-600 text-lg">Sin contratos. Sin tarjeta para probar. Cancela cuando quieras.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($pages['plans'] as $p)
            <div class="relative bg-white rounded-2xl p-7 border-2 {{ !empty($p['popular']) ? 'border-orange-500 shadow-2xl scale-105 z-10' : 'border-gray-200 hover:border-teal-300' }} transition">
                @if (!empty($p['popular']))
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-orange-500 to-amber-500 text-white text-xs font-bold px-4 py-1 rounded-full shadow-lg">★ POPULAR</span>
                @endif
                <h4 class="text-xl font-bold text-gray-900">{{ $p['name'] }}</h4>
                <div class="my-4">
                    <span class="text-5xl font-extrabold {{ !empty($p['popular']) ? 'text-orange-600' : 'text-teal-600' }}">${{ number_format($p['price']) }}</span>
                    <span class="text-gray-500 text-sm">/mes</span>
                </div>
                <p class="text-xs text-gray-500 mb-5 min-h-[40px]">{{ $p['ideal'] }}</p>
                <ul class="space-y-2 mb-6">
                    @foreach ($p['features'] as $feat)
                    <li class="text-sm flex items-start gap-2"><span class="text-teal-500 font-bold mt-0.5">✓</span> <span class="text-gray-700">{{ $feat }}</span></li>
                    @endforeach
                </ul>
                <a href="{{ $registerUrl }}" class="block text-center py-2.5 rounded-lg text-sm font-bold transition {{ !empty($p['popular']) ? 'bg-orange-500 text-white hover:bg-orange-600' : 'bg-gray-100 text-gray-900 hover:bg-teal-500 hover:text-white' }}">
                    {{ $p['price'] == 0 ? 'Empezar gratis' : 'Elegir plan' }}
                </a>
            </div>
            @endforeach
        </div>
        <p class="text-center text-sm text-gray-500 mt-8">14 días gratis con todas las funciones del plan Pro. Sin tarjeta. Sin compromiso.</p>
    </div>
</section>

{{-- CÓMO EMPEZAR --}}
<section class="py-24 px-6 bg-gray-50">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14">
            <span class="inline-block text-xs font-bold text-teal-600 tracking-wider uppercase mb-3">Cómo empezar</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Listos en 3 pasos</h2>
            <p class="text-gray-600 text-lg">Sin instalaciones. Sin tarjeta. Sin perder un solo paciente.</p>
        </div>

        <div class="grid sm:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition border border-gray-100">
                <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-500 text-white rounded-xl flex items-center justify-center text-2xl font-extrabold mb-4 shadow-lg">1</div>
                <h4 class="font-bold text-gray-900 mb-2 text-lg">Regístrate</h4>
                <p class="text-sm text-gray-600 mb-4 leading-relaxed">Crea tu cuenta en 2 minutos. Sin tarjeta. 14 días gratis con el plan Pro completo.</p>
                <div class="rounded-lg overflow-hidden border border-gray-200">
                    <img src="{{ $screens['landing'] }}" alt="Registro" class="w-full block">
                </div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition border border-gray-100">
                <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-500 text-white rounded-xl flex items-center justify-center text-2xl font-extrabold mb-4 shadow-lg">2</div>
                <h4 class="font-bold text-gray-900 mb-2 text-lg">Carga tus pacientes</h4>
                <p class="text-sm text-gray-600 mb-4 leading-relaxed">Sube tu Excel o captura manualmente. Nosotros te ayudamos si tienes más de 200 pacientes.</p>
                <div class="rounded-lg overflow-hidden border border-gray-200">
                    <img src="{{ $screens['pacientes'] }}" alt="Pacientes" class="w-full block">
                </div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition border border-gray-100">
                <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white rounded-xl flex items-center justify-center text-2xl font-extrabold mb-4 shadow-lg">3</div>
                <h4 class="font-bold text-gray-900 mb-2 text-lg">Agenda tu primer día</h4>
                <p class="text-sm text-gray-600 mb-4 leading-relaxed">Abre la agenda, crea tu primera cita, recibe tu primer recordatorio WhatsApp automático.</p>
                <div class="rounded-lg overflow-hidden border border-gray-200">
                    <img src="{{ $screens['calendario'] }}" alt="Agenda" class="w-full block">
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CTA FINAL --}}
<section class="py-24 px-6 bg-white">
    <div class="max-w-5xl mx-auto">
        <div class="relative overflow-hidden bg-gradient-to-br from-teal-600 via-teal-500 to-cyan-500 rounded-3xl p-10 sm:p-14 text-white shadow-2xl">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_20%,rgba(255,255,255,0.15),transparent_50%)]"></div>
            <div class="grid sm:grid-cols-2 gap-10 items-center relative z-10">
                <div>
                    <h3 class="text-3xl sm:text-4xl font-extrabold mb-4 tracking-tight">Empieza gratis ahora</h3>
                    <p class="opacity-95 mb-6 text-lg">Escanea el QR o habla directamente con Omar, el fundador de DocFácil.</p>
                    <div class="space-y-2 text-sm bg-white/10 rounded-xl p-4 ring-1 ring-white/20 mb-6 backdrop-blur-sm">
                        <div class="font-bold text-base">Omar Lerma · Fundador</div>
                        <div class="flex items-center gap-2">📱 <a href="{{ $whatsappLink }}" class="underline font-semibold">668 249 3398</a> (WhatsApp directo)</div>
                        <div class="flex items-center gap-2">✉ <a href="mailto:contacto@docfacil.com" class="underline">contacto@docfacil.com</a></div>
                        <div class="flex items-center gap-2">🌐 <a href="{{ url('/') }}" class="underline">docfacil.tu-app.co</a></div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ $registerUrl }}" class="inline-flex items-center px-6 py-3 bg-white text-teal-700 font-bold rounded-xl hover:scale-105 shadow-xl transition">Crear cuenta gratis</a>
                        <a href="{{ route('brochure.pdf') }}" class="inline-flex items-center px-6 py-3 border-2 border-white/80 text-white font-semibold rounded-xl hover:bg-white/10 transition">Descargar PDF</a>
                    </div>
                </div>
                <div class="text-center">
                    <img src="{{ $qrDataUri }}" alt="QR registro" class="inline-block w-56 h-56 bg-white p-4 rounded-2xl shadow-2xl">
                    <p class="text-xs opacity-90 mt-4">Escanea con tu celular para registrarte</p>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="py-10 px-6 bg-gray-900 text-gray-400 text-sm text-center">
    <div class="max-w-5xl mx-auto">
        <div class="flex justify-center gap-6 mb-4 flex-wrap">
            <span class="inline-block bg-teal-500/10 text-teal-300 px-3 py-1 rounded-full text-xs font-semibold">✓ Hecho en México</span>
            <span class="inline-block bg-teal-500/10 text-teal-300 px-3 py-1 rounded-full text-xs font-semibold">✓ Soporte en español</span>
            <span class="inline-block bg-teal-500/10 text-teal-300 px-3 py-1 rounded-full text-xs font-semibold">✓ PWA instalable</span>
            <span class="inline-block bg-teal-500/10 text-teal-300 px-3 py-1 rounded-full text-xs font-semibold">✓ Sin anuncios</span>
        </div>
        DocFácil © {{ date('Y') }} · Hecho en México con cariño para doctores mexicanos ·
        <a href="{{ url('/') }}" class="text-teal-400 hover:text-teal-300">docfacil.tu-app.co</a>
    </div>
</footer>

</body>
</html>
