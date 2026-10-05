<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Folleto DocFácil — Software para consultorios dentales</title>
    <meta name="description" content="Folleto de DocFácil: qué hace, precios y cómo empezar. Software para consultorios dentales en México.">
    <meta name="theme-color" content="#14b8a6">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="canonical" href="{{ url('/brochure') }}">

    {{-- OpenGraph --}}
    <meta property="og:title" content="Folleto DocFácil — Software para consultorios dentales">
    <meta property="og:description" content="Conozca DocFácil: agenda, expediente, odontograma, recetas PDF y recordatorios por WhatsApp a 1 clic.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">
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
        <span class="inline-block bg-white/20 backdrop-blur-sm text-white text-xs font-semibold px-4 py-1.5 rounded-full mb-6 tracking-wider ring-1 ring-white/30">FOLLETO · EDICIÓN 2026</span>
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold mb-4 tracking-tight leading-tight">Su consultorio dental,<br><span class="text-amber-200">en orden</span><br>y en un solo lugar.</h1>
        <div class="w-16 h-1 bg-white/70 mx-auto rounded-full mb-6"></div>
        <p class="text-lg sm:text-xl opacity-95 max-w-3xl mx-auto leading-relaxed">
            Agenda, expediente, odontograma, recetas y cobros. <strong>Los recordatorios salen de su propio WhatsApp</strong> con el mensaje ya escrito: usted solo da enviar.
        </p>

        <div class="flex flex-wrap justify-center gap-3 mt-8">
            <a href="{{ $registerUrl }}" class="inline-flex items-center px-6 py-3 bg-white text-teal-700 font-bold rounded-xl hover:scale-105 shadow-xl transition">Empiece gratis</a>
            <a href="{{ route('brochure.pdf') }}" class="inline-flex items-center px-6 py-3 border-2 border-white/80 text-white font-semibold rounded-xl hover:bg-white/10 transition">Descargar folleto PDF</a>
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
                <div class="text-3xl font-extrabold">15 días</div>
                <div class="text-xs opacity-90 mt-1">de prueba con todo, sin tarjeta</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 ring-1 ring-white/20">
                <div class="text-3xl font-extrabold">30 días</div>
                <div class="text-xs opacity-90 mt-1">de garantía en su primer pago</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 ring-1 ring-white/20">
                <div class="text-3xl font-extrabold">$0</div>
                <div class="text-xs opacity-90 mt-1">plan Free, para siempre</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 ring-1 ring-white/20">
                <div class="text-3xl font-extrabold">1 clic</div>
                <div class="text-xs opacity-90 mt-1">para mandar un recordatorio</div>
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
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Para dentistas que aún<br>dependen del papel</h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">Hecho para consultorios dentales en México que quieren dejar la libreta y el Excel sin complicarse.</p>
        </div>

        <div class="grid sm:grid-cols-3 gap-6">
            <div class="group relative bg-gradient-to-br from-teal-50 to-cyan-50 border border-teal-100 p-7 rounded-2xl hover:shadow-xl hover:-translate-y-1 transition">
                <div class="absolute top-5 right-5 text-4xl opacity-30 group-hover:opacity-100 transition">🦷</div>
                <span class="inline-block bg-teal-600 text-white text-xs font-bold px-2.5 py-1 rounded-md mb-3">1 DOCTOR</span>
                <h3 class="font-bold text-lg text-gray-900 mb-2">El dentista que trabaja solo</h3>
                <p class="text-sm text-gray-700 leading-relaxed">Odontología general, ortodoncia, endodoncia. Lleva su agenda, sus expedientes y sus cobros en un solo lugar.</p>
            </div>
            <div class="group relative bg-gradient-to-br from-cyan-50 to-blue-50 border border-cyan-100 p-7 rounded-2xl hover:shadow-xl hover:-translate-y-1 transition">
                <div class="absolute top-5 right-5 text-4xl opacity-30 group-hover:opacity-100 transition">🦷</div>
                <span class="inline-block bg-cyan-600 text-white text-xs font-bold px-2.5 py-1 rounded-md mb-3">2 O 3 DOCTORES</span>
                <h3 class="font-bold text-lg text-gray-900 mb-2">Consultorios dentales de 2 o 3 doctores</h3>
                <p class="text-sm text-gray-700 leading-relaxed">Agenda compartida con un color por doctor, lista de espera y pacientes que agendan solos.</p>
            </div>
            <div class="group relative bg-gradient-to-br from-violet-50 to-fuchsia-50 border border-violet-100 p-7 rounded-2xl hover:shadow-xl hover:-translate-y-1 transition">
                <div class="absolute top-5 right-5 text-4xl opacity-30 group-hover:opacity-100 transition">🏥</div>
                <span class="inline-block bg-violet-600 text-white text-xs font-bold px-2.5 py-1 rounded-md mb-3">CLÍNICA</span>
                <h3 class="font-bold text-lg text-gray-900 mb-2">Clínicas dentales con varios doctores</h3>
                <p class="text-sm text-gray-700 leading-relaxed">Doctores ilimitados, producción individual y reportes por doctor.</p>
            </div>
        </div>
    </div>
