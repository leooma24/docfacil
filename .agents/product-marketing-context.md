# Product Marketing Context — DocFácil

*Last updated: 2026-10-05*

> Este documento es la fuente de verdad para todas las skills de marketing y ventas. Cuando hagas un correo, página, anuncio o copy, primero lee esto. Si algo cambia (precios, ICP, posicionamiento), actualízalo aquí y todo lo demás se alinea.
>
> **Regla de Omar:** solo se dice lo que el sistema hace hoy. Lo que trae cada plan sale de `App\Support\LoQueTraeCadaPlan` y `Clinic::featuresForPlan()`; si no está ahí, no se promete.

## Product Overview

**One-liner:** Software para consultorios dentales en México: agenda con recordatorios por WhatsApp a 1 clic, odontograma FDI, presupuestos, recetas PDF con cédula y cobros con abonos.

**What it does:** SaaS multi-tenant para dentistas en México. Reemplaza el cuaderno/Excel con: agenda + recordatorios por WhatsApp a 1 clic (DocFácil abre el WhatsApp del doctor con el mensaje escrito y él da enviar), confirmación de cita con liga, expediente clínico pensado para la NOM-004 (notas que se bloquean a las 24 horas), odontograma FDI interactivo, presupuestos que el paciente acepta en línea, recetas PDF con cédula, cobros con abonos y planes de pago, check-in con QR, pantalla de la sala de espera, portal del paciente y gastos con corte del mes. En Pro: agenda en línea para que el paciente agende solo, recall, lista de espera, consentimientos con firma en pantalla, inventario de insumos, reportes y alertas.

**Lo que NO hace (no se promete):** WhatsApp que se manda solo, recordatorio a las 2 horas, links de pago o pagos en línea del paciente, mandar recetas u odontograma por WhatsApp o correo (son PDF), correos al paciente que salen solos, varios consultorios en una cuenta, reparto de ganancias entre doctores, CFDI/factura (nunca se ofrece), funciones con IA (están apagadas), trabajar sin internet. Las frases que no pueden volver están en `MaterialesDeVentaHonestosTest`.

**Product category:** Software para consultorio dental (búsqueda real: "software dental México", "agenda dental", "expediente dental digital", "sistema para dentistas")

**Product type:** SaaS multi-tenant B2B, prepago mensual o anual (tarjeta con Stripe o transferencia SPEI)

**Business model:** Free + 3 planes pagados + add-ons. Anual = mensual × 10 (2 meses gratis). Prueba de 15 días con todo lo de Pro, sin tarjeta. Garantía de 30 días sobre el primer pago (`/terminos#garantia`). Cancela cuando quiera.

| Plan | $/mes | $/año | Doctores | Pacientes | Lo que trae |
|---|---|---|---|---|---|
| Free | $0 | $0 | 1 | 15 (y 10 citas al mes) | Agenda y expediente. Para siempre, sin tarjeta |
| Básico | $499 | $4,990 | 1 | 200 | Odontograma FDI, presupuestos, recordatorios a 1 clic, confirmación con liga, recetas PDF con cédula, cobros con abonos y cobro por WhatsApp a 1 clic, check-in QR, pantalla de la sala, portal del paciente, gastos y corte del mes |
| Pro | $999 | $9,990 | Hasta 3 | Ilimitados | Todo Básico + agenda en línea, recall, lista de espera, consentimientos con firma, inventario, reportes avanzados, alertas |
| Clínica | $1,999 | $19,990 | Ilimitados | Ilimitados | Todo Pro + producción y reportes por doctor + onboarding 1 a 1 |

**Decisión de pricing (2026-04-28):** El odontograma FDI vive en Básico (no en Pro) porque es lo que más distingue al producto para un dentista que trabaja solo. Pro vende escala (varios doctores, agenda en línea, recall, lista de espera, consentimientos, inventario, reportes), no funciones clínicas básicas.

**Presupuestos (2026-10-02):** vienen en todos los planes de pago desde el Básico. Ya no son add-on.

**Add-ons** (`config/addons.php`):
- **Recall: a quién ya le toca volver** — $49/mes para el Básico (en Pro ya viene). DocFácil calcula a qué pacientes ya les toca volver; un clic abre su WhatsApp con el mensaje y él da enviar. 
- **Reseñas en Google: a quién pedírsela** — $49/mes. Le muestra a qué pacientes pedirles reseña; un clic abre su WhatsApp con el mensaje y su link de Google, y él da enviar.

