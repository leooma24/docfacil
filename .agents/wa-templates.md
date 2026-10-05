# Plantillas WhatsApp — DocFácil

> Tono: de usted, conversacional pero profesional. Sin emojis. Sin jerga ("le caigo", "le late", "todo bien"). Sin anglicismos vendedores ("pitch", "tip").
>
> Última actualización: 2026-10-05
>
> **Fuente de verdad:** los mensajes los arma el CRM de `/ventas` en `app/Filament/Sales/Resources/ProspectResource.php` (`buildContextualWhatsappUrl()` según el `contact_day` del prospecto). Este documento explica la lógica; si cambia el código, cambia aquí. Lo que aquí aparece entre llaves lo pone el CRM.

## Reglas universales

1. **Saludo:** "Buenas tardes/Buenos días, Dr. {Nombre}" (antes de las 12, "Buenos días"). Si el nombre del prospecto es de negocio ("Consultorio Dental X"), saludo sin nombre: ahí contesta recepción.
2. **Identidad:** "Soy Omar Lerma, ingeniero de Los Mochis". Al de Los Mochis: "ingeniero de aquí de Los Mochis" (a uno de fuera sería mentira).
3. **Primera persona** ("hice", "le escribo", "le enseño"), no "estamos" / "nuestro equipo".
4. **Sin emojis.**
5. **Sin links en el primer mensaje.**
6. **Una sola pregunta** en el primer mensaje, sobre cómo le hace hoy. No Sí/No.
7. **Siempre una salida:** "Si no le interesa, me lo dice y no lo molesto más". Es una promesa: al que dice que no, ya no se le escribe.
8. **Solo lo que el sistema hace.** Los mensajes no salen solos: DocFácil abre el WhatsApp del doctor con el texto ya escrito y él da enviar. Nada de "automático", nada de cifras sin fuente.
9. **Solo dentistas.**

---

## Día 0 · Primer contacto

Corto, con una sola pregunta. Lo demás (quién soy a fondo, programa Fundador, precios) se cuenta cuando ya contestó. En octubre de 2026 casi nadie contestaba un primer mensaje que traía todo junto.

```
Buenas tardes, Dr. {Nombre}.

Soy Omar Lerma, ingeniero de Los Mochis, y hice un sistema para consultorios dentales. ¿Cómo le hace hoy para recordarles a sus pacientes su cita?

Si no le interesa, me lo dice y no lo molesto más.
```

**La pregunta cambia según lo que hace:**

| Perfil | Pregunta |
|---|---|
| Ortodoncia | "En ortodoncia un control que se pierde retrasa todo el tratamiento, por eso le pregunto: ¿cómo le hace hoy para recordarles a sus pacientes su cita?" |
| Odontopediatría | "Con niños la cita la tienen que recordar los papás, por eso le pregunto: ¿cómo le hace hoy para avisarles?" |
| Cirugía / maxilofacial | "Con cirugías, la revisión de después es la que más se olvida, por eso le pregunto: ¿cómo le hace hoy para que sus pacientes no falten a esa cita?" |
| Los demás (se alternan por id par/impar para comparar) | "¿Cómo le hace hoy para recordarles a sus pacientes su cita?" / "¿Qué hace hoy cuando un paciente no llega a su cita?" |

**Si es cuenta de consultorio (contesta recepción):**

```
Buenas tardes. Le escribo para el doctor o la doctora del consultorio, no es para una cita.

Soy Omar Lerma, ingeniero de Los Mochis, y hice un sistema para consultorios dentales. ¿Cómo le hacen hoy para recordarles a sus pacientes su cita?

Si no les interesa, me lo dicen y no los molesto más.
```

**Por qué esta pregunta:** es operativa (cómo lo hace), no financiera (cuánto pierde): es fácil de contestar. Nadie dice que no avisa, y de su respuesta sale solo el tiempo que se le va haciéndolo a mano.

**Cuando contesta:** en el CRM, botón "Contestó", anotar cómo le hace hoy y lo que dijo con sus palabras. Ahí va la segunda pregunta, la concreta, y luego pedir la cita (ver "Pedir la demo").

---

## Día 1 · Segundo toque, con video

Repetir la pregunta a quien no la contestó casi nunca funciona. Este trae algo nuevo: un video. El video no viaja en la liga (WhatsApp no deja); la cola del CRM dice "Adjunte el video" con la liga para bajarlo.

- **Ortodoncistas:** `v3-ortodoncia` (mensualidades de brackets).
- **Los demás:** `v4-recordatorios` (botón de WhatsApp en la cita, se abre su WhatsApp con el texto, él da enviar y el paciente confirma).

