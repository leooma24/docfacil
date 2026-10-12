{{-- Cascarón de los correos de acceso (confirmar correo, restablecer
     contraseña). Con logo y en tablas: así se ve igual en Gmail, Outlook y
     el celular (11-oct-2026). Los estilos van en línea al salir
     (AppServiceProvider), porque Gmail a veces ignora el <style>. Y a prueba
     de Gmail: background-color (no «background»), bgcolor en las celdas con
     fondo y nada de !important, que Gmail tira junto con el estilo entero. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ?? 'DocFácil' }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #eef6f5; font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif; color: #1f2937; }
        .franja { background-color: #0d9488; background-image: linear-gradient(90deg, #0f766e, #14b8a6, #0891b2); height: 6px; font-size: 0; line-height: 0; }
        .marca { background-color: #ffffff; padding: 26px 32px 18px; text-align: center; border-bottom: 1px solid #e2e8f0; }
        .lema { color: #0f766e; font-size: 13px; font-weight: 600; letter-spacing: 0.02em; margin-top: 8px; }
        .content { padding: 32px 32px 8px; line-height: 1.6; font-size: 15px; }
        .content p { margin: 0 0 14px; }
        .titulo { font-size: 22px; line-height: 1.3; font-weight: 800; color: #0f172a; margin: 0 0 12px; }
        .btn { display: inline-block; color: #ffffff; padding: 15px 34px; text-decoration: none; font-weight: 800; font-size: 16px; }
        .info { background-color: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 12px; padding: 14px 18px; font-size: 14px; color: #134e4a; }
        .liga-larga { font-size: 12px; color: #94a3b8; }
        .liga-larga span { color: #0d9488; word-break: break-all; }
        .ayuda { padding: 18px 32px; border-top: 1px solid #e2e8f0; font-size: 14px; color: #475569; }
        .ayuda a { color: #0f766e; font-weight: 700; }
        .footer { padding: 18px 32px 26px; color: #94a3b8; font-size: 12px; text-align: center; line-height: 1.5; }
        .footer a { color: #0d9488; }
    </style>
</head>
<body>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#eef6f5" style="background-color:#eef6f5;">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="max-width:580px;background-color:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr><td class="franja" bgcolor="#0d9488" height="6">&nbsp;</td></tr>
                    <tr>
                        <td class="marca" bgcolor="#ffffff">
                            <img src="{{ rtrim(config('app.url'), '/') }}/images/logo_doc_facil.png" width="140" height="83" alt="DocFácil" style="display:block;margin:0 auto;width:140px;height:83px;border:0;">
                            <div class="lema">Su consultorio dental, en orden y conectado</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="content">
                            {{ $slot }}
                        </td>
                    </tr>
                    <tr>
                        <td class="ayuda">
                            ¿Dudas? Escríbanos por WhatsApp al <a href="https://wa.me/526682493398">668 249 3398</a>; le contesta una persona.
                        </td>
                    </tr>
                </table>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">
                    <tr>
                        <td class="footer">
                            DocFácil · Software para consultorios dentales · Hecho en Los Mochis, Sinaloa<br>
                            <a href="{{ rtrim(config('app.url'), '/') }}">docfacil.tu-app.co</a> · <a href="{{ rtrim(config('app.url'), '/') }}/privacidad">Aviso de privacidad</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
