<x-mail-shell titulo="Restablezca su contraseña · DocFácil">
    <p class="titulo">Elija una contraseña nueva</p>

    <p>
        Hola{{ $nombre ? ' ' . $nombre : '' }}, recibimos una solicitud para
        cambiar la contraseña de su cuenta en DocFácil.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr><td align="center" style="padding:6px 0 18px;">
            <table role="presentation" cellpadding="0" cellspacing="0"><tr>
                <td bgcolor="#0d9488" style="background-color:#0d9488;border-radius:12px;">
                    <a href="{{ $url }}" class="btn">Elegir nueva contraseña</a>
                </td>
            </tr></table>
        </td></tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:6px 0 18px;"><tr>
        <td class="info" bgcolor="#f0fdfa">
            Esta liga vence en {{ $minutos }} minutos. Si se le pasa, pida otra
            desde la pantalla de acceso.
        </td>
    </tr></table>

    <p>
        Si usted no pidió el cambio, no tiene que hacer nada: su contraseña
        sigue igual.
    </p>

    <p class="liga-larga">
        ¿No le funciona el botón? Copie y pegue esta liga en su navegador:<br>
        <span>{{ $url }}</span>
    </p>
</x-mail-shell>
