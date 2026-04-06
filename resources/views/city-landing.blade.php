<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software para Consultorios en {{ $city }} — DocFácil</title>
    <meta name="description" content="El mejor software para consultorios médicos y dentales en {{ $city }}, {{ $state }}. Agenda citas, expedientes clínicos, recetas PDF y recordatorios WhatsApp. Prueba gratis 15 días.">
    <meta name="theme-color" content="#14b8a6">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="canonical" href="{{ url("/software-dental/{$slug}") }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "DocFácil",
        "description": "Software para consultorios médicos y dentales en {{ $city }}",
        "applicationCategory": "HealthApplication",
        "operatingSystem": "Web",
        "offers": {
            "@@type": "Offer",
            "price": "0",
            "priceCurrency": "MXN"
        },
        "areaServed": {
            "@@type": "City",
            "name": "{{ $city }}"
        }
    }
    </script>
</head>
<body class="bg-white text-gray-900 antialiased">
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-20">
            <a href="{{ url('/') }}">
                <img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" class="h-16">
            </a>
            <a href="{{ url('/doctor/register') }}" class="px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700 transition">
                Prueba gratis
            </a>
        </div>
    </nav>

    <section class="py-20 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 leading-tight">
                Software para consultorios<br>en <span class="text-teal-600">{{ $city }}</span>
            </h1>
            <p class="mt-6 text-lg text-gray-600 max-w-2xl mx-auto">
                DocFácil es el software #1 para doctores y dentistas en {{ $city }}, {{ $state }}.
                Gestiona citas, pacientes, expedientes y cobros desde cualquier dispositivo.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ url('/doctor/register') }}" class="px-8 py-3 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition shadow-lg shadow-teal-200">
                    Empieza gratis — 15 días de prueba
                </a>
            </div>
        </div>
    </section>

    <section class="py-16 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4">
            <h2 class="text-2xl font-bold text-center text-gray-900 mb-10">¿Por qué los doctores en {{ $city }} eligen DocFácil?</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <div class="text-3xl mb-3">📋</div>
                    <h3 class="font-bold text-gray-900 mb-2">Expediente clínico digital</h3>
                    <p class="text-sm text-gray-600">Olvídate del papeleo. Todo el historial de tus pacientes en un solo lugar, accesible desde cualquier dispositivo.</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <div class="text-3xl mb-3">📱</div>
                    <h3 class="font-bold text-gray-900 mb-2">Recordatorios WhatsApp</h3>
                    <p class="text-sm text-gray-600">Reduce inasistencias un 40%. Tus pacientes reciben recordatorio automático 24hrs antes de su cita.</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <div class="text-3xl mb-3">💰</div>
                    <h3 class="font-bold text-gray-900 mb-2">Desde $0/mes</h3>
                    <p class="text-sm text-gray-600">Plan gratuito para siempre. Sin tarjeta de crédito. Perfecto para consultorios en {{ $city }} que quieren empezar.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Empieza hoy en {{ $city }}</h2>
            <p class="text-gray-600 mb-8">Configúralo en 2 minutos. Sin instalaciones. 100% en la nube.</p>
            <a href="{{ url('/doctor/register') }}" class="px-8 py-3 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition">
                Crear cuenta gratis
            </a>
        </div>
    </section>

    <footer class="py-8 bg-gray-900 text-center">
        <p class="text-sm text-gray-500">&copy; {{ date('Y') }} DocFácil. Software para consultorios en {{ $city }}, {{ $state }}.</p>
    </footer>
</body>
</html>
