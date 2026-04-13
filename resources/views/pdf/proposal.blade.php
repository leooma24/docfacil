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

        .roi-box { background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 12px; padding: 20px; margin: 20px 0; }
        .roi-title { font-weight: 800; color: #0d9488; font-size: 14px; margin-bottom: 10px; }
        .roi-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #e0f2f1; font-size: 12px; }
        .roi-row:last-child { border-bottom: none; font-weight: 800; font-size: 14px; color: #0d9488; }

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
        <p>Software para {{ $isDentist ? 'consultorios dentales' : 'consultorios médicos' }} · {{ $date }}</p>
    </div>

    <div class="content">
        <p class="greeting">
            Estimado/a <strong>{{ $prospect->name }}</strong>{{ $prospect->clinic_name ? ' — ' . $prospect->clinic_name : '' }},
        </p>
        <p>
            Gracias por su interés en DocFácil. A continuación le presento cómo nuestro software puede ayudarle
            a organizar su consultorio, recuperar citas perdidas y ahorrar tiempo cada día.
        </p>

        <div class="section-title">El problema que resolvemos</div>
        <div class="benefits">
            <div class="benefit">
                <div class="benefit-title">📅 Citas que se pierden</div>
                <div class="benefit-desc">El 30% de pacientes no llegan porque se les olvida. Nuestros recordatorios WhatsApp automáticos reducen esto un 40%.</div>
            </div>
            <div class="benefit">
                <div class="benefit-title">📝 Tiempo en papeleo</div>
                <div class="benefit-desc">Expedientes, recetas, cobros — todo en un solo lugar. Ahorra 2+ horas al día en tareas administrativas.</div>
            </div>
            <div class="benefit">
                <div class="benefit-title">💰 Cobros lentos</div>
                <div class="benefit-desc">Envía el cobro por WhatsApp al terminar la consulta. El paciente paga sin salir de la app.</div>
            </div>
            <div class="benefit">
                <div class="benefit-title">📄 Recetas ilegibles</div>
                <div class="benefit-desc">Recetas PDF profesionales con su cédula, membrete y firma digital. El paciente las recibe por WhatsApp.</div>
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
                <ul class="plan-features">
                    @foreach($plan['features'] as $f)
                    <li>{{ $f }}</li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>

        <div class="section-title">Retorno de inversión</div>
        <div class="roi-box">
            <div class="roi-title">Con el plan Básico ($149/mes):</div>
            <div class="roi-row"><span>Citas recuperadas por mes (8 × $600)</span><span>+$4,800</span></div>
            <div class="roi-row"><span>Tiempo ahorrado (10 hrs × $200/hr)</span><span>+$2,000</span></div>
            <div class="roi-row"><span>Costo DocFácil</span><span>-$149</span></div>
            <div class="roi-row"><span>Beneficio neto mensual</span><span>+$6,651</span></div>
        </div>

        <div class="cta-box">
            <h3>14 días gratis · Sin tarjeta de crédito</h3>
            <p>Regístrese en docfacil.tu-app.co/doctor/register o contacte a {{ $repName }} para una demo personalizada.</p>
        </div>
    </div>

    <div class="footer">
        DocFácil · Software para consultorios médicos y dentales · docfacil.tu-app.co<br>
        Propuesta preparada por {{ $repName }} · {{ $date }}
    </div>
</body>
</html>
