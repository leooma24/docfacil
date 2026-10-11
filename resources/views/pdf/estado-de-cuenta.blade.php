@php
    $domicilio = $clinica ? collect([$clinica->address, $clinica->city, $clinica->state])->filter()->implode(', ') : '';
    $nombre = trim($paciente->first_name . ' ' . $paciente->last_name);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Estado de cuenta · {{ $nombre }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10.5px; color: #333; line-height: 1.45; }
        .page { padding: 30px 40px; }
        .brand { font-size: 16px; font-weight: bold; color: #0f766e; }
        .muted { color: #777; font-size: 9px; }
        .line { border: none; border-top: 3px solid #14b8a6; margin: 10px 0 16px; }
        h1 { font-size: 15px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        td, th { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
        th { font-size: 9px; color: #777; text-transform: uppercase; }
        .num { text-align: right; white-space: nowrap; }
        .abono td { border: none; padding: 1px 8px 1px 22px; color: #666; font-size: 9.5px; }
        .resumen { margin-top: 16px; width: 100%; }
        .resumen td { border: none; padding: 8px; text-align: center; background: #f1f5f9; }
        .resumen .cifra { font-size: 15px; font-weight: bold; color: #0f172a; }
        .resumen .debe { color: #b45309; }
        .nota { margin-top: 18px; font-size: 9px; color: #777; }
    </style>
</head>
<body>
<div class="page">
    <div class="brand">{{ $clinica?->name }}</div>
    @if($domicilio)<div class="muted">{{ $domicilio }}</div>@endif
    @if($clinica?->phone)<div class="muted">Tel. {{ $clinica->phone }}</div>@endif
    <hr class="line">

    <h1>Estado de cuenta</h1>
    <div class="muted">{{ $nombre }} · al {{ now()->format('d/m/Y') }}</div>

    <table class="resumen">
        <tr>
            <td><div class="muted">Total</div><div class="cifra">${{ number_format($total, 2) }}</div></td>
            <td><div class="muted">Pagado</div><div class="cifra">${{ number_format($pagado, 2) }}</div></td>
            <td><div class="muted">Saldo</div><div class="cifra {{ $debe > 0 ? 'debe' : '' }}">${{ number_format($debe, 2) }}</div></td>
            @if($aFavor > 0)
            <td><div class="muted">A su favor</div><div class="cifra" style="color:#047857;">${{ number_format($aFavor, 2) }}</div></td>
            @endif
        </tr>
    </table>

    <table>
        <tr><th>Fecha</th><th>Concepto</th><th class="num">Total</th><th class="num">Pagado</th><th class="num">Saldo</th></tr>
        @forelse($cobros as $c)
            <tr>
                <td>{{ $c['fecha'] }}</td><td>{{ $c['concepto'] }}</td>
                <td class="num">${{ number_format($c['total'], 2) }}</td>
                <td class="num">${{ number_format($c['pagado'], 2) }}</td>
                <td class="num">${{ number_format($c['debe'], 2) }}</td>
            </tr>
            @foreach($c['abonos'] as $a)
                <tr class="abono"><td colspan="3">{{ $a['fecha'] }} · {{ $a['forma'] }}</td><td class="num">${{ number_format($a['monto'], 2) }}</td><td></td></tr>
            @endforeach
        @empty
            <tr><td colspan="5">Sin cobros.</td></tr>
        @endforelse
    </table>

    <div class="nota">Este estado de cuenta es un comprobante de lo pagado, no una factura. Las mensualidades de un plan de pagos que todavía no vencen no se incluyen.</div>
</div>
</body>
</html>
