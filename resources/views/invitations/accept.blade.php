<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aceptar Invitación — DocFácil</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" class="h-12 mx-auto mb-4">
        </div>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-2">Te han invitado</h2>
            <p class="text-gray-600 mb-6">
                <strong>{{ $invitation->clinic->name }}</strong> te invita a unirte como doctor en su consultorio.
            </p>

            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="text-sm text-gray-500">Nombre</div>
                <div class="font-semibold">{{ $invitation->name }}</div>
                <div class="text-sm text-gray-500 mt-2">Email</div>
                <div class="font-semibold">{{ $invitation->email }}</div>
                @if($invitation->specialty)
                <div class="text-sm text-gray-500 mt-2">Especialidad</div>
                <div class="font-semibold">{{ $invitation->specialty }}</div>
                @endif
            </div>

            <form method="POST" action="{{ route('invitation.store', $invitation->token) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    @error('password')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                </div>
                <button type="submit"
                    class="w-full bg-teal-600 text-white font-semibold py-2.5 rounded-lg hover:bg-teal-700 transition">
                    Aceptar invitación y crear cuenta
                </button>
            </form>
        </div>
    </div>
</body>
</html>
