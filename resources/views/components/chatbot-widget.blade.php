@if(config('services.ai.chatbot_enabled', true))
<div x-data="docfacilChatbot()" x-cloak>
    <!-- Burbuja flotante -->
    <button type="button" x-show="!open" @click="toggle()" aria-label="Abrir chat"
        style="position:fixed;right:22px;bottom:22px;z-index:9998;width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#0891b2);color:#fff;border:0;box-shadow:0 12px 32px -8px rgba(13,148,136,0.6);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:transform .2s;"
        onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="30" height="30" fill="currentColor" aria-hidden="true">
            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM9 11c0 .55-.45 1-1 1s-1-.45-1-1 .45-1 1-1 1 .45 1 1zm4 0c0 .55-.45 1-1 1s-1-.45-1-1 .45-1 1-1 1 .45 1 1zm4 0c0 .55-.45 1-1 1s-1-.45-1-1 .45-1 1-1 1 .45 1 1z"/>
        </svg>
        <span x-show="hasUnread" style="position:absolute;top:-2px;right:-2px;background:#ef4444;color:#fff;border-radius:50%;width:22px;height:22px;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.25);">1</span>
    </button>

    <!-- Panel -->
    <div x-show="open" x-transition.opacity
        style="position:fixed;right:22px;bottom:22px;z-index:9999;width:380px;max-width:calc(100vw - 28px);height:580px;max-height:calc(100vh - 80px);background:#fff;border-radius:18px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);display:flex;flex-direction:column;overflow:hidden;border:1px solid #e5e7eb;">

        <!-- Header -->
        <div style="background:linear-gradient(135deg,#0d9488,#0891b2);color:#fff;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;">A</div>
                <div>
                    <div style="font-weight:700;font-size:14px;">Ana · DocFácil</div>
                    <div style="font-size:11px;opacity:0.9;display:flex;align-items:center;gap:4px;">
                        <span style="width:7px;height:7px;border-radius:50%;background:#34d399;display:inline-block;"></span>
                        En línea · Respuesta inmediata
                    </div>
                </div>
            </div>
            <button type="button" @click="toggle()" aria-label="Cerrar chat"
                style="background:none;border:0;color:#fff;cursor:pointer;padding:6px;opacity:0.8;"
                onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Mensajes -->
        <div x-ref="scroll" style="flex:1;overflow-y:auto;padding:16px;background:#f9fafb;display:flex;flex-direction:column;gap:10px;">
            <template x-for="(m, i) in messages" :key="i">
                <div :style="m.role === 'user' ? 'align-self:flex-end;background:#0d9488;color:#fff;padding:10px 14px;border-radius:16px 16px 4px 16px;max-width:78%;font-size:14px;line-height:1.45;white-space:pre-wrap;' : 'align-self:flex-start;background:#fff;color:#111827;padding:10px 14px;border-radius:16px 16px 16px 4px;max-width:78%;font-size:14px;line-height:1.45;white-space:pre-wrap;border:1px solid #e5e7eb;'">
                    <span x-text="m.content"></span>
                </div>
            </template>

            <!-- Typing -->
            <div x-show="typing" style="align-self:flex-start;background:#fff;padding:10px 14px;border-radius:16px 16px 16px 4px;border:1px solid #e5e7eb;display:flex;gap:4px;">
                <span style="width:6px;height:6px;border-radius:50%;background:#9ca3af;animation:chatDot 1.4s infinite;"></span>
                <span style="width:6px;height:6px;border-radius:50%;background:#9ca3af;animation:chatDot 1.4s infinite 0.2s;"></span>
                <span style="width:6px;height:6px;border-radius:50%;background:#9ca3af;animation:chatDot 1.4s infinite 0.4s;"></span>
            </div>

            <!-- Offer create buttons -->
            <div x-show="tags.offer_create && !hotMode" style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" @click="startHotPath()"
                    style="flex:1;min-width:140px;background:linear-gradient(135deg,#0d9488,#0891b2);color:#fff;border:0;padding:11px 14px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;">
                    ✅ Sí, créala aquí en 2 min
                </button>
                <button type="button" @click="rejectHotPath()"
                    style="flex:1;min-width:140px;background:#fff;color:#374151;border:1px solid #d1d5db;padding:11px 14px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;">
                    🕒 Solo quiero probar gratis
                </button>
            </div>

            <!-- Accept terms inline -->
            <div x-show="tags.accept_terms" style="background:#fff;padding:12px;border-radius:12px;border:1px solid #e5e7eb;">
                <div style="font-size:12px;color:#374151;margin-bottom:8px;">
                    <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;">
                        <input type="checkbox" x-model="termsAccepted" style="margin-top:2px;">
                        <span>Acepto los <a href="/terminos" target="_blank" style="color:#0d9488;text-decoration:underline;">Términos</a> y el <a href="/privacidad" target="_blank" style="color:#0d9488;text-decoration:underline;">Aviso de Privacidad</a>.</span>
                    </label>
                </div>
                <button type="button" @click="confirmTerms()" :disabled="!termsAccepted"
                    :style="termsAccepted ? 'width:100%;background:#0d9488;color:#fff;border:0;padding:9px;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px;' : 'width:100%;background:#d1d5db;color:#6b7280;border:0;padding:9px;border-radius:8px;font-weight:600;cursor:not-allowed;font-size:13px;'">
                    Acepto y continúo
                </button>
            </div>

            <!-- CTA cold path -->
            <div x-show="tags.close_data && !tags.create_data" style="background:linear-gradient(135deg,#ecfeff,#f0fdfa);padding:14px;border-radius:12px;border:1px solid #99f6e4;">
                <div style="font-size:13px;font-weight:600;color:#0f766e;margin-bottom:10px;">¡Listo! Activa tu prueba gratis:</div>
                <button type="button" @click="finalizeCold()"
                    style="width:100%;background:linear-gradient(135deg,#0d9488,#0891b2);color:#fff;border:0;padding:12px;border-radius:10px;font-weight:700;cursor:pointer;font-size:14px;">
                    🚀 Activar prueba gratis (15 días)
                </button>
            </div>

            <!-- CTA hot path -->
            <div x-show="tags.create_data" style="background:linear-gradient(135deg,#fefce8,#fff7ed);padding:14px;border-radius:12px;border:1px solid #fcd34d;">
                <div style="font-size:13px;font-weight:600;color:#92400e;margin-bottom:10px;">¡Listo para crear tu cuenta!</div>
                <button type="button" @click="finalizeHot()" :disabled="creating"
                    :style="creating ? 'width:100%;background:#d1d5db;color:#6b7280;border:0;padding:12px;border-radius:10px;font-weight:700;cursor:wait;font-size:14px;' : 'width:100%;background:linear-gradient(135deg,#f59e0b,#ea580c);color:#fff;border:0;padding:12px;border-radius:10px;font-weight:700;cursor:pointer;font-size:14px;'">
                    <span x-show="!creating">🎉 Crear mi cuenta</span>
                    <span x-show="creating">Creando...</span>
                </button>
                <div x-show="createError" x-text="createError" style="margin-top:8px;font-size:12px;color:#dc2626;"></div>
            </div>
        </div>

        <!-- Input -->
        <form @submit.prevent="send()" style="padding:12px;background:#fff;border-top:1px solid #e5e7eb;display:flex;gap:8px;">
            <input x-ref="input" x-model="draft" :type="tags.input_type === 'password' ? 'password' : 'text'"
                :placeholder="tags.input_type === 'password' ? 'Contraseña (mín 8 caracteres)' : 'Escribe tu mensaje...'"
                :disabled="typing || disabled"
                maxlength="2000"
                style="flex:1;border:1px solid #d1d5db;border-radius:10px;padding:10px 12px;font-size:14px;outline:none;"
                onfocus="this.style.borderColor='#0d9488'" onblur="this.style.borderColor='#d1d5db'">
            <button type="submit" :disabled="!draft.trim() || typing || disabled"
                :style="(!draft.trim() || typing || disabled) ? 'background:#d1d5db;color:#6b7280;border:0;border-radius:10px;padding:0 14px;cursor:not-allowed;' : 'background:#0d9488;color:#fff;border:0;border-radius:10px;padding:0 14px;cursor:pointer;'"
                aria-label="Enviar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            </button>
        </form>
    </div>
