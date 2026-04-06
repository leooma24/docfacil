<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Consentimiento Informado #{{ $consent->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; }
        .header { border-bottom: 3px solid #14b8a6; padding-bottom: 15px; margin-bottom: 20px; }
        .header-title { font-size: 22px; font-weight: bold; color: #14b8a6; }
        .header-subtitle { font-size: 10px; color: #666; margin-top: 2px; }
        .doctor-info .name { font-size: 14px; font-weight: bold; color: #111; margin-top: 8px; }
        .doctor-info .detail { font-size: 10px; color: #555; }
        .clinic-info { float: right; text-align: right; font-size: 10px; color: #555; }
        .title { font-size: 18px; font-weight: bold; text-align: center; margin: 20px 0; color: #111; text-transform: uppercase; }
        .patient-box { background: #f9fafb; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .patient-box .label { font-size: 10px; color: #666; text-transform: uppercase; }
        .patient-box .value { font-size: 13px; font-weight: bold; color: #111; }
        .procedure-box { background: #f0fdfa; border-left: 3px solid #14b8a6; padding: 10px; margin-bottom: 20px; }
        .procedure-label { font-size: 10px; color: #0d9488; text-transform: uppercase; font-weight: bold; }
        .content { margin: 20px 0; line-height: 1.8; font-size: 11px; }
        .content ul { margin-left: 20px; }
        .content li { margin-bottom: 5px; }
        .risks-box { margin: 15px 0; padding: 10px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; }
        .risks-label { font-size: 10px; color: #dc2626; text-transform: uppercase; font-weight: bold; }
        .alternatives-box { margin: 15px 0; padding: 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; }
        .alternatives-label { font-size: 10px; color: #2563eb; text-transform: uppercase; font-weight: bold; }
        .signatures { margin-top: 60px; }
        .sig-row { overflow: hidden; }
        .sig-col { float: left; width: 45%; text-align: center; }
        .sig-col + .sig-col { float: right; }
        .sig-line { border-top: 1px solid #333; margin-top: 60px; padding-top: 5px; }
        .sig-name { font-weight: bold; font-size: 12px; }
        .sig-detail { font-size: 10px; color: #666; }
        .footer { position: fixed; bottom: 20px; left: 0; right: 0; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .signed-badge { display: inline-block; background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 4px; font-size: 10px; font-weight: bold; margin-top: 10px; }
        table.info-table { width: 100%; }
        table.info-table td { padding: 3px 10px 3px 0; }
    </style>
</head>
<body>
    <div style="padding: 30px;">
        {{-- Header --}}
        <div class="header">
            <div style="overflow: hidden;">
                <div style="float: left;">
                    <div class="header-title">DocFácil</div>
                    <div class="header-subtitle">CONSENTIMIENTO INFORMADO</div>
                    <div class="doctor-info">
                        <div class="name">{{ $consent->doctor->user->name ?? '' }}</div>
                        <div class="detail">{{ $consent->doctor->specialty ?? '' }}</div>
                        <div class="detail">Céd. Prof. {{ $consent->doctor->license_number ?? '' }}</div>
                    </div>
                </div>
                @if($consent->doctor->clinic)
                <div class="clinic-info">
                    <strong>{{ $consent->doctor->clinic->name }}</strong><br>
                    {{ $consent->doctor->clinic->address ?? '' }}<br>
                    {{ $consent->doctor->clinic->city ?? '' }}, {{ $consent->doctor->clinic->state ?? '' }}<br>
                    Tel: {{ $consent->doctor->clinic->phone ?? '' }}
                </div>
                @endif
            </div>
        </div>

        {{-- Title --}}
        <div class="title">{{ $consent->title }}</div>

        {{-- Patient Info --}}
        <div class="patient-box">
            <table class="info-table">
                <tr>
                    <td>
                        <span class="label">Paciente</span><br>
                        <span class="value">{{ $consent->patient->first_name }} {{ $consent->patient->last_name }}</span>
                    </td>
                    <td>
                        <span class="label">Fecha</span><br>
                        <span class="value">{{ $consent->created_at->format('d/m/Y') }}</span>
                    </td>
                    <td>
                        <span class="label">Edad</span><br>
                        <span class="value">
                            @if($consent->patient->birth_date)
                                {{ $consent->patient->birth_date->age }} años
                            @else
                                -
                            @endif
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Procedure --}}
        @if($consent->procedure_name)
        <div class="procedure-box">
            <div class="procedure-label">Procedimiento</div>
            <div style="margin-top: 4px; font-size: 13px; font-weight: bold;">{{ $consent->procedure_name }}</div>
        </div>
        @endif

        {{-- Content --}}
        <div class="content">
            {!! strip_tags($consent->content, '<p><br><ul><ol><li><strong><em><u><h1><h2><h3><h4><table><tr><td><th>') !!}
        </div>

        {{-- Risks --}}
        @if($consent->risks)
        <div class="risks-box">
            <div class="risks-label">Riesgos del procedimiento</div>
            <div style="margin-top: 4px;">{{ $consent->risks }}</div>
        </div>
        @endif

        {{-- Alternatives --}}
        @if($consent->alternatives)
        <div class="alternatives-box">
            <div class="alternatives-label">Alternativas al tratamiento</div>
            <div style="margin-top: 4px;">{{ $consent->alternatives }}</div>
        </div>
        @endif

        {{-- Signed badge --}}
        @if($consent->signed_at)
        <div style="text-align: center; margin: 20px 0;">
            <span class="signed-badge">FIRMADO DIGITALMENTE — {{ $consent->signed_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif

        {{-- Signatures --}}
        <div class="signatures">
            <div class="sig-row">
                <div class="sig-col">
                    @if($consent->signature)
                    <img src="{{ storage_path('app/public/' . $consent->signature) }}" style="max-height: 50px; margin-bottom: 5px;">
                    @endif
                    <div class="sig-line">
                        <div class="sig-name">{{ $consent->patient->first_name }} {{ $consent->patient->last_name }}</div>
                        <div class="sig-detail">Paciente</div>
                    </div>
                </div>
                <div class="sig-col">
                    <div class="sig-line">
                        <div class="sig-name">{{ $consent->doctor->user->name ?? '' }}</div>
                        <div class="sig-detail">{{ $consent->doctor->specialty ?? 'Médico tratante' }}</div>
                        <div class="sig-detail">Céd. Prof. {{ $consent->doctor->license_number ?? '' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            Documento generado por DocFácil | docfacil.tu-app.co | {{ now()->format('d/m/Y H:i') }} | Folio: CI-{{ str_pad($consent->id, 6, '0', STR_PAD_LEFT) }}
        </div>
    </div>
</body>
</html>
