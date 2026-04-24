<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora gratis: ¿cuánto pierdes al mes en tu consultorio dental? · DocFácil</title>
    <meta name="description" content="Calcula en 1 minuto cuánto dinero pierdes cada mes por citas no confirmadas, papeleo y cobros olvidados. Calculadora interactiva gratis para consultorios dentales en México.">
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <link rel="canonical" href="{{ url('/herramientas/calculadora-consultorio') }}">

    {{-- SEO --}}
    <meta name="keywords" content="calculadora consultorio dental, cuánto gana un dentista, no-shows dental, ingresos consultorio, pérdidas por citas, ROI dentista México">
    <meta name="robots" content="index, follow">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/herramientas/calculadora-consultorio') }}">
    <meta property="og:title" content="¿Cuánto pierdes al mes en tu consultorio dental? Calculadora gratis">
    <meta property="og:description" content="1 minuto, sin registro. Descubre cuánto se te está yendo cada mes por citas no confirmadas, papeleo y cobros olvidados.">
    <meta property="og:image" content="{{ asset('images/og-default.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="¿Cuánto pierdes al mes en tu consultorio? Calculadora gratis">

    {{-- JSON-LD structured data para rich snippets --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "Calculadora ROI Consultorio Dental",
        "url": "{{ url('/herramientas/calculadora-consultorio') }}",
        "applicationCategory": "BusinessApplication",
        "offers": {"@type": "Offer", "price": "0", "priceCurrency": "MXN"},
        "description": "Calculadora gratuita para dentistas en México que calcula pérdidas mensuales por no-shows, papeleo y cobros no recuperados.",
        "publisher": {"@type": "Organization", "name": "DocFácil", "url": "{{ url('/') }}"}
    }
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { background: #f8fafc; }
        body {
            font-family: -apple-system, "Segoe UI", Arial, sans-serif;
            color: #1f2937;
            line-height: 1.55;
        }
        .hero {
            background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%);
            color: white;
            padding: 48px 24px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(ellipse at top, rgba(255,255,255,0.15), transparent 60%);
        }
        .hero-inner { max-width: 720px; margin: 0 auto; position: relative; }
        .hero-kicker {
            display: inline-block;
            padding: 6px 14px;
            background: rgba(255,255,255,0.2);
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        .hero h1 {
            font-size: clamp(24px, 4vw, 38px);
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 10px;
            line-height: 1.15;
        }
        .hero p { font-size: 16px; opacity: 0.95; max-width: 600px; margin: 0 auto; }

        .container { max-width: 960px; margin: -24px auto 0; padding: 0 16px 60px; }
        .card-main {
            background: white;
            border-radius: 20px;
            padding: 32px 28px;
            box-shadow: 0 24px 48px -12px rgba(13, 148, 136, 0.18);
            border: 1px solid rgba(13, 148, 136, 0.08);
        }

        .grid-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-top: 8px;
        }
        @@media (max-width: 820px) { .grid-split { grid-template-columns: 1fr; gap: 20px; } }

        .inputs-section h2, .results-section h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0f766e;
            margin-bottom: 16px;
        }

        .input-row { margin-bottom: 18px; }
        .input-row label {
            display: flex; justify-content: space-between; align-items: baseline;
            font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px;
        }
        .input-row label .value {
            color: #0d9488; font-weight: 700; font-size: 15px;
        }
        .input-row input[type="range"] {
            width: 100%; height: 6px; border-radius: 3px;
            background: #e5e7eb; outline: none; -webkit-appearance: none;
            accent-color: #0d9488;
        }
        .input-row input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none; appearance: none;
            width: 22px; height: 22px; border-radius: 50%;
            background: linear-gradient(135deg, #14b8a6, #0d9488);
            cursor: pointer; border: 3px solid white;
            box-shadow: 0 4px 12px rgba(13,148,136,0.35);
        }
        .input-helper { font-size: 12px; color: #9ca3af; margin-top: 4px; }

        .results-section { display: flex; flex-direction: column; }
        .result-total {
            background: linear-gradient(135deg, #fef2f2, #ffe4e6);
            border: 1px solid #fecaca;
            border-radius: 16px;
            padding: 22px;
            text-align: center;
            margin-bottom: 20px;
        }
        .result-total .label { font-size: 13px; color: #991b1b; font-weight: 600; letter-spacing: 0.02em; text-transform: uppercase; }
        .result-total .amount {
            font-size: clamp(32px, 5vw, 44px);
            font-weight: 800;
            color: #b91c1c;
            margin: 4px 0;
            letter-spacing: -0.02em;
        }
        .result-total .sub { font-size: 12px; color: #7f1d1d; }

        .breakdown { border-top: 1px solid #f3f4f6; padding-top: 18px; }
        .breakdown-row {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 10px;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f9fafb;
        }
        .breakdown-row:last-child { border-bottom: none; }
        .breakdown-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: #fef2f2; color: #dc2626;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .breakdown-label { font-weight: 600; color: #374151; font-size: 14px; }
        .breakdown-sub { font-size: 12px; color: #9ca3af; }
        .breakdown-amount { font-size: 16px; font-weight: 700; color: #dc2626; }

        .cta-section {
            margin-top: 36px;
            padding: 30px 28px;
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1px solid #6ee7b7;
            border-radius: 18px;
            text-align: center;
        }
        .cta-section h3 {
            font-size: 22px; font-weight: 800; color: #064e3b;
            margin-bottom: 8px; letter-spacing: -0.02em;
        }
        .cta-section p { color: #065f46; font-size: 15px; margin-bottom: 20px; max-width: 560px; margin-left: auto; margin-right: auto; }
        .cta-btn {
            display: inline-block;
            background: linear-gradient(135deg, #0d9488, #06b6d4);
            color: white;
            padding: 16px 32px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 12px 24px -6px rgba(13,148,136,0.4);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .cta-btn:hover { transform: translateY(-2px); box-shadow: 0 16px 32px -6px rgba(13,148,136,0.5); }
        .cta-footnote { font-size: 12px; color: #065f46; margin-top: 10px; opacity: 0.8; }

        .share-section {
            margin-top: 24px; text-align: center; font-size: 14px; color: #6b7280;
        }
        .share-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; margin: 4px;
            background: white; border: 1px solid #e5e7eb;
            border-radius: 999px; text-decoration: none; color: #6b7280;
            font-size: 13px; font-weight: 500;
            transition: all 0.15s;
        }
        .share-btn:hover { border-color: #14b8a6; color: #0d9488; transform: translateY(-1px); }

        .trust-section {
            background: white; border-radius: 16px; padding: 24px;
            margin-top: 28px; border: 1px solid #f3f4f6;
        }
        .trust-section h4 { font-size: 15px; font-weight: 700; color: #374151; margin-bottom: 8px; }
        .trust-section p { font-size: 13px; color: #6b7280; line-height: 1.6; }

        .df-footer { text-align: center; margin-top: 32px; padding: 24px 16px; color: #9ca3af; font-size: 13px; }
        .df-footer a { color: #0d9488; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body x-data="calculator()">

<section class="hero">
    <div class="hero-inner">
        <span class="hero-kicker">Calculadora gratis · sin registro</span>
        <h1>¿Cuánto estás perdiendo cada mes en tu consultorio?</h1>
        <p>Un minuto y te muestro dónde se te están yendo los pesos. Todos los cálculos se hacen en tu navegador — tus datos no se guardan ni se mandan a ningún lado.</p>
    </div>
</section>

<div class="container">
    <div class="card-main">
        <div class="grid-split">
            <div class="inputs-section">
                <h2>Tus datos</h2>

                <div class="input-row">
                    <label>
                        Pacientes que ves al mes
                        <span class="value" x-text="patients"></span>
                    </label>
                    <input type="range" min="10" max="200" step="5" x-model.number="patients">
                    <div class="input-helper">Promedio general (todas las sillas)</div>
                </div>

                <div class="input-row">
                    <label>
                        Porcentaje que no llega sin avisar
                        <span class="value" x-text="noShowPct + '%'"></span>
                    </label>
                    <input type="range" min="5" max="45" step="1" x-model.number="noShowPct">
                    <div class="input-helper">15-25% es lo típico en consultorios sin recordatorios</div>
                </div>

                <div class="input-row">
                    <label>
                        Ticket promedio por consulta
                        <span class="value">$<span x-text="avgTicket.toLocaleString('es-MX')"></span></span>
                    </label>
                    <input type="range" min="200" max="3000" step="50" x-model.number="avgTicket">
                    <div class="input-helper">Lo que un paciente deja en una visita típica</div>
                </div>

                <div class="input-row">
                    <label>
                        Horas/semana que gastas en papeleo
                        <span class="value" x-text="paperworkHours + ' hrs'"></span>
                    </label>
                    <input type="range" min="0" max="25" step="1" x-model.number="paperworkHours">
                    <div class="input-helper">Agenda, recetas, cobros, búsqueda de expedientes</div>
                </div>

                <div class="input-row">
                    <label>
                        Valor de tu hora profesional
                        <span class="value">$<span x-text="hourlyRate.toLocaleString('es-MX')"></span></span>
                    </label>
                    <input type="range" min="150" max="1500" step="50" x-model.number="hourlyRate">
                    <div class="input-helper">Lo que podrías estar cobrando en una consulta</div>
                </div>

                <div class="input-row">
                    <label>
                        Cobros que olvidas cobrar al mes
                        <span class="value" x-text="forgottenPct + '%'"></span>
                    </label>
                    <input type="range" min="0" max="20" step="1" x-model.number="forgottenPct">
                    <div class="input-helper">Pacientes que "te pagan después" y nunca regresas</div>
                </div>
            </div>

            <div class="results-section">
                <h2>Lo que pierdes al mes</h2>

                <div class="result-total">
                    <div class="label">Total perdido</div>
                    <div class="amount">~$<span x-text="Math.round(total).toLocaleString('es-MX')"></span></div>
                    <div class="sub">al mes · <span x-text="Math.round(total * 12).toLocaleString('es-MX')"></span> pesos al año</div>
                </div>

                <div class="breakdown">
                    <div class="breakdown-row">
                        <div class="breakdown-icon">🚫</div>
                        <div>
                            <div class="breakdown-label">Citas que no llegan</div>
                            <div class="breakdown-sub"><span x-text="Math.round(missedAppointments)"></span> citas × $<span x-text="avgTicket.toLocaleString('es-MX')"></span></div>
                        </div>
                        <div class="breakdown-amount">$<span x-text="Math.round(lossNoShows).toLocaleString('es-MX')"></span></div>
                    </div>
                    <div class="breakdown-row">
                        <div class="breakdown-icon">📝</div>
                        <div>
                            <div class="breakdown-label">Horas perdidas en papeleo</div>
                            <div class="breakdown-sub"><span x-text="paperworkHours * 4"></span> hrs/mes × $<span x-text="hourlyRate.toLocaleString('es-MX')"></span>/hr</div>
                        </div>
                        <div class="breakdown-amount">$<span x-text="Math.round(lossPaperwork).toLocaleString('es-MX')"></span></div>
                    </div>
                    <div class="breakdown-row">
                        <div class="breakdown-icon">💸</div>
                        <div>
                            <div class="breakdown-label">Cobros que se te olvidan</div>
                            <div class="breakdown-sub"><span x-text="forgottenPct"></span>% × <span x-text="patients"></span> pacientes × $<span x-text="avgTicket.toLocaleString('es-MX')"></span></div>
                        </div>
                        <div class="breakdown-amount">$<span x-text="Math.round(lossForgotten).toLocaleString('es-MX')"></span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cta-section">
            <h3>DocFácil te ayuda a recuperar esto</h3>
            <p>Agenda + recordatorios WhatsApp a 1 clic + expediente digital + recetas PDF + cobros por WhatsApp + recall de pacientes perdidos — desde <strong>$499/mes</strong>. Pagarías menos del 10% de lo que hoy pierdes.</p>
            <a href="{{ url('/dentistas?utm_source=calculadora&utm_medium=tools&utm_campaign=roi_calculator') }}" class="cta-btn">Probar DocFácil 15 días gratis →</a>
            <div class="cta-footnote">Sin tarjeta · sin compromisos · 15 días con todo desbloqueado</div>
        </div>

        <div class="share-section">
            <p style="margin-bottom: 10px;">💡 Compártelo con un colega que siga en libreta:</p>
            <a href="https://wa.me/?text=%C2%BFCu%C3%A1nto%20pierdes%20al%20mes%20en%20tu%20consultorio%3F%20Est%C3%A1%20calculadora%20gratis%20lo%20dice%20en%201%20minuto%3A%20{{ urlencode(url('/herramientas/calculadora-consultorio')) }}" target="_blank" rel="noopener" class="share-btn">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347M12.05 21.785h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884Z"/></svg>
                WhatsApp
            </a>
            <a href="#" x-on:click.prevent="copy()" class="share-btn">
                <span x-show="!copied">📋 Copiar link</span>
                <span x-show="copied" x-cloak>✓ Copiado</span>
            </a>
        </div>

        <div class="trust-section">
            <h4>💡 Cómo hicimos los cálculos</h4>
            <p><strong>Citas que no llegan:</strong> # pacientes × % no-show × ticket promedio. El rango 15-25% de no-shows en consultorios sin recordatorios viene de estudios de citas confirmadas por WhatsApp/SMS.</p>
            <p style="margin-top:8px;"><strong>Papeleo:</strong> horas/semana × 4 semanas × valor de tu hora. Representa el costo de oportunidad — si en vez de buscar expedientes estuvieras atendiendo pacientes.</p>
            <p style="margin-top:8px;"><strong>Cobros olvidados:</strong> % de pacientes × pacientes totales × ticket. Típicamente 3-8% dependiendo de qué tanto registras.</p>
            <p style="margin-top:10px; color:#4b5563;"><strong>Son estimaciones.</strong> Úsalas como punto de partida para pensar tu operación, no como reporte financiero exacto.</p>
        </div>
    </div>

    <div class="df-footer">
        Calculadora gratis de <a href="{{ url('/') }}?utm_source=calculadora&utm_medium=tools&utm_campaign=footer">DocFácil</a> · Software para consultorios dentales en México.
    </div>
</div>

<script>
function calculator() {
    return {
        // Inputs (defaults = consultorio dental típico MX)
        patients: 60,
        noShowPct: 20,
        avgTicket: 600,
        paperworkHours: 8,
        hourlyRate: 400,
        forgottenPct: 4,
        copied: false,

        get missedAppointments() { return this.patients * (this.noShowPct / 100); },
        get lossNoShows() { return this.missedAppointments * this.avgTicket; },
        get lossPaperwork() { return this.paperworkHours * 4 * this.hourlyRate; },
        get lossForgotten() { return this.patients * (this.forgottenPct / 100) * this.avgTicket; },
        get total() { return this.lossNoShows + this.lossPaperwork + this.lossForgotten; },

        copy() {
            navigator.clipboard.writeText(window.location.href);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
    }
}
</script>
</body>
</html>
