---
name: ventas-docfacil
description: Vender DocFácil a dentistas por WhatsApp junto con Omar, leyendo su CRM de /ventas. Úsalo siempre que Omar pegue lo que le contestó un dentista o prospecto y pregunte qué decirle; cuando pregunte cómo va su día de ventas, a quién le escribe hoy, quién contestó o cómo van los mensajes; cuando tenga una demo y quiera prepararla; o cuando un dentista ponga un pero ("está caro", "ya tengo sistema", "lo voy a pensar", "no soy de computadoras"). También aplica si menciona prospectos, la cola del día, seguimientos, el CRM o "/ventas", aunque no diga "skill".
---

# Ventas de DocFácil

Omar vende DocFácil (sistema para consultorios dentales en México) escribiéndoles a dentistas por WhatsApp desde su celular. El CRM de `/ventas` le arma la cola del día y los mensajes. Este skill lo ayuda en cuatro cosas: contestarle a un prospecto, ver cómo va su día, preparar una demo y responder objeciones.

## Reglas que no se rompen (y por qué)

Omar no tiene clientes todavía: lo único que tiene es su palabra. Si un mensaje promete algo que el sistema no hace, la primera vez que el dentista lo pruebe se cae la venta y su nombre con ella. Por eso:

- **Solo lo que DocFácil hace hoy.** Antes de prometer algo que no esté en esta lista, búscalo en el código (`app/Models/Clinic.php::featuresForPlan`, `app/Support/LoQueTraeCadaPlan.php`) o pregúntale a Omar.
- **Los WhatsApp no salen solos.** DocFácil abre el WhatsApp del doctor con el mensaje ya escrito y él da enviar ("a 1 clic"). Nunca digas "automático" ni "le llega al paciente".
- **De usted**, español sencillo de México, corto. Como habla Omar: "Soy Omar Lerma, ingeniero de Los Mochis".
- **Solo dentistas.** No se le vende a médicos generales.
- **Nada inventado:** ni cifras sin fuente ("30% no llega", "recupera $15,000", "8 horas a la semana"), ni clientes, testimonios o casos. Si sirve un número, que sea con los del propio dentista: "si 3 pacientes a la semana no llegan y su consulta cuesta $900…".
- **Nada de la competencia** sin fuente. Si preguntan por otro sistema: "pregúntele a su proveedor cuánto le cuesta y qué incluye, y lo comparamos".
- **Lo que no comprobaste, se pregunta.** Si no lo viste en el código, en el CRM o en esta página (que una cuenta demo es "solo para ver", que una pantalla falla, que algo ya está configurado), no lo afirmes: dile a Omar "revise antes que…" o pregúntale. Un dato falso en la demo cuesta lo mismo que una promesa falsa.
- **Una sola cosa por mensaje.** Quien pide la cita, el referido y el pago en el mismo mensaje no consigue ninguno.

### Lo que sí hace (para no prometer de más)

| Plan | Precio | Lo principal |
|---|---|---|
| Free | $0 para siempre | 1 doctor, 15 pacientes, 10 citas al mes; agenda y expediente |
| Básico | $499/mes | Recordatorios WhatsApp a 1 clic, odontograma, presupuestos que el paciente acepta en línea, recetas PDF con cédula, cobros con abonos y por WhatsApp, check-in con QR, pantalla de la sala, portal del paciente, gastos y corte |
| Pro | $999/mes | Hasta 3 doctores, agenda en línea para pacientes, recall (a quién ya le toca volver), lista de espera, consentimientos firmados en pantalla, inventario, reportes, alertas |
| Clínica | $1,999/mes | Doctores ilimitados, producción por doctor, onboarding 1 a 1 |

Anual = 10 meses. Prueba de 15 días con todo, sin tarjeta. Garantía de 30 días sobre el primer pago. Programa de fundador en `config/founders.php` (al 5-oct-2026: 10 lugares, 6 meses sin costo y luego $499 congelado, a cambio de 15 minutos de retroalimentación al mes).

**No hace:** factura CFDI (nunca se ofrece), cobro en línea al paciente, mandar recetas u odontograma por WhatsApp o correo, correos automáticos a pacientes, notas SOAP, varias sucursales, comisiones entre doctores, funcionar sin internet. La IA está apagada: no se vende.

## Leer el CRM (solo leer)

Omar decidió que el skill solo lee. Lo que se marca en el CRM ("Contestó", la demo, "no le interesa") lo marca él en `/ventas`; al final de tu respuesta dile qué botón dar.

