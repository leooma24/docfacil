<x-filament-panels::page>
    <div style="display:grid;gap:0.75rem;max-width:760px;">
        @foreach($modulos as $m)
            <div style="display:flex;align-items:center;gap:1rem;padding:1rem 1.1rem;background:#fff;border:1px solid {{ $m['prendido'] && ! $m['desde'] ? '#99f6e4' : '#e5e7eb' }};border-radius:12px;">
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;color:#0f172a;">{{ $m['nombre'] }}</div>
                    <div style="font-size:0.85rem;color:#64748b;margin-top:2px;">{{ $m['que_es'] }}</div>
                    @if($m['desde'])
                        <div style="font-size:0.8rem;color:#b45309;font-weight:600;margin-top:4px;">{{ $m['desde'] }} · <a href="{{ \App\Filament\Doctor\Pages\Upgrade::getUrl() }}" style="text-decoration:underline;">Ver planes</a></div>
                    @endif
                </div>
                <button type="button" wire:click="alternar('{{ $m['clave'] }}')" role="switch" aria-checked="{{ $m['prendido'] ? 'true' : 'false' }}"
                        aria-label="{{ $m['prendido'] ? 'Apagar' : 'Prender' }} {{ $m['nombre'] }}"
                        style="flex:none;width:48px;height:28px;border-radius:999px;border:none;cursor:pointer;position:relative;background:{{ $m['prendido'] ? '#0d9488' : '#cbd5e1' }};">
                    <span style="position:absolute;top:3px;{{ $m['prendido'] ? 'right:3px' : 'left:3px' }};width:22px;height:22px;border-radius:999px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);"></span>
                </button>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
