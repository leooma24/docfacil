<x-filament-panels::page>
<div style="max-width:820px;margin:0 auto;">

    {{-- Hero intro --}}
    <div style="background:linear-gradient(135deg,#f5f3ff,#ede9fe);border:1px solid #c4b5fd;border-radius:1.25rem;padding:1.5rem;margin-bottom:1.5rem;">
        <div style="display:flex;align-items:start;gap:1rem;">
            <div style="font-size:2.5rem;flex-shrink:0;">💡</div>
            <div style="flex:1;">
                <h2 style="font-size:1.15rem;font-weight:800;color:#4c1d95;margin-bottom:0.25rem;">Tú diseñas el futuro de DocFácil</h2>
                <p style="font-size:0.875rem;color:#5b21b6;line-height:1.55;">
                    Cada mes elegimos <strong>2 ideas ganadoras</strong>: una la construimos como add-on de pago (al precio que la mayoría votó), otra queda <strong>gratis para todos</strong>. Si tu idea gana, aparece tu nombre en el landing y ganas meses gratis de DocFácil.
                </p>
            </div>
        </div>
    </div>

    {{-- Stats + Propose button --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:0.75rem;margin-bottom:1rem;">
        <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:0.75rem;padding:0.9rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:800;color:#7c3aed;">{{ $this->stats['my_proposals'] }}</div>
            <div style="font-size:0.7rem;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">Mis propuestas</div>
        </div>
        <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:0.75rem;padding:0.9rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:800;color:#7c3aed;">{{ $this->stats['my_votes'] }}</div>
            <div style="font-size:0.7rem;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">Votos emitidos</div>
        </div>
        <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:0.75rem;padding:0.9rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:800;color:#059669;">{{ $this->stats['total_shipped'] }}</div>
            <div style="font-size:0.7rem;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">Entregadas</div>
        </div>
        <button type="button" wire:click="openProposeModal"
            style="background:linear-gradient(135deg,#7c3aed,#6d28d9);color:white;border:none;padding:0.9rem;border-radius:0.75rem;font-weight:700;font-size:0.875rem;cursor:pointer;">
            💡 Proponer idea
        </button>
    </div>

    {{-- Tabs --}}
    <div style="display:flex;gap:0.5rem;margin-bottom:1rem;border-bottom:1px solid #e5e7eb;">
        @foreach(['proposed' => 'Propuestas', 'in_progress' => 'En construcción', 'shipped' => 'Entregadas'] as $tab => $label)
            <button type="button" wire:click="setTab('{{ $tab }}')"
                style="padding:0.75rem 1rem;background:none;border:none;border-bottom:3px solid {{ $activeTab === $tab ? '#7c3aed' : 'transparent' }};color:{{ $activeTab === $tab ? '#7c3aed' : '#6b7280' }};font-weight:{{ $activeTab === $tab ? '700' : '500' }};cursor:pointer;transition:all 0.15s;">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Features list --}}
    @if(count($this->features) === 0)
        <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1rem;padding:3rem 1.5rem;text-align:center;">
            <div style="font-size:3rem;margin-bottom:0.5rem;opacity:0.4;">📭</div>
            <p style="color:#6b7280;font-size:0.9rem;">
                @if($activeTab === 'proposed')
                    No hay propuestas abiertas. <strong>¡Sé el primero!</strong> Propón una idea y empiezas con tu voto automático.
                @elseif($activeTab === 'in_progress')
                    Nada en construcción por ahora. El ganador del próximo mes aparecerá aquí.
                @else
                    Todavía no entregamos features de este roadmap. Propón y vota para que empecemos.
                @endif
            </p>
        </div>
    @else
        <div style="display:grid;gap:0.75rem;">
            @foreach($this->features as $f)
            <div class="bg-white dark:bg-gray-800" style="border:1px solid {{ $f['is_mine'] ? '#c4b5fd' : '#e5e7eb' }};border-radius:1rem;padding:1.25rem;{{ $f['is_mine'] ? 'background:#faf5ff;' : '' }}">
                <div style="display:flex;gap:0.9rem;align-items:start;">
                    {{-- Vote button --}}
                    <button type="button" wire:click="openVoteModal({{ $f['id'] }})"
                        style="display:flex;flex-direction:column;align-items:center;justify-content:center;width:56px;min-height:64px;border-radius:0.75rem;border:2px solid {{ $f['has_voted'] ? '#7c3aed' : '#e5e7eb' }};background:{{ $f['has_voted'] ? '#7c3aed' : 'white' }};color:{{ $f['has_voted'] ? 'white' : '#7c3aed' }};cursor:pointer;transition:all 0.15s;flex-shrink:0;">
                        <svg style="width:18px;height:18px;" fill="currentColor" viewBox="0 0 24 24"><path d="M7 14l5-5 5 5H7z"/></svg>
                        <span style="font-size:1.05rem;font-weight:800;margin-top:2px;">{{ $f['votes_count'] }}</span>
                    </button>

                    {{-- Content --}}
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:start;justify-content:space-between;gap:0.5rem;margin-bottom:0.35rem;">
                            <h3 style="font-size:1rem;font-weight:700;color:#111827;line-height:1.3;">
                                {{ $f['title'] }}
                                @if($f['is_mine'])<span style="font-size:0.65rem;padding:2px 6px;background:#c4b5fd;color:#4c1d95;border-radius:999px;margin-left:0.35rem;vertical-align:middle;">Tuya</span>@endif
                            </h3>
                            @if(!empty($f['price_label']))
                            <span style="font-size:0.7rem;padding:3px 8px;background:{{ $f['proposed_price_tier'] === 'free' ? '#ecfdf5' : '#fef3c7' }};color:{{ $f['proposed_price_tier'] === 'free' ? '#065f46' : '#78350f' }};border-radius:999px;font-weight:600;white-space:nowrap;">
                                {{ $f['price_label'] }}
                            </span>
                            @endif
                        </div>
                        <p style="font-size:0.85rem;color:#4b5563;line-height:1.55;margin-bottom:0.6rem;">{{ \Illuminate\Support\Str::limit($f['description'], 240) }}</p>
                        <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.75rem;color:#9ca3af;">
                            <div style="width:22px;height:22px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:white;font-weight:700;display:flex;align-items:center;justify-content:center;font-size:0.65rem;">{{ $f['author_initials'] }}</div>
                            <span>{{ $f['author_name'] }}</span>
                            @if(!empty($f['author_clinic']))<span>·</span><span>{{ $f['author_clinic'] }}</span>@endif
                            @if($f['status'] === 'shipped' && !empty($f['shipped_at']))
                                <span>·</span>
                                <span style="color:#059669;">✓ Entregada {{ \Carbon\Carbon::parse($f['shipped_at'])->translatedFormat('d M Y') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- Propose modal --}}
    @if($showProposeModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:100;display:flex;align-items:center;justify-content:center;padding:1rem;" wire:click.self="closeProposeModal">
        <div class="bg-white dark:bg-gray-800" style="max-width:540px;width:100%;border-radius:1rem;padding:1.75rem;max-height:90vh;overflow-y:auto;">
            <h3 style="font-size:1.15rem;font-weight:800;color:#4c1d95;margin-bottom:0.25rem;">💡 Propón una idea</h3>
            <p style="font-size:0.8rem;color:#6b7280;margin-bottom:1.25rem;">Tu voto se registra automáticamente. Compártela después para que tus colegas voten.</p>

            <form wire:submit.prevent="submitProposal">
                <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.35rem;">Título (resumen corto)</label>
                <input type="text" wire:model="newTitle" maxlength="160" placeholder="Ej: Integración con laboratorios dentales"
                    style="width:100%;padding:0.6rem 0.8rem;border:1px solid #d1d5db;border-radius:0.5rem;margin-bottom:0.85rem;font-size:0.9rem;">
                @error('newTitle')<p style="color:#dc2626;font-size:0.75rem;margin-top:-0.75rem;margin-bottom:0.75rem;">{{ $message }}</p>@enderror

                <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.35rem;">Descripción — qué problema resuelve</label>
                <textarea wire:model="newDescription" rows="4" maxlength="2000"
                    placeholder="Ej: Mandar órdenes al laboratorio directamente desde el expediente del paciente, con seguimiento del trabajo y notificación cuando esté listo..."
                    style="width:100%;padding:0.6rem 0.8rem;border:1px solid #d1d5db;border-radius:0.5rem;margin-bottom:0.85rem;font-size:0.9rem;resize:vertical;"></textarea>
                @error('newDescription')<p style="color:#dc2626;font-size:0.75rem;margin-top:-0.75rem;margin-bottom:0.75rem;">{{ $message }}</p>@enderror

                <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.35rem;">¿Cuánto pagarías al mes por esto?</label>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(90px,1fr));gap:0.4rem;margin-bottom:1.25rem;">
                    @foreach(App\Models\FeatureRequest::PRICE_TIERS as $tier => $label)
                    <label style="display:flex;align-items:center;justify-content:center;padding:0.55rem;border:2px solid {{ $newPriceTier === $tier ? '#7c3aed' : '#e5e7eb' }};border-radius:0.5rem;cursor:pointer;font-size:0.75rem;font-weight:600;background:{{ $newPriceTier === $tier ? '#f5f3ff' : 'white' }};color:{{ $newPriceTier === $tier ? '#4c1d95' : '#6b7280' }};">
                        <input type="radio" wire:model="newPriceTier" value="{{ $tier }}" style="display:none;">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>

                <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                    <button type="button" wire:click="closeProposeModal"
                        style="padding:0.65rem 1.25rem;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:0.5rem;font-weight:600;font-size:0.85rem;cursor:pointer;">Cancelar</button>
                    <button type="submit"
                        style="padding:0.65rem 1.5rem;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:white;border:none;border-radius:0.5rem;font-weight:700;font-size:0.85rem;cursor:pointer;">Enviar idea</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Vote modal --}}
    @if($votingFeatureId)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:100;display:flex;align-items:center;justify-content:center;padding:1rem;" wire:click.self="closeVoteModal">
        <div class="bg-white dark:bg-gray-800" style="max-width:440px;width:100%;border-radius:1rem;padding:1.75rem;">
            <h3 style="font-size:1.1rem;font-weight:800;color:#4c1d95;margin-bottom:0.25rem;">🗳️ Tu voto con precio</h3>
            <p style="font-size:0.85rem;color:#6b7280;margin-bottom:1.25rem;">¿Cuánto pagarías al mes por esta feature? Tu respuesta nos ayuda a decidir si la construimos como pagada o gratis.</p>

            <div style="display:grid;gap:0.4rem;margin-bottom:1.25rem;">
                @foreach(App\Models\FeatureRequest::PRICE_TIERS as $tier => $label)
                <label style="display:flex;align-items:center;gap:0.6rem;padding:0.7rem 0.9rem;border:2px solid {{ $voteWillingness === $tier ? '#7c3aed' : '#e5e7eb' }};border-radius:0.6rem;cursor:pointer;background:{{ $voteWillingness === $tier ? '#f5f3ff' : 'white' }};color:{{ $voteWillingness === $tier ? '#4c1d95' : '#374151' }};font-weight:{{ $voteWillingness === $tier ? '700' : '500' }};font-size:0.9rem;">
                    <input type="radio" wire:model="voteWillingness" value="{{ $tier }}" style="accent-color:#7c3aed;">
                    {{ $label }}
                </label>
                @endforeach
            </div>

            <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                <button type="button" wire:click="closeVoteModal"
                    style="padding:0.65rem 1.25rem;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:0.5rem;font-weight:600;font-size:0.85rem;cursor:pointer;">Cancelar</button>
                <button type="button" wire:click="submitVote"
                    style="padding:0.65rem 1.5rem;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:white;border:none;border-radius:0.5rem;font-weight:700;font-size:0.85rem;cursor:pointer;">Votar</button>
            </div>
        </div>
    </div>
    @endif

</div>
</x-filament-panels::page>
