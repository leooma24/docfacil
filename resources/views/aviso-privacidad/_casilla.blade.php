{{--
    Aviso de privacidad simplificado (art. 16) y consentimiento expreso para
    datos de salud (art. 8) de la ley de datos personales de 2025. Va en todo
    formulario público donde el paciente escribe sus datos.
--}}
<div style="margin-top:18px;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;font-size:12.5px;line-height:1.5;color:#475569;">
    <strong style="color:#0f172a;">{{ $clinic->name }}</strong> usa tus datos, incluidos los de salud, para atenderte, llevar tu expediente y recordarte tus citas. No los usa para publicidad.
    <a href="{{ route('aviso-privacidad.show', $clinic->slug) }}" target="_blank" rel="noopener" style="color:#0d9488;font-weight:600;">Lee el aviso de privacidad completo</a>.

    <label style="display:flex;gap:10px;align-items:flex-start;margin:10px 0 0;font-size:13px;font-weight:600;color:#1f2937;cursor:pointer;">
        <input type="checkbox" name="acepta_aviso" value="1" required {{ old('acepta_aviso') ? 'checked' : '' }}
               style="width:18px;height:18px;min-width:18px;padding:0;margin:1px 0 0;accent-color:#0d9488;">
        <span>Acepto que {{ $clinic->name }} trate mis datos, incluidos los de salud, como dice el aviso de privacidad.</span>
    </label>
    @error('acepta_aviso')
        <div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>
    @enderror
</div>
