<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Plan de tratamiento · {{ $plan->clinic->name ?? 'DocFácil' }}</title>
    <style>
        @page { margin: 50px 45px; }
        body { font-family: Arial, sans-serif; color: #1f2937; font-size: 12px; line-height: 1.5; margin: 0; }
        .header { display: table; width: 100%; border-bottom: 3px solid #0d9488; padding-bottom: 14px; margin-bottom: 24px; }
        .header-left { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; width: 40%; }
        .clinic-name { font-size: 20px; font-weight: 700; color: #0d9488; margin: 0 0 4px; }
        .clinic-detail { font-size: 11px; color: #6b7280; }
        .doc-title { font-size: 16px; font-weight: 700; color: #374151; margin: 0; }
        .doc-number { font-size: 11px; color: #6b7280; }
        .meta-box { background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 14px; margin-bottom: 20px; }
        .meta-row { padding: 3px 0; }
        .meta-label { font-weight: 700; color: #374151; display: inline-block; min-width: 90px; }
        .plan-title { font-size: 18px; font-weight: 700; color: #1f2937; margin: 18px 0 6px; }
        .plan-desc { color: #4b5563; margin-bottom: 18px; font-size: 12px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        table.items th { background: #0d9488; color: white; padding: 9px 10px; text-align: left; font-size: 11px; font-weight: 700; }
        table.items th.right { text-align: right; }
        table.items td { padding: 9px 10px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        table.items td.right { text-align: right; }
        table.items tr:nth-child(even) td { background: #f9fafb; }
        .totals { margin-left: auto; width: 280px; margin-top: 10px; }
        .totals-row { display: table; width: 100%; padding: 5px 0; font-size: 12px; }
        .totals-row .label { display: table-cell; text-align: right; padding-right: 14px; color: #6b7280; }
        .totals-row .amount { display: table-cell; text-align: right; width: 110px; font-weight: 600; }
        .totals-row.grand { font-size: 15px; padding: 8px 0; border-top: 2px solid #0d9488; margin-top: 6px; }
        .totals-row.grand .label { color: #0d9488; font-weight: 700; }
        .totals-row.grand .amount { color: #0d9488; font-weight: 700; }
        .footer-note { margin-top: 30px; padding-top: 14px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #6b7280; text-align: center; }
        .signature-line { margin-top: 40px; border-top: 1px solid #9ca3af; padding-top: 6px; font-size: 11px; color: #4b5563; text-align: center; width: 260px; }
        .valid-badge { display: inline-block; background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <p class="clinic-name">{{ $plan->clinic->name ?? 'Clínica' }}</p>
            @if($plan->clinic->address) <p class="clinic-detail">{{ $plan->clinic->address }}</p> @endif
            @if($plan->clinic->phone) <p class="clinic-detail">Tel: {{ $plan->clinic->phone }}</p> @endif
            @if($plan->doctor && $plan->doctor->user) <p class="clinic-detail">Atiende: {{ $plan->doctor->user->name }}@if($plan->doctor->license_number) · Céd. prof. {{ $plan->doctor->license_number }}@endif</p> @endif
        </div>
        <div class="header-right">
            <p class="doc-title">PLAN DE TRATAMIENTO</p>
            <p class="doc-number">Folio: #{{ str_pad($plan->id, 5, '0', STR_PAD_LEFT) }}</p>
            <p class="doc-number">Fecha: {{ $plan->created_at->format('d/m/Y') }}</p>
            @if($plan->valid_until)
            <p class="doc-number" style="margin-top:6px;"><span class="valid-badge">Válido hasta {{ $plan->valid_until->format('d/m/Y') }}</span></p>
            @endif
        </div>
    </div>

    <div class="meta-box">
        <div class="meta-row"><span class="meta-label">Paciente:</span> {{ $plan->patient->first_name }} {{ $plan->patient->last_name }}</div>
        @if($plan->patient->phone) <div class="meta-row"><span class="meta-label">Teléfono:</span> {{ $plan->patient->phone }}</div> @endif
        @if($plan->patient->email) <div class="meta-row"><span class="meta-label">Correo:</span> {{ $plan->patient->email }}</div> @endif
    </div>

    <p class="plan-title">{{ $plan->title }}</p>
    @if($plan->description)
    <p class="plan-desc">{{ $plan->description }}</p>
    @endif

    @php $etapas = $plan->porEtapas(); $conEtapas = $etapas->keys()->filter()->isNotEmpty(); $opcionales = $plan->items->where('opcional', true); @endphp
    <table class="items">
        <thead>
            <tr>
                <th style="width:45%;">Concepto</th>
                <th style="width:12%;">Diente</th>
                <th class="right" style="width:10%;">Cant.</th>
                <th class="right" style="width:16%;">P. unitario</th>
                <th class="right" style="width:17%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($etapas as $etapa => $items)
                @php $incluidos = $items->where('opcional', false); @endphp
                @continue($incluidos->isEmpty())
                @if($conEtapas)
                <tr><td colspan="4" style="background:#f0fdfa;color:#0f766e;font-weight:700;">{{ $etapa !== '' ? $etapa : 'Otros' }}</td>
                    <td class="right" style="background:#f0fdfa;color:#0f766e;font-weight:700;">${{ number_format($incluidos->sum('subtotal'), 2) }}</td></tr>
                @endif
                @foreach($incluidos as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->tooth_number ?: '—' }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    @if($opcionales->isNotEmpty())
    <table class="items">
        <thead><tr><th colspan="5" style="background:#b45309;">Opcional (no incluido en el total)</th></tr></thead>
        <tbody>
            @foreach($opcionales as $item)
            <tr>
                <td style="width:45%;">{{ $item->description }}</td>
                <td style="width:12%;">{{ $item->tooth_number ?: '—' }}</td>
                <td class="right" style="width:10%;">{{ $item->quantity }}</td>
                <td class="right" style="width:16%;">${{ number_format($item->unit_price, 2) }}</td>
                <td class="right" style="width:17%;">${{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="totals">
        <div class="totals-row">
            <span class="label">Subtotal</span>
            <span class="amount">${{ number_format($plan->subtotal, 2) }}</span>
        </div>
        @if($plan->discount > 0)
        <div class="totals-row">
            <span class="label">Descuento @if($plan->descuento_porcentaje > 0) ({{ rtrim(rtrim(number_format($plan->descuento_porcentaje, 2), '0'), '.') }}%)@endif</span>
            <span class="amount">− ${{ number_format($plan->discount, 2) }}</span>
        </div>
        @endif
        <div class="totals-row grand">
            <span class="label">TOTAL</span>
            <span class="amount">${{ number_format($plan->total, 2) }} MXN</span>
        </div>
    </div>

    @if($plan->firma_paciente)
    <div style="margin-top:36px;text-align:center;">
        <img src="{{ $plan->firma_paciente }}" alt="Firma" style="max-width:220px;max-height:80px;">
        <div class="signature-line" style="margin-top:2px;">Firma del paciente · {{ $plan->firmado_por }}</div>
    </div>
    @else
    <div class="signature-line">
        Firma del paciente
    </div>
    @endif

    <div class="footer-note">
        Este presupuesto es informativo y no constituye factura fiscal.<br>
        @if($plan->accepted_at)
            <strong>Aceptado @if($plan->firma_paciente)y firmado en línea @endif el {{ $plan->accepted_at->format('d/m/Y H:i') }}</strong> @if($plan->accepted_ip)desde IP {{ $plan->accepted_ip }}@endif.
        @endif
    </div>
</body>
</html>
