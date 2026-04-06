<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Receta Médica #{{ $prescription->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; }
        .header { border-bottom: 3px solid #14b8a6; padding-bottom: 15px; margin-bottom: 20px; }
        .header-title { font-size: 22px; font-weight: bold; color: #14b8a6; }
        .header-subtitle { font-size: 10px; color: #666; margin-top: 2px; }
        .doctor-info { margin-top: 8px; }
        .doctor-info .name { font-size: 16px; font-weight: bold; color: #111; }
        .doctor-info .detail { font-size: 10px; color: #555; }
        .clinic-info { float: right; text-align: right; font-size: 10px; color: #555; }
        .patient-section { background: #f9fafb; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .patient-section .label { font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
        .patient-section .value { font-size: 13px; font-weight: bold; color: #111; }
        .patient-row { display: flex; margin-bottom: 6px; }
        .diagnosis { margin-bottom: 20px; padding: 10px; border-left: 3px solid #14b8a6; background: #f0fdfa; }
        .diagnosis-label { font-size: 10px; color: #0d9488; text-transform: uppercase; font-weight: bold; }
        .diagnosis-text { font-size: 12px; color: #111; margin-top: 4px; }
        .medications-title { font-size: 14px; font-weight: bold; color: #111; margin-bottom: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }
        .medication { padding: 10px; margin-bottom: 8px; border: 1px solid #e5e7eb; border-radius: 6px; }
        .medication .med-name { font-size: 13px; font-weight: bold; color: #111; }
        .medication .med-detail { font-size: 11px; color: #555; margin-top: 3px; }
        .medication .med-detail span { margin-right: 15px; }
        .medication .med-instructions { font-size: 10px; color: #666; font-style: italic; margin-top: 4px; }
        .notes { margin-top: 20px; padding: 10px; background: #fffbeb; border-radius: 6px; border: 1px solid #fde68a; }
        .notes-label { font-size: 10px; color: #92400e; text-transform: uppercase; font-weight: bold; }
        .signature { margin-top: 60px; text-align: center; }
        .signature-line { border-top: 1px solid #333; width: 250px; margin: 0 auto; padding-top: 5px; }
        .signature .name { font-weight: bold; }
        .signature .detail { font-size: 10px; color: #666; }
        .footer { position: fixed; bottom: 20px; left: 0; right: 0; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        table.patient-table { width: 100%; }
        table.patient-table td { padding: 3px 10px 3px 0; }
    </style>
</head>
<body>
    <div style="padding: 30px;">
        {{-- Header --}}
        <div class="header">
            <div style="overflow: hidden;">
                <div style="float: left;">
                    <div class="header-title">DocFácil</div>
                    <div class="header-subtitle">RECETA MÉDICA</div>
                    <div class="doctor-info">
                        <div class="name">{{ $prescription->doctor->user->name ?? '' }}</div>
                        <div class="detail">{{ $prescription->doctor->specialty ?? '' }}</div>
                        <div class="detail">Céd. Prof. {{ $prescription->doctor->license_number ?? '' }}</div>
                    </div>
                </div>
                @if($prescription->doctor->clinic)
                <div class="clinic-info">
                    <strong>{{ $prescription->doctor->clinic->name }}</strong><br>
                    {{ $prescription->doctor->clinic->address ?? '' }}<br>
                    {{ $prescription->doctor->clinic->city ?? '' }}, {{ $prescription->doctor->clinic->state ?? '' }}<br>
                    Tel: {{ $prescription->doctor->clinic->phone ?? '' }}
                </div>
                @endif
            </div>
        </div>

        {{-- Patient Info --}}
        <div class="patient-section">
            <table class="patient-table">
                <tr>
                    <td>
                        <span class="label">Paciente</span><br>
                        <span class="value">{{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}</span>
                    </td>
                    <td>
                        <span class="label">Fecha</span><br>
                        <span class="value">{{ $prescription->prescription_date->format('d/m/Y') }}</span>
                    </td>
                    <td>
                        <span class="label">Edad</span><br>
                        <span class="value">
                            @if($prescription->patient->birth_date)
                                {{ $prescription->patient->birth_date->age }} años
                            @else
                                -
                            @endif
                        </span>
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
        <div class="medications-title">Rx - Medicamentos</div>
        @foreach($prescription->items as $index => $item)
        <div class="medication">
            <div class="med-name">{{ $index + 1 }}. {{ $item->medication }}</div>
            <div class="med-detail">
                @if($item->dosage)<span><strong>Dosis:</strong> {{ $item->dosage }}</span>@endif
                @if($item->frequency)<span><strong>Frecuencia:</strong> {{ $item->frequency }}</span>@endif
                @if($item->duration)<span><strong>Duración:</strong> {{ $item->duration }}</span>@endif
            </div>
            @if($item->instructions)
            <div class="med-instructions">{{ $item->instructions }}</div>
            @endif
        </div>
        @endforeach

        {{-- Notes --}}
        @if($prescription->notes)
        <div class="notes">
            <div class="notes-label">Indicaciones generales</div>
            <div style="margin-top: 4px;">{{ $prescription->notes }}</div>
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

        {{-- Footer --}}
        <div class="footer">
            Receta generada por DocFácil | {{ now()->format('d/m/Y H:i') }} | Este documento no es válido sin firma del médico
        </div>
    </div>
</body>
</html>
