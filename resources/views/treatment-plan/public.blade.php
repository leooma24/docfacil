<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $plan->title }} · {{ $plan->clinic->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%); min-height: 100vh; padding: 20px; color: #1f2937; }
        .container { max-width: 680px; margin: 0 auto; }
        .card { background: #ffffff; border-radius: 20px; padding: 32px 26px; box-shadow: 0 20px 40px -8px rgba(13, 148, 136, 0.12); border: 1px solid rgba(13, 148, 136, 0.1); margin-bottom: 20px; }
        .brand { color: #0d9488; font-size: 22px; font-weight: 700; text-align: center; margin-bottom: 4px; }
        .clinic-sub { text-align: center; color: #6b7280; font-size: 13px; margin-bottom: 24px; }
        h1 { font-size: 22px; font-weight: 700; color: #1f2937; margin-bottom: 8px; }
        .description { color: #4b5563; font-size: 14px; line-height: 1.55; margin-bottom: 24px; }
        .meta-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .meta-row .label { color: #6b7280; }
        .meta-row .value { color: #1f2937; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 13px; }
        th { background: #0d9488; color: white; padding: 10px; text-align: left; font-size: 12px; }
        th.right { text-align: right; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        td.right { text-align: right; }
        .total-box { background: #f0fdfa; border: 2px solid #0d9488; border-radius: 12px; padding: 16px; text-align: right; margin-top: 12px; }
        .total-label { color: #6b7280; font-size: 13px; }
        .total-amount { font-size: 26px; font-weight: 700; color: #0d9488; margin-top: 4px; }
        .actions { margin-top: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .btn { display: block; width: 100%; padding: 14px; border-radius: 12px; font-weight: 700; text-align: center; text-decoration: none; font-size: 15px; border: none; cursor: pointer; transition: transform 0.15s; }
        .btn:hover { transform: translateY(-1px); }
        .btn-accept { background: linear-gradient(135deg, #14b8a6, #0d9488); color: white; }
        .btn-reject { background: #f3f4f6; color: #6b7280; }
        .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; margin-bottom: 12px; }
        .status-accepted { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .footer { text-align: center; margin-top: 20px; color: #9ca3af; font-size: 12px; }
        .footer .docfacil { color: #14b8a6; font-weight: 700; }
        .tooth { display: inline-block; padding: 2px 6px; background: #f0fdfa; color: #0d9488; border-radius: 4px; font-size: 11px; margin-left: 6px; font-weight: 600; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <p class="brand">{{ $plan->clinic->name }}</p>
        <p class="clinic-sub">
            @if($plan->doctor && $plan->doctor->user) {{ $plan->doctor->user->name }} @endif
            @if($plan->clinic->phone) · {{ $plan->clinic->phone }} @endif
        </p>

        @if($plan->status === 'accepted')
            <div style="text-align:center;margin-bottom:16px;">
                <span class="status-badge status-accepted">✓ Aceptado el {{ $plan->accepted_at->format('d/m/Y') }}</span>
            </div>
        @elseif($plan->status === 'rejected')
            <div style="text-align:center;margin-bottom:16px;">
                <span class="status-badge status-rejected">✗ Rechazado</span>
            </div>
        @endif

        <h1>{{ $plan->title }}</h1>
        @if($plan->description)<p class="description">{{ $plan->description }}</p>@endif

        <div>
            <div class="meta-row"><span class="label">Paciente</span><span class="value">{{ $plan->patient->first_name }} {{ $plan->patient->last_name }}</span></div>
            <div class="meta-row"><span class="label">Fecha</span><span class="value">{{ $plan->created_at->format('d/m/Y') }}</span></div>
            @if($plan->valid_until)<div class="meta-row"><span class="label">Válido hasta</span><span class="value">{{ $plan->valid_until->format('d/m/Y') }}</span></div>@endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th class="right">Cant.</th>
                    <th class="right">P. unit.</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plan->items as $item)
                <tr>
                    <td>{{ $item->description }}@if($item->tooth_number)<span class="tooth">D{{ $item->tooth_number }}</span>@endif</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="right"><strong>${{ number_format($item->subtotal, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-box">
            @if($plan->discount > 0)
                <div style="color:#6b7280;font-size:13px;">Subtotal: ${{ number_format($plan->subtotal, 2) }} · Descuento: −${{ number_format($plan->discount, 2) }}</div>
            @endif
            <div class="total-label">Total a pagar</div>
            <div class="total-amount">${{ number_format($plan->total, 2) }} MXN</div>
        </div>

        @if($plan->status === 'sent')
        <div class="actions">
            <a href="{{ $acceptUrl }}" class="btn btn-accept">✓ Aceptar plan</a>
            <a href="{{ $rejectUrl }}" class="btn btn-reject" onclick="return confirm('¿Seguro que quieres rechazar este plan?');">Por ahora no</a>
        </div>
        <p style="text-align:center;color:#6b7280;font-size:12px;margin-top:12px;">Al aceptar, {{ $plan->clinic->name }} te contactará para agendar la primera cita.</p>
        @endif
    </div>

    <p class="footer">Presupuesto enviado vía <span class="docfacil">DocFácil</span></p>
</div>
</body>
</html>
