<x-filament-panels::page>
<div style="max-width:760px;margin:0 auto;">

    {{-- Share card --}}
    <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1.25rem;padding:2rem;margin-bottom:1.5rem;">
        <div style="text-align:center;margin-bottom:1.5rem;">
            <div style="font-size:3rem;margin-bottom:0.5rem;">🎁</div>
            <h2 style="font-size:1.5rem;font-weight:800;">Invita a un colega y ambos ganan</h2>
            <p style="color:#6b7280;font-size:0.875rem;margin-top:0.5rem;max-width:560px;margin-left:auto;margin-right:auto;">
                Tu colega gana <strong style="color:#0d9488;">30 días de trial</strong> (vs 15 normales). Tú ganas <strong style="color:#0d9488;">15 días extra al registrarse</strong> + <strong style="color:#0d9488;">1 mes gratis por cada mes que pague</strong> (hasta 12 meses = 1 año completo de DocFácil sin costo).
            </p>
        </div>

        <div style="background:linear-gradient(135deg,#f0fdfa,#ecfdf5);border:2px solid #14b8a6;border-radius:1rem;padding:1.5rem;text-align:center;margin-bottom:1.5rem;">
            <div style="font-size:0.75rem;color:#6b7280;text-transform:uppercase;font-weight:600;letter-spacing:0.05em;">Tu código de referido</div>
            <div style="font-size:2.5rem;font-weight:800;color:#0d9488;letter-spacing:0.1em;margin-top:0.5rem;">{{ $this->getReferralCode() }}</div>
            <div style="font-size:0.8rem;color:#6b7280;margin-top:0.5rem;">Compártelo con otros doctores</div>
        </div>

        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
            <a href="{{ $this->getWhatsAppShareLink() }}" target="_blank"
                style="flex:1;min-width:200px;display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.875rem;background:#22c55e;color:white;border-radius:0.75rem;font-weight:700;font-size:0.9rem;text-decoration:none;">
                <svg style="width:20px;height:20px;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                Compartir por WhatsApp
            </a>
            <button onclick="navigator.clipboard.writeText('{{ $this->getReferralLink() }}');this.textContent='✓ Copiado';setTimeout(()=>this.textContent='Copiar link de registro',2000)"
                style="padding:0.875rem 1.5rem;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:0.75rem;font-weight:600;font-size:0.9rem;cursor:pointer;">
                Copiar link de registro
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
        <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1rem;padding:1.25rem;text-align:center;">
            <div style="font-size:1.75rem;font-weight:800;color:#0d9488;">{{ $this->stats['total_referred'] }}</div>
            <div style="font-size:0.8rem;color:#6b7280;">Colegas invitados</div>
        </div>
        <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1rem;padding:1.25rem;text-align:center;">
            <div style="font-size:1.75rem;font-weight:800;color:#059669;">{{ $this->stats['paid_referred'] }}</div>
            <div style="font-size:0.8rem;color:#6b7280;">Ya pagando</div>
        </div>
        <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1rem;padding:1.25rem;text-align:center;">
            <div style="font-size:1.75rem;font-weight:800;color:#7c3aed;">{{ $this->stats['cascade_rewards'] }}/{{ $this->stats['cap'] }}</div>
            <div style="font-size:0.8rem;color:#6b7280;">Meses cascade ganados</div>
        </div>
        <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1rem;padding:1.25rem;text-align:center;">
            <div style="font-size:1.75rem;font-weight:800;color:#0d9488;">{{ $this->stats['total_months'] }}</div>
            <div style="font-size:0.8rem;color:#6b7280;">Total meses gratis</div>
        </div>
    </div>

    {{-- Referral history --}}
    @if(count($this->referrals) > 0)
    <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1rem;overflow:hidden;margin-bottom:1.5rem;">
        <div style="padding:1rem 1.25rem;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-weight:700;font-size:0.875rem;" class="dark:bg-gray-700">
            Tus referidos
        </div>
        <div style="overflow-x:auto;">
        <table style="width:100%;font-size:0.85rem;min-width:560px;">
            <thead>
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <th style="padding:0.75rem 1.25rem;text-align:left;color:#6b7280;font-weight:600;">Colega</th>
                    <th style="padding:0.75rem;text-align:center;color:#6b7280;font-weight:600;">Plan</th>
                    <th style="padding:0.75rem;text-align:center;color:#6b7280;font-weight:600;">Cascade</th>
                    <th style="padding:0.75rem 1.25rem;text-align:right;color:#6b7280;font-weight:600;">Desde</th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->referrals as $ref)
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:0.75rem 1.25rem;">
                        <div style="font-weight:600;">{{ $ref['name'] }}</div>
                        <div style="font-size:0.75rem;color:#9ca3af;">{{ $ref['clinic_name'] }} · {{ $ref['email'] }}</div>
                    </td>
                    <td style="padding:0.75rem;text-align:center;">
                        @if($ref['plan'] === 'free')
                            <span style="padding:0.25rem 0.75rem;border-radius:9999px;font-size:0.75rem;font-weight:600;background:#f3f4f6;color:#6b7280;">Trial</span>
                        @else
                            <span style="padding:0.25rem 0.75rem;border-radius:9999px;font-size:0.75rem;font-weight:600;background:#d1fae5;color:#065f46;">{{ ucfirst($ref['plan']) }}</span>
                        @endif
                    </td>
                    <td style="padding:0.75rem;text-align:center;">
                        @if($ref['cascade_rewards'] > 0)
                            <span style="font-weight:700;color:#7c3aed;">+{{ $ref['cascade_rewards'] }} mes{{ $ref['cascade_rewards'] !== 1 ? 'es' : '' }}</span>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td style="padding:0.75rem 1.25rem;text-align:right;color:#6b7280;">{{ $ref['date'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    {{-- Leaderboard --}}
    @if(count($this->leaderboard) > 0)
    <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1rem;overflow:hidden;">
        <div style="padding:1rem 1.25rem;background:linear-gradient(135deg,#fef3c7,#fde68a);border-bottom:1px solid #fcd34d;font-weight:700;font-size:0.875rem;color:#78350f;">
            🏆 Top embajadores DocFácil
        </div>
        <div style="padding:0.5rem 1.25rem;">
            @foreach($this->leaderboard as $row)
            <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 0;border-bottom:1px solid #f3f4f6;{{ $row['is_me'] ? 'background:#fffbeb;margin:0 -1.25rem;padding-left:1.25rem;padding-right:1.25rem;' : '' }}">
                <div style="font-size:1rem;font-weight:800;color:#9ca3af;width:30px;">
                    @if($row['position'] === 1)🥇@elseif($row['position'] === 2)🥈@elseif($row['position'] === 3)🥉@else#{{ $row['position'] }}@endif
                </div>
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#14b8a6,#0d9488);color:white;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $row['initials'] }}</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;color:#1f2937;">{{ $row['name'] }}{{ $row['is_me'] ? ' (tú)' : '' }}</div>
                    <div style="font-size:0.75rem;color:#6b7280;">{{ $row['referred'] }} colegas invitados</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:700;color:#7c3aed;">{{ $row['cascade_months'] }} meses</div>
                    <div style="font-size:0.7rem;color:#9ca3af;">ganados</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
</x-filament-panels::page>
