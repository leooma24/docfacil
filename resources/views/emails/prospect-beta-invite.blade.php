<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .content { padding: 36px 32px; line-height: 1.65; font-size: 15px; }
        .content p { margin: 0 0 16px; }
        .scene { color: #4a4a4a; font-style: italic; border-left: 3px solid #14b8a6; padding: 4px 0 4px 14px; margin: 18px 0; }
        .arrow { color: #14b8a6; font-weight: 700; }
        .btn { display: inline-block; background: #14b8a6; color: #ffffff !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 15px; margin: 18px 0; }
        .note { font-size: 13px; color: #666; margin-top: 8px; }
        .signature { margin-top: 28px; color: #2d2d2d; }
        .footer { padding: 18px 30px; background: #f9fafb; color: #888; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <p>Hola Dr. <strong>{{ $prospectName }}</strong>,</p>

            <p>Le escribo porque vi su consultorio{{ $clinicName ? ' — ' . $clinicName . ' —' : '' }} y me imaginé una escena que probablemente ya vivió esta semana:</p>

            <p class="scene">Abrió la agenda a las 10. A las 10:15 el paciente no llegaba. A las 10:30 ya había pasado a otro. Y el hueco del primero se quedó ahí.</p>

            <p>Eso pasa en casi todos los consultorios del país. No es descuido del paciente — es que nadie le recuerda la cita de forma que no se pueda ignorar. Y cada hueco así son entre $500 y $1,500 que se evaporan, sin contar el tratamiento que no siguió.</p>

            <p>Le ayudo a que eso sea la excepción, no la regla. Soy <strong>Omar Lerma</strong>, y lo que hago es simple:</p>

            <p>
                <span class="arrow">→</span> <strong>A 1 clic desde la agenda</strong> le manda un WhatsApp al paciente con la fecha y un link para confirmar. Se olvida de perseguir a nadie.<br>
                <span class="arrow">→</span> Cuando el paciente llega, abre su <strong>expediente en 5 segundos</strong>, no buscándolo entre carpetas.<br>
                <span class="arrow">→</span> Su receta sale <strong>firmada y en PDF</strong>. Se ve seria, no escrita al vuelo.
            </p>

            <p>Los consultorios que ya están con nosotros notan tres cosas en el primer mes:</p>

            <ul style="padding-left: 20px; margin: 0 0 18px;">
                <li>Recuperan <strong>$6,000 a $10,000</strong> que antes se iban en huecos de agenda.</li>
                <li>Se ahorran <strong>20-30 minutos al día</strong> escribiendo recordatorios uno por uno.</li>
                <li>La recepcionista deja de cargar con lo que no le toca, y eso se nota.</li>
            </ul>

            <p><strong>No le pido que se comprometa con nada.</strong> Al registrarse tiene <strong>15 días con todo desbloqueado</strong>, sin tarjeta. Al terminar, su cuenta se queda viva en plan gratis (1 doctor, 15 pacientes) — nunca pierde acceso. Si le gusta cómo funciona, puede quedarse con un plan pagado desde $499/mes.</p>

            <p style="text-align: center;">
                <a href="{{ $ctaUrl ?? url('/register') }}" class="btn">Probarlo 15 días gratis</a>
                <br>
                <span class="note">Sin tarjeta. 2 minutos para registrarse.</span>
            </p>

            <p>O si prefiere verlo antes de registrarse, respóndame este correo con su mejor día y le muestro en 10 minutos cómo se vería en <em>su</em> consultorio.</p>

            <p class="signature">
                Un saludo,<br>
                <strong>Omar Lerma</strong> · Fundador de DocFácil<br>
                WhatsApp directo: <a href="https://wa.me/526682493398" style="color:#14b8a6;">668 249 3398</a>
            </p>
        </div>
        <div class="footer">
            DocFácil · Software para consultorios médicos y dentales · <a href="{{ url('/') }}" style="color:#14b8a6;">docfacil.tu-app.co</a><br>
            <small>Si no desea recibir más correos, responda con "No me interesa" y lo saco de la lista.</small>
        </div>
    </div>
</body>
</html>
