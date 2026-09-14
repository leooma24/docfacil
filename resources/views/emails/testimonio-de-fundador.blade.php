<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frase de fundador</title>
</head>
<body style="margin:0;padding:20px;background:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">
<div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;padding:28px 30px;color:#333333;line-height:1.6;font-size:15px;">
    <p style="margin:0;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#b45309;">⭐ Programa Fundador</p>
    <h1 style="margin:6px 0 0;font-size:20px;color:#111827;">{{ $clinic->name }} dejó su frase</h1>

    <div style="margin:18px 0;padding:14px 18px;background:#fffbeb;border-left:4px solid #f59e0b;border-radius:8px;font-size:16px;color:#1f2937;">
        “{{ $clinic->case_study_testimonial }}”
        <div style="margin-top:6px;font-size:14px;color:#6b7280;">— {{ $clinic->testimonio_firma }}</div>
    </div>

    @if ($clinic->testimonio_permiso_at)
        <p style="margin:0;color:#065f46;"><strong>✅ Dio permiso de publicarla</strong> en la página y en anuncios, el {{ $clinic->testimonio_permiso_at->format('d/m/Y \a \l\a\s H:i') }}.</p>
    @else
        <p style="margin:0;color:#991b1b;"><strong>⛔ No dio permiso de publicarla.</strong> Sirve para mejorar el producto; no la uses en la landing ni en anuncios.</p>
    @endif

    <p style="margin:18px 0 0;"><a href="{{ url('/admin/clinics/' . $clinic->id . '/edit') }}" style="color:#0d9488;font-weight:700;">Ver el consultorio en el admin &rarr;</a></p>
</div>
</body>
</html>