</section>

{{-- Dolores --}}
<section class="py-20 px-6 bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <span class="inline-block text-xs font-bold text-red-500 tracking-wider uppercase mb-3">Lo de todos los días</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Si se identifica con 2 o más,<br>DocFácil es para usted</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white border border-red-100 p-5 rounded-2xl shadow-sm">
                <div class="text-3xl mb-3">📋</div>
                <strong class="block text-red-700 mb-1.5">Agenda caótica</strong>
                <span class="text-sm text-gray-600 leading-relaxed">Papel y Excel: se pierden citas, cuesta encontrar a un paciente, cada cambio pesa.</span>
            </div>
            <div class="bg-white border border-red-100 p-5 rounded-2xl shadow-sm">
                <div class="text-3xl mb-3">📞</div>
                <strong class="block text-red-700 mb-1.5">Pacientes que no llegan</strong>
                <span class="text-sm text-gray-600 leading-relaxed">Se les olvida la cita y el espacio se queda vacío.</span>
            </div>
            <div class="bg-white border border-red-100 p-5 rounded-2xl shadow-sm">
                <div class="text-3xl mb-3">✍</div>
                <strong class="block text-red-700 mb-1.5">Recetas a mano</strong>
                <span class="text-sm text-gray-600 leading-relaxed">Letra difícil de leer, sin copia, sin respaldo.</span>
            </div>
            <div class="bg-white border border-red-100 p-5 rounded-2xl shadow-sm">
                <div class="text-3xl mb-3">💸</div>
                <strong class="block text-red-700 mb-1.5">No sabe si gana</strong>
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
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Todo el consultorio,<br>conectado</h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">Agenda, expediente, odontograma, recetas y cobros hablan entre sí.</p>
        </div>

        {{-- Feature 1: Agenda --}}
        <div class="grid md:grid-cols-2 gap-10 items-center mb-24">
            <div class="md:order-1 order-2">
                <span class="inline-block bg-teal-100 text-teal-800 text-xs font-bold px-3 py-1 rounded-full mb-4">01 · AGENDA</span>
                <h3 class="text-3xl font-extrabold text-gray-900 mb-4 tracking-tight">Calendario + recordatorios por WhatsApp a 1 clic</h3>
                <p class="text-gray-600 mb-5 leading-relaxed">Vista diaria, semanal y mensual. Arrastre una cita para cambiarla de hora; cada estado tiene su color. Para las citas de mañana, DocFácil abre su WhatsApp con el recordatorio ya escrito y usted da enviar.</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> El paciente confirma su cita con un link</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Desde computadora, tablet o celular</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Un color por doctor (planes Pro y Clínica)</li>
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
                <h3 class="text-3xl font-extrabold text-gray-900 mb-4 tracking-tight">Expediente clínico digital</h3>
                <p class="text-gray-600 mb-5 leading-relaxed">En cada consulta: motivo, diagnóstico con CIE-10, tratamiento, signos vitales y alergias. Las notas se bloquean 24 horas después de guardarlas.</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Todo organizado por paciente y consulta</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Hasta 10 fotos por nota, de 5 MB cada una</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Búsqueda por nombre o teléfono</li>
                </ul>
            </div>
        </div>

        {{-- Feature 3: Recetas --}}
        <div class="grid md:grid-cols-2 gap-10 items-center mb-24">
            <div class="md:order-1 order-2">
                <span class="inline-block bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full mb-4">03 · RECETAS</span>
                <h3 class="text-3xl font-extrabold text-gray-900 mb-4 tracking-tight">Recetas PDF claras y con cédula</h3>
                <p class="text-gray-600 mb-5 leading-relaxed">Con su nombre, especialidad, cédula profesional y datos del consultorio. Se descargan en PDF con un clic y el paciente las ve en su portal. Sin letra difícil de leer.</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Historial de recetas por paciente</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Se bloquean 24 horas después de creadas</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> El paciente las consulta en su portal</li>
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
                <h3 class="text-3xl font-extrabold text-gray-900 mb-4 tracking-tight">Odontograma FDI interactivo</h3>
                <p class="text-gray-600 mb-5 leading-relaxed">Haga clic en el diente, elija el estado y se guarda solo. De ahí salen los presupuestos que el paciente acepta en línea. Viene desde el plan Básico.</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Historial visual de cada pieza dental</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Colores por tipo de tratamiento</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Se imprime en PDF</li>
                </ul>
            </div>
        </div>

        {{-- Feature 5: Cobros --}}
        <div class="grid md:grid-cols-2 gap-10 items-center mb-24">
            <div class="md:order-1 order-2">
                <span class="inline-block bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-full mb-4">05 · COBROS</span>
                <h3 class="text-3xl font-extrabold text-gray-900 mb-4 tracking-tight">Cobros, abonos e ingresos del mes</h3>
                <p class="text-gray-600 mb-5 leading-relaxed">Anote cada pago: efectivo, transferencia o tarjeta, con abonos. Sabe quién le debe y cuánto. Con un clic se abre su WhatsApp con el recordatorio del saldo: monto, fecha y sus datos para pagar.</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Ingresos del mes al día</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Gastos y corte del mes</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Pagos parciales y abonos</li>
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
                <h3 class="text-3xl font-extrabold text-gray-900 mb-4 tracking-tight">Todos sus pacientes, a la mano</h3>
                <p class="text-gray-600 mb-5 leading-relaxed">Teléfono, correo, último motivo y próxima cita. Con un clic abre el expediente, le escribe por WhatsApp o le agenda la siguiente consulta.</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Búsqueda por nombre o teléfono</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Los cumpleaños del día en su escritorio</li>
                    <li class="flex items-start gap-2"><span class="text-teal-500 font-bold">✓</span> Importe sus pacientes desde Excel</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- +6 features compactas --}}
