<div>
    @if($signed)
    <div style="text-align:center;padding:2rem;background:#f0fdf4;border:1px solid #86efac;border-radius:1rem;">
        <svg style="width:48px;height:48px;color:#22c55e;margin:0 auto 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p style="font-weight:700;color:#166534;font-size:1.125rem;">Firmado correctamente</p>
        <p style="color:#4ade80;font-size:0.875rem;">La firma ha sido guardada.</p>
    </div>
    @else
    <div style="border:2px dashed #d1d5db;border-radius:1rem;padding:1rem;background:#fafafa;">
        <p style="text-align:center;font-size:0.875rem;color:#6b7280;margin-bottom:0.75rem;">Firma del paciente — dibuja con el dedo o mouse</p>
        <canvas
            id="signature-canvas-{{ $consentFormId }}"
            width="600"
            height="200"
            style="border:1px solid #e5e7eb;border-radius:0.5rem;background:white;width:100%;cursor:crosshair;touch-action:none;"
        ></canvas>
        <div style="display:flex;gap:0.75rem;margin-top:0.75rem;justify-content:flex-end;">
            <button
                onclick="clearSignature()"
                type="button"
                style="padding:0.5rem 1.25rem;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:0.5rem;font-size:0.875rem;font-weight:600;cursor:pointer;">
                Borrar
            </button>
            <button
                onclick="saveSignature()"
                type="button"
                style="padding:0.5rem 1.25rem;background:#14b8a6;color:white;border:none;border-radius:0.5rem;font-size:0.875rem;font-weight:600;cursor:pointer;">
                Guardar firma
            </button>
        </div>
    </div>

    <script>
    (function() {
        const canvas = document.getElementById('signature-canvas-{{ $consentFormId }}');
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let lastX = 0;
        let lastY = 0;

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const touch = e.touches ? e.touches[0] : e;
            return {
                x: (touch.clientX - rect.left) * scaleX,
                y: (touch.clientY - rect.top) * scaleY
            };
        }

        function startDraw(e) {
            e.preventDefault();
            drawing = true;
            const pos = getPos(e);
            lastX = pos.x;
            lastY = pos.y;
        }

        function draw(e) {
            e.preventDefault();
            if (!drawing) return;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.strokeStyle = '#1a1a1a';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.stroke();
            lastX = pos.x;
            lastY = pos.y;
        }

        function stopDraw(e) {
            e.preventDefault();
            drawing = false;
        }

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);
        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDraw);

        window.clearSignature = function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        };

        window.saveSignature = function() {
            const data = canvas.toDataURL('image/png');
            // Check if canvas has any drawing
            const blank = document.createElement('canvas');
            blank.width = canvas.width;
            blank.height = canvas.height;
            if (canvas.toDataURL() === blank.toDataURL()) {
                alert('Por favor dibuja tu firma antes de guardar.');
                return;
            }
            @this.call('saveSignature', data);
        };
    })();
    </script>
    @endif
</div>
