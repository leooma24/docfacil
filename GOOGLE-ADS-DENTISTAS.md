# Google Ads — Plan de Cuenta para DocFácil (Nicho Dental)

> **Objetivo:** captar dentistas con alta intención de compra que buscan "software para consultorio dental" en México.
> **Presupuesto inicial:** $2,000 MXN/mes · 30 días de prueba.
> **Landing destino:** [`/dentistas`](resources/views/dentistas.blade.php).
> **Targeting geográfico:** México (todas las ciudades, bias a Top 10 metropolitanas).

---

## 1. Estructura de la cuenta

```
Cuenta: DocFácil
  └── Campaña 1: Search — Software Dental México
        ├── Ad Group 1: Software Dental (genéricos alta intención)
        ├── Ad Group 2: Agenda / Citas Dentistas
        ├── Ad Group 3: Expediente Clínico Dental
        ├── Ad Group 4: Odontograma Digital
        ├── Ad Group 5: Recordatorios WhatsApp
        └── Ad Group 6: NOM-004 Dental (intención regulatoria)

  └── Campaña 2 (FASE 2, cuando haya tráfico): Retargeting Display
        └── Ad Group: Visitantes sin registro
```

**Por qué 1 sola campaña de Search al inicio:**
- Budget chico ($2k/mes) no alcanza para fragmentar en varias campañas.
- La segmentación fina va en los **ad groups** (cada uno con su keyword tema).
- Cuando identifique qué ad group convierte mejor, separa en campañas individuales para asignar budget.

---

## 2. Configuración de la campaña

| Campo | Valor | Por qué |
|---|---|---|
| **Tipo** | Search Network (solo) | Maximiza intención, sin desperdicio en display |
| **Ubicaciones** | México | Sin exclusiones de ciudad |
| **Idioma** | Español | Obvio |
| **Puja** | Maximizar conversiones (con límite de CPA $400 MXN) | Que Google aprenda |
| **Conversión** | "Registro trial" (ver sección 6) | Única conversión activa |
| **Presupuesto diario** | $67 MXN (~$2,000/mes) | Google puede gastar 2x un día, compensa después |
| **Ad rotation** | Optimizar para conversiones | Lo default |
| **Horario** | L-V 8am-9pm, S 10am-6pm | Horario laboral dentistas |
| **Device bid** | Mobile -10%, Desktop 0%, Tablet -20% | Dentistas deciden frente a laptop |
| **Audiencias observadas** | Profesionales de la salud (dental) | Solo observar, no excluir |

---

## 3. Keywords por Ad Group

Cada keyword se agrega en **3 match types** (exact, phrase, broad match modifier via headline keyword insertion):

### Ad Group 1 — Software Dental (genéricos alta intención)

```
[software para consultorio dental]
[software dental mexico]
[sistema para consultorio dental]
[programa para dentistas]
[software para odontologos]
"software consultorio dental"
"sistema dental"
"software dentistas mexico"
+software +consultorio +dental
+programa +dentistas +mexico
```

### Ad Group 2 — Agenda / Citas Dentistas

```
[agenda para dentistas]
[agenda para consultorio dental]
[sistema de citas dentistas]
[software de agenda dental]
"agenda dentista"
"citas dentales online"
"sistema de agenda para consultorio"
+agenda +dentistas
+sistema +citas +dental
```

### Ad Group 3 — Expediente Clínico Dental

```
[expediente clinico dental]
[expediente electronico dental]
[historia clinica dental digital]
[ficha clinica dental software]
"expediente dental electronico"
"historia clinica dental"
+expediente +clinico +dental
```

### Ad Group 4 — Odontograma Digital (diferenciador estrella)

```
[odontograma digital]
[odontograma electronico]
[software con odontograma]
[odontograma online]
"odontograma digital"
"odontograma software"
+odontograma +digital
```

### Ad Group 5 — Recordatorios WhatsApp (diferenciador estrella)