**Programa Fundador** (`config/founders.php`): 10 lugares. 6 meses sin costo y después $499/mes de por vida, congelado. A cambio: que lo use en serio y que le diga a Omar la verdad. El número de lugares que quedan sale de contar los fundadores reales en la base.

## Target Audience

**Target companies:** Consultorios dentales independientes en México, 1-3 sillones, dueño practicante. Solo dentistas. Mercado nacional (hay prospectos en CDMX, GDL, MTY, Mérida, Tijuana, León, Cancún, Saltillo, Toluca, AGS, La Laguna, Cuernavaca, Morelia, Querétaro, Puebla, además de Sinaloa).

**Decision-makers:** Dueño = dentista practicante. No hay separación entre user, champion y buyer. En consultorios de 2-3 sillones a veces la asistente influye ("yo lo voy a usar más").

**Primary use case:** Que los pacientes no se le olviden de su cita sin escribir los recordatorios uno por uno, y tener el papeleo del consultorio en una sola herramienta.

**Jobs to be done:**
- Llenar los huecos de la agenda
- Quitarse de la cabeza el "tengo que llamarle a Don Pedro"
- Verse profesional con recetas con cédula y expediente digital
- Cobrar lo que le deben sin tener que perseguir al paciente
- Saber cuánto entró y cuánto gastó en el mes

**Use cases:**
- Recordatorio por WhatsApp a 1 clic para las citas de mañana (se abre su WhatsApp con el mensaje escrito y él da enviar)
- Confirmación con liga: el paciente confirma o cancela con un toque y la cita cambia sola en la agenda
- Lista de espera: al cancelar una cita, aviso con "Ofrecer a ..." que abre su WhatsApp (Pro)
- Cobro pendiente por WhatsApp a 1 clic, con el monto ya puesto
- Receta PDF con cédula
- Recall del paciente que ya le toca volver (Pro, o add-on en Básico)

## Personas

| Persona | Le importa | Su reto | Lo que le ofrecemos |
|---|---|---|---|
| **Dentista solo, 1 sillón** | No perder citas, atender más pacientes | Pacientes que no llegan, papeleo | Recordatorios a 1 clic, odontograma, presupuestos y recetas en un lugar |
| **Dentista con 2-3 sillones + asistente** | Que la asistente no se sature | Recordatorios manuales, agenda en cuaderno o Excel | La lista de mañana con el mensaje ya hecho: van dando enviar desde su WhatsApp |
| **Asistente / recepcionista (champion)** | No olvidar nada, no llevarse trabajo a casa | Mil cosas en la cabeza, errores de agenda | Agenda, cobros pendientes y "Lo que sigue" de cada paciente a la vista |

## Problems & Pain Points

**Core problem:** Pacientes que no llegan a su cita y recordatorios que se mandan a mano, uno por uno, cuando hay tiempo. No tenemos una cifra con fuente de cuántos faltan; se le pregunta al dentista sus propios números.

**Why alternatives fall short:**
- Cuaderno/Excel: no arma los recordatorios, no lleva los abonos, se pierde
- WhatsApp manual uno por uno: se come tiempo todos los días, se olvida, tono inconsistente
- Asistente sola: se enferma, renuncia, y se lleva la "memoria" del consultorio

**What it costs them:** Huecos en la agenda, tiempo diario escribiendo recordatorios y tratamientos que no se completan (paciente con endodoncia a la mitad y nadie le da seguimiento). Si hay que hablar de dinero, con los números del dentista: "si le recupera una cita al mes de $X, ya pagó el plan".

**Emotional tension:**
- "Sillón vacío" — frustración
- "Tengo todo en la cabeza" — miedo a olvidar
- "Ya estoy viejo para esto" — pena con la tecnología
- "Si me ven en cuaderno parezco improvisado" — imagen profesional

## Competitive Landscape

No se dicen precios ni funciones de otros sistemas sin fuente. Si el dentista ya usa otro, la postura es neutral: "pregúntele a su proveedor actual si le arma los recordatorios, si tiene odontograma y cuánto le cuesta al año; con eso compare".

**Secondary:** Excel + WhatsApp manual — funciona con pocos pacientes; después los datos no se cruzan y no hay avisos.

