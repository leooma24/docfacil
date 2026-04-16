<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualización de precios DocFácil</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f9fafb; padding: 20px; color: #1f2937; }
        .wrap { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .header { background: linear-gradient(135deg, #0d9488, #06b6d4); color: #fff; padding: 28px 24px; text-align: center; }
        .header img { height: 38px; margin-bottom: 10px; }
        .content { padding: 28px 24px; line-height: 1.6; }
        .highlight { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 14px 16px; border-radius: 6px; margin: 16px 0; }
        .cta { display: inline-block; background: #14b8a6; color: #fff !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; }
        .footer { padding: 18px 24px; background: #f3f4f6; font-size: 12px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            <img src="https://docfacil.tu-app.co/images/logo_doc_facil_white.png" alt="DocFácil">
            <h1 style="margin:0;font-size:22px;">Actualización de precios</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $doctorName }}</strong>,</p>

            <p>Gracias por confiar en DocFácil para tu consultorio <strong>{{ $clinic->name }}</strong>. Queremos avisarte con anticipación que estamos ajustando nuestros precios a partir del <strong>{{ \Carbon\Carbon::parse($effectiveDate)->translatedFormat('d \d\e F \d\e Y') }}</strong>.</p>

            <p>Los nuevos precios son:</p>
            <ul>
                <li><strong>Básico:</strong> $499 MXN/mes (o $4,990/año — 2 meses gratis)</li>
                <li><strong>Pro:</strong> $999 MXN/mes (o $9,990/año — 2 meses gratis)</li>
                <li><strong>Clínica:</strong> $1,999 MXN/mes (o $19,990/año — 2 meses gratis)</li>
            </ul>

            <div class="highlight">
                <strong>Tu plan actual es {{ \App\Models\Clinic::displayNameForPlan($clinic->plan) }}.</strong>
                El nuevo precio aplicará en tu próximo ciclo de cobro a partir de la fecha anterior.
            </div>

            <p><strong>💡 Consejo:</strong> si cambias ahora a facturación anual, te quedas con el precio actual durante 12 meses y ahorras 2 meses completos.</p>

            <p style="text-align:center;margin:28px 0;">
                <a href="https://docfacil.tu-app.co/doctor/actualizar-plan" class="cta">Cambiar a plan anual →</a>
            </p>

            <p>¿Dudas? Respóndeme este correo directamente o escríbeme por WhatsApp al <a href="https://wa.me/526682493398">668 249 3398</a>.</p>

            <p style="margin-top:24px;">Gracias por la confianza,<br><strong>Omar Lerma</strong><br>Fundador de DocFácil</p>
        </div>
        <div class="footer">
            DocFácil — Software para consultorios médicos y dentales · Hecho en México 🇲🇽
        </div>
    </div>
</body>
</html>
