<x-filament-panels::page>
    <style>
        .qr-card { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); max-width: 500px; margin: 0 auto; text-align: center; }
        .qr-card img { width: 280px; height: 280px; border: 8px solid #f0fdfa; border-radius: 16px; margin: 12px auto; display: block; }
        .qr-url { background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 10px; padding: 10px 14px; font-size: 12px; color: #4b5563; word-break: break-all; margin: 12px 0; font-family: monospace; }
        .qr-actions { display: flex; gap: 10px; justify-content: center; margin-top: 16px; flex-wrap: wrap; }
        .qr-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; }
        .qr-btn-primary { background: #0d9488; color: white; }
        .qr-btn-secondary { background: #f3f4f6; color: #374151; }
        .instructions { background: #fefce8; border: 1px solid #fde68a; border-radius: 12px; padding: 16px; margin-top: 20px; font-size: 13px; color: #713f12; }
        .instructions strong { display: block; margin-bottom: 6px; color: #92400e; }
        .step-num { display: inline-block; width: 20px; height: 20px; background: #0d9488; color: white; border-radius: 50%; font-size: 11px; font-weight: 700; text-align: center; line-height: 20px; margin-right: 6px; }
        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area { position: absolute; left: 0; top: 0; width: 100%; padding: 40px; text-align: center; }
            .qr-actions, .instructions, .fi-sidebar, .fi-topbar, header { display: none !important; }
            .qr-card { box-shadow: none; border: 2px solid #0d9488; }
        }
    </style>

    <div class="print-area">
        <div class="qr-card">
            <div style="font-size: 13px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Check-in</div>
            <h2 style="font-size: 24px; color: #0f766e; font-weight: 800; margin-top: 4px;">{{ auth()->user()->clinic->name }}</h2>
            <p style="color: #6b7280; font-size: 14px; margin-top: 4px;">Escanea el código para registrarte</p>

            <img src="{{ $this->getQrCodeUrl() }}" alt="QR Check-in">

            <p style="font-size: 12px; color: #9ca3af;">O visita:</p>
            <div class="qr-url">{{ $this->getCheckInUrl() }}</div>

            <div class="qr-actions">
                <button onclick="window.print()" class="qr-btn qr-btn-primary">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimir
                </button>
                <a href="{{ $this->getCheckInUrl() }}" target="_blank" class="qr-btn qr-btn-secondary">Ver página</a>
                <button onclick="navigator.clipboard.writeText('{{ $this->getCheckInUrl() }}'); this.textContent='¡Copiado!'" class="qr-btn qr-btn-secondary">Copiar link</button>
            </div>
        </div>

        <div class="instructions">
            <strong>Cómo usarlo:</strong>
            <div style="line-height: 1.8;">
                <div><span class="step-num">1</span> Imprime este código QR y pégalo en la recepción</div>
                <div><span class="step-num">2</span> Los pacientes lo escanean con su celular al llegar</div>
                <div><span class="step-num">3</span> Llenan sus datos desde su teléfono mientras esperan</div>
                <div><span class="step-num">4</span> Cuando los atiendes, ya aparecen en tu lista de pacientes</div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
