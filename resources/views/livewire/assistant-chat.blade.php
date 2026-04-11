<div>
    <style>
        .chat-fab { position: fixed; bottom: 24px; right: 24px; z-index: 9999; width: 62px; height: 62px; border-radius: 50%; background: linear-gradient(135deg, #0d9488, #0891b2 50%, #7c3aed); border: none; cursor: pointer; box-shadow: 0 12px 35px rgba(13,148,136,0.45), inset 0 1px 0 rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }
        .chat-fab:hover { transform: scale(1.12) translateY(-2px); box-shadow: 0 16px 40px rgba(13,148,136,0.6), inset 0 1px 0 rgba(255,255,255,0.4); }
        .chat-fab svg { width: 28px; height: 28px; color: white; }
        .chat-fab .badge-ai { position: absolute; top: -4px; right: -4px; background: #fbbf24; color: #78350f; font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 999px; border: 2px solid white; }

        .chat-panel { position: fixed; bottom: 100px; right: 24px; z-index: 9998; width: 380px; max-width: calc(100vw - 32px); height: 560px; max-height: calc(100vh - 140px); background: rgba(255,255,255,0.96); backdrop-filter: blur(30px) saturate(180%); border: 1px solid rgba(13,148,136,0.15); border-radius: 24px; box-shadow: 0 25px 60px -10px rgba(13,148,136,0.35), 0 0 0 1px rgba(255,255,255,0.8); display: flex; flex-direction: column; overflow: hidden; animation: chatIn 0.25s cubic-bezier(0.4,0,0.2,1); }
        @keyframes chatIn { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .chat-header { padding: 18px 20px; background: linear-gradient(135deg, #0d9488 0%, #0891b2 50%, #7c3aed 100%); color: white; display: flex; align-items: center; justify-content: space-between; position: relative; overflow: hidden; }
        .chat-header::before { content: ''; position: absolute; top: -50%; right: -20%; width: 120px; height: 120px; background: radial-gradient(circle, rgba(255,255,255,0.15), transparent 70%); border-radius: 50%; }
        .chat-header-left { display: flex; align-items: center; gap: 10px; }
        .chat-avatar { width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .chat-title { font-weight: 800; font-size: 14px; }
        .chat-sub { font-size: 10px; opacity: 0.85; }
        .chat-close { background: rgba(255,255,255,0.15); border: none; color: white; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; }

        .chat-body { flex: 1; overflow-y: auto; padding: 16px; background: #f9fafb; display: flex; flex-direction: column; gap: 10px; }

        .msg { max-width: 85%; padding: 10px 14px; border-radius: 14px; font-size: 13px; line-height: 1.5; white-space: pre-wrap; word-wrap: break-word; }
        .msg-user { align-self: flex-end; background: #0d9488; color: white; border-bottom-right-radius: 4px; }
        .msg-assistant { align-self: flex-start; background: white; color: #1f2937; border: 1px solid #e5e7eb; border-bottom-left-radius: 4px; }

        .chat-thinking { display: flex; align-items: center; gap: 6px; padding: 10px 14px; background: white; border: 1px solid #e5e7eb; border-radius: 14px; align-self: flex-start; }
        .chat-dot { width: 6px; height: 6px; background: #0d9488; border-radius: 50%; animation: chatDot 1.4s infinite; }
        .chat-dot:nth-child(2) { animation-delay: 0.2s; }
        .chat-dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes chatDot { 0%,60%,100% { opacity: 0.3; transform: scale(0.8); } 30% { opacity: 1; transform: scale(1); } }

        .chat-input-area { padding: 12px; border-top: 1px solid #e5e7eb; background: white; display: flex; gap: 8px; align-items: flex-end; }
        .chat-input { flex: 1; border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 12px; font-size: 13px; resize: none; font-family: inherit; outline: none; max-height: 80px; }
        .chat-input:focus { border-color: #0d9488; }
        .chat-send { background: #0d9488; color: white; border: none; border-radius: 12px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; }
        .chat-send:disabled { background: #9ca3af; cursor: not-allowed; }

        .chat-quick { display: flex; flex-wrap: wrap; gap: 6px; padding: 8px 16px 0; }
        .chat-quick button { background: #f0fdfa; border: 1px solid #99f6e4; color: #0f766e; font-size: 11px; padding: 4px 10px; border-radius: 999px; cursor: pointer; font-weight: 600; }
        .chat-quick button:hover { background: #ccfbf1; }

        @media (max-width: 640px) {
            .chat-panel { width: calc(100vw - 24px); right: 12px; bottom: 90px; height: calc(100vh - 120px); }
            .chat-fab { bottom: 16px; right: 16px; }
        }
    </style>

    @if($open)
    <div class="chat-panel">
        <div class="chat-header">
            <div class="chat-header-left">
                <div class="chat-avatar">
                    <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <div>
                    <div class="chat-title">Asistente IA</div>
                    <div class="chat-sub">Siempre disponible</div>
                </div>
            </div>
            <button wire:click="toggle" class="chat-close" title="Cerrar">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="chat-body" id="chat-body">
            @foreach($messages as $msg)
            <div class="msg msg-{{ $msg['role'] }}">{{ $msg['content'] }}</div>
            @endforeach

            @if($thinking)
            <div class="chat-thinking">
                <div class="chat-dot"></div>
                <div class="chat-dot"></div>
                <div class="chat-dot"></div>
            </div>
            @endif
        </div>

        @if(count($messages) <= 1)
        <div class="chat-quick">
            <button type="button" wire:click="$set('input', '¿Cuánto facturé este mes?')">💰 Ingresos del mes</button>
            <button type="button" wire:click="$set('input', '¿Qué citas tengo hoy?')">📅 Citas de hoy</button>
            <button type="button" wire:click="$set('input', '¿Cuántos pacientes tengo?')">👥 Total pacientes</button>
        </div>
        @endif

        <div class="chat-input-area">
            <textarea
                wire:model="input"
                wire:keydown.enter.prevent="send"
                class="chat-input"
                rows="1"
                placeholder="Pregunta sobre tu consultorio..."></textarea>
            <button wire:click="send" wire:loading.attr="disabled" wire:target="send" class="chat-send">
                <svg wire:loading.remove wire:target="send" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                <svg wire:loading wire:target="send" style="width:18px;height:18px;animation:spin 1s linear infinite;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>
    </div>
    @endif

    <button wire:click="toggle" class="chat-fab" title="Asistente IA">
        @if($open)
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        @else
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <span class="badge-ai">IA</span>
        @endif
    </button>

    <script>
        document.addEventListener('livewire:updated', () => {
            const body = document.getElementById('chat-body');
            if (body) body.scrollTop = body.scrollHeight;
        });
    </script>
</div>
