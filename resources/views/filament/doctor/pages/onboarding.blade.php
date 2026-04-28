<x-filament-panels::page>
<div style="max-width:680px;margin:0 auto;">

    {{-- Progress bar (5 steps) --}}
    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:2rem;">
        @for($i = 1; $i <= 5; $i++)
        <div style="flex:1;height:6px;border-radius:3px;background:{{ $step >= $i ? '#14b8a6' : '#e5e7eb' }};transition:all 0.3s;"></div>
        @endfor
    </div>

    {{-- Step 1: Clinic --}}
    @if($step === 1)
    <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1.25rem;padding:2rem;">
        <div style="text-align:center;margin-bottom:1.5rem;">
            <div style="width:56px;height:56px;background:#ccfbf1;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                <svg style="width:28px;height:28px;color:#0d9488;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <h2 style="font-size:1.5rem;font-weight:800;">Tu consultorio</h2>
            <p style="color:#6b7280;font-size:0.875rem;">Paso 1 de 5 — Datos básicos de tu consultorio</p>
        </div>
        <div style="display:grid;gap:1rem;">
            <div>
                <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Nombre del consultorio *</label>
                <input type="text" wire:model="clinic_name" placeholder="Consultorio Dental Sonrisas" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Teléfono</label>
                    <input type="tel" wire:model="clinic_phone" placeholder="55 1234 5678" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Ciudad</label>
                    <input type="text" wire:model="clinic_city" placeholder="CDMX" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                </div>
            </div>
            <div>
                <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Dirección</label>
                <input type="text" wire:model="clinic_address" placeholder="Av. Reforma 100, Col. Centro" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
            </div>

            {{-- Logo upload --}}
            <div style="margin-top:0.5rem;padding-top:1rem;border-top:1px dashed #e5e7eb;">
                <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.5rem;">Logo del consultorio <span style="font-weight:400;color:#9ca3af;font-size:0.7rem;">(opcional · se imprime en recetas y aparece en el portal)</span></label>
                <div style="display:flex;align-items:center;gap:1rem;">
                    <div style="width:64px;height:64px;border-radius:50%;border:2px dashed #d1d5db;background:#f9fafb;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                        @if($logo && method_exists($logo, 'temporaryUrl'))
                            <img src="{{ $logo->temporaryUrl() }}" alt="Logo" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <svg style="width:24px;height:24px;color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @endif
                    </div>
                    <div style="flex:1;min-width:0;">
                        <input type="file" wire:model="logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml" style="font-size:0.8rem;width:100%;">
                        <div wire:loading wire:target="logo" style="font-size:0.7rem;color:#0d9488;margin-top:0.25rem;">Subiendo…</div>
                        @error('logo')<div style="font-size:0.7rem;color:#dc2626;margin-top:0.25rem;">{{ $message }}</div>@enderror
                        @if($logo && method_exists($logo, 'temporaryUrl'))
                            <button type="button" wire:click="$set('logo', null)" style="font-size:0.7rem;color:#dc2626;background:none;border:none;cursor:pointer;margin-top:0.25rem;padding:0;text-decoration:underline;">Quitar</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Step 2: Doctor profile --}}
    @if($step === 2)
    <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1.25rem;padding:2rem;">
        <div style="text-align:center;margin-bottom:1.5rem;">
            <div style="width:56px;height:56px;background:#dbeafe;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                <svg style="width:28px;height:28px;color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h2 style="font-size:1.5rem;font-weight:800;">Tu perfil profesional</h2>
            <p style="color:#6b7280;font-size:0.875rem;">Paso 2 de 5 — Esta información aparece en tus recetas</p>
        </div>
        <div style="display:grid;gap:1rem;">
            <div>
                <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Especialidad</label>
                <input type="text" wire:model.live.debounce.300ms="specialty" placeholder="Ej: Odontología General, Ortodoncia, Implantología..." style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                <p style="margin-top:0.375rem;font-size:0.7rem;color:#6b7280;">Sugerimos servicios según tu especialidad en el siguiente paso.</p>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Cédula profesional</label>
                    <input type="text" wire:model="license_number" placeholder="12345678" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Tu celular</label>
                    <input type="tel" wire:model="doctor_phone" placeholder="55 9876 5432" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Step 3: Services with smart suggestions --}}
    @if($step === 3)
    <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1.25rem;padding:2rem;">
        <div style="text-align:center;margin-bottom:1.5rem;">
            <div style="width:56px;height:56px;background:#fef3c7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                <svg style="width:28px;height:28px;color:#f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h2 style="font-size:1.5rem;font-weight:800;">Tus servicios</h2>
            <p style="color:#6b7280;font-size:0.875rem;">Paso 3 de 5 — @if($suggestions_loaded)Pre-cargamos servicios típicos de tu especialidad. Edita o elimina los que no apliquen.@else Agrega los servicios que ofreces.@endif</p>
        </div>

        @if($suggestions_loaded && count($quick_services) > 0)
        <div style="background:#f0fdfa;border:1px solid #99f6e4;border-radius:0.75rem;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.8rem;color:#0f766e;">
            ✨ Te dejamos {{ count($quick_services) }} servicios pre-cargados. Edita precios y duración a tu gusto.
        </div>
        @endif

        @foreach($quick_services as $i => $svc)
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:0.5rem;margin-bottom:0.75rem;align-items:end;">
            <div>
                @if($i === 0)<label style="display:block;font-size:0.75rem;font-weight:600;margin-bottom:0.25rem;">Servicio</label>@endif
                <input type="text" wire:model="quick_services.{{ $i }}.name" placeholder="Ej: Limpieza dental" style="width:100%;padding:0.625rem;border:1px solid #d1d5db;border-radius:0.5rem;font-size:0.85rem;">
            </div>
            <div>
                @if($i === 0)<label style="display:block;font-size:0.75rem;font-weight:600;margin-bottom:0.25rem;">Precio (MXN)</label>@endif
                <input type="number" wire:model="quick_services.{{ $i }}.price" placeholder="400" style="width:100%;padding:0.625rem;border:1px solid #d1d5db;border-radius:0.5rem;font-size:0.85rem;">
            </div>
            <div>
                @if($i === 0)<label style="display:block;font-size:0.75rem;font-weight:600;margin-bottom:0.25rem;">Minutos</label>@endif
                <input type="number" wire:model="quick_services.{{ $i }}.duration" placeholder="30" style="width:100%;padding:0.625rem;border:1px solid #d1d5db;border-radius:0.5rem;font-size:0.85rem;">
            </div>
            <button type="button" wire:click="removeService({{ $i }})" style="padding:0.625rem;background:#fde2e2;border:none;border-radius:0.5rem;cursor:pointer;color:#dc2626;font-size:1rem;">✕</button>
        </div>
        @endforeach

        <button type="button" wire:click="addService" style="width:100%;padding:0.75rem;border:2px dashed #d1d5db;border-radius:0.75rem;background:none;color:#6b7280;font-weight:600;font-size:0.85rem;cursor:pointer;margin-top:0.5rem;">
            + Agregar otro servicio
        </button>

        @if(empty($quick_services))
        <p style="text-align:center;color:#9ca3af;font-size:0.8rem;margin-top:1rem;">Puedes agregar servicios ahora o después desde el menú "Servicios".</p>
        @endif
    </div>
    @endif

    {{-- Step 4: Add-ons opcionales --}}
    @if($step === 4)
    <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1.25rem;padding:2rem;">
        <div style="text-align:center;margin-bottom:1.5rem;">
            <div style="width:56px;height:56px;background:#fae8ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                <svg style="width:28px;height:28px;color:#7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
            </div>
            <h2 style="font-size:1.5rem;font-weight:800;">Add-ons opcionales</h2>
            <p style="color:#6b7280;font-size:0.875rem;">Paso 4 de 5 — Activa los que te interesen con <strong>30 días gratis</strong>. Cancelas cuando quieras.</p>
        </div>

        <div style="display:grid;gap:0.75rem;">
            @foreach($this->availableAddons as $addon)
            @php $isSelected = in_array($addon['slug'], $addons_activate); @endphp
            <div wire:click="toggleAddon('{{ $addon['slug'] }}')" style="cursor:pointer;border:2px solid {{ $isSelected ? '#7c3aed' : '#e5e7eb' }};border-radius:1rem;padding:1.25rem;transition:all 0.15s;background:{{ $isSelected ? '#fdf4ff' : 'white' }};">
                <div style="display:flex;align-items:start;justify-content:space-between;gap:0.75rem;">
                    <div style="display:flex;align-items:start;gap:0.75rem;flex:1;min-width:0;">
                        <div style="font-size:1.75rem;flex-shrink:0;">{{ $addon['icon'] }}</div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                                <h4 style="font-weight:700;font-size:0.95rem;">{{ $addon['name'] }}</h4>
                                <span style="font-size:0.7rem;font-weight:700;padding:0.15rem 0.5rem;border-radius:9999px;background:#f0fdfa;color:#0d9488;">${{ number_format($addon['monthly_price'], 0) }}/mes</span>
                            </div>
                            <p style="color:#6b7280;font-size:0.8rem;margin-top:0.25rem;line-height:1.4;">{{ $addon['short_description'] }}</p>
                            <p style="color:#15803d;font-size:0.75rem;margin-top:0.375rem;font-weight:600;">💰 {{ $addon['revenue_hypothesis'] }}</p>
                        </div>
                    </div>
                    <div style="flex-shrink:0;width:24px;height:24px;border-radius:50%;border:2px solid {{ $isSelected ? '#7c3aed' : '#d1d5db' }};background:{{ $isSelected ? '#7c3aed' : 'white' }};display:flex;align-items:center;justify-content:center;">
                        @if($isSelected)<svg style="width:14px;height:14px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>@endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <p style="margin-top:1rem;text-align:center;font-size:0.75rem;color:#9ca3af;">
            @if(count($addons_activate) === 0)Selecciona los que te interesen, o sigue sin activar ninguno.
            @else{{ count($addons_activate) }} add-on{{ count($addons_activate) === 1 ? '' : 's' }} seleccionado{{ count($addons_activate) === 1 ? '' : 's' }} · 30 días gratis cada uno
            @endif
        </p>
    </div>
    @endif

    {{-- Step 5: Done --}}
    @if($step === 5)
    <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1.25rem;padding:2rem;text-align:center;">
        <div style="width:72px;height:72px;background:#d1fae5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
            <svg style="width:36px;height:36px;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h2 style="font-size:1.5rem;font-weight:800;">¡Todo listo!</h2>
        <p style="color:#6b7280;font-size:0.9rem;margin-top:0.5rem;max-width:400px;margin-left:auto;margin-right:auto;">
            Tu consultorio está configurado. Ya puedes empezar a atender pacientes.
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-top:1.5rem;max-width:440px;margin-left:auto;margin-right:auto;text-align:left;">
            <div style="padding:1rem;background:#f0fdfa;border-radius:0.75rem;">
                <div style="font-weight:700;font-size:0.9rem;">{{ $clinic_name ?: 'Tu consultorio' }}</div>
                <div style="font-size:0.75rem;color:#6b7280;">{{ $clinic_city ?: 'Sin ciudad' }}</div>
            </div>
            <div style="padding:1rem;background:#eff6ff;border-radius:0.75rem;">
                <div style="font-weight:700;font-size:0.9rem;">{{ $specialty ?: 'Sin especialidad' }}</div>
                <div style="font-size:0.75rem;color:#6b7280;">Céd. {{ $license_number ?: '—' }}</div>
            </div>
        </div>

        <div style="display:flex;gap:0.5rem;justify-content:center;flex-wrap:wrap;margin-top:1rem;font-size:0.8rem;color:#6b7280;">
            @if(count(array_filter($quick_services, fn($s) => !empty($s['name']) && !empty($s['price']))) > 0)
            <span style="background:#fef3c7;color:#92400e;padding:0.25rem 0.625rem;border-radius:9999px;font-weight:600;">{{ count(array_filter($quick_services, fn($s) => !empty($s['name']) && !empty($s['price']))) }} servicios</span>
            @endif
            @if(count($addons_activate) > 0)
            <span style="background:#fae8ff;color:#7c3aed;padding:0.25rem 0.625rem;border-radius:9999px;font-weight:600;">{{ count($addons_activate) }} add-on{{ count($addons_activate) === 1 ? '' : 's' }} activos</span>
            @endif
        </div>

        @if($this->portalUrl)
        <div style="margin-top:1.75rem;padding:1rem 1.25rem;background:linear-gradient(135deg,#ecfeff,#f0fdfa);border:1px solid #99f6e4;border-radius:1rem;text-align:left;max-width:480px;margin-left:auto;margin-right:auto;">
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">
                <span style="font-size:1.25rem;">🌐</span>
                <span style="font-weight:700;font-size:0.9rem;color:#0f766e;">Tu portal de agendamiento ya está vivo</span>
            </div>
            <div style="background:white;padding:0.6rem 0.85rem;border-radius:0.625rem;border:1px solid #99f6e4;font-size:0.78rem;color:#0d9488;font-family:monospace;word-break:break-all;">{{ $this->portalUrl }}</div>
            <div style="display:flex;gap:0.5rem;margin-top:0.625rem;flex-wrap:wrap;">
                <a href="{{ $this->portalUrl }}" target="_blank" style="font-size:0.75rem;color:#0d9488;text-decoration:none;font-weight:600;padding:0.3rem 0.6rem;background:white;border-radius:0.375rem;border:1px solid #99f6e4;">Abrir portal ↗</a>
                <a href="https://wa.me/?text={{ urlencode('Agenda tu cita conmigo aquí: ' . $this->portalUrl) }}" target="_blank" style="font-size:0.75rem;color:#16a34a;text-decoration:none;font-weight:600;padding:0.3rem 0.6rem;background:white;border-radius:0.375rem;border:1px solid #bbf7d0;">Compartir por WhatsApp</a>
            </div>
            <p style="font-size:0.7rem;color:#6b7280;margin-top:0.5rem;">Tus pacientes pueden agendar sin descargar nada. Compártelo en tu Instagram, ficha de Google, o por WhatsApp.</p>
        </div>
        @endif

        <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid #f3f4f6;text-align:left;max-width:440px;margin-left:auto;margin-right:auto;">
            <p style="font-size:0.85rem;font-weight:700;margin-bottom:0.5rem;">💡 Para terminar tu marca:</p>
            <ul style="font-size:0.8rem;color:#6b7280;list-style:none;padding:0;margin:0;display:grid;gap:0.375rem;">
                @if(!$logo)
                <li>→ Agrega tu logo desde <strong>Mi cuenta › Configuración</strong></li>
                @endif
                <li>→ Conecta tu link de Google para activar reseñas automáticas</li>
                <li>→ Comparte tu QR de check-in en recepción</li>
            </ul>
        </div>
    </div>
    @endif

    {{-- Navigation --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1.5rem;">
        <div>
            @if($step > 1)
            <button type="button" wire:click="prevStep" style="padding:0.625rem 1.5rem;background:#f3f4f6;color:#374151;border:none;border-radius:0.75rem;font-weight:600;font-size:0.875rem;cursor:pointer;">
                ← Anterior
            </button>
            @else
            <button type="button" wire:click="skipOnboarding" style="padding:0.625rem 1.5rem;background:none;border:none;color:#9ca3af;font-size:0.8rem;cursor:pointer;text-decoration:underline;">
                Saltar configuración
            </button>
            @endif
        </div>
        <div>
            @if($step < 5)
            <button type="button" wire:click="nextStep" style="padding:0.625rem 1.5rem;background:#0d9488;color:white;border:none;border-radius:0.75rem;font-weight:600;font-size:0.875rem;cursor:pointer;">
                Siguiente →
            </button>
            @else
            <button type="button" wire:click="completeOnboarding" style="padding:0.75rem 2rem;background:linear-gradient(135deg,#0d9488,#0891b2);color:white;border:none;border-radius:0.75rem;font-weight:700;font-size:0.9rem;cursor:pointer;">
                Empezar a usar DocFácil
            </button>
            @endif
        </div>
    </div>
</div>
</x-filament-panels::page>