**Indirect:**
- Asistente haciendo todo a mano
- Cuaderno físico — barato pero no escala, se pierde, no tiene respaldo, no arma recordatorios

## Differentiation

**Key differentiators:**
- **WhatsApp a 1 clic, desde su propio WhatsApp** — sin API de Meta ni costo por mensaje. DocFácil abre su WhatsApp con el mensaje escrito; él da enviar.
- **Hecho para dentistas en México** — odontograma FDI, presupuestos, mensualidades de ortodoncia, expediente pensado para la NOM-004, aviso de privacidad del consultorio para sus pacientes, pago del plan por SPEI, 100% español.
- **Founder-led** — soporte por WhatsApp directo con Omar Lerma, fundador (668 249 3398).
- **Plan Free de verdad** — 1 doctor, 15 pacientes y 10 citas al mes, para siempre.
- **Sin contratos forzosos** — cancela cuando quiera.
- **Garantía de 30 días** — si en los primeros 30 días de su primer pago decide que no le sirve, se le devuelve ese pago (una vez por consultorio, ver términos).
- **Le pasamos sus pacientes** — manda su Excel por WhatsApp y Omar lo sube, sin costo. También hay "Importar de Excel" en Pacientes.

## Objections

Ver `.agents/objection-playbook.md` para las respuestas completas. Postura corta:

| Objection (verbatim) | Response posture |
|---|---|
| Está caro / No tengo presupuesto | "¿Cuánto le deja una cita? Si le recupera una al mes, ya pagó el plan. Pruébelo 15 días sin tarjeta." |
| ¿Por qué pagar si uso Excel? | "¿Excel le arma los recordatorios de mañana? Aquí se abre su WhatsApp con el mensaje escrito." |
| Hay opciones gratis | "Yo también tengo plan Free para siempre. Compare qué trae cada una." |
| No sé si lo voy a usar | "15 días con todo, sin tarjeta. Y el primer pago tiene garantía de 30 días." |
| No soy tecnológico | "Si usa WhatsApp, puede usarlo. Le acompaño por WhatsApp las primeras semanas." |
| El papel me funciona bien | "El papel no le avisa a nadie. Pruébelo 15 días y compare." |
| Ya tengo otro sistema | "¿Cuál es? Si cambia, le paso sus pacientes desde Excel sin costo." |
| ¿Y si se cae el internet? | "Necesita internet; si se cae, funciona con los datos del celular. Hay respaldo automático diario." |
| ¿Mis datos están seguros? | "Conexión cifrada, respaldo diario, cada consultorio aislado. Los servidores están en Estados Unidos (DigitalOcean)." |
| ¿Quién está detrás? | "Soy Omar Lerma, ingeniero de Los Mochis. Mi WhatsApp es 668 249 3398." |
| ¿Y si desaparecen? | "Si cancela, sus datos quedan 30 días por si quiere una copia. Y el plan Free no se apaga." |
| Necesito pensarlo | "Sin presión. ¿Qué información le ayudaría a decidir?" |
| Ahorita no es buen momento | "¿Cuándo le escribo? Lo anoto." |
| Cuando tenga más pacientes | "Con pocos es más fácil empezar. El Free es para eso." |
| Solo atiendo IMSS/ISSSTE | (No es nuestro ICP — pasar a otro prospecto) |
| Ya estoy viejo para esto | "Si usa WhatsApp, lo puede usar. Y si tiene asistente, ella puede llevarlo." |
| Mi consultorio es muy pequeño | "El plan Free está hecho para eso. Cero costo." |

**Anti-persona:**
- Dentistas IMSS/ISSSTE puros (no cobran al paciente directo)
- Dentistas que rechazan la tecnología y no usan WhatsApp
- Consultorios institucionales sin agenda propia (pertenecen a hospital)
- Dentistas contentos con su sistema actual desde hace años (mejor no nadar contra la corriente)
- Quien necesita factura (CFDI) desde el sistema: no se ofrece

## Switching Dynamics (JTBD 4 forces)

**Push:** Citas que se pierden; ya no aguanta el cuaderno; la asistente está saturada o renunció; quiere verse más profesional con recetas PDF.

**Pull:** Recordatorios a 1 clic desde su WhatsApp; odontograma que se vuelve presupuesto; founder mexicano que contesta directo; plan Free de verdad; prueba sin tarjeta.

