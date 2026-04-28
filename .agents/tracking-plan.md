# DocFácil Tracking Plan

*Last updated: 2026-04-28*

> Source of truth de todos los eventos de tracking. Cuando agregues, modifiques o desactives un evento, **actualiza este documento**. Las skills de marketing leen este archivo para informar análisis y recomendaciones.

## Stack
- **Google Analytics 4** (gratis)
- **gtag.js directo**, sin GTM (overhead innecesario para 1 fundador)
- **Server-side ya existente**: `prospect_email_events` (clicks de correos), no se duplica en GA4
- **Privacy**: IP anonymizada, respeta `Do-Not-Track`, sin cookies de marketing

## Configuración

### Variables `.env`
```
ANALYTICS_ENABLED=true
GA_MEASUREMENT_ID=G-XXXXXXXXXX
```

Si `ANALYTICS_ENABLED=false` o no hay `GA_MEASUREMENT_ID`, el partial **no renderiza nada** (cero overhead).

### Archivos clave
- `config/services.php` → bloque `analytics`
- `resources/views/partials/analytics.blade.php` → snippet GA4 + helper `window.trackEvent()` + auto-tracker `data-track`
- `resources/views/dentistas.blade.php` → incluye partial en `<head>`, CTAs marcados con `data-track`
- `app/Providers/Filament/DoctorPanelProvider.php` → carga partial en panel doctor (signup + dashboard)

### Server-side flash events
Cualquier controlador puede disparar un evento que se ejecuta en el siguiente render del usuario:
```php
session()->push('analytics_events', [
    'name' => 'mi_evento',
    'params' => ['foo' => 'bar'],
]);
```
El partial los lee con `session()->pull(...)` y los dispara una sola vez.

---

## Eventos

### Landing page (`/` y `/dentistas`)

| Evento | Propiedades | Trigger |
|---|---|---|
| `page_view` | `utm_source`, `utm_medium`, `utm_campaign` | Auto via gtag config |
| `cta_clicked` | `location`, `text` | Click en cualquier `[data-track="cta_clicked"]` |
| `pricing_tier_clicked` | `tier`, `cycle` | Click en card de pricing |
| `roi_calculator_used` | `monthly_savings`, `patients`, `price_per_visit` | Cambio de input (1 fire por sesión) |
| `whatsapp_clicked` | `location` | Click en cualquier link `wa.me` |
| `form_submitted` | `form_type` (`contact`) | Submit form de contacto |
| `exit_intent_shown` | — | Modal de exit intent aparece |
| `exit_intent_converted` | — | Click "Agendar 20 min con Omar" en modal |

### Funnel signup + activación

| Evento | Propiedades | Trigger |
|---|---|---|
| `signup_completed` | `method`, `from_email_track`, `has_sales_rep`, `plan` | Tras `Register::handleRegistration()` |
| `onboarding_step_completed` | `step` (1-4) | Click "Siguiente" en wizard |
| `onboarding_completed` | `services_count`, `addons_activated` | Click "Empezar a usar DocFácil" en step 5 |
| `subscription_upgraded` | `plan`, `billing_cycle`, `value_mxn`, `gateway` | Stripe success redirect |

### CTAs marcados con `data-track`

| Location | Text | Where |
|---|---|---|
| `navbar` | `prueba_gratis` | Navbar desktop |
| `navbar_mobile` | `prueba_gratis` | Navbar mobile |
| `hero` | `probar_15_dias_gratis` | Hero primary CTA |
| `hero` | `ver_demo_en_vivo` | Hero secondary CTA |
| `final_cta` | `crear_mi_cuenta_gratis` | Final section CTA |
| `sticky_mobile` | `empieza_gratis` | Sticky bottom (mobile) |
| `sticky_desktop` | `ir` | Sticky bottom-right (desktop) |
| (pricing tier) | tier slug | Una entrada por card de pricing |

WhatsApp:

| Location | Where |
|---|---|
| `founder_section` | Botón verde "Escríbeme: 668..." |
| `contact_section` | Link teléfono en sección de contacto |
| `exit_intent` | Botón "Agendar 20 min con Omar" del modal |

---

## UTMs estandarizados

### Correos del pipeline (ya implementados)
- `utm_source=prospect_email`
- `utm_medium=email`
- `utm_campaign=` `beta_invite` | `followup` | `last_chance`

Se construyen automáticamente en cada Mailable. Pasan por el `track.click` redirect → preservados al landing.

### Convenciones para campañas futuras

| Parámetro | Formato | Ejemplo |
|---|---|---|
| `utm_source` | lowercase, sin espacios | `google`, `facebook`, `linkedin`, `prospect_email` |
| `utm_medium` | tipo de canal | `cpc`, `email`, `social`, `affiliate`, `referral` |
| `utm_campaign` | nombre claro | `dentistas_q2_2026`, `relanzamiento_pro` |
| `utm_content` | variante (opcional) | `hero_cta`, `bottom_cta`, `version_a` |

**Convención:** `lowercase_con_guiones_bajos`. Documenta cada campaña en este archivo cuando la lances.

---

## Conversiones a marcar en GA4

En GA4 Admin → Eventos → marcar como conversión:

1. **`signup_completed`** — primaria del funnel
2. **`subscription_upgraded`** — la que importa para revenue
3. **`form_submitted`** — lead nurture
4. **`exit_intent_converted`** — recuperación de visitor que se iba

---

## Checklist de validación post-deploy

- [ ] Visitar `/dentistas`, abrir DevTools → Network → filtrar `google-analytics.com/g/collect` → debe haber al menos 1 request de pageview
- [ ] Click cualquier CTA → debe disparar request con `en=cta_clicked` en payload
- [ ] Submit form contacto → request con `en=form_submitted`
- [ ] GA4 → Admin → DebugView → activar y ver eventos llegar en tiempo real
- [ ] Probar Do-Not-Track activo: en Firefox/Brave activar DNT, recargar `/dentistas`, verificar que NO se hace request a `googletagmanager.com`

---

## Roadmap (no implementado aún)

- **Server-side fire via Measurement Protocol**: para webhook Stripe (no client-side), webhook WhatsApp, eventos de chatbot. Requiere `MEASUREMENT_PROTOCOL_API_SECRET` en `.env` + clase helper.
- **`subscription_cancelled`**: hook en cancel flow del doctor
- **Cohort analysis**: enviar `user_id` (hashed) como user property para análisis longitudinal
- **Server-side enriched dimensions**: clinic plan, days since signup, source — para slicing avanzado
- **Heatmaps + session replay**: PostHog (gratis self-hosted) cuando empezamos a tener tráfico orgánico real
