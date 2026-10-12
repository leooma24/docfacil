{{-- El único correo del registro (11-oct-2026): da la bienvenida y pide
     confirmar el correo. Ya no detiene la entrada; sirve para recuperar la
     contraseña. --}}
<x-mail-shell titulo="Confirme su correo · DocFácil">
    <p>Hola{{ $nombre ? ' ' . $nombre : '' }},</p>

    <p>
        Le damos la bienvenida a DocFácil. Su consultorio ya está listo, con
        sus servicios y su horario: puede agendar su primera cita desde ahora.
    </p>

    <p>Confirme que este correo es suyo; con él recupera su contraseña si algún día la olvida.</p>

    <p><a href="{{ $url }}" class="btn">Confirmar mi correo</a></p>

    <div class="info">
        Tiene <strong>15 días de prueba con todo incluido</strong>, sin tarjeta.
        ¿Ya tiene sus pacientes en Excel? En «Traer sus datos» los sube de un jalón.
    </div>

    <p>Si usted no creó esta cuenta, puede ignorar este correo.</p>

    <p class="liga-larga">
        ¿No le funciona el botón? Copie y pegue esta liga en su navegador:<br>
        <span>{{ $url }}</span>
    </p>
</x-mail-shell>
