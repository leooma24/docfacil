<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .content { padding: 36px 32px; line-height: 1.65; font-size: 15px; }
        .content p { margin: 0 0 16px; }
        .story { background: #f0fdfa; border-left: 3px solid #14b8a6; padding: 16px 18px; margin: 20px 0; border-radius: 4px; }
        .story ul { padding-left: 20px; margin: 10px 0 0; }
        .btn { display: inline-block; background: #14b8a6; color: #ffffff !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 15px; margin: 18px 0; }
        .note { font-size: 13px; color: #666; }
        .signature { margin-top: 28px; color: #2d2d2d; }
        .footer { padding: 18px 30px; background: #f9fafb; color: #888; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <p>Hola Dr. <strong>{{ $prospectName }}</strong>,</p>

            <p>Le escribí hace unos días y no quiero ser insistente, así que solo le dejo una cosa y me quito.</p>

            <p>Hay un {{ $specialty && str_contains(strtolower($specialty), 'dent') ? 'dentista' : 'doctor' }} aquí en Culiacán — mismo perfil que usted, 1-2 {{ $specialty && str_contains(strtolower($specialty), 'dent') ? 'sillas' : 'consultorios' }} — que antes perdía 6 u 8 pacientes a la semana sin avisar. Cada cita entre $500 y $1,000. Haga la cuenta: eran $20,000 al mes que se iban por la coladera.</p>

            <div class="story">
                <strong>En dos meses con DocFácil esto cambió:</strong>
                <ul>
                    <li>Las faltas bajaron a <strong>1 o 2 por semana</strong>. Él manda el recordatorio por WhatsApp a 1 clic desde la agenda, el paciente da clic al link de "Confirmar" y listo.</li>
                    <li>Dejó de perder <strong>30 minutos diarios</strong> buscando expedientes y escribiendo recordatorios a mano.</li>
                    <li>Cuando sí se cancela, DocFácil le avisa qué paciente de su <strong>lista de espera</strong> puede tomar el slot — otro hueco cerrado.</li>
                </ul>
            </div>

            <p>Calcula que recupera unos <strong>$8,000 a $10,000 al mes</strong>. Paga $499 por el plan básico.</p>

            <p>Si a su consultorio le pasa algo parecido, probablemente también le conviene. Y si no, ignore este correo con toda confianza — respeto que quizá no es el momento.</p>

            <p style="text-align: center;">
                <a href="{{ $ctaUrl ?? url('/register') }}" class="btn">Probarlo 15 días gratis</a>
                <br>
                <span class="note">Sin tarjeta. Al terminar, su cuenta se queda viva en plan gratis.</span>
            </p>

            <p>Si prefiere que se lo muestre antes de registrarse, respóndame con un "me interesa" o escríbame al <strong>668 249 3398</strong> y le agendo 10 minutos.</p>

            <p class="signature">
                <strong>Omar Lerma</strong> · DocFácil
            </p>
        </div>
        <div class="footer">
            DocFácil · Software para consultorios médicos y dentales · <a href="{{ url('/') }}" style="color:#14b8a6;">docfacil.tu-app.co</a><br>
            @if(!empty($unsubscribeUrl))
                <small>Si no desea recibir más correos, <a href="{{ $unsubscribeUrl }}" style="color:#6b7280;text-decoration:underline;">dé de baja su correo aquí</a>. Un solo clic, lo respetamos.</small>
            @else
                <small>Si no desea recibir más correos, responda con "No me interesa" y lo saco de la lista.</small>
            @endif
        </div>
    </div>
</body>
</html>
