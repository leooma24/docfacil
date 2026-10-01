{{-- Lo que el doctor tiene que tener a la vista antes de tratar y recetar:
     alergias (o que nadie ha preguntado), anticoagulantes y antecedentes
     anotados en sus notas médicas. --}}
@php
    $pac = $this->appointment?->patient;
    $antecedentes = \App\Support\AlertasClinicas::antecedentes($pac?->medical_notes);
    $anticoagulado = \App\Support\AlertasClinicas::tomaAnticoagulantes($pac?->medical_notes);
@endphp
@if($pac)
    @if($this->isFieldEnabled('allergies_alert'))
        @if($pac->tieneAlergias())
            <div style="background:#fef2f2;border-left:4px solid #ef4444;padding:10px 14px;border-radius:8px;margin-bottom:12px;display:flex;align-items:center;gap:10px;">
                <span style="font-size:18px;">⚠️</span>
                <div style="font-size:13px;color:#991b1b;"><strong>Alergias:</strong> {{ $pac->allergies }}</div>
            </div>
        @elseif(blank($pac->allergies))
            <div style="background:#fffbeb;border-left:4px solid #f59e0b;padding:10px 14px;border-radius:8px;margin-bottom:12px;">
                <div style="font-size:13px;color:#92400e;margin-bottom:8px;"><strong>Alergias no registradas.</strong> Pregúntele antes de recetar y anótelas aquí; quedan en su expediente.</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <input type="text" wire:model="alergiasNuevas" placeholder="Ej. Penicilina, látex" style="flex:1;min-width:180px;padding:7px 10px;border:1px solid #fcd34d;border-radius:8px;font-size:13px;">
                    <button type="button" wire:click="guardarAlergias" style="padding:7px 12px;border-radius:8px;background:#b45309;color:#fff;font-size:12px;font-weight:700;">Guardar</button>
                    <button type="button" wire:click="sinAlergias" style="padding:7px 12px;border-radius:8px;background:#fff;color:#92400e;border:1px solid #fcd34d;font-size:12px;font-weight:700;">No tiene</button>
                </div>
            </div>
        @endif
    @endif
    @if($this->isFieldEnabled('anticoagulants_alert') && $anticoagulado)
        <div style="background:#fff7ed;border-left:4px solid #f97316;padding:10px 14px;border-radius:8px;margin-bottom:12px;display:flex;align-items:center;gap:10px;">
            <span style="font-size:18px;">🩸</span>
            <div style="font-size:13px;color:#9a3412;"><strong>Toma anticoagulantes</strong> (según sus notas): precaución con procedimientos invasivos y antiinflamatorios.</div>
        </div>
    @endif
    @if($antecedentes)
        <div style="background:#f8fafc;border-left:4px solid #64748b;padding:8px 14px;border-radius:8px;margin-bottom:12px;font-size:13px;color:#334155;">
            <strong>Antecedentes en sus notas:</strong> {{ implode(' · ', $antecedentes) }}
        </div>
    @endif
@endif
