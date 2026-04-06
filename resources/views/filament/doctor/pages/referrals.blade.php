<x-filament-panels::page>
<div style="max-width:720px;margin:0 auto;">

    {{-- Share card --}}
    <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1.25rem;padding:2rem;margin-bottom:1.5rem;">
        <div style="text-align:center;margin-bottom:1.5rem;">
            <div style="font-size:3rem;margin-bottom:0.5rem;">🎁</div>
            <h2 style="font-size:1.5rem;font-weight:800;">Invita a un colega y ambos ganan</h2>
            <p style="color:#6b7280;font-size:0.875rem;margin-top:0.5rem;">
                Comparte tu codigo. Cuando se registren, ambos reciben <strong style="color:#0d9488;">15 dias gratis extra</strong>.
            </p>
        </div>

        {{-- Code display --}}
        <div style="background:linear-gradient(135deg,#f0fdfa,#ecfdf5);border:2px solid #14b8a6;border-radius:1rem;padding:1.5rem;text-align:center;margin-bottom:1.5rem;">
            <div style="font-size:0.75rem;color:#6b7280;text-transform:uppercase;font-weight:600;letter-spacing:0.05em;">Tu codigo de referido</div>
            <div style="font-size:2.5rem;font-weight:800;color:#0d9488;letter-spacing:0.1em;margin-top:0.5rem;">{{ $this->getReferralCode() }}</div>
            <div style="font-size:0.8rem;color:#6b7280;margin-top:0.5rem;">Compartelo con otros doctores</div>
        </div>

        {{-- Share buttons --}}
        <div style="display:flex;gap:0.75rem;">
            <a href="{{ $this->getWhatsAppShareLink() }}" target="_blank"
                style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.875rem;background:#22c55e;color:white;border-radius:0.75rem;font-weight:700;font-size:0.9rem;text-decoration:none;">
                <svg style="width:20px;height:20px;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                Compartir por WhatsApp
            </a>
            <button onclick="navigator.clipboard.writeText('{{ $this->getReferralCode() }}');this.textContent='Copiado!';setTimeout(()=>this.textContent='Copiar codigo',2000)"
                style="padding:0.875rem 1.5rem;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:0.75rem;font-weight:600;font-size:0.9rem;cursor:pointer;">
                Copiar codigo
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
        <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1rem;padding:1.25rem;text-align:center;">
            <div style="font-size:2rem;font-weight:800;color:#0d9488;">{{ count($this->referrals) }}</div>
            <div style="font-size:0.8rem;color:#6b7280;">Colegas invitados</div>
        </div>
        <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1rem;padding:1.25rem;text-align:center;">
            <div style="font-size:2rem;font-weight:800;color:#0d9488;">+{{ $this->totalRewards }} dias</div>
            <div style="font-size:0.8rem;color:#6b7280;">Dias ganados</div>
        </div>
    </div>

    {{-- Referral history --}}
    @if(count($this->referrals) > 0)
    <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1rem;overflow:hidden;">
        <div style="padding:1rem 1.25rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:700;font-size:0.875rem;" class="dark:bg-gray-700">
            Historial de referidos
        </div>
        <table style="width:100%;font-size:0.85rem;">
            <thead>
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <th style="padding:0.75rem 1.25rem;text-align:left;color:#6b7280;font-weight:600;">Colega</th>
                    <th style="padding:0.75rem;text-align:center;color:#6b7280;font-weight:600;">Estado</th>
                    <th style="padding:0.75rem;text-align:center;color:#6b7280;font-weight:600;">Recompensa</th>
                    <th style="padding:0.75rem 1.25rem;text-align:right;color:#6b7280;font-weight:600;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->referrals as $ref)
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:0.75rem 1.25rem;">
                        <div style="font-weight:600;">{{ $ref['name'] }}</div>
                        <div style="font-size:0.75rem;color:#9ca3af;">{{ $ref['email'] }}</div>
                    </td>
                    <td style="padding:0.75rem;text-align:center;">
                        <span style="padding:0.25rem 0.75rem;border-radius:9999px;font-size:0.75rem;font-weight:600;
                            {{ $ref['status'] === 'rewarded' ? 'background:#d1fae5;color:#065f46;' : ($ref['status'] === 'registered' ? 'background:#dbeafe;color:#1e40af;' : 'background:#fef3c7;color:#92400e;') }}">
                            {{ $ref['status'] === 'rewarded' ? 'Recompensado' : ($ref['status'] === 'registered' ? 'Registrado' : 'Pendiente') }}
                        </span>
                    </td>
                    <td style="padding:0.75rem;text-align:center;font-weight:700;color:#0d9488;">{{ $ref['reward'] }}</td>
                    <td style="padding:0.75rem 1.25rem;text-align:right;color:#6b7280;">{{ $ref['date'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
</x-filament-panels::page>
