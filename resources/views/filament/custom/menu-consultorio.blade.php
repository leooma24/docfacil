{{-- Arriba del menú: en qué consultorio está el doctor. --}}
@php $clinica = auth()->user()?->clinic; @endphp
@if($clinica)
<div class="docfacil-menu-consultorio" style="margin:0.25rem 0 1rem;padding:0.6rem 0.75rem;border-radius:0.7rem;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);">
    <div style="font-size:0.62rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.45);">Consultorio</div>
    <div style="font-size:0.85rem;font-weight:600;color:#ffffff;line-height:1.3;margin-top:2px;">{{ $clinica->name }}</div>
</div>
@endif
