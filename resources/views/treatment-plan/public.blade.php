<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $plan->title }} · {{ $plan->clinic->name }}</title>
    <meta name="description" content="Plan de tratamiento de {{ $plan->clinic->name }}. Revise los detalles y acéptelo en línea.">
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">

    {{-- OpenGraph / Twitter Cards --}}
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $plan->title }} · {{ $plan->clinic->name }}">
    <meta property="og:description" content="Plan de tratamiento personalizado. @if($plan->muestra('total')) Total: ${{ number_format($plan->total, 0) }} MXN @endif">
    <meta property="og:image" content="@if($plan->clinic->logo){{ asset('storage/' . $plan->clinic->logo) }}@else{{ asset('images/og-default.png') }}@endif">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $plan->title }} · {{ $plan->clinic->name }}">
    <meta name="twitter:description" content="Plan de tratamiento personalizado.">
    <meta name="robots" content="noindex"><!-- presupuestos no se indexan -->
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
        .etapa { display: flex; justify-content: space-between; margin-top: 18px; padding: 8px 10px; background: #f0fdfa; border-radius: 10px; font-weight: 700; color: #0f766e; font-size: 14px; }
        .etapa-monto { color: #0f766e; }
        .opcional { margin-top: 14px; padding: 12px 14px; background: #fffbeb; border: 1px dashed #fcd34d; border-radius: 12px; }
        .tooth { display: inline-block; padding: 2px 6px; background: #f0fdfa; color: #0d9488; border-radius: 4px; font-size: 12px; margin-left: 6px; font-weight: 600; }
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

        @php
            $etapas = $plan->porEtapas();
            $conEtapas = $etapas->keys()->filter()->isNotEmpty();
            $precios = $plan->muestra('precios');
            $opcionales = $plan->items->where('opcional', true);
        @endphp
        @foreach($etapas as $etapa => $items)
            @php $incluidos = $items->where('opcional', false); @endphp
            @continue($incluidos->isEmpty())
            @if($conEtapas)
                <div class="etapa">
                    <span>{{ $etapa !== '' ? $etapa : 'Otros' }}</span>
                    @if($precios)<span class="etapa-monto">${{ number_format($incluidos->sum('subtotal'), 2) }}</span>@endif
                </div>
            @endif
            <table>
                @if(! $conEtapas || $loop->first)
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th class="right">Cant.</th>
                        @if($precios)<th class="right">P. unit.</th><th class="right">Subtotal</th>@endif
                    </tr>
                </thead>
                @endif
                <tbody>
                    @foreach($incluidos as $item)
                    <tr>
                        <td>{{ $item->description }}@if($item->tooth_number)<span class="tooth">D{{ $item->tooth_number }}</span>@endif</td>
                        <td class="right">{{ $item->quantity }}</td>
                        @if($precios)
                        <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="right"><strong>${{ number_format($item->subtotal, 2) }}</strong></td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        @if($opcionales->isNotEmpty())
            <div class="opcional">
                <div style="font-weight:700;color:#92400e;margin-bottom:6px;">Opcional (no incluido en el total)</div>
                @foreach($opcionales as $item)
                    <div style="display:flex;justify-content:space-between;gap:10px;font-size:13px;padding:4px 0;">
                        <span>{{ $item->description }}@if($item->tooth_number)<span class="tooth">D{{ $item->tooth_number }}</span>@endif @if($item->quantity > 1) · {{ $item->quantity }}@endif</span>
                        @if($plan->muestra('total'))<strong>${{ number_format($item->subtotal, 2) }}</strong>@endif
                    </div>
                @endforeach
                <div style="font-size:12px;color:#92400e;margin-top:6px;">Si le interesa, dígaselo al consultorio y se lo agregan.</div>
            </div>
        @endif

        @if($plan->muestra('total'))
        <div class="total-box">
            @if($plan->discount > 0)
                <div style="color:#6b7280;font-size:13px;">Subtotal: ${{ number_format($plan->subtotal, 2) }} · Descuento @if($plan->descuento_porcentaje > 0) ({{ rtrim(rtrim(number_format($plan->descuento_porcentaje, 2), '0'), '.') }}%)@endif: −${{ number_format($plan->discount, 2) }}</div>
            @endif
            <div class="total-label">Total a pagar</div>
            <div class="total-amount">${{ number_format($plan->total, 2) }} MXN</div>
        </div>
        @else
        <p style="text-align:center;color:#6b7280;font-size:13px;margin-top:12px;">El costo se lo explican en el consultorio.</p>
        @endif

        @if($plan->status === 'accepted' && $plan->firma_paciente)
            <div style="margin-top:18px;text-align:center;">
                <img src="{{ $plan->firma_paciente }}" alt="Firma" style="max-width:260px;max-height:90px;">
                <div style="font-size:12px;color:#6b7280;border-top:1px solid #e5e7eb;display:inline-block;padding-top:4px;min-width:220px;">{{ $plan->firmado_por }} · {{ $plan->accepted_at?->format('d/m/Y H:i') }}</div>
            </div>
        @endif

        @if($plan->status === 'sent')
        <form method="POST" action="{{ $acceptUrl }}" id="form-aceptar" style="margin-top:24px;">
            @csrf
            <div style="font-weight:700;font-size:15px;margin-bottom:6px;">Para aceptar, firme aquí</div>
            @if($errors->any())
                <div style="background:#fee2e2;color:#991b1b;border-radius:10px;padding:10px 12px;font-size:13px;margin-bottom:8px;">{{ $errors->first() }}</div>
            @endif
            <canvas id="firma" width="600" height="200" style="width:100%;height:150px;border:2px dashed #99f6e4;border-radius:12px;background:#fff;touch-action:none;"></canvas>
            <div style="display:flex;justify-content:space-between;align-items:center;margin:6px 0 12px;">
                <span style="font-size:12px;color:#6b7280;">Con el dedo o el mouse.</span>
                <button type="button" id="borrar-firma" style="background:none;border:none;color:#0d9488;font-weight:700;font-size:13px;cursor:pointer;">Borrar</button>
            </div>
            <label style="display:block;font-size:13px;color:#4b5563;margin-bottom:4px;">Su nombre</label>
            <input type="text" name="firmado_por" maxlength="120" required value="{{ old('firmado_por', trim($plan->patient->first_name . ' ' . $plan->patient->last_name)) }}"
                style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:10px;font-size:15px;margin-bottom:12px;">
            <input type="hidden" name="firma" id="firma-dato">
            <button type="submit" class="btn btn-accept" style="border:none;cursor:pointer;font:inherit;font-weight:700;">✓ Firmar y aceptar</button>
        </form>
        <form method="POST" action="{{ $rejectUrl }}" style="margin-top:10px;" onsubmit="return confirm('¿Seguro que por ahora no quiere este plan?');">
            @csrf
            <button type="submit" class="btn btn-reject" style="border:none;cursor:pointer;font:inherit;">Por ahora no</button>
        </form>
        <p style="text-align:center;color:#6b7280;font-size:12px;margin-top:12px;">Al aceptar, en {{ $plan->clinic->name }} le van a escribir para agendar su primera cita. Su firma queda en este presupuesto.</p>
        <script>
        (function () {
            var c = document.getElementById('firma'), x = c.getContext('2d'), dibujando = false, firmo = false, ultimo = null;
            x.lineWidth = 3; x.lineCap = 'round'; x.lineJoin = 'round'; x.strokeStyle = '#111827';
            function punto(e) { var r = c.getBoundingClientRect(), t = e.touches ? e.touches[0] : e; return { x: (t.clientX - r.left) * c.width / r.width, y: (t.clientY - r.top) * c.height / r.height }; }
            function empieza(e) { e.preventDefault(); dibujando = true; ultimo = punto(e); }
            function mueve(e) { if (!dibujando) return; e.preventDefault(); var p = punto(e); x.beginPath(); x.moveTo(ultimo.x, ultimo.y); x.lineTo(p.x, p.y); x.stroke(); ultimo = p; firmo = true; }
            function termina() { dibujando = false; }
            c.addEventListener('mousedown', empieza); c.addEventListener('mousemove', mueve); window.addEventListener('mouseup', termina);
            c.addEventListener('touchstart', empieza, { passive: false }); c.addEventListener('touchmove', mueve, { passive: false }); c.addEventListener('touchend', termina);
            document.getElementById('borrar-firma').addEventListener('click', function () { x.clearRect(0, 0, c.width, c.height); firmo = false; });
            document.getElementById('form-aceptar').addEventListener('submit', function (e) {
                if (!firmo) { e.preventDefault(); alert('Firme con el dedo en el recuadro para aceptar.'); return; }
                document.getElementById('firma-dato').value = c.toDataURL('image/png');
            });
        })();
        </script>
        @endif
    </div>
    <div style="text-align:center;margin-top:18px;padding:14px;">
        <a href="{{ url('/') }}?utm_source=portal&utm_medium=treatment_plan&utm_campaign=powered_by" target="_blank" rel="noopener"
           style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:rgba(255,255,255,0.85);border:1px solid rgba(13, 148, 136, 0.15);border-radius:999px;text-decoration:none;font-size:12px;color:#6b7280;font-weight:500;transition:all 0.2s;">
            <span style="width:16px;height:16px;border-radius:4px;background:linear-gradient(135deg, #14b8a6, #06b6d4);"></span>
            Presupuesto vía <strong style="color:#0d9488;font-weight:700;">DocFácil</strong>
        </a>
    </div>
</div>
</body>
</html>
