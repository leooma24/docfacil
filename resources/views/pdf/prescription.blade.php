<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Receta Médica #{{ $prescription->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.4; }

        .page { padding: 25px 35px; }

        /* Header */
        .header { border-bottom: 3px solid #14b8a6; padding-bottom: 12px; margin-bottom: 18px; overflow: hidden; }
        .header-left { float: left; width: 55%; }
        .header-right { float: right; width: 42%; text-align: right; }
        .header-title { font-size: 20px; font-weight: bold; color: #14b8a6; }
        .header-subtitle { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: 2px; margin-top: 1px; }
        .doctor-name { font-size: 15px; font-weight: bold; color: #111; margin-top: 8px; }
        .doctor-detail { font-size: 9px; color: #666; margin-top: 1px; }
        .clinic-name { font-size: 11px; font-weight: bold; color: #333; }
        .clinic-detail { font-size: 9px; color: #666; margin-top: 1px; }

        /* Patient */
        .patient-box { background: #f8fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; }
        .patient-table { width: 100%; border-collapse: collapse; }
        .patient-table td { vertical-align: top; padding: 2px 0; }
        .patient-table .label { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
        .patient-table .value { font-size: 13px; font-weight: bold; color: #111; }
        .col-patient { width: 50%; }
        .col-date { width: 25%; }
        .col-age { width: 25%; }

        /* Diagnosis */
        .diagnosis { margin-bottom: 16px; padding: 8px 12px; border-left: 3px solid #14b8a6; background: #f0fdfa; }
        .diagnosis-label { font-size: 9px; color: #0d9488; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }
        .diagnosis-text { font-size: 12px; color: #111; margin-top: 3px; }

        /* Medications */
        .rx-title { font-size: 13px; font-weight: bold; color: #111; margin-bottom: 8px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .med-item { padding: 8px 10px; margin-bottom: 6px; border: 1px solid #e5e7eb; border-radius: 5px; }
        .med-name { font-size: 13px; font-weight: bold; color: #111; }
        .med-details { font-size: 10px; color: #555; margin-top: 3px; }
        .med-details span { margin-right: 12px; }
        .med-instructions { font-size: 9px; color: #777; font-style: italic; margin-top: 3px; }

        /* Notes */
        .notes { margin-top: 16px; padding: 8px 12px; background: #fffbeb; border-radius: 5px; border: 1px solid #fde68a; }
        .notes-label { font-size: 9px; color: #92400e; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }
        .notes-text { font-size: 11px; margin-top: 3px; color: #333; }

        /* Signature */
        .signature { margin-top: 50px; text-align: center; }
        .signature-line { border-top: 1px solid #333; width: 220px; margin: 0 auto; padding-top: 5px; }
        .signature .name { font-size: 12px; font-weight: bold; }
        .signature .detail { font-size: 9px; color: #666; }

        /* Footer */
        .footer { position: fixed; bottom: 15px; left: 35px; right: 35px; text-align: center; font-size: 8px; color: #bbb; border-top: 1px solid #eee; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="page">
        {{-- Header --}}
        <div class="header">
            <div class="header-left">
                <div class="header-title">DocFácil</div>
                <div class="header-subtitle">Receta Médica</div>
                <div class="doctor-name">{{ $prescription->doctor->user->name ?? '' }}</div>
                <div class="doctor-detail">{{ $prescription->doctor->specialty ?? '' }}</div>
                <div class="doctor-detail">Céd. Prof. {{ $prescription->doctor->license_number ?? '' }}</div>
            </div>
            @if($prescription->doctor->clinic)
            <div class="header-right">
                <div class="clinic-name">{{ $prescription->doctor->clinic->name }}</div>
                <div class="clinic-detail">{{ $prescription->doctor->clinic->address ?? '' }}</div>
                <div class="clinic-detail">{{ $prescription->doctor->clinic->city ?? '' }}{{ $prescription->doctor->clinic->state ? ', ' . $prescription->doctor->clinic->state : '' }}</div>
                <div class="clinic-detail">Tel: {{ $prescription->doctor->clinic->phone ?? '' }}</div>
            </div>
            @endif
        </div>

        {{-- Patient --}}
        <div class="patient-box">
            <table class="patient-table">
                <tr>
                    <td class="col-patient">
                        <div class="label">Paciente</div>
                        <div class="value">{{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}</div>
                    </td>
                    <td class="col-date">
                        <div class="label">Fecha</div>
                        <div class="value">{{ $prescription->prescription_date->format('d/m/Y') }}</div>
                    </td>
                    <td class="col-age">
                        <div class="label">Edad</div>
                        <div class="value">
                            @if($prescription->patient->birth_date)
                                {{ $prescription->patient->birth_date->age }} años
                            @else
                                —
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Diagnosis --}}
        @if($prescription->diagnosis)
        <div class="diagnosis">
            <div class="diagnosis-label">Diagnóstico</div>
            <div class="diagnosis-text">{{ $prescription->diagnosis }}</div>
        </div>
        @endif

        {{-- Medications --}}
        @if($prescription->items->count())
        <div class="rx-title">Rx — Medicamentos</div>
        @foreach($prescription->items as $index => $item)
        <div class="med-item">
            <div class="med-name">{{ $index + 1 }}. {{ $item->medication }}</div>
            <div class="med-details">
                @if($item->dosage)<span><strong>Dosis:</strong> {{ $item->dosage }}</span>@endif
                @if($item->frequency)<span><strong>Frecuencia:</strong> {{ $item->frequency }}</span>@endif
                @if($item->duration)<span><strong>Duración:</strong> {{ $item->duration }}</span>@endif
            </div>
            @if($item->instructions)
            <div class="med-instructions">{{ $item->instructions }}</div>
            @endif
        </div>
        @endforeach
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
            <div class="signature-line">
                <div class="name">{{ $prescription->doctor->user->name ?? '' }}</div>
                <div class="detail">{{ $prescription->doctor->specialty ?? '' }}</div>
                <div class="detail">Céd. Prof. {{ $prescription->doctor->license_number ?? '' }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        Receta generada por DocFácil | docfacil.tu-app.co | {{ now()->format('d/m/Y H:i') }} | Este documento no es válido sin firma del médico
    </div>
</body>
</html>
