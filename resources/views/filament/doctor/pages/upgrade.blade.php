<x-filament-panels::page>
    @php
        $clinic = $this->getClinic();
        $expired = $this->isExpired();
        $founder = $this->isFounder();
        $founderPrice = $this->getFounderPrice();
    @endphp

    <div style="max-width:900px;margin:0 auto;">

        {{-- Expired banner --}}
        @if($expired)
        <div style="background:linear-gradient(135deg,#fef3c7,#fde68a);border:1px solid #f59e0b;border-radius:1rem;padding:1.5rem;margin-bottom:2rem;display:flex;align-items:center;gap:1rem;">
            <div style="width:48px;height:48px;background:#f59e0b;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:24px;height:24px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div style="font-weight:700;font-size:1rem;color:#92400e;">Tu {{ $clinic->is_beta ? 'periodo beta' : 'prueba gratuita' }} ha terminado</div>
                <div style="font-size:0.875rem;color:#a16207;">Tus datos estan seguros. Activa un plan para seguir usando todas las funciones.</div>
            </div>
        </div>
        @endif

        {{-- Current plan info --}}
        <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1rem;padding:1.5rem;margin-bottom:2rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-size:0.75rem;color:#6b7280;text-transform:uppercase;font-weight:700;">Tu plan actual</div>
                    <div style="font-size:1.5rem;font-weight:800;color:#111;text-transform:capitalize;">{{ $clinic->plan }}</div>
                </div>
                <div style="text-align:right;">
                    @if($clinic->is_beta)
                    <span style="padding:0.375rem 0.75rem;background:#fef3c7;color:#92400e;border-radius:9999px;font-size:0.75rem;font-weight:700;">BETA TESTER</span>
                    @endif
                    @if($founder)
                    <span style="padding:0.375rem 0.75rem;background:#d1fae5;color:#065f46;border-radius:9999px;font-size:0.75rem;font-weight:700;margin-left:0.5rem;">FUNDADOR</span>
                    @endif
                </div>
            </div>
            @if($clinic->trial_ends_at)
            <div style="font-size:0.8rem;color:#6b7280;margin-top:0.5rem;">
                {{ $clinic->trial_ends_at->isPast() ? 'Vencio el' : 'Vence el' }} {{ $clinic->trial_ends_at->format('d/m/Y') }}
            </div>
            @endif
        </div>

        {{-- Founder discount banner --}}
        @if($founder)
        <div style="background:linear-gradient(135deg,#0d9488,#0891b2);border-radius:1rem;padding:1.5rem;margin-bottom:2rem;color:white;">
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem;">
                <svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                <div style="font-weight:700;font-size:1.1rem;">Precio de fundador de por vida</div>
            </div>
            <div style="font-size:0.9rem;opacity:0.9;">Como beta tester, tienes <strong>50% de descuento permanente</strong> en cualquier plan. Este precio no cambia nunca.</div>
        </div>
        @endif

        {{-- Plans --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;">
            @php
            $plans = [
                [
                    'name' => 'Básico',
                    'price' => $founder ? $founderPrice : '249',
                    'original' => $founder ? '249' : null,
                    'features' => [
                        '1 doctor',
                        '200 pacientes',
                        'Citas ilimitadas',
                        '🎤 Dictado por voz',
                        '✨ Resumen IA del paciente',
                        '💡 Sugerencias de Dx con IA',
                        'Recetas PDF',
                    ],
                    'popular' => false,
                ],
                [
                    'name' => 'Pro',
                    'price' => $founder ? (string)(intval($founderPrice) > 249 ? $founderPrice : '349') : '499',
                    'original' => $founder ? '499' : null,
                    'features' => [
                        'Hasta 3 doctores',
                        'Pacientes ilimitados',
                        '✨ Todo lo del Básico',
                        '🤖 Dictado inteligente (IA llena todo)',
                        '📋 Consentimientos con IA',
                        '📊 Análisis IA del consultorio',
                        '💬 Cobro por WhatsApp',
                        '📱 Check-in con QR',
                        'Portal del paciente',
                    ],
                    'popular' => true,
                ],
                [
                    'name' => 'Clínica',
                    'price' => $founder ? (string)(intval($founderPrice) > 499 ? $founderPrice : '699') : '999',
                    'original' => $founder ? '999' : null,
                    'features' => [
                        'Doctores ilimitados',
                        'Multi-sucursal',
                        'Todo lo del Pro',
                        'Soporte prioritario',
                        'Onboarding 1 a 1',
                    ],
                    'popular' => false,
                ],
            ];
            @endphp

            @foreach($plans as $plan)
            <div class="bg-white dark:bg-gray-800" style="border:{{ $plan['popular'] ? '2px solid #0d9488' : '1px solid #e5e7eb' }};border-radius:1.25rem;padding:1.5rem;position:relative;{{ $plan['popular'] ? 'box-shadow:0 8px 25px rgba(13,148,136,0.15);' : '' }}">
                @if($plan['popular'])
                <div style="position:absolute;top:-0.75rem;left:50%;transform:translateX(-50%);padding:0.25rem 1rem;background:linear-gradient(135deg,#0d9488,#0891b2);color:white;border-radius:9999px;font-size:0.7rem;font-weight:700;">RECOMENDADO</div>
                @endif
                <div style="font-weight:700;font-size:1rem;">{{ $plan['name'] }}</div>
                <div style="margin-top:0.75rem;">
                    @if($plan['original'])
                    <span style="text-decoration:line-through;color:#9ca3af;font-size:1rem;">${{ $plan['original'] }}</span>
                    @endif
                    <span style="font-size:2.5rem;font-weight:800;">${{ $plan['price'] }}</span>
                    <span style="color:#6b7280;">/mes</span>
                </div>
                @if($founder)
                <div style="font-size:0.75rem;color:#059669;font-weight:600;margin-top:0.25rem;">Precio de fundador</div>
                @endif
                <ul style="margin-top:1rem;list-style:none;padding:0;">
                    @foreach($plan['features'] as $feature)
                    <li style="padding:0.375rem 0;font-size:0.85rem;color:#374151;display:flex;align-items:center;gap:0.5rem;">
                        <svg style="width:16px;height:16px;color:#0d9488;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
                <a href="https://wa.me/526682493398?text={{ urlencode('Hola, quiero contratar el plan ' . $plan['name'] . ' de DocFácil' . ($founder ? ' con precio de fundador' : '') . '. Mi consultorio: ' . ($clinic->name ?? '')) }}"
                    target="_blank"
                    style="display:block;text-align:center;margin-top:1.25rem;padding:0.75rem;background:{{ $plan['popular'] ? 'linear-gradient(135deg,#0d9488,#0891b2)' : '#f3f4f6' }};color:{{ $plan['popular'] ? 'white' : '#374151' }};border-radius:0.75rem;font-weight:700;font-size:0.9rem;text-decoration:none;">
                    Contratar por WhatsApp
                </a>
            </div>
            @endforeach
        </div>

        <div style="text-align:center;margin-top:2rem;font-size:0.8rem;color:#9ca3af;">
            Contactanos por WhatsApp al <a href="https://wa.me/526682493398" target="_blank" style="color:#0d9488;font-weight:600;">668 249 3398</a> para activar tu plan.
            <br>Aceptamos transferencia, tarjeta o efectivo.
        </div>
    </div>
</x-filament-panels::page>
