{{-- Le deben: una tarjeta por paciente. Estilos en línea: los colores de
     Tailwind no existen en el CSS del panel (auditoría del 12-oct-2026). --}}
<x-filament-widgets::widget>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:18px 18px 14px;color:#0f172a;">
        <div style="display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:6px 16px;margin-bottom:14px;">
            <div>
                <div style="font-size:12px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#64748b;">Le deben</div>
                <div style="font-size:26px;font-weight:800;line-height:1.2;">${{ number_format($total, 2) }}</div>
                @if($vencido > 0)
                    <div style="font-size:13px;font-weight:700;color:#b91c1c;">${{ number_format($vencido, 2) }} ya vencido</div>
                @endif
            </div>
            <a href="{{ \App\Filament\Doctor\Resources\PaymentResource::getUrl('index', ['tableFilters' => ['with_balance' => ['isActive' => true]]], panel: 'doctor') }}"
               style="font-size:13px;font-weight:700;color:#0f766e;text-decoration:underline;">Ver todos los cobros</a>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:12px;">
            @foreach($tarjetas as $t)
                @php $rojo = $t['diasVencido'] > 0; @endphp
                <div style="border:1px solid {{ $rojo ? '#fecaca' : '#e5e7eb' }};background:{{ $rojo ? '#fef2f2' : '#f8fafc' }};border-radius:12px;padding:14px;display:flex;flex-direction:column;gap:8px;">
                    <div>
                        <a href="{{ \App\Filament\Doctor\Pages\PatientProfile::getUrl(['patient' => $t['patient_id']], panel: 'doctor') }}"
                           style="font-weight:800;font-size:15px;color:#0f172a;">{{ $t['nombre'] }}</a>
                        <div style="font-size:22px;font-weight:800;color:{{ $rojo ? '#b91c1c' : '#0f172a' }};">${{ number_format($t['debe'], 2) }}</div>
                        <div style="font-size:13px;color:#475569;">
                            @if($rojo)
                                <strong style="color:#b91c1c;">Vencido hace {{ $t['diasVencido'] }} {{ $t['diasVencido'] === 1 ? 'día' : 'días' }}</strong>
                            @else
                                Desde el {{ $t['desde']?->locale('es')->isoFormat('D [de] MMMM') }}
                            @endif
                            · {{ $t['cuantos'] === 1 ? ($t['que'] ?: '1 cobro') : $t['cuantos'] . ' cobros' }}
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:auto;">
                        <div class="le-deben-cobrar" style="flex:1;min-width:110px;">{{ ($this->cobrarAction)(['patient' => $t['patient_id']]) }}</div>
                        @if($t['whatsapp'])
                            <a href="{{ $t['whatsapp'] }}" target="_blank" rel="noopener"
                               style="flex:1;min-width:110px;min-height:40px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid #86efac;background:#fff;color:#166534;font-size:14px;font-weight:700;">Recordarle</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($mas > 0)
            <div style="margin-top:10px;font-size:13px;color:#475569;">Y {{ $mas }} {{ $mas === 1 ? 'paciente más' : 'pacientes más' }} en Cobros.</div>
        @endif
    </div>

    <style>.le-deben-cobrar .fi-btn{width:100%;min-height:40px;}</style>
    <x-filament-actions::modals />
</x-filament-widgets::widget>
