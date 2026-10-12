{{-- El único correo del registro (11-oct-2026): da la bienvenida, dice qué
     hacer hoy y pide confirmar el correo. Confirmar ya no detiene la
     entrada; sirve para recuperar la contraseña. --}}
<x-mail-shell titulo="Su consultorio en DocFácil">
    <p class="titulo">Su consultorio ya está listo{{ $nombre ? ', ' . $nombre : '' }}</p>

    <p>
        Le damos la bienvenida a DocFácil. Ya tiene sus servicios con precio y su
        horario: puede agendar su primera cita desde ahora.
    </p>

    <p>Solo confirme que este correo es suyo; con él recupera su contraseña si algún día la olvida.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr><td align="center" style="padding:6px 0 22px;">
            <table role="presentation" cellpadding="0" cellspacing="0"><tr>
                <td bgcolor="#0d9488" style="background-color:#0d9488;border-radius:12px;">
                    <a href="{{ $url }}" class="btn">Confirmar mi correo</a>
                </td>
            </tr></table>
        </td></tr>
    </table>

    <p style="font-weight:800;color:#0f172a;margin:0 0 10px;">Para empezar hoy</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 6px;">
        @foreach([
            ['Agende su primera cita', 'Desde su escritorio. Si el paciente es nuevo, se da de alta ahí mismo.'],
            ['Traiga sus pacientes', '¿Los tiene en Excel o en otro sistema? En «Traer sus datos» sube sus pacientes, sus citas y lo que le deben.'],
            ['Prenda solo lo que use', 'Inventario, laboratorio, consentimientos… se prenden en Mi cuenta → Módulos.'],
        ] as $i => [$paso, $detalle])
            <tr>
                <td width="40" valign="top" style="padding:0 12px 14px 0;">
                    <table role="presentation" cellpadding="0" cellspacing="0"><tr><td width="30" height="30" align="center" bgcolor="#ccfbf1" style="width:30px;height:30px;border-radius:999px;background-color:#ccfbf1;color:#0f766e;font-weight:800;font-size:14px;text-align:center;">{{ $i + 1 }}</td></tr></table>
                </td>
                <td valign="top" style="padding:0 0 14px;font-size:14px;line-height:1.5;color:#475569;">
                    <strong style="color:#0f172a;font-size:15px;">{{ $paso }}</strong><br>{{ $detalle }}
                </td>
            </tr>
        @endforeach
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:18px 0 20px;"><tr>
        <td class="info" bgcolor="#f0fdfa">
            <strong>15 días con todo incluido, sin tarjeta.</strong> Después usted elige su plan, o se queda gratis con lo básico.
        </td>
    </tr></table>

    <p class="liga-larga">
        Si usted no creó esta cuenta, ignore este correo. ¿No le funciona el botón? Copie esta liga en su navegador:<br>
        <span>{{ $url }}</span>
    </p>
</x-mail-shell>
