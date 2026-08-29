<x-mail-shell titulo="Restablece tu contraseña · DocFácil">
    <p>Hola{{ $nombre ? ' ' . $nombre : '' }},</p>

    <p>
        Recibimos una solicitud para cambiar la contraseña de tu cuenta en
        DocFácil.
    </p>

    <p><a href="{{ $url }}" class="btn">Elegir nueva contraseña</a></p>

    <div class="info">
        Esta liga vence en {{ $minutos }} minutos. Si se te pasa, pide otra
        desde la pantalla de acceso.
    </div>

    <p>
        Si tú no pediste el cambio, no tienes que hacer nada: tu contraseña
        sigue igual.
    </p>

    <p class="liga-larga">
        ¿No te funciona el botón? Copia y pega esta liga en tu navegador:<br>
        <span>{{ $url }}</span>
    </p>
</x-mail-shell>