```
[recordatorios whatsapp pacientes]
[confirmacion citas whatsapp]
[software recordatorios dentista]
"recordatorio whatsapp dentista"
"confirmar citas whatsapp"
+recordatorios +whatsapp +pacientes
```

### Ad Group 6 — NOM-004 Dental (regulatoria)

```
[nom 004 consultorio dental]
[cumplir nom 004 dental]
[expediente nom-004 dental]
"nom 004 dentista"
"expediente clinico nom 004 dental"
+nom +004 +dental
```

---

## 4. Negative Keywords (críticas — evitan quemar budget)

### Nivel campaña (aplican a todos los ad groups)

```
-gratis
-free
-curso
-cursos
-diplomado
-empleo
-empleos
-vacante
-trabajo
-sueldo
-salario
-clases
-escuela
-universidad
-universitario
-estudiante
-unam
-ipn
-udg
-segundo año
-tercer año
-tesis
-investigacion
-dentrix
-carestream
-softdent
-eaglesoft
-crack
-descargar
-pirata
-torrent
-full
-serial
-keygen
-apk
-android
-ios
-app store
-google play
-imss
-issste
-sector salud
-hospital
-hospitales
-clinica del issste
-seguro popular
-insabi
-lista de precios
-precio barato
-tratamiento dental
-pacientes
-cepillo dental
-crema dental
-blanqueamiento
-ortodoncia precio
-limpieza dental
-frenos
-brackets
-carillas
-implante dental precio
```

### Nivel Ad Group — negativos específicos

**Ad Group 4 (Odontograma):** negar consultas sobre "qué es un odontograma" (investigación, no compra):
```
-definicion
-que es
-significado
-anatomia
-tipos de odontograma
```

**Ad Group 6 (NOM-004):** negar términos de formación:
```
-diplomado nom 004
-curso nom 004
-capacitacion
-manual nom 004
-pdf
```

---

## 5. Ad Copy — RSA (Responsive Search Ads)

Cada Ad Group necesita **2 RSAs** con 15 headlines + 4 descriptions para que Google optimice. Plantilla general que puedes variar por ad group:

### Headlines (15 — reúsa keyword tema del ad group donde aplique)

```
1. Software para Consultorio Dental
2. DocFácil · Agenda + Odontograma
3. WhatsApp Automático a Pacientes
4. Bajen sus No-Shows 40-60%
5. Recetas PDF con Firma Digital
6. Pruebe 15 Días Gratis, Sin Tarjeta
7. Hecho en México para Dentistas
8. Expediente Dental Digital NOM-004
9. Odontograma Digital FDI Interactivo
10. Recupere $8,000/mes en Huecos
11. Desde $499 al Mes, Sin Contratos
12. Soporte WhatsApp Directo
13. Listo en 2 Minutos
14. Cumple NOM-004 y LFPDPPP
15. {KeyWord: Software Dental}   ← inserción dinámica
```

### Descriptions (4)

```
1. Agenda, expediente, recetas PDF y recordatorios WhatsApp automáticos. Su consultorio dental organizado desde el día uno. 15 días gratis, sin tarjeta.

2. El odontograma digital FDI que se actualiza solo. 13 condiciones dentales, editor visual. Todo en la nube, sin instalar nada. Empiece hoy.

3. Los dentistas que ya lo usan bajan 40-60% sus citas perdidas y recuperan $6-10k/mes. Plan Pro $999/mes o Básico $499. Sin letra chiquita.

4. Hecho en México, cumple NOM-004, soporte real por WhatsApp. Pruébelo 15 días y decide después. Sin tarjeta al registrarse.
```

### Paths (visibles en el URL)

```
Path 1: dentistas
Path 2: 15-dias-gratis
```

URL display: `docfacil.tu-app.co/dentistas/15-dias-gratis`

---

## 6. Conversion tracking (OBLIGATORIO antes de prender ads)

