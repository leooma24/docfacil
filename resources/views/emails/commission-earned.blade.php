<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nueva comisión ganada</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f0; margin: 0; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <div style="background: linear-gradient(135deg, #0F6E56, #0891b2); padding: 32px 24px; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 24px;">🎉 ¡Nueva comisión ganada!</h1>
        </div>

        <div style="padding: 32px 24px;">
            <p style="font-size: 16px; color: #333; margin: 0 0 16px;">Hola <strong>{{ $user->name }}</strong>,</p>

            <p style="font-size: 15px; color: #555; line-height: 1.6;">
                @if($commission->tier === 'first')
                    La clínica <strong>{{ $clinic->name }}</strong> acaba de realizar su primer pago. Ganaste la <strong>primera mitad</strong> de tu comisión.
                @else
                    La clínica <strong>{{ $clinic->name }}</strong> completó su segundo pago. Ganaste la <strong>segunda mitad</strong> de tu comisión.
                @endif
            </p>

            <div style="background: #E1F5EE; border-left: 4px solid #0F6E56; border-radius: 8px; padding: 20px; margin: 24px 0;">
                <div style="color: #0F6E56; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Monto</div>
                <div style="color: #0F6E56; font-size: 32px; font-weight: 800;">${{ number_format($commission->amount, 2) }} MXN</div>
                <div style="color: #666; font-size: 13px; margin-top: 8px;">Plan {{ ucfirst($commission->plan_at_sale) }} · {{ $commission->tier === 'first' ? 'Primera mitad' : 'Segunda mitad' }}</div>
            </div>

            <p style="font-size: 14px; color: #666; line-height: 1.6;">
                Tu comisión está en estado <strong>Pendiente</strong> y se te pagará en el próximo corte mensual.
            </p>

            <div style="text-align: center; margin: 32px 0 16px;">
                <a href="{{ url('/ventas/commissions') }}" style="display: inline-block; background: #0F6E56; color: white; text-decoration: none; padding: 12px 32px; border-radius: 10px; font-weight: 600; font-size: 15px;">
                    Ver mis comisiones
                </a>
            </div>

            <p style="font-size: 13px; color: #999; text-align: center; margin-top: 24px;">
                Sigue así 💪 — tu trabajo está dando resultados.
            </p>
        </div>

        <div style="background: #f8f9f6; padding: 16px; text-align: center; border-top: 1px solid #e5e5e0;">
            <p style="font-size: 12px; color: #999; margin: 0;">
                DocFácil · Panel de Ventas<br>
                <a href="{{ url('/ventas') }}" style="color: #0F6E56; text-decoration: none;">docfacil.tu-app.co/ventas</a>
            </p>
        </div>
    </div>
</body>
</html>
