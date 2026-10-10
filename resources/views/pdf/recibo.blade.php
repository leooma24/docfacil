@php
    $domicilio = $clinica ? collect([$clinica->address, $clinica->city, $clinica->state])->filter()->implode(', ') : '';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo de pago #{{ $cobro->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.45; }
        .page { padding: 30px 40px; }
        .brand { font-size: 16px; font-weight: bold; color: #0f766e; }
        .muted { color: #777; font-size: 9px; }
        .line { border: none; border-top: 3px solid #14b8a6; margin: 10px 0 16px; }
        h1 { font-size: 15px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td, th { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { font-size: 9px; color: #777; text-transform: uppercase; }
        .num { text-align: right; }
        .totales td { border: none; padding: 3px 8px; }
        .grande { font-size: 13px; font-weight: bold; }
        .nota { margin-top: 18px; font-size: 9px; color: #777; }
    </style>
</head>
<body>
<div class="page">
    <div class="brand">{{ $clinica?->name }}</div>
    @if($domicilio)<div class="muted">{{ $domicilio }}</div>@endif
    @if($clinica?->phone)<div class="muted">Tel. {{ $clinica->phone }}</div>@endif
    <hr class="line">

    <h1>Recibo de pago</h1>
    <div class="muted">Folio {{ $cobro->id }} · {{ now()->format('d/m/Y') }}</div>

    <table>
        <tr><th>Paciente</th><th>Concepto</th><th class="num">Importe</th></tr>
        <tr><td>{{ $paciente }}</td><td>{{ $concepto }}</td><td class="num">${{ number_format($total, 2) }}</td></tr>
    </table>

    <table>
        <tr><th>Fecha</th><th>Forma de pago</th><th class="num">Abono</th></tr>
        @forelse($abonos as $a)
            <tr><td>{{ $a['fecha'] }}</td><td>{{ $a['metodo'] }}</td><td class="num">${{ number_format($a['monto'], 2) }}</td></tr>
        @empty
            <tr><td colspan="3">Sin abonos todavía.</td></tr>
        @endforelse
    </table>

    <table class="totales">
        <tr><td class="num">Pagado a la fecha:</td><td class="num grande" style="width:130px;">${{ number_format($pagado, 2) }}</td></tr>
        <tr><td class="num">Saldo pendiente:</td><td class="num grande">${{ number_format($saldo, 2) }}</td></tr>
    </table>

    <div class="nota">Este recibo es un comprobante de lo pagado. Si necesita factura, pídala en el consultorio.</div>
</div>
</body>
</html>
