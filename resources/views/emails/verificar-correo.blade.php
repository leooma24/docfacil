<x-mail-shell titulo="Confirma tu correo · DocFácil">
    <p>Hola{{ $nombre ? ' ' . $nombre : '' }},</p>

    <p>
        Ya casi. Solo falta que confirmes que este correo es tuyo y entras
        a tu consultorio.
    </p>

    <p><a href="{{ $url }}" class="btn">Confirmar mi correo</a></p>

    <div class="info">
        Tienes <strong>15 días de prueba con todo incluido</strong>: agenda,
        expedientes, recetas en PDF con tu cédula, odontograma y
        recordatorios por WhatsApp. Sin tarjeta.
    </div>

    <p>Si tú no creaste esta cuenta, puedes ignorar este correo.</p>

    <p class="liga-larga">
        ¿No te funciona el botón? Copia y pega esta liga en tu navegador:<br>
        <span>{{ $url }}</span>
    </p>
</x-mail-shell>
