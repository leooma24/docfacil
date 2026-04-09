<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso denegado — DocFácil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        .animate-blob { animation: blob 7s infinite; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-white to-amber-50 min-h-screen flex items-center justify-center px-4 overflow-hidden relative">
    <div class="absolute top-20 -left-40 w-96 h-96 bg-amber-200/30 rounded-full blur-3xl animate-blob"></div>
    <div class="absolute bottom-20 -right-40 w-96 h-96 bg-orange-200/30 rounded-full blur-3xl animate-blob" style="animation-delay:3s"></div>

    <div class="max-w-lg w-full text-center relative z-10">
        <a href="/" class="inline-block mb-8">
            <img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" class="h-12 mx-auto">
        </a>

        <h1 class="text-[10rem] font-extrabold leading-none bg-gradient-to-r from-amber-500 via-orange-500 to-amber-500 bg-clip-text text-transparent">
            403
        </h1>

        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 -mt-4 mb-3">
            Acceso denegado
        </h2>

        <p class="text-gray-600 mb-8 leading-relaxed">
            No tienes permiso para ver esta página.<br>
            Es posible que tu sesión haya expirado o que esta sección no esté disponible para tu cuenta.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-10">
            <a href="/" class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-semibold rounded-xl hover:shadow-xl hover:shadow-teal-300/50 transition-all hover:-translate-y-0.5">
                ← Volver al inicio
            </a>
            <a href="/doctor/login" class="w-full sm:w-auto px-6 py-3 bg-white text-gray-800 font-semibold rounded-xl border border-gray-200 hover:border-teal-300 hover:bg-teal-50 transition-all">
                Iniciar sesión
            </a>
        </div>

        <div class="bg-white/80 backdrop-blur rounded-2xl border border-gray-200 p-6 text-left shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Accede a tu panel</p>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <a href="/doctor/login" class="text-teal-600 hover:underline flex items-center gap-1">
                    <span>→</span> Panel Doctor
                </a>
                <a href="/paciente/login" class="text-teal-600 hover:underline flex items-center gap-1">
                    <span>→</span> Portal Paciente
                </a>
                <a href="/ventas/login" class="text-teal-600 hover:underline flex items-center gap-1">
                    <span>→</span> Panel Ventas
                </a>
                <a href="/admin/login" class="text-teal-600 hover:underline flex items-center gap-1">
                    <span>→</span> Administración
                </a>
            </div>
        </div>

        <p class="text-xs text-gray-500 mt-6">
            ¿Crees que esto es un error? Escríbenos a
            <a href="mailto:soporte@docfacil.tu-app.co" class="text-teal-600 hover:underline">soporte@docfacil.tu-app.co</a>
        </p>
    </div>
</body>
</html>