Sin esto, estás tirando dinero sin saber qué keyword convierte.

### 6.1 Crear la conversión en Google Ads

1. Google Ads UI → Tools → Conversions → `+ New conversion action`
2. Tipo: **Website**
3. Configuración:
   - **Category:** Sign-up
   - **Conversion name:** `Registro Trial Dentista`
   - **Value:** $4,990 MXN (LTV estimado primer año plan Básico)
   - **Count:** One
   - **Click-through conversion window:** 30 days
   - **Attribution model:** Data-driven

### 6.2 Instalar el tag

Opción A — Google Tag Manager (recomendado):
1. Crear contenedor GTM en `tagmanager.google.com`.
2. Agregar a [dentistas.blade.php](resources/views/dentistas.blade.php) el snippet GTM (en `<head>` y justo después de `<body>`).
3. Dentro de GTM configurar:
   - Tag: **Google Ads Conversion Tracking** con el `conversion_id` y `conversion_label` que da Google Ads.
   - Trigger: **Page View** en `/billing/stripe/success` OR `/register/success` OR que Laravel dispare un evento dataLayer al completar registro.

Opción B — Tag directo en la página de éxito post-registro:
```html
<!-- En la vista de éxito de registro trial -->
<script>
  gtag('event', 'conversion', {
    'send_to': 'AW-XXXXXXXXX/XXXXXXXXXXXXXXXX',
    'value': 4990.0,
    'currency': 'MXN'
  });
</script>
```

### 6.3 GA4 (además de Google Ads)

Linkea GA4 con Google Ads para ver audiencias observed, tráfico orgánico vs pagado, bounce rate por landing. GA4 ID en `GA_MEASUREMENT_ID`.

---

## 7. Ad Extensions (todas, SIEMPRE)

### Sitelink Extensions (mínimo 4)

```
Sitelink 1: Odontograma Digital  → /dentistas#features
Sitelink 2: Precios y Planes     → /dentistas#precio
Sitelink 3: Preguntas Frecuentes → /dentistas (ancla FAQ)
Sitelink 4: Demo en 10 Minutos   → https://wa.me/526682493398?text=Quiero%20una%20demo
```

### Callout Extensions (mínimo 6)

```
15 Días Gratis
Sin Tarjeta
Hecho en México
Soporte WhatsApp
Cumple NOM-004
Desde $499/mes
Sin Contratos
Configuración en 2 min
```

### Structured Snippets

```
Tipo: Features
Valores: Odontograma Digital, Recordatorios WhatsApp, Recetas PDF, Agenda Online, Expediente Clínico, Cobros Integrados
```

### Call Extension

```
Número: 668 249 3398
Horario: L-V 9:00-19:00, S 9:00-14:00
País: México
```

### Lead Form Extension (opcional, avanzado)

- Campos: Nombre, WhatsApp, Especialidad (dropdown), Ciudad
- CTA: "Agendar demo en 10 min"
- Mensaje post-submit: "Le escribimos por WhatsApp en los próximos 30 min"

---

## 8. Primera semana — qué monitorear

| Día | Qué revisar | Acción |
|---|---|---|
| 1-2 | Impresiones empezando | Si no hay impresiones en 48h, CPC muy bajo o quality score muy malo. Sube bid o ajusta copy |
| 3 | Search terms report | Identifica queries que NO deberían mandarte tráfico → agrégalas a negative keywords |
| 4 | CTR por Ad Group | Si un ad group tiene CTR <2%, el copy no conecta con esa intención |
| 5 | Landing conversion rate | `/dentistas` debería convertir 3-8% de visitas a trial. Si <2%, problema en landing |
| 7 | CPA real | Objetivo: CPA ≤ $400. Si >$600 con <5 conversiones, pausa ad groups con peor desempeño |

---

## 9. Señales de alarma

