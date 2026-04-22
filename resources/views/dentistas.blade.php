<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software para Consultorio Dental en México — DocFácil</title>
    <meta name="description" content="Agenda, expediente, recetas PDF y recordatorios WhatsApp para dentistas en México. Odontograma digital FDI. 15 días gratis, sin tarjeta. Desde $499/mes.">
    <meta name="theme-color" content="#14b8a6">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <link rel="canonical" href="{{ url('/dentistas') }}">

    {{-- OpenGraph --}}
    <meta property="og:title" content="Software para Consultorio Dental en México — DocFácil">
    <meta property="og:description" content="Odontograma digital, recordatorios WhatsApp, recetas PDF. 15 días gratis para dentistas.">
    <meta property="og:image" content="{{ url('/images/og-image.png') }}">
    <meta property="og:url" content="{{ url('/dentistas') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_MX">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .fade-up { animation: fadeUp 0.6s ease-out both; }
        .gradient-text { background: linear-gradient(135deg, #0d9488, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .tooth-bg { background-image: radial-gradient(circle at 20% 20%, rgba(13, 148, 136, 0.08), transparent 50%), radial-gradient(circle at 80% 80%, rgba(6, 182, 212, 0.06), transparent 50%); }
    </style>

    {{-- JSON-LD estructurado para SEO + Ads --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "DocFácil para Dentistas",
        "description": "Software para consultorios dentales en México. Odontograma digital, recordatorios WhatsApp, expediente clínico, recetas PDF.",
        "applicationCategory": "HealthApplication",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "499",
            "priceCurrency": "MXN"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "ratingCount": "37"
        }
    }
    </script>

    {{-- Google Analytics / conversión (reemplazar con tu GA4 ID) --}}
    {{-- <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXX"></script> --}}
</head>
<body class="bg-white text-gray-900 antialiased">

    {{-- ═══════════ NAVBAR MINIMAL ═══════════ --}}
    <nav class="sticky top-0 z-50 backdrop-blur-lg bg-white/80 border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-teal-500 to-cyan-500 flex items-center justify-center text-white font-bold">D</div>
                <span class="font-bold text-lg">DocFácil</span>
                <span class="hidden sm:inline text-sm text-gray-500 font-medium">· para Dentistas</span>
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ url('/login') }}" class="hidden sm:inline text-sm text-gray-600 hover:text-gray-900 px-3 py-2">Ingresar</a>
                <a href="{{ url('/register') }}" class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold px-4 py-2.5 rounded-lg shadow-sm">Empezar gratis</a>
            </div>
        </div>
    </nav>

    {{-- ═══════════ HERO ═══════════ --}}
    <section class="tooth-bg py-14 md:py-20">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-10 items-center">
                <div class="fade-up">
                    <div class="inline-flex items-center gap-2 bg-teal-50 text-teal-700 px-3 py-1.5 rounded-full text-xs font-semibold mb-5">
                        <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                        Software para consultorio dental · Hecho en México
                    </div>

                    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-5">
                        La agenda y expediente dental que <span class="gradient-text">sus pacientes no ignoran</span>.
                    </h1>

                    <p class="text-lg text-gray-600 mb-7 leading-relaxed">
                        DocFácil manda recordatorios por WhatsApp automático un día antes de cada cita, guarda el <strong>odontograma digital</strong> con notación FDI, y genera recetas PDF firmadas. Sus faltas bajan, su día se ordena, su consultorio se ve profesional.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 mb-5">
                        <a href="{{ url('/register') }}" class="bg-teal-600 hover:bg-teal-700 text-white font-bold px-7 py-4 rounded-xl shadow-lg shadow-teal-500/20 transition-all hover:scale-[1.02] text-center">
                            Probar 15 días gratis
                        </a>
                        <a href="#precio" class="bg-white border border-gray-300 hover:border-teal-500 text-gray-900 font-bold px-7 py-4 rounded-xl text-center">
                            Ver planes y precios
                        </a>
                    </div>

                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm text-gray-500">
                        <span class="flex items-center gap-1.5">✓ Sin tarjeta</span>
                        <span class="flex items-center gap-1.5">✓ 2 minutos de registro</span>
                        <span class="flex items-center gap-1.5">✓ Soporte WhatsApp directo</span>
                    </div>
                </div>

                <div class="fade-up" style="animation-delay: 0.2s">
                    <div class="relative">
                        <div class="absolute -inset-4 bg-gradient-to-r from-teal-200 to-cyan-200 rounded-3xl blur-2xl opacity-40"></div>
                        <img src="{{ asset('images/screenshot-odontograma.png') }}" alt="Odontograma digital DocFácil" class="relative rounded-2xl shadow-2xl border border-gray-200 w-full" onerror="this.style.display='none'">
                        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-200 p-8" style="display:{{ 'none' }}">
                            {{-- Fallback visual si no hay imagen --}}
                            <div class="text-center">
                                <div class="w-20 h-20 mx-auto rounded-full bg-teal-100 flex items-center justify-center text-4xl mb-4">🦷</div>
                                <div class="text-sm text-gray-500">Odontograma Digital · FDI</div>
                                <div class="text-xs text-gray-400 mt-1">13 condiciones · Editor visual</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════ DOLOR + SOLUCIÓN ═══════════ --}}
    <section class="py-14 bg-gray-50">
        <div class="max-w-5xl mx-auto px-4">
            <div class="text-center mb-10">
                <h2 class="text-3xl md:text-4xl font-bold mb-3">La escena que probablemente vivió esta semana</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Abre la agenda a las 10. A las 10:15 el paciente no llega. A las 10:30 ya pasó a otro. Y el hueco del primero se queda ahí.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-5">
                {{-- Antes --}}
                <div class="bg-white border border-red-200 rounded-2xl p-6 md:p-8">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold">✗</div>
                        <h3 class="text-xl font-bold text-gray-900">Sin un sistema</h3>
                    </div>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex gap-2">• Pierde 6-8 pacientes a la semana por no-shows sin recordatorio.</li>
                        <li class="flex gap-2">• Busca 20-30 minutos al día expedientes en papel entre carpetas.</li>
                        <li class="flex gap-2">• Escribe WhatsApp de recordatorio uno por uno, manual.</li>
                        <li class="flex gap-2">• Su odontograma está en papel, el paciente nuevo empieza de cero.</li>
                        <li class="flex gap-2">• Sus recetas se ven amateur — escritas al vuelo sin firma.</li>
                    </ul>
                </div>

                {{-- Después --}}
                <div class="bg-white border-2 border-teal-400 rounded-2xl p-6 md:p-8 shadow-xl shadow-teal-500/10">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center font-bold">✓</div>
                        <h3 class="text-xl font-bold text-gray-900">Con DocFácil</h3>
                    </div>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex gap-2">• Faltas bajan a 1-2 por semana. WhatsApp automático un día antes.</li>
                        <li class="flex gap-2">• Expediente completo del paciente en 5 segundos desde cualquier dispositivo.</li>
                        <li class="flex gap-2">• Recordatorios salen solos. Usted solo atiende.</li>
                        <li class="flex gap-2">• Odontograma digital FDI con 13 condiciones, se guarda al momento.</li>
                        <li class="flex gap-2">• Recetas PDF con firma digital, enviadas por WhatsApp al paciente.</li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 text-center">
                <div class="inline-block bg-teal-600 text-white px-6 py-4 rounded-xl">
                    <div class="text-sm opacity-80 mb-1">Ahorro típico en el primer mes</div>
                    <div class="text-3xl font-bold">$6,000 – $10,000 MXN</div>
                    <div class="text-xs opacity-80 mt-1">que antes se iban en huecos de agenda</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════ FEATURES CORE DENTAL ═══════════ --}}
    <section id="features" class="py-14">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-10">
                <h2 class="text-3xl md:text-4xl font-bold mb-3">Todo lo que su consultorio dental necesita</h2>
                <p class="text-gray-600">Diseñado desde cero para odontólogos. No es software médico adaptado.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-5">
                @foreach ([
                    ['🦷', 'Odontograma digital FDI', 'Editor visual con 13 condiciones: sano, caries, obturación, corona, extracción, endodoncia, implante, fractura, sellante, carilla y más. Se guarda solo.'],
                    ['💬', 'Recordatorios WhatsApp', 'Un día antes y 2 horas antes. El paciente responde "confirmo" o "reagendar" con un botón. Bajan los no-shows 40-60%.'],
                    ['📋', 'Expediente clínico dental', 'Historial completo, fotos clínicas, tratamientos en curso, presupuestos. Cumple NOM-004.'],
                    ['📄', 'Recetas PDF con firma', 'Recetas profesionales en 30 segundos. Se mandan por WhatsApp al paciente. Sin recetario en papel.'],
                    ['📅', 'Agenda visual inteligente', 'Calendario con vista día, semana y mes. Arrastra citas, bloquea horarios, múltiples doctores.'],
                    ['💰', 'Cobros y pagos pendientes', 'Registra cobros parciales de tratamientos largos (ortodoncia, implantes). Reporte mensual claro.'],
                    ['🦷', 'Consentimientos informados', 'Plantillas de consentimiento por procedimiento (exodoncia, endodoncia, blanqueamiento). Firma digital.'],
                    ['📊', 'Reportes de ingresos', 'Cuánto entra, cuánto debe cobrar, por tratamiento, por paciente, por mes.'],
                    ['📱', 'Portal del paciente', 'Su paciente ve sus citas, expediente y recetas desde su celular. Se ve profesional.'],
                ] as [$icon, $title, $desc])
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 hover:border-teal-400 hover:shadow-lg transition-all">
                        <div class="text-3xl mb-3">{{ $icon }}</div>
                        <h3 class="font-bold text-gray-900 mb-2">{{ $title }}</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════ PRUEBA SOCIAL ═══════════ --}}
    <section class="py-14 bg-gradient-to-br from-teal-50 to-cyan-50">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center gap-1 text-yellow-500 text-xl mb-3">
                ★★★★★
            </div>
            <div class="text-sm text-gray-600 mb-6">4.8/5 · Basado en dentistas activos</div>

            <blockquote class="text-xl md:text-2xl text-gray-800 font-medium leading-relaxed mb-6">
                "Antes perdía 6 u 8 pacientes a la semana. Con DocFácil bajé a 1 o 2. Recupero como $8,000 al mes y pago $499. La cuenta se hace sola."
            </blockquote>
            <div class="text-sm text-gray-600">
                <strong>Dra. María Fernández</strong> · Odontología General · Culiacán
            </div>
        </div>
    </section>

    {{-- ═══════════ PRECIO ═══════════ --}}
    <section id="precio" class="py-14">
        <div class="max-w-5xl mx-auto px-4">
            <div class="text-center mb-10">
                <h2 class="text-3xl md:text-4xl font-bold mb-3">Un precio para cada etapa de su consultorio</h2>
                <p class="text-gray-600">15 días con todo, sin tarjeta. Al terminar, queda en plan gratis — nunca pierde acceso.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-5">
                {{-- Free --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Gratis</div>
                    <div class="text-3xl font-bold mb-1">$0</div>
                    <div class="text-sm text-gray-500 mb-5">Para siempre</div>
                    <ul class="space-y-2 text-sm text-gray-700 mb-6">
                        <li>✓ 1 doctor, 15 pacientes</li>
                        <li>✓ Agenda básica</li>
                        <li>✓ 10 citas al mes</li>
                        <li class="text-gray-400">✗ Odontograma</li>
                        <li class="text-gray-400">✗ WhatsApp automático</li>
                    </ul>
                    <a href="{{ url('/register') }}" class="block text-center bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold py-3 rounded-lg">Empezar</a>
                </div>

                {{-- Pro destacado --}}
                <div class="bg-gradient-to-br from-teal-600 to-cyan-600 text-white rounded-2xl p-6 shadow-2xl shadow-teal-500/30 relative md:scale-105">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-yellow-400 text-gray-900 text-xs font-bold px-3 py-1 rounded-full">EL MÁS ELEGIDO</div>
                    <div class="text-xs font-semibold uppercase tracking-wide opacity-80 mb-2">Pro</div>
                    <div class="text-3xl font-bold mb-1">$999<span class="text-base font-normal opacity-80">/mes</span></div>
                    <div class="text-sm opacity-80 mb-5">O $9,990/año (2 meses gratis)</div>
                    <ul class="space-y-2 text-sm mb-6">
                        <li>✓ Pacientes ilimitados</li>
                        <li>✓ <strong>Odontograma digital FDI</strong></li>
                        <li>✓ WhatsApp automático</li>
                        <li>✓ Recetas PDF con firma</li>
                        <li>✓ Consentimientos informados</li>
                        <li>✓ Portal del paciente</li>
                    </ul>
                    <a href="{{ url('/register') }}" class="block text-center bg-white text-teal-600 font-bold py-3 rounded-lg hover:bg-gray-50">Probar 15 días gratis</a>
                </div>

                {{-- Clínica --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Clínica</div>
                    <div class="text-3xl font-bold mb-1">$1,999<span class="text-base font-normal text-gray-500">/mes</span></div>
                    <div class="text-sm text-gray-500 mb-5">Para clínicas multi-doctor</div>
                    <ul class="space-y-2 text-sm text-gray-700 mb-6">
                        <li>✓ Hasta 10 doctores</li>
                        <li>✓ Todo lo de Pro</li>
                        <li>✓ Reportes avanzados</li>
                        <li>✓ Multi-sucursal</li>
                        <li>✓ Soporte prioritario</li>
                    </ul>
                    <a href="{{ url('/register') }}" class="block text-center bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold py-3 rounded-lg">Probar 15 días</a>
                </div>
            </div>

            <p class="text-center text-sm text-gray-500 mt-6">
                Hay también plan <strong>Básico $499/mes</strong> para consultorios que aún no necesitan odontograma. <a href="#" class="text-teal-600 underline">Ver comparativa completa</a>.
            </p>
        </div>
    </section>

    {{-- ═══════════ FAQ ═══════════ --}}
    <section class="py-14 bg-gray-50" x-data="{ open: null }">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold mb-8 text-center">Preguntas de dentistas como usted</h2>

            @foreach ([
                ['¿Qué pasa al terminar los 15 días de prueba?', 'Su cuenta sigue activa en plan Gratis (1 doctor, 15 pacientes, agenda básica). Nunca pierde el acceso ni sus datos. Si quiere mantener el odontograma, WhatsApp automático y pacientes ilimitados, se cambia al plan Pro $999/mes — pago con tarjeta o transferencia SPEI, sin contratos.'],
                ['¿El odontograma es interactivo o solo texto?', 'Es un editor visual FDI: usted hace clic en el diente, elige la condición (caries, corona, extracción, endodoncia, implante, etc.) y se guarda automático. Funciona en tablet, laptop y celular. Soporta 32 dientes adultos con notación FDI internacional.'],
                ['¿Cómo funcionan los recordatorios WhatsApp?', 'Automáticos desde el número oficial de DocFácil. El paciente recibe un mensaje un día antes y otro 2 horas antes de su cita. Puede responder "confirmo" o "reagendar". Sus faltas bajan entre 40% y 60% en el primer mes.'],
                ['¿Cumple con la NOM-004 y protección de datos?', 'Sí. Expediente clínico electrónico conforme NOM-004-SSA3-2012, servidores en México, respaldo diario cifrado, cumplimiento LFPDPPP. Sus pacientes firman consentimiento digital al registrarse.'],
                ['¿Me pueden ayudar a pasar mi información de Excel o papel?', 'Sí. Si traen sus pacientes en Excel, los importamos sin costo. Si están en papel, le damos plantilla y en 1 semana con su asistente ya tiene 50-80 pacientes cargados. No tiene que hacerlo usted.'],
                ['¿Funciona si soy yo solo sin recepcionista?', 'Especialmente si es usted solo. El sistema le quita trabajo administrativo: recordatorios que se mandan solos, receta PDF al paciente por WhatsApp, pagos registrados en 3 toques. Recupera 20-30 minutos al día.'],
            ] as $i => [$q, $a])
                <div class="bg-white rounded-xl mb-3 border border-gray-200">
                    <button @click="open === {{ $i }} ? open = null : open = {{ $i }}" class="w-full px-5 py-4 flex items-center justify-between text-left">
                        <span class="font-semibold text-gray-900">{{ $q }}</span>
                        <span class="text-teal-600 text-xl" x-text="open === {{ $i }} ? '−' : '+'">+</span>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse class="px-5 pb-5 text-gray-600 leading-relaxed">{{ $a }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ═══════════ CTA FINAL ═══════════ --}}
    <section class="py-14 md:py-20 bg-gradient-to-br from-teal-600 to-cyan-600 text-white">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Empiece hoy. Su próxima cita ya podría llegar con recordatorio automático.</h2>
            <p class="text-lg opacity-90 mb-7">15 días con todo desbloqueado. Sin tarjeta. Sin contratos. Si no es para usted, no paga nada.</p>
            <a href="{{ url('/register') }}" class="inline-block bg-white text-teal-600 font-bold px-8 py-4 rounded-xl text-lg shadow-2xl hover:scale-[1.02] transition-all">
                Empezar mi prueba gratis
            </a>
            <div class="mt-5 text-sm opacity-80">
                O escríbame directo por WhatsApp: <a href="https://wa.me/526682493398" class="underline font-semibold">668 249 3398</a>
            </div>
        </div>
    </section>

    {{-- ═══════════ FOOTER MINIMAL ═══════════ --}}
    <footer class="py-8 bg-gray-900 text-gray-400 text-sm">
        <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-3">
            <div>© {{ date('Y') }} DocFácil · Software para consultorios dentales en México</div>
            <div class="flex gap-4">
                <a href="{{ route('legal.privacy') }}" class="hover:text-white">Privacidad</a>
                <a href="{{ route('legal.terms') }}" class="hover:text-white">Términos</a>
                <a href="/" class="hover:text-white">Inicio</a>
            </div>
        </div>
    </footer>

    @include('components.chatbot-widget')
</body>
</html>
