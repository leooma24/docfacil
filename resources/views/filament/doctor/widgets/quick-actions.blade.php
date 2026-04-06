<x-filament-widgets::widget>
    <div style="display:flex;flex-wrap:wrap;gap:0.75rem;">
        <a href="{{ route('filament.doctor.resources.appointments.create') }}" style="flex:1;min-width:140px;display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;background:#f0fdfa;border:1px solid #99f6e4;border-radius:0.75rem;text-decoration:none;">
            <div style="width:36px;height:36px;background:#14b8a6;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:20px;height:20px;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <span style="font-size:0.875rem;font-weight:700;color:#134e4a;">Nueva cita</span>
        </a>
        <a href="{{ route('filament.doctor.resources.patients.create') }}" style="flex:1;min-width:140px;display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:0.75rem;text-decoration:none;">
            <div style="width:36px;height:36px;background:#3b82f6;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:20px;height:20px;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <span style="font-size:0.875rem;font-weight:700;color:#1e3a5f;">Nuevo paciente</span>
        </a>
        <a href="{{ route('filament.doctor.resources.payments.create') }}" style="flex:1;min-width:140px;display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:0.75rem;text-decoration:none;">
            <div style="width:36px;height:36px;background:#10b981;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:20px;height:20px;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
            </div>
            <span style="font-size:0.875rem;font-weight:700;color:#064e3b;">Registrar cobro</span>
        </a>
        <a href="{{ route('filament.doctor.resources.prescriptions.create') }}" style="flex:1;min-width:140px;display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;background:#faf5ff;border:1px solid #d8b4fe;border-radius:0.75rem;text-decoration:none;">
            <div style="width:36px;height:36px;background:#8b5cf6;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:20px;height:20px;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <span style="font-size:0.875rem;font-weight:700;color:#4c1d95;">Nueva receta</span>
        </a>
    </div>
</x-filament-widgets::widget>
