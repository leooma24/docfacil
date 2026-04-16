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
                <span class="sp-price-val">$499/mes · $4,990/año · Comisión $1,497</span>
            </div>
            <div class="sp-price-row">
                <span class="sp-price-plan">Pro ⭐</span>
                <span class="sp-price-val">$999/mes · $9,990/año · Comisión $2,997</span>
            </div>
            <div class="sp-price-row">
                <span class="sp-price-plan">Clínica</span>
                <span class="sp-price-val">$1,999/mes · $19,990/año · Comisión $5,997</span>
            </div>
            <div class="sp-tip">
                <strong>Comisión = 3× mensualidad</strong> (igual para mensual o anual).<br>
                🟢 <strong>Si vende anual:</strong> recibes el 100% de la comisión en <strong>un solo pago</strong>.<br>
                🔵 <strong>Si vende mensual:</strong> 50% al 1er pago + 50% al 2do pago del cliente.<br>
                💡 <strong>Anual te conviene</strong>: cobras toda la comisión de golpe y el cliente se queda 12 meses asegurados.
            </div>
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
                <div class="sp-step-text"><strong>Demo rápida:</strong> Muestra en tu celular: 1) Agenda con drag & drop, 2) Recordatorio WhatsApp (auto + 1 clic), 3) Receta PDF con cédula, 4) Cobro por WhatsApp</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num">4</div>
                <div class="sp-step-text"><strong>ROI:</strong> "Por $499/mes recupera 8+ citas que antes perdía. Si cada cita vale $600, son $4,800 más al mes. DocFácil se paga 9 veces + tiempo que ahorras."</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num">5</div>
                <div class="sp-step-text"><strong>Cierre suave:</strong> "¿Qué tal si lo prueba 15 días gratis? Sin tarjeta, sin compromiso. Y tenemos garantía de 30 días: si no ve resultados le devolvemos su dinero."</div>
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

        {{-- SECUENCIAS POR TIPO --}}
        <div class="sp-card sp-card-purple sp-full">
            <div class="sp-card-title">📬 Secuencias de contacto por tipo</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;">
                <div style="background:#f5f3ff;border:1px solid #e9d5ff;border-radius:12px;padding:16px;">
                    <div style="font-weight:800;color:#6d28d9;font-size:0.9rem;margin-bottom:8px;">❄️ Contacto frío (no te conoce)</div>
                    <div class="sp-step" style="border:none;padding:4px 0;">
                        <div class="sp-step-num" style="background:#ede9fe;color:#6d28d9;width:24px;height:24px;font-size:0.65rem;">D1</div>
                        <div class="sp-step-text">Pregunta de dolor + ofrece demo. <strong>No vendas aún.</strong></div>
                    </div>
                    <div class="sp-step" style="border:none;padding:4px 0;">
                        <div class="sp-step-num" style="background:#ede9fe;color:#6d28d9;width:24px;height:24px;font-size:0.65rem;">D3</div>
                        <div class="sp-step-text">Dato ROI (8-12 citas recuperadas = $4,000+/mes).</div>
                    </div>
                    <div class="sp-step" style="border:none;padding:4px 0;">
                        <div class="sp-step-num" style="background:#ede9fe;color:#6d28d9;width:24px;height:24px;font-size:0.65rem;">D7</div>
                        <div class="sp-step-text">"Último mensaje. Le dejo link." No insistas más.</div>
                    </div>
                </div>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px;">
                    <div style="font-weight:800;color:#15803d;font-size:0.9rem;margin-bottom:8px;">🤝 Contacto cálido (ya te conoce)</div>
                    <div class="sp-step" style="border:none;padding:4px 0;">
                        <div class="sp-step-num" style="background:#dcfce7;color:#15803d;width:24px;height:24px;font-size:0.65rem;">D1</div>
                        <div class="sp-step-text">"Nos vimos en [lugar/evento], le mostré DocFácil. ¿Le gustó?"</div>
                    </div>
                    <div class="sp-step" style="border:none;padding:4px 0;">
                        <div class="sp-step-num" style="background:#dcfce7;color:#15803d;width:24px;height:24px;font-size:0.65rem;">D4</div>
                        <div class="sp-step-text">Comparte caso de éxito o ROI concreto de un doctor similar.</div>
                    </div>
                    <div class="sp-step" style="border:none;padding:4px 0;">
                        <div class="sp-step-num" style="background:#dcfce7;color:#15803d;width:24px;height:24px;font-size:0.65rem;">D8</div>
                        <div class="sp-step-text">Oferta directa: "¿Agendamos 15 min para configurarlo juntos?"</div>
                    </div>
                </div>
                <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:12px;padding:16px;">
                    <div style="font-weight:800;color:#92400e;font-size:0.9rem;margin-bottom:8px;">🔄 Referido (te lo recomendaron)</div>
                    <div class="sp-step" style="border:none;padding:4px 0;">
                        <div class="sp-step-num" style="background:#fef9c3;color:#92400e;width:24px;height:24px;font-size:0.65rem;">D1</div>
                        <div class="sp-step-text">"El Dr. [nombre] me sugirió contactarlo. Usa DocFácil y le va muy bien."</div>
                    </div>
                    <div class="sp-step" style="border:none;padding:4px 0;">
                        <div class="sp-step-num" style="background:#fef9c3;color:#92400e;width:24px;height:24px;font-size:0.65rem;">D4</div>
                        <div class="sp-step-text">Follow-up con demo o link de registro directo.</div>
                    </div>
                    <div class="sp-step" style="border:none;padding:4px 0;">
                        <div class="sp-step-num" style="background:#fef9c3;color:#92400e;width:24px;height:24px;font-size:0.65rem;">D10</div>
                        <div class="sp-step-text">"Le preguntó al Dr. [nombre] y le dijo que sí funciona. ¿Lo probamos?"</div>
                    </div>
                </div>
            </div>
            <div class="sp-tip" style="margin-top:14px;">💡 <strong>Framework LAER para objeciones:</strong> Escucha → Reconoce → Explora → Responde. Nunca contradigas al doctor directamente.</div>
        </div>

        {{-- PIPELINE VISUAL --}}
        <div class="sp-card sp-card-blue sp-full">
            <div class="sp-card-title">📊 Pipeline — Flujo del prospecto al cliente</div>
            <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:14px;">
                @php
                $stages = [
                    ['label' => 'Nuevo', 'color' => '#94a3b8', 'desc' => 'En la base, sin contactar'],
                    ['label' => 'Contactado', 'color' => '#3b82f6', 'desc' => 'Ya hablaste con él'],
                    ['label' => 'Interesado', 'color' => '#f59e0b', 'desc' => 'Quiere saber más / demo'],
                    ['label' => 'En trial', 'color' => '#8b5cf6', 'desc' => 'Se registró, probando'],
                    ['label' => 'Convertido ✓', 'color' => '#10b981', 'desc' => 'Pagó = comisión'],
                ];
                @endphp
                @foreach($stages as $i => $s)
                <div style="flex:1;min-width:120px;background:{{ $s['color'] }}18;border:2px solid {{ $s['color'] }}40;border-radius:12px;padding:12px;text-align:center;">
                    <div style="font-size:0.7rem;font-weight:800;color:{{ $s['color'] }};text-transform:uppercase;letter-spacing:0.08em;">{{ $s['label'] }}</div>
                    <div style="font-size:0.7rem;color:#64748b;margin-top:2px;">{{ $s['desc'] }}</div>
                </div>
                @if($i < count($stages) - 1)
                <div style="display:flex;align-items:center;color:#cbd5e1;font-size:1.2rem;">→</div>
                @endif
                @endforeach
            </div>
            <div class="sp-tip">Usa el botón <strong>"Avanzar"</strong> en cada prospecto para moverlo por el pipeline. Al llegar a "Convertido" se genera tu comisión automáticamente.</div>
        </div>

        {{-- ACUERDO DESCARGABLE --}}
        <div class="sp-card sp-card-green">
            <div class="sp-card-title">📄 Acuerdo de vendedor</div>
            <div style="font-size:0.85rem;color:#374151;margin-bottom:12px;">Descarga, revisa y firma el acuerdo de comisiones antes de tu primera venta.</div>
            <a href="/docs/acuerdo-vendedor.xlsx" download
                style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:linear-gradient(135deg,#10b981,#059669);color:white;border-radius:12px;font-weight:800;font-size:0.85rem;text-decoration:none;box-shadow:0 6px 20px rgba(16,185,129,0.3);transition:transform 0.2s;"
                onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Descargar acuerdo (Excel)
            </a>
        </div>

        {{-- LINK DE REGISTRO --}}
        <div class="sp-card sp-card-green">
            <div class="sp-card-title">🔗 Tu link personalizado</div>
            @php $code = auth()->user()->sales_rep_code ?? 'TU-CODIGO'; @endphp
            <div style="background:#0f172a;color:#5eead4;padding:14px 18px;border-radius:10px;font-family:monospace;font-size:0.85rem;word-break:break-all;">
                https://docfacil.tu-app.co/doctor/register?vnd={{ $code }}
            </div>
            <div class="sp-tip" style="margin-top:10px;">Cuando un doctor se registra con tu link, la venta se te atribuye automáticamente. Cópialo y mándalo por WhatsApp con el botón "Link registro" de cada prospecto.</div>
        </div>

        {{-- MEETING PREP --}}
        <div class="sp-card sp-card-amber">
            <div class="sp-card-title">📋 Checklist pre-visita</div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#fef3c7;color:#92400e;">☐</div>
                <div class="sp-step-text"><strong>Investigar:</strong> ¿Especialidad? ¿Cuántas sillas/consultorios? ¿Tiene redes sociales?</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#fef3c7;color:#92400e;">☐</div>
                <div class="sp-step-text"><strong>Demo lista:</strong> Abrir docfacil.tu-app.co/demo en tu celular. Tener WiFi/datos.</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#fef3c7;color:#92400e;">☐</div>
                <div class="sp-step-text"><strong>Propuesta:</strong> Tener el PDF de propuesta descargado por si lo pide.</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#fef3c7;color:#92400e;">☐</div>
                <div class="sp-step-text"><strong>Objeciones:</strong> Repasar las top 5 antes de entrar. Las respuestas están en tu panel.</div>
            </div>
            <div class="sp-step">
                <div class="sp-step-num" style="background:#fef3c7;color:#92400e;">☐</div>
                <div class="sp-step-text"><strong>Cierre:</strong> Tener tu link de registro listo para mandarlo ahí mismo por WhatsApp.</div>
            </div>
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
                <div class="sp-tip">🎪 <strong>Post-visita:</strong> Manda WhatsApp de agradecimiento + link demo EL MISMO DÍA. No al día siguiente.</div>
                <div class="sp-tip">📞 <strong>Secretaria es tu aliada:</strong> "¿Me podría pasar al doctor solo 3 minutos? Le quiero mostrar algo que le va a ahorrar tiempo."</div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
