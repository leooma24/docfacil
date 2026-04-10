<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Receta Médica #{{ $prescription->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .page { padding: 30px 40px; }

        /* Header */
        .header-table { width: 100%; margin-bottom: 15px; }
        .header-table td { vertical-align: top; }
        .brand { font-size: 18px; font-weight: bold; color: #14b8a6; }
        .brand-sub { font-size: 8px; color: #999; text-transform: uppercase; letter-spacing: 2px; }
        .doc-name { font-size: 14px; font-weight: bold; color: #111; margin-top: 6px; }
        .doc-detail { font-size: 9px; color: #666; }
        .clinic-name { font-size: 10px; font-weight: bold; color: #333; }
        .clinic-detail { font-size: 9px; color: #777; }
        .header-line { border: none; border-top: 3px solid #14b8a6; margin-bottom: 18px; }

        /* Patient */
        .patient-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .patient-table td { padding: 8px 12px; vertical-align: top; border: 1px solid #e5e7eb; }
        .patient-table .label { font-size: 8px; color: #999; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px; }
        .patient-table .value { font-size: 12px; font-weight: bold; color: #111; }

        /* Diagnosis */
        .diagnosis { margin-bottom: 18px; padding: 8px 12px; border-left: 3px solid #14b8a6; background: #f0fdfa; }
        .section-label { font-size: 9px; color: #0d9488; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; display: block; margin-bottom: 3px; }
        .section-text { font-size: 11px; color: #111; }

        /* Medications */
        .rx-title { font-size: 12px; font-weight: bold; color: #111; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #ddd; }
        .med-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .med-table td { padding: 6px 8px; border: 1px solid #eee; vertical-align: top; font-size: 10px; }
        .med-table .med-num { width: 25px; text-align: center; font-weight: bold; color: #14b8a6; }
        .med-table .med-name { font-weight: bold; font-size: 11px; color: #111; }
        .med-table .med-info { color: #555; }
        .med-table .med-info strong { color: #333; }
        .med-table .med-instr { font-size: 9px; color: #777; font-style: italic; margin-top: 2px; }

        /* Notes */
        .notes { margin-top: 16px; padding: 8px 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 4px; }
        .notes-label { font-size: 9px; color: #92400e; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }
        .notes-text { font-size: 10px; margin-top: 3px; color: #333; }

        /* Signature */
        .signature { margin-top: 50px; text-align: center; }
        .sig-line { border-top: 1px solid #333; width: 200px; margin: 0 auto; padding-top: 4px; }
        .sig-name { font-size: 11px; font-weight: bold; }
        .sig-detail { font-size: 9px; color: #666; }

        /* Footer */
        .footer { position: fixed; bottom: 15px; left: 40px; right: 40px; text-align: center; font-size: 7px; color: #ccc; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="page">

        {{-- Header --}}
        <table class="header-table">
            <tr>
                <td style="width:55%;">
                    <div class="brand">DocFácil</div>
                    <div class="brand-sub">Receta Médica</div>
                    <div class="doc-name">{{ $prescription->doctor->user->name ?? '' }}</div>
                    <div class="doc-detail">{{ $prescription->doctor->specialty ?? '' }}</div>
                    <div class="doc-detail">Céd. Prof. {{ $prescription->doctor->license_number ?? '' }}</div>
                </td>
                <td style="width:45%; text-align:right;">
                    @if($prescription->doctor->clinic)
                    <div class="clinic-name">{{ $prescription->doctor->clinic->name }}</div>
                    <div class="clinic-detail">{{ $prescription->doctor->clinic->address ?? '' }}</div>
                    <div class="clinic-detail">{{ $prescription->doctor->clinic->city ?? '' }}{{ $prescription->doctor->clinic->state ? ', ' . $prescription->doctor->clinic->state : '' }}</div>
                    <div class="clinic-detail">Tel: {{ $prescription->doctor->clinic->phone ?? '' }}</div>
                    @endif
                </td>
            </tr>
        </table>
        <hr class="header-line">

        {{-- Patient --}}
        <table class="patient-table">
            <tr>
                <td style="width:50%;">
                    <span class="label">Paciente</span>
                    <span class="value">{{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}</span>
                </td>
                <td style="width:25%;">
                    <span class="label">Fecha</span>
                    <span class="value">{{ $prescription->prescription_date->format('d/m/Y') }}</span>
                </td>
                <td style="width:25%;">
                    <span class="label">Edad</span>
                    <span class="value">@if($prescription->patient->birth_date){{ $prescription->patient->birth_date->age }} años @else — @endif</span>
                </td>
            </tr>
        </table>

        {{-- Diagnosis --}}
        @if($prescription->diagnosis)
        <div class="diagnosis">
            <span class="section-label">Diagnóstico</span>
            <span class="section-text">{{ $prescription->diagnosis }}</span>
        </div>
        @endif

        {{-- Medications --}}
        @if($prescription->items->count())
        <div class="rx-title">Rx — Medicamentos</div>
        <table class="med-table">
            @foreach($prescription->items as $index => $item)
            <tr>
                <td class="med-num">{{ $index + 1 }}</td>
                <td>
                    <div class="med-name">{{ $item->medication }}</div>
                    <div class="med-info">
                        @if($item->dosage)<strong>Dosis:</strong> {{ $item->dosage }} &nbsp; @endif
                        @if($item->frequency)<strong>Frecuencia:</strong> {{ $item->frequency }} &nbsp; @endif
                        @if($item->duration)<strong>Duración:</strong> {{ $item->duration }}@endif
                    </div>
                    @if($item->instructions)
                    <div class="med-instr">{{ $item->instructions }}</div>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
        @endif

        {{-- Notes --}}
        @if($prescription->notes)
        <div class="notes">
            <div class="notes-label">Indicaciones generales</div>
            <div class="notes-text">{{ $prescription->notes }}</div>
        </div>
        @endif

        {{-- Signature --}}
        <div class="signature">
            <div class="sig-line">
                <div class="sig-name">{{ $prescription->doctor->user->name ?? '' }}</div>
                <div class="sig-detail">{{ $prescription->doctor->specialty ?? '' }}</div>
                <div class="sig-detail">Céd. Prof. {{ $prescription->doctor->license_number ?? '' }}</div>
            </div>
        </div>

    </div>

    <div class="footer">
        Receta generada por DocFácil | docfacil.tu-app.co | {{ now()->format('d/m/Y H:i') }} | Este documento no es válido sin firma del médico
    </div>
</body>
</html>
