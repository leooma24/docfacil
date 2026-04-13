<x-filament-panels::page>
    <style>
        .sp-hero {
            position: relative; border-radius: 1.5rem; padding: 28px 32px;
            overflow: hidden; color: white; margin-bottom: 24px;
            background: linear-gradient(135deg, #7c3aed 0%, #6366f1 40%, #0ea5e9 100%);
            box-shadow: 0 20px 60px -15px rgba(124,58,237,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
        }
        .sp-hero::before { content:''; position:absolute; top:-80px; right:-60px; width:280px; height:280px; background:radial-gradient(circle,rgba(255,255,255,0.15),transparent 70%); border-radius:50%; }
        .sp-hero::after { content:''; position:absolute; bottom:-100px; left:-40px; width:240px; height:240px; background:radial-gradient(circle,rgba(255,255,255,0.12),transparent 70%); border-radius:50%; }
        .sp-hero-grain { position:absolute; inset:0; background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,0.08) 1px,transparent 0); background-size:20px 20px; }
        .sp-hero-content { position:relative; z-index:1; }
        .sp-hero-title { font-size:1.75rem; font-weight:800; letter-spacing:-0.02em; color:white !important; -webkit-text-fill-color:white !important; }
        .sp-hero-sub { font-size:0.9rem; opacity:0.9; margin-top:4px; }

        .sp-grid { display:grid; grid-template-columns:1fr; gap:20px; }
        @media (min-width:1024px) { .sp-grid { grid-template-columns:1fr 1fr; } }

        .sp-card { background:white; border:1px solid #e5e7eb; border-radius:1.25rem; padding:24px; position:relative; overflow:hidden; }
        .dark .sp-card { background:rgba(15,23,42,0.6); border-color:rgba(94,234,212,0.15); }
        .sp-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; }
        .sp-card-purple::before { background:linear-gradient(90deg,#7c3aed,#a855f7); }
        .sp-card-teal::before { background:linear-gradient(90deg,#0d9488,#06b6d4); }
        .sp-card-amber::before { background:linear-gradient(90deg,#f59e0b,#ef4444); }
        .sp-card-blue::before { background:linear-gradient(90deg,#3b82f6,#0ea5e9); }
        .sp-card-green::before { background:linear-gradient(90deg,#10b981,#059669); }

        .sp-card-title { font-size:1.05rem; font-weight:800; color:#0f172a; letter-spacing:-0.01em; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
        .dark .sp-card-title { color:#f0fdfa; }

        .sp-step { display:flex; gap:12px; padding:12px 0; border-bottom:1px solid #f3f4f6; }
        .sp-step:last-child { border-bottom:none; }
        .sp-step-num { width:28px; height:28px; border-radius:8px; background:#f0fdfa; color:#0d9488; font-weight:800; font-size:0.75rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .sp-step-text { font-size:0.82rem; color:#374151; line-height:1.5; }
        .sp-step-text strong { color:#0f172a; }
        .dark .sp-step-text { color:#d1d5db; }

        .sp-tip { background:#f0fdfa; border:1px solid #ccfbf1; border-radius:10px; padding:12px 14px; font-size:0.8rem; color:#134e4a; margin-top:12px; }
        .sp-tip-warn { background:#fef3c7; border-color:#fde68a; color:#78350f; }

        .sp-price-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f3f4f6; font-size:0.85rem; }
        .sp-price-row:last-child { border-bottom:none; }
        .sp-price-plan { font-weight:700; color:#0f172a; }
        .sp-price-val { font-weight:800; color:#0d9488; }

        .sp-full { grid-column: 1 / -1; }
    </style>

    {{-- HERO --}}
    <div class="sp-hero">
        <div class="sp-hero-grain"></div>
        <div class="sp-hero-content">
            <div style="display:flex;align-items:center;gap:16px;">
                <div style="width:56px;height:56px;border-radius:16px;background:rgba(255,255,255,0.18);backdrop-filter:blur(12px);border:1.5px solid rgba(255,255,255,0.3);display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0;">📖</div>
                <div>
                    <h2 class="sp-hero-title">Guía de Venta DocFácil</h2>
                    <div class="sp-hero-sub">Todo lo que necesitas para cerrar. ICP, precios, script, cadencia y objeciones en un solo lugar.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="sp-grid">
        {{-- ICP --}}
        <div class="sp-card sp-card-teal">
            <div class="sp-card-title">🎯 ¿A quién venderle?</div>
            <div class="sp-step">
                <div class="sp-step-num">1</div>
                <div class="sp-step-text"><strong>Dentista general privado</strong> · 1-2 sillas · 28-45 años · Culiacán/Mazatlán/Los Mochis. Factura $40K-$150K/mes. <strong>Es tu cliente #1.</strong></div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num">2</div>
                <div class="sp-step-text"><strong>Médico general/especialista privado</strong> · Consultorio propio · Mismo perfil pero ciclo más largo. Gancho: recetas PDF + NOM-004.</div>
            </div>
            <div class="sp-tip">💡 NO perseguir: hospitales, IMSS/ISSSTE, doctores 55+ sin asistente, farmacias Similares, consultorios con EHR existente.</div>
        </div>

        {{-- PRECIOS --}}
        <div class="sp-card sp-card-green">
            <div class="sp-card-title">💰 Precios y comisiones</div>
            <div class="sp-price-row">
                <span class="sp-price-plan">Free</span>
                <span style="color:#9ca3af;">$0 · Sin comisión</span>
            </div>
            <div class="sp-price-row">
                <span class="sp-price-plan">Básico</span>
                <span class="sp-price-val">$149/mes · Comisión $447</span>
            </div>
            <div class="sp-price-row">
                <span class="sp-price-plan">Pro ⭐</span>
                <span class="sp-price-val">$299/mes · Comisión $897</span>
            </div>
            <div class="sp-price-row">
                <span class="sp-price-plan">Clínica</span>
                <span class="sp-price-val">$499/mes · Comisión $1,497</span>
            </div>
            <div class="sp-tip">Comisión = 3× mensualidad. 50% al 1er pago + 50% al 2do pago del cliente.</div>
        </div>

        {{-- SCRIPT DE VISITA --}}
        <div class="sp-card sp-card-purple sp-full">
            <div class="sp-card-title">📞 Script de venta (5 minutos)</div>
            <div class="sp-step">
                <div class="sp-step-num">1</div>
                <div class="sp-step-text"><strong>Apertura:</strong> "Hola Dr./Dra., soy [tu nombre] de DocFácil. Ayudamos a consultorios como el suyo a recuperar citas perdidas y organizar todo en un solo lugar. ¿Tiene 3 minutos?"</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num">2</div>
                <div class="sp-step-text"><strong>Pregunta de dolor:</strong> "Doctor, ¿cuántos pacientes a la semana no llegan a su cita sin avisar?" (Espera respuesta — este es el gancho)</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num">3</div>
                <div class="sp-step-text"><strong>Demo rápida:</strong> Muestra en tu celular: 1) Agenda con drag & drop, 2) Recordatorio WhatsApp automático, 3) Receta PDF con cédula, 4) Cobro por WhatsApp</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num">4</div>
                <div class="sp-step-text"><strong>ROI:</strong> "Por $149/mes recupera 8+ citas que antes perdía. Si cada cita vale $500, son $4,000 más al mes. DocFácil se paga 27 veces."</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num">5</div>
                <div class="sp-step-text"><strong>Cierre suave:</strong> "¿Qué tal si lo prueba 14 días gratis? Sin tarjeta, sin compromiso. Se lo configuro ahorita en 2 minutos."</div>
            </div>
        </div>

        {{-- CADENCIA --}}
        <div class="sp-card sp-card-blue">
            <div class="sp-card-title">🔄 Cadencia de contacto</div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#dbeafe;color:#2563eb;">D1</div>
                <div class="sp-step-text"><strong>Primer contacto.</strong> WhatsApp o visita presencial. Pregunta de dolor + ofrece demo.</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#dbeafe;color:#2563eb;">D3</div>
                <div class="sp-step-text"><strong>Follow-up.</strong> Dato ROI: "Doctores recuperan 8-12 citas/mes". Link demo.</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#dbeafe;color:#2563eb;">D7</div>
                <div class="sp-step-text"><strong>Último intento.</strong> "No quiero ser molesto. Le dejo el acceso gratuito." Link trial.</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#fef3c7;color:#92400e;">D14</div>
                <div class="sp-step-text"><strong>Reactivación.</strong> "Desde entonces agregamos funciones. Sigo disponible."</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#fee2e2;color:#991b1b;">D30</div>
                <div class="sp-step-text"><strong>Breakup.</strong> "Si algún día necesita organizar su consultorio, aquí estamos." Último contacto.</div>
            </div>
        </div>

        {{-- TOP OBJECIONES --}}
        <div class="sp-card sp-card-amber">
            <div class="sp-card-title">🛡️ Top 5 objeciones</div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#fef3c7;color:#92400e;">1</div>
                <div class="sp-step-text"><strong>"Está caro"</strong> → "¿Cuánto cobra por consulta? $500? DocFácil cuesta menos de 1 consulta y le recupera 8+ al mes."</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#fef3c7;color:#92400e;">2</div>
                <div class="sp-step-text"><strong>"No soy tecnológico"</strong> → "Si usa WhatsApp, sabe usar DocFácil. Lo acompañamos en la configuración."</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#fef3c7;color:#92400e;">3</div>
                <div class="sp-step-text"><strong>"Ya tengo sistema"</strong> → "¿Le manda WhatsApp automático? ¿Genera recetas PDF? Pruébelo 14 días sin cancelar lo actual."</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#fef3c7;color:#92400e;">4</div>
                <div class="sp-step-text"><strong>"Necesito pensarlo"</strong> → "Le activo la prueba gratuita para que lo explore. Sin tarjeta. ¿Le parece?"</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#fef3c7;color:#92400e;">5</div>
                <div class="sp-step-text"><strong>"Ahorita no"</strong> → "Cada semana sin recordatorios son 3-5 citas perdidas. La prueba es gratis y se configura en 10 min."</div>
            </div>
            <div class="sp-tip sp-tip-warn">⚡ Todas las objeciones están en el botón "Objeciones" de cada prospecto en el listado.</div>
        </div>

        {{-- TIPS --}}
        <div class="sp-card sp-card-teal sp-full">
            <div class="sp-card-title">⭐ Tips del vendedor estrella</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px;">
                <div class="sp-tip">📱 <strong>Mejor hora WhatsApp:</strong> Mar-Jue 1:00-2:30 PM (hora de comida entre pacientes)</div>
                <div class="sp-tip">🚪 <strong>Visita presencial:</strong> Habla primero con la secretaria. Si te ganas a ella, te ganas al doctor.</div>
                <div class="sp-tip">🎯 <strong>Demo killer:</strong> Muestra el recordatorio WhatsApp EN VIVO. Manda uno al doctor ahí mismo.</div>
                <div class="sp-tip">💰 <strong>Nunca digas el precio primero.</strong> Primero el dolor, luego la demo, y al final "son $149/mes".</div>
                <div class="sp-tip">🔄 <strong>Regla de 3:</strong> Si no responde al 3er mensaje (Día 7), espera 7 días más. No insistas antes.</div>
                <div class="sp-tip">📊 <strong>Meta diaria:</strong> 5 contactos nuevos + 3 follow-ups = 8 actividades. Haz esto y llegas a 50 en 12 semanas.</div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
