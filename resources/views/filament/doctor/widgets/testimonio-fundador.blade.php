{{-- Estilos en línea a propósito: en producción algunas utilidades de Tailwind v4 no compilan. Ver CLAUDE.md. --}}
<x-filament-widgets::widget>
    @unless ($oculto)
        <div style="border-radius:1.25rem;border:2px solid #fde68a;background:linear-gradient(135deg,#fffbeb 0%,#ffffff 65%);padding:22px 26px;box-shadow:0 8px 24px -8px rgba(217,119,6,.18);">
            @if ($enviado)
                <div style="display:flex;gap:16px;align-items:center;">
                    <img src="{{ asset('images/founder-omar.jpg') }}" alt="Omar" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:3px solid #fbbf24;flex-shrink:0;">
                    <div>
                        <div style="font-weight:800;font-size:1.1rem;color:#0f172a;">¡Gracias! Ya me llegó.</div>
                        <div style="color:#475569;font-size:.9rem;margin-top:2px;line-height:1.5;">
                            @if ($permiso)
                                La leo yo antes de publicar nada.
                            @else
                                No la voy a publicar: me sirve para saber qué mejorar.
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div style="display:flex;gap:16px;align-items:flex-start;">
                    <img src="{{ asset('images/founder-omar.jpg') }}" alt="Omar" style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:3px solid #fbbf24;flex-shrink:0;">
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:.66rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#b45309;">⭐ Programa Fundador</div>
                        <h3 style="font-size:1.2rem;font-weight:800;color:#0f172a;margin-top:2px;line-height:1.25;">Ya llevas un mes con DocFácil. ¿Me regalas una frase?</h3>
                        <p style="font-size:.9rem;color:#475569;margin-top:6px;line-height:1.5;">
                            Cómo era tu consultorio antes y qué cambió. Con una o dos oraciones basta, en tus palabras, como se lo contarías a un colega. — Omar
                        </p>
                    </div>
                </div>

                <form wire:submit="enviar" style="margin-top:16px;">
                    <textarea wire:model="frase" rows="3" maxlength="600"
                        placeholder="Antes llevaba la agenda en una libreta y..."
                        style="width:100%;border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;font-size:.95rem;line-height:1.5;color:#0f172a;background:#fff;resize:vertical;"></textarea>
                    @error('frase')
                        <div style="color:#dc2626;font-size:.8rem;margin-top:4px;">{{ $message }}</div>
                    @enderror

                    <label style="display:block;margin-top:12px;font-size:.8rem;font-weight:700;color:#334155;">
                        Cómo quieres que aparezca tu nombre
                        <input type="text" wire:model="firma" maxlength="120"
                            style="display:block;width:100%;margin-top:4px;border:1px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:.9rem;font-weight:500;color:#0f172a;background:#fff;">
                    </label>
                    @error('firma')
                        <div style="color:#dc2626;font-size:.8rem;margin-top:4px;">{{ $message }}</div>
                    @enderror

                    <label style="display:flex;gap:10px;align-items:flex-start;margin-top:12px;font-size:.85rem;color:#334155;cursor:pointer;line-height:1.45;">
                        <input type="checkbox" wire:model="permiso" style="margin-top:2px;width:16px;height:16px;accent-color:#d97706;flex-shrink:0;">
                        <span>Doy permiso de publicar mi frase, con este nombre, en la página y en los anuncios de DocFácil.</span>
                    </label>

                    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:16px;">
                        <button type="submit" wire:loading.attr="disabled"
                            style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-weight:700;font-size:.9rem;border:none;border-radius:10px;padding:10px 18px;cursor:pointer;">
                            Enviar mi frase
                        </button>
                        <button type="button" wire:click="despues"
                            style="background:none;border:none;color:#64748b;font-weight:600;font-size:.85rem;cursor:pointer;padding:6px;">
                            Ahora no
                        </button>
                        <button type="button" wire:click="noPreguntar"
                            style="background:none;border:none;color:#94a3b8;font-size:.78rem;cursor:pointer;padding:6px;margin-left:auto;">
                            No volver a preguntar
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @endunless
</x-filament-widgets::widget>
