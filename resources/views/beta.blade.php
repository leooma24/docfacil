<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Programa Beta — DocFácil</title>
    <meta name="description" content="Únete al programa beta de DocFácil. 6 meses gratis del Plan Profesional a cambio de tu retroalimentación.">
    <meta property="og:title" content="Programa Beta — DocFácil | 6 meses gratis">
    <meta property="og:description" content="Buscamos 5 doctores para probar nuestro software de consultorios GRATIS por 6 meses. Agenda, expedientes, recetas PDF, WhatsApp y más.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-image.png">
    <meta property="og:url" content="https://docfacil.tu-app.co/beta">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DocFácil">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Programa Beta — DocFácil | 6 meses gratis">
    <meta name="twitter:description" content="Software para consultorios médicos y dentales. 6 meses gratis a cambio de tu retroalimentación.">
    <meta name="twitter:image" content="https://docfacil.tu-app.co/images/og-image.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body style="background:linear-gradient(135deg,#f0fdfa 0%,#e0f2fe 50%,#f0fdfa 100%);min-height:100vh;">

    <div style="max-width:960px;margin:0 auto;padding:2rem 1rem;">

        {{-- Header --}}
        <div style="text-align:center;margin-bottom:2rem;">
            <a href="{{ url('/') }}"><img src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil" style="height:3rem;margin-bottom:1rem;"></a>
        </div>

        @if(session('beta_success'))
        <div style="max-width:560px;margin:0 auto;background:white;border-radius:1.5rem;padding:3rem;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.08);">
            <div style="width:80px;height:80px;background:#d1fae5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                <svg style="width:40px;height:40px;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 style="font-size:1.75rem;font-weight:800;color:#111;">Registro exitoso</h2>
            <p style="color:#6b7280;margin-top:0.75rem;line-height:1.6;">Gracias por tu interes en DocFácil. Te contactaremos en menos de 24 horas por WhatsApp para agendar tu llamada de configuracion.</p>
            <a href="{{ url('/') }}" style="display:inline-block;margin-top:1.5rem;padding:0.75rem 2rem;background:#0d9488;color:white;border-radius:0.75rem;text-decoration:none;font-weight:600;">Volver al inicio</a>
        </div>

        @else
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;align-items:start;">

            {{-- Left: Benefits --}}
            <div>
                <div style="display:inline-block;padding:0.375rem 1rem;background:#fef3c7;color:#92400e;border-radius:9999px;font-size:0.8rem;font-weight:700;margin-bottom:1rem;">PROGRAMA BETA — Solo 5 lugares</div>
                <h1 style="font-size:2.25rem;font-weight:800;color:#111;line-height:1.2;">Usa DocFácil<br><span style="color:#0d9488;">6 meses gratis</span></h1>
                <p style="color:#6b7280;margin-top:1rem;line-height:1.6;">Buscamos 5 doctores o dentistas que quieran modernizar su consultorio. A cambio de tu retroalimentacion, te damos acceso completo sin costo.</p>

                <div style="margin-top:2rem;space-y:1rem;">
                    <div style="display:flex;align-items:flex-start;gap:0.75rem;margin-bottom:1rem;">
                        <div style="width:36px;height:36px;background:#d1fae5;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:18px;height:18px;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div style="font-weight:700;color:#111;">Plan Profesional gratis 6 meses</div>
                            <div style="font-size:0.8rem;color:#6b7280;">Valor de $1,794 MXN. Citas ilimitadas, recetas PDF, reportes.</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:flex-start;gap:0.75rem;margin-bottom:1rem;">
                        <div style="width:36px;height:36px;background:#d1fae5;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:18px;height:18px;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div style="font-weight:700;color:#111;">Configuracion personalizada</div>
                            <div style="font-size:0.8rem;color:#6b7280;">Nosotros configuramos tu consultorio, servicios y datos.</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:flex-start;gap:0.75rem;margin-bottom:1rem;">
                        <div style="width:36px;height:36px;background:#d1fae5;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:18px;height:18px;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div style="font-weight:700;color:#111;">Soporte directo por WhatsApp</div>
                            <div style="font-size:0.8rem;color:#6b7280;">Linea directa con el equipo de desarrollo.</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:flex-start;gap:0.75rem;margin-bottom:1rem;">
                        <div style="width:36px;height:36px;background:#d1fae5;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:18px;height:18px;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div style="font-weight:700;color:#111;">Precio de fundador de por vida</div>
                            <div style="font-size:0.8rem;color:#6b7280;">Cuando decidas pagar: $249/mes en vez de $499 (50% descuento permanente).</div>
                        </div>
                    </div>
                </div>

                <div style="margin-top:2rem;padding:1rem;background:white;border-radius:0.75rem;border:1px solid #e5e7eb;">
                    <div style="font-size:0.75rem;color:#6b7280;text-transform:uppercase;font-weight:700;letter-spacing:0.05em;">Lo que te pedimos</div>
                    <ul style="margin-top:0.5rem;font-size:0.85rem;color:#374151;list-style:none;padding:0;">
                        <li style="padding:0.25rem 0;">1. Usar DocFacil minimo 2 semanas con pacientes reales</li>
                        <li style="padding:0.25rem 0;">2. Una llamada de 15 min semanal para feedback</li>
                        <li style="padding:0.25rem 0;">3. Responder una encuesta al final</li>
                        <li style="padding:0.25rem 0;">4. Si te gusta, un testimonial (opcional)</li>
                    </ul>
                </div>
            </div>

            {{-- Right: Form --}}
            <div style="background:white;border-radius:1.5rem;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,0.08);position:sticky;top:2rem;">
                <h2 style="font-size:1.25rem;font-weight:800;margin-bottom:0.25rem;">Solicita tu lugar</h2>
                <p style="font-size:0.8rem;color:#6b7280;margin-bottom:1.5rem;">Solo 5 lugares disponibles. Te contactamos en 24hrs.</p>

                <form action="{{ route('beta.store') }}" method="POST">
                    @csrf
                    <div style="position:absolute;left:-9999px;" aria-hidden="true">
                        <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                    </div>

                    <div style="margin-bottom:1rem;">
                        <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Nombre completo *</label>
                        <input type="text" name="name" required value="{{ old('name') }}" placeholder="Dr. Juan Perez" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1rem;">
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Email *</label>
                            <input type="email" name="email" required value="{{ old('email') }}" placeholder="doctor@email.com" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                        </div>
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">WhatsApp *</label>
                            <input type="tel" name="phone" required value="{{ old('phone') }}" placeholder="55 1234 5678" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1rem;">
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Especialidad</label>
                            <input type="text" name="specialty" value="{{ old('specialty') }}" placeholder="Odontologia, Medicina General..." style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                        </div>
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Ciudad</label>
                            <input type="text" name="city" value="{{ old('city') }}" placeholder="CDMX, Guadalajara..." style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                        </div>
                    </div>
                    <div style="margin-bottom:1.5rem;">
                        <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem;">Nombre del consultorio</label>
                        <input type="text" name="clinic_name" value="{{ old('clinic_name') }}" placeholder="Consultorio Dental Sonrisas" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.875rem;">
                    </div>
                    <button type="submit" style="width:100%;padding:0.875rem;background:linear-gradient(135deg,#0d9488,#0891b2);color:white;border:none;border-radius:0.75rem;font-weight:700;font-size:1rem;cursor:pointer;">
                        Quiero ser beta tester
                    </button>
                    <p style="text-align:center;font-size:0.75rem;color:#9ca3af;margin-top:0.75rem;">Sin compromiso. Sin tarjeta. Te contactamos por WhatsApp.</p>
                </form>
            </div>
        </div>
        @endif
    </div>

</body>
</html>