**Habit:** Lleva años con cuaderno/Excel; "ya tengo mi sistema"; cambiar requiere meter pacientes; la asistente está acostumbrada.

**Anxiety:** ¿Y si se cae internet?, ¿son seguros los datos?, ¿voy a saber usarlo?, ¿y si después suben el precio?, ¿y si desaparecen?

## Customer Language

**How they describe the problem:**
- "No me llegan los pacientes"
- "Tengo el sillón vacío"
- "Se me olvida llamarles"
- "Tengo todo en un cuaderno"
- "El papel me funciona"
- "La asistente se sabe todo, pero si no viene…"
- "El paciente quedó a la mitad de su tratamiento y no sé cómo se llamaba"

**Words to use:** consultorio, paciente, sillón, recordatorio, expediente, cédula, agenda, receta, odontograma, presupuesto, abono, hueco (de agenda), "a 1 clic", "se abre su WhatsApp y usted da enviar"

**Words to avoid:** "plataforma", "ecosistema", "solución integral", "transformación digital", "leverage", "engagement", "stakeholder", anglicismos en general, "automático" para cualquier mensaje. Tampoco "clínica" para el consultorio de un dentista solo (suena institucional — usa "consultorio").

**Glossary:**
| Término | Meaning |
|---|---|
| Odontograma FDI | Diagrama dental con notación internacional FDI (numera dientes 11-48) |
| NOM-004-SSA3 | Norma Oficial Mexicana del expediente clínico |
| LFPDPPP | Ley Federal de Protección de Datos Personales en Posesión de Particulares |
| SPEI | Sistema de Pagos Electrónicos Interbancarios (transferencias MX) |
| Cédula | Cédula profesional, va impresa en la receta |
| Recall | Avisarle al paciente meses después para revisión/limpieza/seguimiento |

## Brand Voice

**Tone:** De usted con prospectos y pacientes, conversacional dentro del cuerpo, NUNCA corporativo o robótico.

**Style:**
- Directo, sin rodeos
- Narrativo (cuenta escenas: "Abrió la agenda a las 10. A las 10:15 el paciente no llegaba…")
- Con los números del dentista, no con cifras inventadas
- Con salida ("si no le interesa, me lo dice y no lo molesto más")
- No mendiga, no es agresivo. Si no pega, sigue.

**Personality (5 adjetivos):** humano · founder-led · mexicano · pragmático · honesto

## Proof Points

**Metrics:** No hay métricas de resultados con fuente todavía. No se usan cifras de pacientes que faltan, dinero recuperado ni horas ahorradas.

**Customers:** 0 clientes que pagan. No hay testimonios ni casos: no se inventan ni se usan "ilustrativos".

**Lo que sí se puede decir (y se puede enseñar en el demo):**
- Conexión cifrada (HTTPS)
- Respaldo automático diario
- Cada consultorio aislado: los datos no se mezclan
- Notas clínicas y recetas se bloquean 24 horas después de creadas, con historial de cambios
- Servidores en Estados Unidos (DigitalOcean)
- Garantía de 30 días sobre el primer pago

**Value themes:**
| Theme | Cómo lo hace |
|---|---|
| Que no se olviden de su cita | Recordatorio por WhatsApp a 1 clic, confirmación con liga, lista de espera (Pro) |
| Menos papeleo | Receta PDF con cédula, expediente digital, odontograma que se vuelve presupuesto |
| Profesionalismo | Cédula en recetas, consentimientos con firma en pantalla (Pro), expediente pensado para la NOM-004 |
| Cobrar sin perseguir | Cobro por WhatsApp a 1 clic con el monto ya puesto, abonos y mensualidades |
| Hecho para México | Español, aviso de privacidad del consultorio, pago del plan por SPEI, soporte de Omar |

## Goals

**Primary business goal:** Llegar a 100 consultorios pagando ($499+) en 2026.

**Conversion action (key metric):** Registro en `/doctor/register` con prueba de 15 días, sin tarjeta.

**Current metrics (al 2026-04-28, anotado entonces):**
- 1,168 prospectos en pipeline (16 ciudades MX)
- 5 clínicas en prod (1 demo activa)
- 0 conversiones reales aún
- 0 testimonios

**Foco:** solo dentistas.