- **CTR <1%** en un ad group → quality score malo, puja más cara, reescribe ads
- **CPC >$80 MXN** en keywords core → alguien nuevo entró a pujar, revisa negatives
- **Bounce rate >80%** en `/dentistas` → copy del ad no coincide con landing
- **Conversion rate <2%** sostenido → landing falla (form muy largo, load lento, falta prueba social visible)
- **Impresiones que caen 50%** de un día a otro → posible disapproval de un ad (revisa Policy tab)

---

## 10. Presupuesto escalable

| Resultado a 30 días | Decisión | Nuevo budget |
|---|---|---|
| ≥3 trials pagando | Funciona. Escala | $5,000 MXN/mes |
| 1-2 trials pagando | Mixed. Optimizar | $2,000 (igual, con fixes) |
| 0 trials pagando pero muchos registros | Problema en activación/pricing | $1,000 (pausa parcial) |
| 0 trials + 0 registros | No funciona, apagar | $0, reanalizar propuesta |

---

## 11. Antes de prender (checklist final)

- [ ] Landing `/dentistas` en producción y cargando rápido (<2s)
- [ ] Google Analytics 4 instalado y verificando eventos
- [ ] Google Tag Manager con el tag de conversión activo
- [ ] Conversión "Registro Trial Dentista" creada en Google Ads
- [ ] Cuenta Google Ads con información de pago (tarjeta MX)
- [ ] Budget mensual configurado en $2,000 MXN
- [ ] 6 ad groups con 2 RSAs cada uno (12 ads total)
- [ ] Negative keywords cargadas (campaña y ad group)
- [ ] 4 sitelink extensions, 6+ callouts, structured snippets, call extension
- [ ] Horario laboral configurado
- [ ] Device bid adjustments aplicados
- [ ] Conversion tracking verificado con un registro de prueba
- [ ] WhatsApp Business listo para responder <10 min los clics al botón

---

## 12. Plantillas para Google Ads Editor (bulk import)

### campaigns.csv

```csv
Campaign,Campaign daily budget,Campaign status,Networks,Languages,Bid strategy type,Target CPA
Search Software Dental MX,67.00,Enabled,Google search,Spanish,Maximize conversions,400.00
```

### ad_groups.csv

```csv
Campaign,Ad group,Max CPC
Search Software Dental MX,Software Dental,50
Search Software Dental MX,Agenda Dentistas,50
Search Software Dental MX,Expediente Clinico Dental,45
Search Software Dental MX,Odontograma Digital,40
Search Software Dental MX,Recordatorios WhatsApp,40
Search Software Dental MX,NOM 004 Dental,35
```

### keywords.csv (extracto)

```csv
Campaign,Ad group,Keyword,Match type
Search Software Dental MX,Software Dental,software para consultorio dental,Exact
Search Software Dental MX,Software Dental,"software consultorio dental",Phrase
Search Software Dental MX,Software Dental,+software +consultorio +dental,Broad
...
```

(La lista completa de keywords está en la sección 3 de este documento — expandir en CSV antes de importar.)

---

## 13. Resumen ejecutivo (TL;DR)

**Qué:** 1 campaña de Search Ads, 6 ad groups por tema, ~60 keywords, 12 RSAs, extensions completas, conversion tracking obligatorio.

**Cuánto:** $2,000 MXN/mes por 30 días de prueba.

**Math esperada:**
- ~80-100 clics
- CPC promedio ~$25-40 MXN
- Landing convierte ~5% → 4-5 trials
- Trial → paga ~25% → **1-2 clientes nuevos**
- LTV primer año: $4,990 MXN × 1-2 = **$5k-10k MXN**
- ROI mes 1: 2.5x – 5x

**Siguiente paso cuando funcione:** escalar a $5,000 MXN, agregar Meta retargeting, crear más landings dedicadas por especialidad (ortodoncistas, estética).

---

*Documento vivo. Actualizar después de cada revisión mensual de la cuenta.*
