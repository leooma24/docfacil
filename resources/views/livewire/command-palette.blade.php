<div>
    <style>
        .cmdk-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(12px) saturate(180%); z-index: 9999; display: flex; align-items: flex-start; justify-content: center; padding-top: 15vh; }
        .cmdk-panel { width: 100%; max-width: 640px; background: rgba(255,255,255,0.92); backdrop-filter: blur(30px) saturate(180%); border: 1px solid rgba(255,255,255,0.6); border-radius: 20px; box-shadow: 0 25px 60px -15px rgba(13,148,136,0.35), 0 0 0 1px rgba(13,148,136,0.1); overflow: hidden; display: flex; flex-direction: column; max-height: 70vh; animation: cmdkIn 0.2s cubic-bezier(0.4,0,0.2,1); }
        @keyframes cmdkIn { from { opacity: 0; transform: translateY(-10px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .cmdk-header { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 12px; }
        .cmdk-header svg { width: 20px; height: 20px; color: #9ca3af; flex-shrink: 0; }
        .cmdk-input { flex: 1; border: none; outline: none; font-size: 16px; background: transparent; color: #111; }
        .cmdk-input::placeholder { color: #9ca3af; }
        .cmdk-kbd { padding: 2px 8px; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 11px; color: #6b7280; font-family: 'SF Mono', Monaco, 'Courier New', monospace; font-weight: 600; }
        .cmdk-body { overflow-y: auto; flex: 1; padding: 8px; }
        .cmdk-section { padding: 4px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; font-weight: 700; margin-top: 8px; }
        .cmdk-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 10px; cursor: pointer; transition: all 0.1s; text-decoration: none; color: #111; }
        .cmdk-item:hover, .cmdk-item.selected { background: #f0fdfa; }
        .cmdk-item-icon { font-size: 22px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #f3f4f6; border-radius: 8px; flex-shrink: 0; }
        .cmdk-item.selected .cmdk-item-icon { background: #ccfbf1; }
        .cmdk-item-text { flex: 1; min-width: 0; }
        .cmdk-item-title { font-weight: 600; font-size: 14px; color: #111; }
        .cmdk-item-subtitle { font-size: 12px; color: #6b7280; }
        .cmdk-item-arrow { color: #9ca3af; }
        .cmdk-footer { padding: 10px 16px; border-top: 1px solid #e5e7eb; background: #f9fafb; display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: #6b7280; }
        .cmdk-footer kbd { display: inline-block; padding: 1px 6px; background: white; border: 1px solid #d1d5db; border-radius: 4px; font-family: monospace; font-size: 10px; margin: 0 2px; }
        .cmdk-ai-btn { background: linear-gradient(135deg, #0d9488, #0891b2); color: white; border: none; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; }
        .cmdk-ai-answer { padding: 14px 16px; background: linear-gradient(135deg, #ecfeff, #f0fdfa); border: 1px solid #5eead4; border-radius: 12px; margin: 8px; font-size: 14px; line-height: 1.6; color: #0f766e; }
        .cmdk-ai-label { font-size: 11px; font-weight: 700; color: #0d9488; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px; }
        .cmdk-empty { padding: 40px 20px; text-align: center; color: #9ca3af; font-size: 13px; }
    </style>

    @if($open)
    <div class="cmdk-backdrop" wire:click.self="close" x-data="{}" @keydown.escape.window="$wire.close()">
        <div class="cmdk-panel">
            <div class="cmdk-header">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input
                    type="text"
                    wire:model.live.debounce.250ms="query"
                    class="cmdk-input"
                    placeholder="Busca pacientes, acciones, o pregúntale algo a la IA..."
                    autofocus
                    x-init="$nextTick(() => $el.focus())"
                    @keydown.enter="$wire.query && $wire.query.length > 5 ? $wire.askAi() : null"
                >
                <span class="cmdk-kbd">ESC</span>
            </div>

            <div class="cmdk-body">
                @if($askingAi)
                <div class="cmdk-ai-answer">
                    <div class="cmdk-ai-label">💭 Pensando...</div>
                    <div>La IA está analizando tu pregunta</div>
                </div>
                @elseif($aiAnswer)
                <div class="cmdk-ai-answer">
                    <div class="cmdk-ai-label">✨ Respuesta IA</div>
                    <div>{{ $aiAnswer }}</div>
                </div>
                @endif

                @if(empty($query))
                <div class="cmdk-section">Acciones rápidas</div>
                @elseif(count($results) > 0)
                <div class="cmdk-section">Resultados</div>
                @endif

                @forelse($results as $i => $result)
                <a href="{{ $result['url'] }}" class="cmdk-item {{ $selectedIndex === $i ? 'selected' : '' }}">
                    <div class="cmdk-item-icon">{{ $result['icon'] }}</div>
                    <div class="cmdk-item-text">
                        <div class="cmdk-item-title">{{ $result['title'] }}</div>
                        <div class="cmdk-item-subtitle">{{ $result['subtitle'] }}</div>
                    </div>
                    <svg class="cmdk-item-arrow" style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @empty
                @if(!empty($query))
                <div class="cmdk-empty">
                    <div style="font-size:32px;margin-bottom:8px;">🤔</div>
                    <div>No se encontraron resultados para "<strong>{{ $query }}</strong>"</div>
                    <button wire:click="askAi" class="cmdk-ai-btn" style="margin-top:12px;">
                        ✨ Preguntarle a la IA
                    </button>
                </div>
                @endif
                @endforelse
            </div>

            <div class="cmdk-footer">
                <div>
                    <kbd>↑</kbd><kbd>↓</kbd> navegar &nbsp;
                    <kbd>↵</kbd> abrir &nbsp;
                    <kbd>ESC</kbd> cerrar
                </div>
                <div>
                    @if(!empty($query) && !$aiAnswer && !$askingAi)
                    <button wire:click="askAi" class="cmdk-ai-btn">
                        ✨ Preguntar a IA
                    </button>
                    @else
                    <span>Powered by IA 🤖</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                Livewire.dispatch('openCommandPalette');
            }
        });
    </script>
</div>