</div>

<style>
    @keyframes chatDot {
        0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
        30% { opacity: 1; transform: translateY(-3px); }
    }
    [x-cloak] { display: none !important; }
</style>

<script>
    function docfacilChatbot() {
        return {
            open: false,
            hasUnread: true,
            messages: [],
            draft: '',
            typing: false,
            disabled: false,
            hotMode: false,
            termsAccepted: false,
            creating: false,
            createError: '',
            tags: {},
            sessionId: '',
            capturedData: {},

            init() {
                let sid = sessionStorage.getItem('docfacil_chatbot_sid');
                if (!sid) {
                    sid = (crypto.randomUUID ? crypto.randomUUID() : this.fallbackUUID());
                    sessionStorage.setItem('docfacil_chatbot_sid', sid);
                }
                this.sessionId = sid;

                setTimeout(() => {
                    if (!this.open && this.messages.length === 0) {
                        this.hasUnread = true;
                    }
                }, 8000);
            },

            fallbackUUID() {
                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
                    const r = Math.random() * 16 | 0;
                    return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
                });
            },

            async toggle() {
                this.open = !this.open;
                this.hasUnread = false;
                if (this.open && this.messages.length === 0) {
                    await this.greet();
                }
            },

            async greet() {
                this.messages.push({
                    role: 'assistant',
                    content: '¡Hola! 👋 Soy Ana de DocFácil. ¿Me cuentas qué tipo de consultorio tienes? Dental, médico, otro...'
                });
                this.$nextTick(() => this.scrollBottom());
            },

            async send() {
                const text = this.draft.trim();
                if (!text || this.typing || this.disabled) return;

                this.messages.push({ role: 'user', content: text });
                this.draft = '';
                this.typing = true;
                this.tags = {};
                this.$nextTick(() => this.scrollBottom());

                try {
                    const res = await fetch('/chatbot/message', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ session_id: this.sessionId, message: text })
                    });
                    const data = await res.json();

                    if (data.disabled) {
                        this.disabled = true;
                        this.messages.push({ role: 'assistant', content: data.reply });
                    } else {
                        this.messages.push({ role: 'assistant', content: data.reply });
                        this.tags = data.tags || {};
                    }
                } catch (e) {
                    this.messages.push({
                        role: 'assistant',
                        content: 'Perdón, no pude procesar tu mensaje. Inténtalo de nuevo 🙏'
                    });
                } finally {
                    this.typing = false;
                    this.$nextTick(() => {
                        this.scrollBottom();
                        if (!this.disabled) this.$refs.input?.focus();
                    });
                }
            },

            startHotPath() {
                this.hotMode = true;
                this.tags = {};
                this.draft = 'Sí, créala aquí';
                this.send();
            },

            rejectHotPath() {
                this.hotMode = false;
                this.tags = {};
                this.draft = 'Prefiero solo probar gratis primero';
                this.send();
            },

            confirmTerms() {
                if (!this.termsAccepted) return;
                this.tags = { ...this.tags, accept_terms: false };
                this.draft = 'Acepto los términos';
                this.send();
            },

            async finalizeCold() {
                const d = this.tags.close_data;
                if (!d) return;
                try {
                    await fetch('/chatbot/close', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ session_id: this.sessionId, data: d })
                    });
                } catch (e) { /* silent */ }

                const params = new URLSearchParams({
                    name: d.name || '',
                    email: d.email || ''
                });
                window.open('/doctor/register?' + params.toString(), '_blank');
            },

            async finalizeHot() {
                const d = this.tags.create_data;
                if (!d) return;
                this.creating = true;
                this.createError = '';
                try {
                    const res = await fetch('/chatbot/create-account', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            session_id: this.sessionId,
                            name: d.name, email: d.email, password: d.password,
                            clinic_name: d.clinic_name, license_number: d.license_number,
                            specialty: d.specialty, phone: d.phone, city: d.city,
                            terms_accepted: true
                        })
                    });
                    const json = await res.json();
                    if (json.ok && json.login_url) {
                        window.location.href = json.login_url;
                    } else if (json.errors) {
                        const first = Object.values(json.errors)[0];
                        this.createError = Array.isArray(first) ? first[0] : (json.error || 'Revisa los datos e intenta de nuevo.');
                    } else {
                        this.createError = json.error || 'No pude crear la cuenta. Intenta el registro normal.';
                    }
                } catch (e) {
                    this.createError = 'Error de red. Intenta otra vez.';
                } finally {
                    this.creating = false;
                }
            },

            scrollBottom() {
                const el = this.$refs.scroll;
                if (el) el.scrollTop = el.scrollHeight;
            },
        };
    }
</script>
@endif
