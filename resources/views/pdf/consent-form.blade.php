<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Consentimiento Informado #{{ $consent->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .page { padding: 25px 35px; }

        /* Header */
        .header-table { width: 100%; margin-bottom: 12px; }
        .header-table td { vertical-align: top; }
        .brand { font-size: 18px; font-weight: bold; color: #14b8a6; }
        .brand-sub { font-size: 8px; color: #999; text-transform: uppercase; letter-spacing: 2px; }
        .doc-name { font-size: 14px; font-weight: bold; color: #111; margin-top: 6px; }
        .doc-detail { font-size: 9px; color: #666; }
        .clinic-name { font-size: 10px; font-weight: bold; color: #333; }
        .clinic-detail { font-size: 9px; color: #777; }
        .header-line { border: none; border-top: 3px solid #14b8a6; margin-bottom: 15px; }

        /* Title */
        .title { font-size: 16px; font-weight: bold; text-align: center; margin: 15px 0; color: #111; text-transform: uppercase; }

        /* Patient */
        .patient-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .patient-table td { padding: 7px 10px; vertical-align: top; border: 1px solid #e5e7eb; }
        .patient-table .label { font-size: 8px; color: #999; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px; }
        .patient-table .value { font-size: 12px; font-weight: bold; color: #111; }

        /* Procedure */
        .procedure-box { background: #f0fdfa; border-left: 3px solid #14b8a6; padding: 8px 12px; margin-bottom: 15px; }
        .section-label { font-size: 9px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; display: block; margin-bottom: 3px; }
        .procedure-label { color: #0d9488; }
        .procedure-value { font-size: 12px; font-weight: bold; color: #111; }

        /* Content */
        .content { margin: 15px 0; line-height: 1.7; font-size: 11px; }
        .content ul, .content ol { margin-left: 18px; }
        .content li { margin-bottom: 4px; }

        /* Risks */
        .risks-box { margin: 12px 0; padding: 8px 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 4px; }
        .risks-label { color: #dc2626; }

        /* Alternatives */
        .alt-box { margin: 12px 0; padding: 8px 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; }
        .alt-label { color: #2563eb; }

        .section-text { font-size: 11px; color: #333; margin-top: 3px; }

        /* Signed badge */
        .signed-badge { display: inline-block; background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 4px; font-size: 10px; font-weight: bold; }

        /* Signatures */
        .sig-table { width: 100%; margin-top: 40px; }
        .sig-table td { width: 45%; text-align: center; vertical-align: bottom; padding: 0 15px; }
        .sig-table td.spacer { width: 10%; }
        .sig-line { border-top: 1px solid #333; padding-top: 5px; }
        .sig-name { font-weight: bold; font-size: 11px; }
        .sig-detail { font-size: 9px; color: #666; }
        .sig-img { max-height: 70px; margin-bottom: 5px; }

        /* Footer */
        .footer { position: fixed; bottom: 15px; left: 35px; right: 35px; text-align: center; font-size: 7px; color: #ccc; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="page">

        {{-- Header --}}
        <table class="header-table">
            <tr>
                <td style="width:55%;">
                    <div class="brand">DocFácil</div>
                    <div class="brand-sub">Consentimiento Informado</div>
                    <div class="doc-name">{{ $consent->doctor->user->name ?? '' }}</div>
                    <div class="doc-detail">{{ $consent->doctor->specialty ?? '' }}</div>
                    <div class="doc-detail">Céd. Prof. {{ $consent->doctor->license_number ?? '' }}</div>
                </td>
                <td style="width:45%; text-align:right;">
                    @if($consent->doctor->clinic)
                    <div class="clinic-name">{{ $consent->doctor->clinic->name }}</div>
                    <div class="clinic-detail">{{ $consent->doctor->clinic->address ?? '' }}</div>
                    <div class="clinic-detail">{{ $consent->doctor->clinic->city ?? '' }}{{ $consent->doctor->clinic->state ? ', ' . $consent->doctor->clinic->state : '' }}</div>
                    <div class="clinic-detail">Tel: {{ $consent->doctor->clinic->phone ?? '' }}</div>
                    @endif
                </td>
            </tr>
        </table>
        <hr class="header-line">

        {{-- Title --}}
        <div class="title">{{ $consent->title }}</div>

        {{-- Patient --}}
        <table class="patient-table">
            <tr>
                <td style="width:50%;">
                    <span class="label">Paciente</span>
                    <span class="value">{{ $consent->patient->first_name }} {{ $consent->patient->last_name }}</span>
                </td>
                <td style="width:25%;">
                    <span class="label">Fecha</span>
                    <span class="value">{{ $consent->created_at->format('d/m/Y') }}</span>
                </td>
                <td style="width:25%;">
                    <span class="label">Edad</span>
                    <span class="value">@if($consent->patient->birth_date){{ $consent->patient->birth_date->age }} años @else — @endif</span>
                </td>
            </tr>
        </table>

        {{-- Procedure --}}
        @if($consent->procedure_name)
        <div class="procedure-box">
            <span class="section-label procedure-label">Procedimiento</span>
            <span class="procedure-value">{{ $consent->procedure_name }}</span>
        </div>
        @endif

        {{-- Content --}}
        <div class="content">
            {!! strip_tags($consent->content, '<p><br><ul><ol><li><strong><em><u><h1><h2><h3><h4><table><tr><td><th>') !!}
        </div>

        {{-- Risks --}}
        @if($consent->risks)
        <div class="risks-box">
            <span class="section-label risks-label">Riesgos del procedimiento</span>
            <div class="section-text">{{ $consent->risks }}</div>
        </div>
        @endif

        {{-- Alternatives --}}
        @if($consent->alternatives)
        <div class="alt-box">
            <span class="section-label alt-label">Alternativas al tratamiento</span>
            <div class="section-text">{{ $consent->alternatives }}</div>
        </div>
        @endif

        {{-- Signed badge --}}
        @if($consent->signed_at)
        <div style="text-align: center; margin: 18px 0;">
            <span class="signed-badge">FIRMADO DIGITALMENTE — {{ $consent->signed_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif

        {{-- Signatures --}}
        <table class="sig-table">
            <tr>
                <td>
                    @if($consent->signature)
                        @if(str_starts_with($consent->signature, 'data:image'))
                        <img src="{{ $consent->signature }}" class="sig-img">
                        @else
                        <img src="{{ storage_path('app/public/' . $consent->signature) }}" class="sig-img">
                        @endif
                    @endif
                    <div class="sig-line">
                        <div class="sig-name">{{ $consent->patient->first_name }} {{ $consent->patient->last_name }}</div>
                        <div class="sig-detail">Paciente</div>
                    </div>
                </td>
                <td class="spacer"></td>
                <td>
                    <div class="sig-line" style="margin-top: 75px;">
                        <div class="sig-name">{{ $consent->doctor->user->name ?? '' }}</div>
                        <div class="sig-detail">{{ $consent->doctor->specialty ?? 'Médico tratante' }}</div>
                        <div class="sig-detail">Céd. Prof. {{ $consent->doctor->license_number ?? '' }}</div>
                    </div>
                </td>
            </tr>
        </table>

    </div>

    <div class="footer">
        Documento generado por DocFácil | docfacil.tu-app.co | {{ now()->format('d/m/Y H:i') }} | Folio: CI-{{ str_pad($consent->id, 6, '0', STR_PAD_LEFT) }}
    </div>
</body>
</html>
