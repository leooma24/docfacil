{{-- Lo último en cada diente y lo que falta: lo que el doctor buscaba en la
     hoja de papel entre paciente y paciente (entrevista del 10-oct-2026). --}}
@php $dientes = $this->appointment ? \App\Support\LoUltimoDelDiente::paraLaCita($this->appointment) : collect(); @endphp
@if($dientes->isNotEmpty())
<div style="border:1px solid #bae6fd;background:#f0f9ff;border-radius:12px;padding:10px 14px;margin-bottom:12px;">
    <div style="font-size:12px;font-weight:800;color:#0369a1;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">Lo último en cada diente</div>
    @foreach($dientes as $d)
    <div style="display:flex;gap:10px;align-items:flex-start;padding:5px 0;{{ $loop->last ? '' : 'border-bottom:1px solid #e0f2fe;' }}">
        <span style="flex-shrink:0;min-width:34px;text-align:center;font-weight:800;font-size:13px;color:#fff;background:#0284c7;border-radius:8px;padding:2px 6px;">{{ $d['diente'] }}</span>
        <div style="font-size:13px;color:#0c4a6e;line-height:1.4;">
            @if($d['hecho'])
                <strong>{{ $d['hecho'] }}</strong> · {{ $d['cuando']->locale('es')->isoFormat('D MMM') }}
                @if($d['nota'])<div style="color:#475569;">{{ $d['nota'] }}</div>@endif
            @endif
            @if($d['falta'])
                <div style="color:#b45309;font-weight:700;">Falta: {{ implode(', ', $d['falta']) }}</div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif
