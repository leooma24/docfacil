@php
    $n = $numeros;
    $pesos = fn ($v) => '$' . number_format((float) $v, 0);
    $cambio = fn ($c) => $c === null ? null : ($c >= 0 ? '▲ ' : '▼ ') . number_format(abs($c), 0) . '% vs. ' . $mesAnterior;
    // Sin gastos anotados no hay "te quedó": sería todo lo que entró.
    $sinGastos = $n['gastos'] <= 0;
    $colorQuedo = $n['utilidad'] >= 0 ? '#059669' : '#dc2626';

    // Estilos en línea: los clientes de correo ignoran buena parte del <style>.
    $caja = 'border-radius:10px;padding:14px 8px;text-align:center;vertical-align:top;';
    $etiqueta = 'font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;';
    $cifra = 'font-size:22px;font-weight:800;margin-top:4px;line-height:1.2;';
    $nota = 'font-size:12px;color:#64748b;margin-top:2px;';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu corte de {{ $nombreMes }}</title>
</head>
<body style="margin:0;padding:20px;background:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">
<div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
    <div style="background:linear-gradient(135deg,#0f766e,#0891b2);background-color:#0f766e;padding:30px;text-align:center;">
        <img src="{{ asset('images/logo_doc_facil_white.png') }}" alt="DocFácil" style="height:36px;margin-bottom:12px;">
        <h1 style="color:#ffffff;font-size:22px;font-weight:700;margin:0;">Tu corte de {{ $nombreMes }}</h1>
        <p style="color:rgba(255,255,255,0.85);font-size:14px;margin:4px 0 0;">{{ $clinic->name }}</p>
    </div>

    <div style="padding:30px;color:#333333;line-height:1.7;font-size:15px;">
        <p style="margin:0;">Hola <strong>{{ $doctor->name }}</strong>,</p>
        <p style="margin:12px 0 0;">Así le fue a tu consultorio en {{ $nombreMes }}:</p>

        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:20px 0 8px;border-collapse:separate;border-spacing:6px 0;">
            <tr>
                <td width="33%" style="{{ $caja }}background:#f0fdfa;">
                    <div style="{{ $etiqueta }}color:#0f766e;">Entró</div>
                    <div style="{{ $cifra }}color:#0d9488;">{{ $pesos($n['ingresos']) }}</div>
                    @if ($cambio($n['cambio_ingresos']))
                        <div style="{{ $nota }}">{{ $cambio($n['cambio_ingresos']) }}</div>
                    @endif
                </td>
                <td width="33%" style="{{ $caja }}background:#fffbeb;">
                    <div style="{{ $etiqueta }}color:#b45309;">Salió</div>
                    <div style="{{ $cifra }}color:#d97706;">{{ $sinGastos ? '—' : $pesos($n['gastos']) }}</div>
                    @if (! $sinGastos && $cambio($n['cambio_gastos']))
                        <div style="{{ $nota }}">{{ $cambio($n['cambio_gastos']) }}</div>
                    @endif
                </td>
                <td width="33%" style="{{ $caja }}background:{{ $n['utilidad'] >= 0 ? '#ecfdf5' : '#fef2f2' }};">
                    <div style="{{ $etiqueta }}color:{{ $colorQuedo }};">Te quedó</div>
                    <div style="{{ $cifra }}color:{{ $colorQuedo }};">{{ $sinGastos ? '—' : $pesos($n['utilidad']) }}</div>
                </td>
            </tr>
        </table>

        @if ($sinGastos)
            <p style="margin:16px 0 0;">Todavía no anotaste gastos de {{ $nombreMes }}, así que no te puedo decir cuánto te quedó. Anota la renta, el laboratorio y los materiales, y el mes que entra te lo digo.</p>
        @elseif ($n['utilidad'] < 0)
            <p style="margin:16px 0 0;">En {{ $nombreMes }} salió más de lo que entró. Pasa en meses de equipo nuevo o de mucho laboratorio; abajo ves en qué se fue.</p>
        @elseif ($n['margen'] !== null)
            <p style="margin:16px 0 0;">De cada $100 que entraron, te quedaron <strong>${{ number_format($n['margen'], 0) }}</strong>.</p>
        @endif

        @if (! empty($n['categorias']))
            <p style="margin:24px 0 6px;font-weight:700;color:#111827;">En qué se fue</p>
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-size:14px;">
                @foreach (array_slice($n['categorias'], 0, 3, true) as $categoria => $monto)
                    <tr>
                        <td style="padding:8px 0;border-bottom:1px solid #f1f5f9;color:#374151;">{{ $categoria }}</td>
                        <td style="padding:8px 0;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:700;color:#111827;">{{ $pesos($monto) }}</td>
                    </tr>
                @endforeach
            </table>
            @if (count($n['categorias']) > 3)
                <p style="margin:6px 0 0;font-size:13px;color:#6b7280;">Y {{ count($n['categorias']) - 3 }} {{ count($n['categorias']) - 3 === 1 ? 'categoría más' : 'categorías más' }} en tu corte.</p>
            @endif
        @endif

        @if ($n['por_cobrar'] > 0)
            <div style="margin:22px 0 0;padding:12px 16px;background:#fffbeb;border-left:3px solid #f59e0b;border-radius:8px;font-size:14px;color:#374151;">
                Te quedaron a deber <strong>{{ $pesos($n['por_cobrar']) }}</strong> de cobros de {{ $nombreMes }}. Eso no está sumado arriba: todavía no entra.
            </div>
        @endif

        <p style="text-align:center;margin:28px 0 0;">
            <a href="{{ $urlCorte }}" style="display:inline-block;background:#0d9488;background:linear-gradient(135deg,#0d9488,#0891b2);color:#ffffff!important;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;font-size:15px;">Ver mi corte completo &rarr;</a>
        </p>

        <div style="height:1px;background:#e5e7eb;margin:28px 0 20px;"></div>
        <p style="margin:0;color:#6b7280;font-size:13px;">¿Algo no cuadra? Escríbeme por WhatsApp al <a href="https://wa.me/526682493398" style="color:#14b8a6;font-weight:600;">668 249 3398</a>.</p>
        <p style="margin:8px 0 0;color:#6b7280;font-size:13px;">— Omar Lerma, DocFácil</p>
    </div>

    <div style="padding:22px 30px;background:#f9fafb;text-align:center;border-top:1px solid #f0f0f0;">
        <p style="margin:0;color:#9ca3af;font-size:12px;line-height:1.6;">
            Te llega el día 1 de cada mes con los números del mes anterior.<br>
            <a href="{{ $urlSinCorreo }}" style="color:#6b7280;">Ya no quiero recibirlo</a>
        </p>
    </div>
</div>
</body>
</html>
