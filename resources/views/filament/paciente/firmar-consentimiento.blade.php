<x-filament-panels::page>
    @if($consentimiento)
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1.1rem 1.2rem;max-width:44rem;">
            <div style="font-weight:800;font-size:1.1rem;color:#0f172a;margin-bottom:0.25rem;">{{ $consentimiento->title }}</div>
            @if($consentimiento->procedure_name)<div style="font-size:0.85rem;color:#64748b;margin-bottom:0.75rem;">{{ $consentimiento->procedure_name }}</div>@endif
            <div style="font-size:0.95rem;line-height:1.55;color:#1f2937;">{!! $contenido !!}</div>
            @if($consentimiento->risks)<div style="margin-top:0.75rem;"><strong>Riesgos:</strong> {{ $consentimiento->risks }}</div>@endif
            @if($consentimiento->alternatives)<div style="margin-top:0.4rem;"><strong>Alternativas:</strong> {{ $consentimiento->alternatives }}</div>@endif

            @if($consentimiento->signed_at)
                <div style="margin-top:1rem;padding:0.75rem;border-radius:8px;background:#d1fae5;color:#065f46;font-weight:700;">Firmado el {{ $consentimiento->signed_at->format('d/m/Y H:i') }}.</div>
            @else
                <div x-data="firmaConsentimiento()" style="margin-top:1rem;">
                    <div style="font-weight:700;margin-bottom:0.35rem;">Si está de acuerdo, firme aquí</div>
                    <canvas x-ref="lienzo" width="600" height="200" style="width:100%;height:150px;border:2px dashed #99f6e4;border-radius:12px;background:#fff;touch-action:none;"
                        x-on:mousedown="empieza($event)" x-on:mousemove="mueve($event)" x-on:mouseup="dibujando = false"
                        x-on:touchstart.prevent="empieza($event)" x-on:touchmove.prevent="mueve($event)" x-on:touchend="dibujando = false"></canvas>
                    <div style="display:flex;justify-content:space-between;margin-top:0.4rem;">
                        <button type="button" x-on:click="borrar()" style="color:#0f766e;font-weight:700;background:none;">Borrar</button>
                        <button type="button" x-on:click="if (firmo) $wire.firmar($refs.lienzo.toDataURL('image/png'))" x-bind:style="{ opacity: firmo ? 1 : .5 }"
                            style="padding:0.55rem 1.1rem;border-radius:8px;background:#0f766e;color:#fff;font-weight:800;">Firmar</button>
                    </div>
                </div>
                <script>
                    function firmaConsentimiento() {
                        return {
                            dibujando: false, firmo: false, ultimo: null,
                            punto(e) { const r = this.$refs.lienzo.getBoundingClientRect(), t = e.touches ? e.touches[0] : e; return { x: (t.clientX - r.left) * 600 / r.width, y: (t.clientY - r.top) * 200 / r.height }; },
                            empieza(e) { this.dibujando = true; this.ultimo = this.punto(e); },
                            mueve(e) { if (!this.dibujando) return; const x = this.$refs.lienzo.getContext('2d'), p = this.punto(e); x.lineWidth = 3; x.lineCap = 'round'; x.strokeStyle = '#111827'; x.beginPath(); x.moveTo(this.ultimo.x, this.ultimo.y); x.lineTo(p.x, p.y); x.stroke(); this.ultimo = p; this.firmo = true; },
                            borrar() { this.$refs.lienzo.getContext('2d').clearRect(0, 0, 600, 200); this.firmo = false; },
                        };
                    }
                </script>
            @endif
        </div>
    @endif
</x-filament-panels::page>