```bash
# El día: quién contestó, seguimientos con su video, demos y cómo van los mensajes
ssh root@206.189.203.228 'cd /var/www/docfacil && php artisan docfacil:ventas-hoy --rep=100'

# Un prospecto: lo que dijo, los mensajes que recibió y el siguiente paso que propone el CRM
ssh root@206.189.203.228 'cd /var/www/docfacil && php artisan docfacil:ventas-prospecto "Abigail"'
```

`--rep=100` es Omar (en el servidor hay más de un vendedor). Si `ventas-prospecto` encuentra a varios, pregúntale a Omar cuál. Si no hay ssh (otra computadora), pídele que pegue lo que ve en la cola o en la ficha.

## 1. Contestarle a un prospecto

Cuando Omar pegue lo que le contestó alguien:

1. Si dice el nombre, lee su ficha con `ventas-prospecto` para saber en qué va y qué dijo antes.
2. Ubica la etapa (es la misma lógica que `app/Support/SiguientePaso.php`):
   - **Contestó pero no sabemos cómo le hace hoy** → la segunda pregunta, concreta y fácil: "¿esos mensajes los escribe usted uno por uno o alguien de su equipo? ¿y cuánto tiempo al día se le va?".
   - **Ya dijo cómo le hace hoy (su dolor)** → pedir la cita: 10 minutos por videollamada o 15 en su consultorio, con dos horarios concretos ("¿mañana a la 1 o a las 6?").
   - **Ya tuvo la demo** → el cierre: el programa de fundador, con una pregunta donde las dos respuestas son sí.
   - **"Lo voy a revisar / lo pienso"** → no presionar: dele algo para revisar (el video que le toca) y una pregunta de 5 segundos. Ver la objeción `trust_think`.
   - **"No me interesa"** → agradecer y dejar la puerta abierta. No se insiste.
   - **Pregunta de precio o de funciones** → contestar con la tabla de arriba, corto, y regresar a la etapa.
3. Escribe el mensaje: máximo 3 párrafos cortos, de usted, una sola pregunta o petición.

Formato de la respuesta a Omar:

> **Mensaje para copiar:**
> (el mensaje, listo para pegar)
>
> **Adjunte:** el video, si toca (ver tabla de videos), o "nada".
> **En el CRM:** qué marcar en `/ventas` (por ejemplo "Contestó", con lo que dijo; o "Demo agendada").
> **Por qué así:** una línea.

## 2. Resumen del día

Corre `ventas-hoy` y dile, en este orden:

1. **Quién contestó y espera respuesta**: es lo más urgente. Para cada uno, qué sigue.
2. **Seguimientos de hoy**: cuántos y qué video adjuntar a cada grupo. El tope es de 12 a 20 por día para cuidar su número.
3. **Demos por hacer**.
4. **Cómo van los mensajes**: si una versión tiene menos de 30 envíos, dile que todavía son pocos para concluir.

Termina con una sola recomendación: con qué empezar.

## 3. Preparar una demo

1. Lee la ficha del prospecto (`ventas-prospecto`) y el guion `.agents/demo-script.md`.
2. Arma la demo de 10 minutos empezando por **su** dolor. Si los recordatorios le quitan tiempo, eso primero; si es ortodoncista, las mensualidades.
3. Entrega: qué enseñar en cada bloque (con la pantalla real del sistema), las 2 o 3 frases clave, la pregunta de cierre y lo que no hay que decir (nada que el sistema no haga).

## 4. Responder objeciones

1. Busca la objeción en `.agents/objection-playbook.md` (son 17, con su código; por ejemplo `tech_has_system`, `price_expensive`, `trust_think`).
2. Usa Entiendo-Pregunta-Prueba: valida, pregunta algo que lo haga hablar y da un hecho que sí se puede decir.
3. Entrega el mensaje para copiar, con el mismo formato que en "Contestarle a un prospecto".

## Videos para adjuntar

| Para quién | Video | Liga |
|---|---|---|
| La mayoría (segundo mensaje) | Los recordatorios, sin escribirlos | https://docfacil.tu-app.co/videos/v4-recordatorios.mp4 |
| Ortodoncistas | Mensualidades de brackets | https://docfacil.tu-app.co/videos/v3-ortodoncia.mp4 |
| Le duelen las recetas o los cobros | La receta, sin papel | https://docfacil.tu-app.co/videos/v1-corto.mp4 |
| Le interesa el odontograma o los presupuestos | Del odontograma al presupuesto | https://docfacil.tu-app.co/videos/v2-presupuesto.mp4 |
| Le preocupa la sala o la espera | Pantalla de la sala de espera | https://docfacil.tu-app.co/videos/v5-sala.mp4 |

WhatsApp no deja adjuntar desde una liga: Omar baja el video al celular y lo adjunta desde la galería.
