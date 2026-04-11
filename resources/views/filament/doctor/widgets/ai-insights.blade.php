<x-filament-widgets::widget>
    @php $insights = $this->getInsights(); @endphp
    <div style="background:linear-gradient(135deg,#ecfeff 0%,#f0fdfa 100%);border:1.5px solid #5eead4;border-radius:16px;padding:20px;position:relative;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#0d9488,#0891b2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(13,148,136,0.3);">
                    <svg style="width:20px;height:20px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <div>
                    <div style="font-size:11px;color:#0f766e;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Asesor IA</div>
                    <div style="font-size:16px;font-weight:800;color:#064e3b;">Análisis de tu consultorio</div>
                </div>
            </div>
            <button wire:click="refresh" wire:loading.attr="disabled" style="display:inline-flex;align-items:center;gap:4px;padding:8px 12px;background:white;border:1px solid #99f6e4;border-radius:10px;font-size:11px;color:#0f766e;cursor:pointer;font-weight:600;">
                <svg wire:loading.remove wire:target="refresh" style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <svg wire:loading wire:target="refresh" style="width:12px;height:12px;animation:spin 1s linear infinite;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Actualizar
            </button>
        </div>

        @if($insights && !empty($insights['summary']))
        <p style="font-size:13px;color:#1f2937;line-height:1.6;margin-bottom:14px;padding:10px 14px;background:white;border-radius:10px;border-left:3px solid #0d9488;">{{ $insights['summary'] }}</p>
        @endif

        @if($insights && !empty($insights['insights']))
        <div style="display:grid;grid-template-columns:1fr;gap:8px;">
            @foreach($insights['insights'] as $item)
            @php
                $colors = [
                    'success' => ['bg' => '#f0fdf4', 'border' => '#86efac', 'text' => '#166534', 'badge' => '#22c55e'],
                    'warning' => ['bg' => '#fffbeb', 'border' => '#fcd34d', 'text' => '#92400e', 'badge' => '#f59e0b'],
                    'opportunity' => ['bg' => '#eff6ff', 'border' => '#93c5fd', 'text' => '#1e40af', 'badge' => '#3b82f6'],
                ];
                $c = $colors[$item['type']] ?? $colors['opportunity'];
            @endphp
            <div style="background:{{ $c['bg'] }};border:1px solid {{ $c['border'] }};border-radius:10px;padding:10px 14px;display:flex;align-items:flex-start;gap:10px;">
                <div style="font-size:20px;line-height:1;flex-shrink:0;">{{ $item['icon'] }}</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;color:{{ $c['text'] }};font-size:13px;margin-bottom:2px;">{{ $item['title'] }}</div>
                    <div style="font-size:12px;color:#374151;line-height:1.5;">{{ $item['message'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="padding:20px;text-align:center;color:#64748b;font-size:12px;">
            <div wire:loading wire:target="refresh">Analizando datos de tu consultorio...</div>
            <div wire:loading.remove wire:target="refresh">
                No hay insights disponibles. Registra más actividad en el consultorio para ver análisis.
            </div>
        </div>
        @endif
    </div>
    <style>
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>
</x-filament-widgets::widget>
