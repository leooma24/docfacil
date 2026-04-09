<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error del servidor — DocFácil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-white to-red-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center">
        <a href="/" class="inline-block mb-8">
            <img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" class="h-12 mx-auto">
        </a>

        <h1 class="text-[10rem] font-extrabold leading-none bg-gradient-to-r from-red-500 to-orange-500 bg-clip-text text-transparent">
            500
        </h1>

        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 -mt-4 mb-3">
            Algo salió mal
        </h2>

        <p class="text-gray-600 mb-8 leading-relaxed">
            Nuestro equipo ya fue notificado y estamos trabajando en ello.<br>
            Por favor intenta de nuevo en unos minutos.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="/" class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-semibold rounded-xl hover:shadow-xl transition-all hover:-translate-y-0.5">
                ← Volver al inicio
            </a>
            <button onclick="location.reload()" class="w-full sm:w-auto px-6 py-3 bg-white text-gray-800 font-semibold rounded-xl border border-gray-200 hover:border-teal-300 transition-all">
                Reintentar
            </button>
        </div>

        <p class="text-xs text-gray-500 mt-8">
            ¿Necesitas ayuda urgente? Escríbenos a
            <a href="mailto:soporte@docfacil.tu-app.co" class="text-teal-600 hover:underline">soporte@docfacil.tu-app.co</a>
        </p>
    </div>
</body>
</html>