<section class="py-20 px-6 bg-gray-50 border-y border-gray-100">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-10">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Y 6 funciones más</h3>
            <p class="text-gray-600">Cada una dice desde qué plan viene.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $extras = [
                    ['icon' => '📱', 'title' => 'Check-in con QR', 'plan' => 'Desde Básico', 'desc' => 'El paciente escanea el código al llegar y en el consultorio ven que ya llegó.'],
                    ['icon' => '📺', 'title' => 'Pantalla de la sala', 'plan' => 'Desde Básico', 'desc' => 'Quién está en consulta y quién sigue, en la pantalla de su sala de espera.'],
                    ['icon' => '👥', 'title' => 'Portal del paciente', 'plan' => 'Desde Básico', 'desc' => 'Sus pacientes ven sus citas, recetas y pagos.'],
                    ['icon' => '✍', 'title' => 'Consentimientos con firma', 'plan' => 'Desde Pro', 'desc' => 'Firmados con el dedo en pantalla, con fecha y hora. Se bloquean al firmarse.'],
                    ['icon' => '🔔', 'title' => 'Alertas inteligentes', 'plan' => 'Desde Pro', 'desc' => 'Pacientes inactivos, cobros atrasados, huecos para la lista de espera, tratamientos aceptados por agendar y citas de mañana sin recordatorio.'],
                    ['icon' => '📊', 'title' => 'Producción por doctor', 'plan' => 'Plan Clínica', 'desc' => 'Cuánto produce cada doctor, con reportes por doctor.'],
                ];
            @endphp
            @foreach ($extras as $e)
            <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-md hover:border-teal-300 transition">
                <div class="text-2xl mb-2">{{ $e['icon'] }}</div>
                <h4 class="font-bold text-gray-900 mb-1">{{ $e['title'] }}</h4>
                <span class="inline-block bg-teal-50 text-teal-700 text-xs font-semibold px-2 py-0.5 rounded mb-2">{{ $e['plan'] }}</span>
                <p class="text-sm text-gray-600 leading-relaxed">{{ $e['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ECOSISTEMA --}}
<section class="py-24 px-6 bg-white">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14">
            <span class="inline-block text-xs font-bold text-teal-600 tracking-wider uppercase mb-3">Incluido</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Todo viene listo, sin instalar nada</h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">Sin configuraciones técnicas. Funciona desde el primer día.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="group bg-gradient-to-br from-green-50 to-emerald-50 border border-green-100 p-6 rounded-2xl hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-green-500 text-white flex items-center justify-center text-xl font-bold mb-4">💬</div>
                <h4 class="font-bold text-gray-900 mb-1">WhatsApp a 1 clic</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Recordatorios y saldos: se abre su WhatsApp con el mensaje ya escrito y usted da enviar, desde su número.</p>
            </div>
            <div class="group bg-gradient-to-br from-blue-50 to-cyan-50 border border-blue-100 p-6 rounded-2xl hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-blue-500 text-white flex items-center justify-center text-xl font-bold mb-4">🗓</div>
                <h4 class="font-bold text-gray-900 mb-1">Agenda en línea</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Sus pacientes agendan solos, a cualquier hora (desde el plan Pro).</p>
            </div>
            <div class="group bg-gradient-to-br from-violet-50 to-purple-50 border border-violet-100 p-6 rounded-2xl hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-violet-500 text-white flex items-center justify-center text-xl font-bold mb-4">💳</div>
                <h4 class="font-bold text-gray-900 mb-1">Registro de pagos con abonos</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Efectivo, transferencia o tarjeta. Cada abono queda anotado y sabe cuánto falta.</p>
            </div>
            <div class="group bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100 p-6 rounded-2xl hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl font-bold mb-4">📱</div>
                <h4 class="font-bold text-gray-900 mb-1">Se instala como app</h4>
                <p class="text-sm text-gray-600 leading-relaxed">En celular o tablet, con su ícono en la pantalla. Necesita internet para funcionar.</p>
            </div>
            <div class="group bg-gradient-to-br from-sky-50 to-cyan-50 border border-sky-100 p-6 rounded-2xl hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-sky-500 text-white flex items-center justify-center text-xl font-bold mb-4">☁</div>
                <h4 class="font-bold text-gray-900 mb-1">Respaldo en la nube</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Respaldo diario automático. Sin USBs ni archivos perdidos.</p>
            </div>
            <div class="group bg-gradient-to-br from-teal-50 to-emerald-50 border border-teal-100 p-6 rounded-2xl hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-teal-500 text-white flex items-center justify-center text-xl font-bold mb-4">👥</div>
                <h4 class="font-bold text-gray-900 mb-1">Portal del paciente</h4>
                <p class="text-sm text-gray-600 leading-relaxed">Sus pacientes ven sus citas, recetas y pagos.</p>
            </div>
        </div>
    </div>
</section>

{{-- SEGURIDAD --}}
<section class="py-20 px-6 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <span class="inline-block text-xs font-bold text-teal-300 tracking-wider uppercase mb-3">Seguridad y privacidad</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold mb-4 tracking-tight">Sus datos y los de sus pacientes,<br>protegidos</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-2xl">
                <div class="text-2xl mb-3">🔒</div>
                <h4 class="font-bold mb-1">Conexión cifrada (HTTPS)</h4>
                <p class="text-sm text-gray-300 leading-relaxed">La información viaja cifrada entre su navegador y nuestros servidores.</p>
            </div>
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-2xl">
                <div class="text-2xl mb-3">🗂</div>
                <h4 class="font-bold mb-1">Cada consultorio aislado</h4>
                <p class="text-sm text-gray-300 leading-relaxed">Sus datos nunca se mezclan con los de otro consultorio. Aviso de privacidad y contrato de encargado para los datos de sus pacientes.</p>
            </div>
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-2xl">
                <div class="text-2xl mb-3">📋</div>
                <h4 class="font-bold mb-1">Pensado para la NOM-004</h4>
                <p class="text-sm text-gray-300 leading-relaxed">Notas clínicas y recetas que se bloquean 24 horas después de creadas, con historial de cambios y diagnósticos con CIE-10.</p>
            </div>
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-2xl">
                <div class="text-2xl mb-3">💾</div>
                <h4 class="font-bold mb-1">Respaldo diario automático</h4>
                <p class="text-sm text-gray-300 leading-relaxed">Cada día se hace un respaldo automático de la base de datos.</p>
            </div>
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-2xl">
                <div class="text-2xl mb-3">🔐</div>
                <h4 class="font-bold mb-1">Roles y permisos</h4>
                <p class="text-sm text-gray-300 leading-relaxed">Cada usuario ve solo lo que necesita. Verificación en dos pasos opcional.</p>
            </div>
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-2xl">
                <div class="text-2xl mb-3">📤</div>
                <h4 class="font-bold mb-1">Sus datos son suyos</h4>
                <p class="text-sm text-gray-300 leading-relaxed">Recetas, consentimientos y presupuestos se descargan en PDF. Si necesita todos sus datos, se los pide a soporte.</p>
            </div>
        </div>
    </div>
</section>

{{-- PRECIOS --}}
<section class="py-24 px-6 bg-white">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-8">
            <span class="inline-block text-xs font-bold text-teal-600 tracking-wider uppercase mb-3">Precios</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Un plan para cada consultorio</h2>
            <p class="text-gray-600 text-lg">Sin contratos. Sin tarjeta para probar. Cancela cuando quiera.</p>
        </div>

        {{-- Banner ahorro anual --}}
        <div class="max-w-2xl mx-auto mb-10 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-4">
            <div class="text-3xl">💡</div>
            <div class="flex-1">
                <div class="font-bold text-amber-900">Pague anual y ahorre 2 meses</div>
                <div class="text-sm text-amber-800">El plan anual cuesta lo de 10 meses.</div>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($pages['plans'] as $p)
            @php $visible = array_slice($p['features'], 0, 4); $hidden = array_slice($p['features'], 4); @endphp
            <div x-data="{ expanded: false }" class="relative flex flex-col bg-white rounded-2xl p-7 border-2 {{ !empty($p['popular']) ? 'border-orange-500 shadow-2xl scale-105 z-10' : 'border-gray-200 hover:border-teal-300' }} transition">
                @if (!empty($p['popular']))
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-orange-500 to-amber-500 text-white text-xs font-bold px-4 py-1 rounded-full shadow-lg">★ POPULAR</span>
                @endif
                <h4 class="text-xl font-bold text-gray-900">{{ $p['name'] }}</h4>
                <div class="mt-4 mb-2">
                    <span class="text-5xl font-extrabold {{ !empty($p['popular']) ? 'text-orange-600' : 'text-teal-600' }}">${{ $p['price'] }}</span>
                    <span class="text-gray-500 text-sm">/mes</span>
                </div>
                @if ($p['annual'] > 0)
                <div class="mb-4 inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 border border-emerald-200 rounded-md text-xs font-semibold text-emerald-700">
                    o ${{ number_format($p['annual']) }}/año · 2 meses gratis
                </div>
                @else
                <div class="mb-4 text-xs text-gray-500">para siempre · sin tarjeta</div>
                @endif
                <p class="text-xs text-gray-500 mb-1">{{ $p['ideal'] }}</p>
                <p class="text-xs font-semibold text-gray-700 mb-4 min-h-[32px]">{{ $p['limits'] }}</p>
                @if (!empty($p['lead']))
                <p class="text-xs font-semibold text-teal-700 mb-2">{{ $p['lead'] }}</p>
                @endif
                <ul class="space-y-2 mb-4">
                    @foreach ($visible as $feat)
                    <li class="text-sm flex items-start gap-2"><span class="text-teal-500 font-bold mt-0.5">✓</span> <span class="text-gray-700">{{ $feat }}</span></li>
                    @endforeach
                    @if (count($hidden) > 0)
                    <template x-if="expanded">
                        <div class="space-y-2">
                            @foreach ($hidden as $feat)
                            <li class="text-sm flex items-start gap-2"><span class="text-teal-500 font-bold mt-0.5">✓</span> <span class="text-gray-700">{{ $feat }}</span></li>
                            @endforeach
                        </div>
                    </template>
                    @endif
                </ul>
                <div class="mt-auto">
                    @if (count($hidden) > 0)
                    <button type="button" @click="expanded = !expanded" class="w-full mb-3 text-xs font-semibold text-teal-600 hover:text-teal-700 flex items-center justify-center gap-1">
                        <span x-show="!expanded">Ver {{ count($hidden) }} {{ count($hidden) === 1 ? 'función' : 'funciones' }} más</span>
                        <span x-show="expanded" x-cloak>Ver menos</span>
                        <svg class="w-3 h-3 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    @endif
                    <a href="{{ $p['name'] === 'Clínica' ? $whatsappLink : $registerUrl }}" class="block text-center py-2.5 rounded-lg text-sm font-bold transition {{ !empty($p['popular']) ? 'bg-orange-500 text-white hover:bg-orange-600' : 'bg-gray-100 text-gray-900 hover:bg-teal-500 hover:text-white' }}">
                        {{ $p['cta'] }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        <p class="text-center text-sm text-gray-500 mt-8">15 días gratis con todas las funciones. Sin tarjeta. Garantía de 30 días en su primer pago. Precios en pesos mexicanos.</p>
    </div>
</section>

{{-- CÓMO EMPEZAR --}}
<section class="py-24 px-6 bg-gray-50">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14">
            <span class="inline-block text-xs font-bold text-teal-600 tracking-wider uppercase mb-3">Cómo empezar</span>
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Listo en 3 pasos</h2>
            <p class="text-gray-600 text-lg">Sin instalaciones. Sin tarjeta.</p>
        </div>

        <div class="grid sm:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition border border-gray-100">
                <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-500 text-white rounded-xl flex items-center justify-center text-2xl font-extrabold mb-4 shadow-lg">1</div>
                <h4 class="font-bold text-gray-900 mb-2 text-lg">Regístrese</h4>
                <p class="text-sm text-gray-600 mb-4 leading-relaxed">Cree su cuenta sin tarjeta. 15 días gratis con todas las funciones.</p>
                <div class="rounded-lg overflow-hidden border border-gray-200">
                    <img src="{{ $screens['landing'] }}" alt="Registro" class="w-full block">
                </div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition border border-gray-100">
                <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-500 text-white rounded-xl flex items-center justify-center text-2xl font-extrabold mb-4 shadow-lg">2</div>
                <h4 class="font-bold text-gray-900 mb-2 text-lg">Cargue sus pacientes</h4>
                <p class="text-sm text-gray-600 mb-4 leading-relaxed">Suba su Excel o captúrelos a mano. Si son muchos, le ayudamos a subirlos.</p>
                <div class="rounded-lg overflow-hidden border border-gray-200">
                    <img src="{{ $screens['pacientes'] }}" alt="Pacientes" class="w-full block">
                </div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition border border-gray-100">
                <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white rounded-xl flex items-center justify-center text-2xl font-extrabold mb-4 shadow-lg">3</div>
                <h4 class="font-bold text-gray-900 mb-2 text-lg">Agende su primer día</h4>
                <p class="text-sm text-gray-600 mb-4 leading-relaxed">Abra la agenda, cree su primera cita y mande su primer recordatorio con un clic desde su WhatsApp.</p>
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
                    <h3 class="text-3xl sm:text-4xl font-extrabold mb-4 tracking-tight">Empiece gratis hoy</h3>
                    <p class="opacity-95 mb-6 text-lg">Escanee el QR o hable directamente con Omar, el fundador de DocFácil.</p>
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
                    <p class="text-xs opacity-90 mt-4">Escanee con su celular para registrarse</p>
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
            <span class="inline-block bg-teal-500/10 text-teal-300 px-3 py-1 rounded-full text-xs font-semibold">✓ Se instala como app</span>
            <span class="inline-block bg-teal-500/10 text-teal-300 px-3 py-1 rounded-full text-xs font-semibold">✓ Sin anuncios</span>
        </div>
        DocFácil © {{ date('Y') }} · Hecho en México para dentistas mexicanos ·
        <a href="{{ url('/') }}" class="text-teal-400 hover:text-teal-300">docfacil.tu-app.co</a>
    </div>
</footer>

</body>
</html>