```
Dr. {Nombre}, le dejo un video corto para que vea cómo queda: los recordatorios de mañana. Se abre su WhatsApp con el mensaje ya escrito, usted le da enviar y el paciente confirma con un toque.

Si le hace sentido, se lo enseño en 10 minutos por videollamada.

Y si no le interesa, dígamelo y no le vuelvo a escribir.
```

Para ortodoncia, la parte del video dice: "las mensualidades de los brackets, con el enganche, los abonos y quién va atrasado."

---

## Día 3 · Lo que hace, con hora concreta

```
Dr. {Nombre}, la última vez que le escribo esta semana.

Lo que hago es quitarle el tiempo que se le va escribiendo uno por uno los recordatorios de las citas del día siguiente. El sistema le arma la lista con el mensaje ya hecho y usted nada más va dando enviar, desde su propio WhatsApp.

Si quiere se lo enseño en 10 minutos por videollamada, o paso 15 minutos a su consultorio. ¿Le queda mejor mañana a la 1 o a las 6 de la tarde?

Y si prefiere verlo usted solo primero, aquí está: {liga del demo}
```

---

## Día 7 · Último mensaje + plan Free

```
Dr. {Nombre}, último mensaje y ya no le insisto.

Le dejo el sistema para que lo vea cuando tenga un rato: {liga del demo}

Y si quiere probarlo con sus pacientes, el plan gratis no pide tarjeta: {liga de registro con código de vendedor}

Aquí quedo por si más adelante le sirve. Gracias por su tiempo.
```

El plan Free es para siempre: 1 doctor, 15 pacientes y 10 citas al mes.

---

## Después · Re-contacto (cuando ya pasó tiempo)

Solo con lo que ya existe. Si no contesta, no se le vuelve a escribir.

```
Dr. {Nombre}, le escribo después de un tiempo.

Desde la última vez el sistema ya lleva inventario de insumos y expediente con firma, además de los recordatorios. Si quiere verlo, aquí está: {liga del demo}

Y si no, con que me lo diga basta y no vuelvo a escribirle.
```

---

## Pedir la demo (cuando ya contestó)

Contestar no es agendar, y "¿le interesa una demo?" se contesta con un no. Dos horas concretas se contestan con una de las dos, o con "mejor el jueves", que también es avanzar.

```
Dr. {Nombre}, le propongo algo concreto.

En 10 minutos por videollamada le enseño cómo quedaría su agenda con lo que me contó, o si prefiere paso 15 minutos a su consultorio, lo que se le haga más cómodo.

¿Le queda mejor el {mañana} a la 1 o a las 6 de la tarde?

Y si antes quiere verlo usted solo con calma, aquí está: {liga del demo}
```

---

## Prueba · Cuando pidió la liga (status → trial)

```
Aquí está su acceso, Dr. {Nombre}.

15 días Pro gratis, sin tarjeta:
{liga de registro}

Detalle: el wizard de bienvenida le pre-llena los datos que ya me compartió. Tarda 2 minutos en configurarlo. Si en algún paso se atora, envíeme captura por aquí y lo resuelvo en el momento.
```

Durante los 15 días trae todo lo del plan Pro. Al terminar se queda con el plan que pague o pasa al Free, que sigue funcionando con sus límites.

---

## Ajustes cuando aplique

- **Si la doctora es mujer:** "Buenas tardes, Dra. {Nombre}"
- **Antes de las 12pm:** "Buenos días" en lugar de "Buenas tardes"
- **Si no es dentista:** no se le escribe. DocFácil es solo para dentistas.
- **Si la respuesta es seca ("No me interesa"):** no insistir. Mandar: *"Entendido, Dr. {Nombre}. Gracias por el tiempo. Si cambia de opinión en el futuro, aquí estoy."* y marcarlo como perdido.

---

## Lo que NO hacer (lecciones acumuladas)

- Emojis: restan profesionalismo.
- "Le caigo", "le late": jerga.
- "Pitch" / "tip" / "feedback" / "chance": anglicismos. Decir "presentación" / "consejo" / "retroalimentación" / "oportunidad".
- "Estamos invitando": corporativo plural.
- "Beta" / "beta gratuito": suena a no-listo. Decir "15 días Pro gratis" o "plan Free para siempre".
- Decir que los mensajes salen solos o que algo es automático: se abre su WhatsApp y él da enviar.
- Cifras sin fuente ("pierde tantos miles al mes"). Si hay que hablar de dinero, con sus números: "si le recupera una cita al mes...".
- Repetir el nombre del consultorio (parece plantilla automática).
- Saludar al negocio en vez de a la persona.
- Saludar con "Hola" seco: mejor "Buenas tardes" o "Buenos días".
- Lista de funciones ("agenda + expediente + recetas + WhatsApp").
- Pregunta cerrada Sí/No al final.
