<x-filament-panels::page>
<div style="max-width:640px;margin:0 auto;">

    {{-- Progress bar --}}
    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:2rem;">
        @for($i = 1; $i <= 4; $i++)
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
            <p style="color:#6b7280;font-size:0.875rem;">Paso 1 de 4 — Datos basicos de tu consultorio</p>
        </div>
        <div style="display:grid;gap:1rem;">
            <div>
                <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Nombre del consultorio *</label>
                <input type="text" wire:model="clinic_name" placeholder="Consultorio Dental Sonrisas" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Telefono</label>
                    <input type="tel" wire:model="clinic_phone" placeholder="55 1234 5678" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Ciudad</label>
                    <input type="text" wire:model="clinic_city" placeholder="CDMX" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                </div>
            </div>
            <div>
                <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Direccion</label>
                <input type="text" wire:model="clinic_address" placeholder="Av. Reforma 100, Col. Centro" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
            </div>
        </div>
    </div>
    @endif

    {{-- Step 2: Doctor --}}
    @if($step === 2)
    <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1.25rem;padding:2rem;">
        <div style="text-align:center;margin-bottom:1.5rem;">
            <div style="width:56px;height:56px;background:#dbeafe;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                <svg style="width:28px;height:28px;color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h2 style="font-size:1.5rem;font-weight:800;">Tu perfil medico</h2>
            <p style="color:#6b7280;font-size:0.875rem;">Paso 2 de 4 — Esta informacion aparece en tus recetas</p>
        </div>
        <div style="display:grid;gap:1rem;">
            <div>
                <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Especialidad</label>
                <input type="text" wire:model="specialty" placeholder="Odontologia General, Medicina General..." style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Cedula profesional</label>
                    <input type="text" wire:model="license_number" placeholder="CED-12345678" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Telefono personal</label>
                    <input type="tel" wire:model="doctor_phone" placeholder="55 9876 5432" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Step 3: Services --}}
    @if($step === 3)
    <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1.25rem;padding:2rem;">
        <div style="text-align:center;margin-bottom:1.5rem;">
            <div style="width:56px;height:56px;background:#fef3c7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                <svg style="width:28px;height:28px;color:#f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h2 style="font-size:1.5rem;font-weight:800;">Tus servicios</h2>
            <p style="color:#6b7280;font-size:0.875rem;">Paso 3 de 4 — Agrega los servicios que ofreces con sus precios</p>
        </div>

        @foreach($quick_services as $i => $svc)
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:0.5rem;margin-bottom:0.75rem;align-items:end;">
            <div>
                @if($i === 0)<label style="display:block;font-size:0.75rem;font-weight:600;margin-bottom:0.25rem;">Servicio</label>@endif
                <input type="text" wire:model="quick_services.{{ $i }}.name" placeholder="Ej: Consulta general" style="width:100%;padding:0.625rem;border:1px solid #d1d5db;border-radius:0.5rem;font-size:0.85rem;">
            </div>
            <div>
                @if($i === 0)<label style="display:block;font-size:0.75rem;font-weight:600;margin-bottom:0.25rem;">Precio</label>@endif
                <input type="number" wire:model="quick_services.{{ $i }}.price" placeholder="300" style="width:100%;padding:0.625rem;border:1px solid #d1d5db;border-radius:0.5rem;font-size:0.85rem;">
            </div>
            <div>
                @if($i === 0)<label style="display:block;font-size:0.75rem;font-weight:600;margin-bottom:0.25rem;">Minutos</label>@endif
                <input type="number" wire:model="quick_services.{{ $i }}.duration" placeholder="30" style="width:100%;padding:0.625rem;border:1px solid #d1d5db;border-radius:0.5rem;font-size:0.85rem;">
            </div>
            <button wire:click="removeService({{ $i }})" style="padding:0.625rem;background:#fde2e2;border:none;border-radius:0.5rem;cursor:pointer;color:#dc2626;font-size:1rem;">x</button>
        </div>
        @endforeach

        <button wire:click="addService" style="width:100%;padding:0.75rem;border:2px dashed #d1d5db;border-radius:0.75rem;background:none;color:#6b7280;font-weight:600;font-size:0.85rem;cursor:pointer;margin-top:0.5rem;">
            + Agregar servicio
        </button>

        @if(empty($quick_services))
        <p style="text-align:center;color:#9ca3af;font-size:0.8rem;margin-top:1rem;">Puedes agregar servicios despues si prefieres.</p>
        @endif
    </div>
    @endif

    {{-- Step 4: Done --}}
    @if($step === 4)
    <div class="bg-white dark:bg-gray-800" style="border:1px solid #e5e7eb;border-radius:1.25rem;padding:2rem;text-align:center;">
        <div style="width:72px;height:72px;background:#d1fae5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
            <svg style="width:36px;height:36px;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h2 style="font-size:1.5rem;font-weight:800;">Todo listo!</h2>
        <p style="color:#6b7280;font-size:0.9rem;margin-top:0.5rem;max-width:400px;margin-left:auto;margin-right:auto;">
            Tu consultorio esta configurado. Ya puedes empezar a atender pacientes con DocFacil.
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-top:2rem;max-width:400px;margin-left:auto;margin-right:auto;text-align:left;">
            <div style="padding:1rem;background:#f0fdfa;border-radius:0.75rem;">
                <div style="font-weight:700;font-size:0.9rem;">{{ $clinic_name ?: 'Tu consultorio' }}</div>
                <div style="font-size:0.75rem;color:#6b7280;">{{ $clinic_city ?: 'Sin ciudad' }}</div>
            </div>
            <div style="padding:1rem;background:#eff6ff;border-radius:0.75rem;">
                <div style="font-weight:700;font-size:0.9rem;">{{ $specialty ?: 'Sin especialidad' }}</div>
                <div style="font-size:0.75rem;color:#6b7280;">{{ $license_number ?: 'Sin cedula' }}</div>
            </div>
        </div>

        @if(count($quick_services) > 0)
        <div style="margin-top:1rem;font-size:0.8rem;color:#6b7280;">{{ count($quick_services) }} servicios configurados</div>
        @endif
    </div>
    @endif

    {{-- Navigation --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1.5rem;">
        <div>
            @if($step > 1)
            <button wire:click="prevStep" style="padding:0.625rem 1.5rem;background:#f3f4f6;color:#374151;border:none;border-radius:0.75rem;font-weight:600;font-size:0.875rem;cursor:pointer;">
                &larr; Anterior
            </button>
            @else
            <button wire:click="skipOnboarding" style="padding:0.625rem 1.5rem;background:none;border:none;color:#9ca3af;font-size:0.8rem;cursor:pointer;text-decoration:underline;">
                Saltar configuracion
            </button>
            @endif
        </div>
        <div>
            @if($step < 4)
            <button wire:click="nextStep" style="padding:0.625rem 1.5rem;background:#0d9488;color:white;border:none;border-radius:0.75rem;font-weight:600;font-size:0.875rem;cursor:pointer;">
                Siguiente &rarr;
            </button>
            @else
            <button wire:click="completeOnboarding" style="padding:0.75rem 2rem;background:linear-gradient(135deg,#0d9488,#0891b2);color:white;border:none;border-radius:0.75rem;font-weight:700;font-size:0.9rem;cursor:pointer;">
                Empezar a usar DocFacil
            </button>
            @endif
        </div>
    </div>
</div>
</x-filament-panels::page>
