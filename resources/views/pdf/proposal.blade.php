<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #1e293b; margin: 0; padding: 0; font-size: 13px; line-height: 1.6; }
        .header { background: linear-gradient(135deg, #0d9488, #0891b2); color: white; padding: 40px; text-align: center; }
        .header h1 { font-size: 28px; margin: 0 0 8px; font-weight: 800; }
        .header p { margin: 0; opacity: 0.9; font-size: 14px; }
        .content { padding: 30px 40px; }
        .section-title { font-size: 18px; font-weight: 800; color: #0d9488; margin: 28px 0 12px; border-bottom: 2px solid #ccfbf1; padding-bottom: 6px; }
        .greeting { font-size: 15px; margin-bottom: 20px; }

        .plans-grid { display: flex; gap: 15px; margin: 20px 0; }
        .plan-card { flex: 1; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px; text-align: center; }
        .plan-popular { border: 2px solid #0d9488; background: #f0fdfa; }
        .plan-name { font-size: 16px; font-weight: 800; color: #0f172a; }
        .plan-price { font-size: 24px; font-weight: 800; color: #0d9488; margin: 8px 0; }
        .plan-price span { font-size: 13px; font-weight: 400; color: #6b7280; }
        .plan-features { list-style: none; padding: 0; margin: 12px 0 0; text-align: left; }
        .plan-features li { padding: 4px 0; font-size: 12px; color: #374151; }
        .plan-features li::before { content: '✓ '; color: #0d9488; font-weight: bold; }
        .plan-badge { display: inline-block; background: #0d9488; color: white; font-size: 10px; font-weight: 700; padding: 2px 10px; border-radius: 20px; margin-bottom: 8px; }


        .cta-box { background: linear-gradient(135deg, #0d9488, #0891b2); color: white; border-radius: 12px; padding: 24px; text-align: center; margin: 24px 0; }
        .cta-box h3 { font-size: 18px; margin: 0 0 8px; }
        .cta-box p { margin: 0; opacity: 0.9; font-size: 13px; }

        .footer { text-align: center; color: #9ca3af; font-size: 11px; padding: 20px 40px; border-top: 1px solid #f1f5f9; }

        .benefits { columns: 2; column-gap: 20px; }
        .benefit { break-inside: avoid; padding: 8px 0; }
        .benefit-title { font-weight: 700; color: #0f172a; font-size: 13px; }
        .benefit-desc { font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Propuesta DocFácil</h1>
        <p>Software para consultorios dentales · {{ $date }}</p>
    </div>

    <div class="content">
        <p class="greeting">
            Estimado/a <strong>{{ $prospect->name }}</strong>{{ $prospect->clinic_name ? ' — ' . $prospect->clinic_name : '' }},
        </p>
        <p>
            Gracias por su interés en DocFácil. Aquí le explico, en corto, qué hace el sistema por su consultorio
            y cuánto cuesta.
        </p>

        <div class="section-title">Lo que hace por usted</div>
        <div class="benefits">
            <div class="benefit">
                <div class="benefit-title">📅 Citas que se pierden</div>
                <div class="benefit-desc">DocFácil le arma el recordatorio de cada cita y abre su WhatsApp con el mensaje escrito. Usted da enviar.</div>
            </div>
            <div class="benefit">
                <div class="benefit-title">📝 Tiempo en papeleo</div>
                <div class="benefit-desc">Expedientes, odontograma, recetas y cobros en un solo lugar, ligados a cada paciente.</div>
            </div>
            <div class="benefit">
                <div class="benefit-title">💰 Cobros lentos</div>
                <div class="benefit-desc">Registre el cobro al terminar la consulta. Si queda saldo, DocFácil abre su WhatsApp con el recordatorio de pago escrito.</div>
            </div>
            <div class="benefit">
                <div class="benefit-title">📄 Recetas ilegibles</div>
                <div class="benefit-desc">Recetas en PDF con su cédula, los datos de su consultorio y espacio para su firma. El paciente también las ve en su portal.</div>
            </div>
        </div>

        <div class="section-title">Planes y precios</div>
        <div class="plans-grid">
            @foreach($plans as $plan)
            <div class="plan-card {{ ($plan['popular'] ?? false) ? 'plan-popular' : '' }}">
                @if($plan['popular'] ?? false)
                <div class="plan-badge">RECOMENDADO</div>
                @endif
                <div class="plan-name">{{ $plan['name'] }}</div>
                <div class="plan-price">${{ number_format($plan['price']) }}<span>/mes</span></div>
                <div style="font-size: 11px; color: #6b7280;">{{ $plan['limits'] }}</div>
                @if($plan['lead'])
                <div style="font-size: 11px; font-weight: 700; color: #0f172a; margin-top: 8px; text-align: left;">{{ $plan['lead'] }}</div>
                @endif
                <ul class="plan-features">
                    @foreach($plan['features'] as $f)
                    <li>{{ $f }}</li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>

        <div class="cta-box">
            <h3>15 días gratis con todo · Sin tarjeta</h3>
            <p>Si paga y en los primeros 30 días no le sirve, le devolvemos su primer pago. También existe el plan Free, gratis para siempre (1 doctor, 15 pacientes, 10 citas al mes).</p>
            <p>Regístrese en docfacil.tu-app.co/doctor/register o contacte a {{ $repName }} para una demo personalizada.</p>
        </div>
    </div>

    <div class="footer">
        DocFácil · Software para consultorios dentales · docfacil.tu-app.co<br>
        Propuesta preparada por {{ $repName }} · {{ $date }}
    </div>
</body>
</html>
