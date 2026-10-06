---
name: DocFácil
description: El estuche de limas. La landing pública para dentistas, donde cada paso del consultorio lleva su color ISO.
colors:
  acero: "#eef1f3"
  acero-2: "#dde3e7"
  acero-3: "#c3ccd3"
  tinta: "#15181c"
  tinta-2: "#353c44"
  tinta-3: "#565f69"
  iso-15-blanco: "#ffffff"
  iso-20-amarillo: "#f2c400"
  iso-25-rojo: "#d7262e"
  iso-30-azul: "#1f5fd1"
  iso-35-verde: "#178049"
  iso-40-negro: "#15181c"
  wa: "#18723f"
  wa-burbuja: "#d9fdd3"
  wa-fondo: "#efe7de"
  carcasa: "#0d0f12"
  error: "#a3161d"
typography:
  display:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "clamp(40px, 5vw, 70px)"
    fontWeight: 850
    lineHeight: 0.98
    letterSpacing: "-0.025em"
    fontVariation: "'wdth' 78"
  headline:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "clamp(34px, 4.6vw, 60px)"
    fontWeight: 820
    lineHeight: 1.02
    letterSpacing: "-0.02em"
    fontVariation: "'wdth' 80"
  title:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "22px"
    fontWeight: 750
    lineHeight: 1.2
    fontVariation: "'wdth' 90"
  lead:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "clamp(18px, 1.6vw, 21px)"
    fontWeight: 400
    lineHeight: 1.5
  body:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "17px"
    fontWeight: 400
    lineHeight: 1.65
    fontVariation: "'wdth' 100"
  label:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "14px"
    fontWeight: 650
    letterSpacing: "0.01em"
  ejemplo:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "12.5px"
    fontWeight: 700
    letterSpacing: "0.06em"
    fontVariation: "'wdth' 75"
  cifra:
    fontFamily: "Archivo, system-ui, sans-serif"
    fontSize: "48px"
    fontWeight: 850
    lineHeight: 1
    letterSpacing: "-0.02em"
    fontVariation: "'wdth' 75"
    fontFeature: "tnum"
rounded:
  chico: "8px"
  medio: "12px"
  grande: "16px"
  panel: "28px"
  pastilla: "999px"
spacing:
  gutter: "16px"
  gutter-desktop: "32px"
  container: "1200px"
  paso: "96px"
  paso-desktop: "128px"
components:
  button-primary:
    backgroundColor: "{colors.tinta}"
    textColor: "{colors.iso-15-blanco}"
    rounded: "{rounded.medio}"
    padding: "0 26px"
    height: "54px"
    typography: "{typography.label}"
  button-primary-hover:
    backgroundColor: "#000000"
  button-outline:
    backgroundColor: "transparent"
    textColor: "{colors.tinta}"
    rounded: "{rounded.medio}"
    padding: "0 26px"
    height: "54px"
  button-outline-hover:
    backgroundColor: "{colors.tinta}"
    textColor: "{colors.iso-15-blanco}"
  button-on-field:
    backgroundColor: "{colors.iso-15-blanco}"
    textColor: "{colors.tinta}"
    rounded: "{rounded.medio}"
    padding: "0 26px"
    height: "54px"
  button-whatsapp:
    backgroundColor: "{colors.wa}"
    textColor: "{colors.iso-15-blanco}"
    rounded: "{rounded.medio}"
    padding: "0 26px"
    height: "54px"
  button-whatsapp-hover:
    backgroundColor: "#135f34"
  input-field:
    backgroundColor: "{colors.iso-15-blanco}"
    textColor: "{colors.tinta}"
    rounded: "{rounded.medio}"
    padding: "12px 14px"
    height: "52px"
  card-panel:
    backgroundColor: "{colors.iso-15-blanco}"
    textColor: "{colors.tinta}"
    rounded: "{rounded.panel}"
    padding: "26px 20px"
  plan-card:
    backgroundColor: "{colors.iso-15-blanco}"
    textColor: "{colors.tinta}"
    rounded: "{rounded.grande}"
    padding: "26px 22px 22px"
  plan-card-popular:
    backgroundColor: "{colors.tinta}"
    textColor: "{colors.iso-15-blanco}"
  status-pill:
    backgroundColor: "{colors.acero-2}"
    textColor: "{colors.tinta-2}"
    rounded: "{rounded.pastilla}"
    padding: "5px 8px"
  nav-bar:
    backgroundColor: "rgba(238,241,243,.86)"
    textColor: "{colors.tinta-2}"
    height: "68px"
---

# Design System: DocFácil

<!-- Alcance: este sistema gobierna solo la landing pública (/ y /dentistas, resources/views/dentistas.blade.php y partials/landing-video.blade.php). El panel de Filament del doctor tiene su propio tema (vidrio teal, resources/views/filament/custom/theme-styles.blade.php) y queda FUERA de este documento: no se mezclan tokens entre los dos. -->

## Overview

**Creative North Star: "El estuche de limas"**

La página se recorre como un estuche de limas de endodoncia que se va abriendo. La serie ISO 15 a 40 es la ley de color: cada paso del consultorio (agenda, recordatorio, consulta, odontograma y presupuesto, mensualidades, Omar) tiene el color de su mango y ese color no se usa para nada más. La base es una charola de acero frío con tinta grafito; los pasos se abren como campos de color a sangre completa, y entre ellos el producto se muestra vivo (celular con agenda que se confirma, odontograma que marca sus dientes, escalera de pagos que se llena) sobre hojas blancas grandes.

La letra es una sola familia variable, Archivo, que se condensa en titulares, cifras y etiquetas de empaque, y se abre a ancho normal en el cuerpo. La densidad es de lectura en el celular: cuerpo de 17px, botones de 54px, mucho aire vertical por paso. El objeto firma es el mango moleteado con su vástago de acero, que aparece como índice del inicio, cabeza de cada paso, riel de navegación, marcador de las preguntas y firma del pie.

Rechazado de forma explícita por el contrato: la landing teal con laptop flotante y cuadrícula de funciones.

**Key Characteristics:**
- Charola de acero frío y tinta grafito como base neutral.
- Seis colores ISO, uno por paso, a sangre completa.
- El mango moleteado como objeto firma y como navegación.
- Una familia variable (Archivo) con el ancho como eje expresivo.
- Producto vivo con datos de ejemplo marcados, no capturas de adorno.
- Movimiento que nace del mango: crece, cae, abre el campo.

## Colors

Acero frío y grafito neutros, con seis colores de calibre que funcionan como señal de paso y nunca como decoración.

### Primary
- **Tinta grafito** (tinta): texto, botón principal, plan popular, campo del paso 40, pie. Es el color de acción en las superficies de acero.

### Secondary
- **Serie ISO de calibres** (iso-15-blanco a iso-40-negro): cada uno pertenece a un paso. 15 blanco agenda; 20 amarillo recordatorio (también la selección de texto, el sello del plan popular y el halo de foco de los campos, porque esos momentos son el recordatorio de la página); 25 rojo consulta; 30 azul odontograma y presupuesto (y el anillo de foco global); 35 verde mensualidades; 40 negro Omar.
- **El verde se oscureció a propósito.** El código ISO usa #1E9E57; el build lleva #178049 porque el texto blanco sobre el campo del paso 35 necesita AA (4.9:1 contra 3.5:1). El token es el valor que se envía, no el del catálogo.

### Tertiary
- **Verde WhatsApp** (wa) y sus fondos (wa-burbuja, wa-fondo): solo para lo que es WhatsApp, el botón que abre WhatsApp y las maquetas de chat. No es un color de paso y no compite con el ISO 35. Va en #18723f y no en el #1f8a52 de antes: con texto blanco de 17px daba unos 4.4:1, debajo de AA; así da unos 5.9:1, igual que se hizo con el ISO 35.

### Neutral
- **Acero** (acero): fondo de página, barra, botón fijo inferior, celdas vacías de la escalera.
- **Acero 2** (acero-2): marco de la foto del inicio, pastillas de estado en reposo, divisores finos.
- **Acero 3** (acero-3): bordes de tarjetas, campos y separadores.
- **Grafito 2 y 3** (tinta-2, tinta-3): texto secundario y terciario sobre acero y blanco.
- **Carcasa** (carcasa): el cuerpo del celular dibujado y el marco de las capturas de teléfono.
- **Error** (error): mensajes de validación de los campos.

### Named Rules
**The Un Calibre, Un Paso Rule.** Cada color ISO se usa solo dentro de su paso (su campo, su mango, sus estados internos) o como la señal del mismo momento en otra parte. Si un color ISO aparece sin su paso, sobra.

**The Contraste Antes Que Catálogo Rule.** Cuando un color de calibre no da AA con su texto, se oscurece el campo y se anota por qué; el catálogo ISO no gana sobre la lectura.

## Typography

**Display Font:** Archivo variable (con system-ui, sans-serif), cargada con ejes wdth 62..125 y wght 400..900.
**Body Font:** Archivo variable a ancho 100.

**Character:** Una sola voz de empaque industrial: condensada y pesada en titulares y cifras como la etiqueta de un estuche, normal y tranquila en el cuerpo.

### Hierarchy
- **Display** (850, clamp(40px, 5vw, 70px), 0.98, wdth 78): el titular del inicio, dos renglones que entran uno por uno.
- **Headline** (820, clamp(34px, 4.6vw, 60px), 1.02, wdth 80): el titular de cada paso y de cada bloque.
- **Title** (750, 22px, 1.2, wdth 90): subtítulos dentro de tarjetas y bloques; títulos menores bajan a 19-21px con wdth 85-88.
- **Lead** (400, clamp(18px, 1.6vw, 21px), 1.5, máx. 34ch): el párrafo bajo el titular del inicio.
- **Body** (400, 17px, 1.65, máx. 56ch; 64ch en preguntas): todo el texto corrido.
- **Label** (650, 14px, 0.01em): notas cortas, totales, pies de foto.
- **Ejemplo** (700, 12.5px, 0.06em, mayúsculas, wdth 75): solo la leyenda "Datos de ejemplo" sobre los componentes vivos.
- **Cifra** (850, 48px, wdth 75, números tabulares): precios y totales; horas y montos siempre en tabular-nums.

### Named Rules
**The Ancho Es La Voz Rule.** La jerarquía se marca con ancho y peso de la misma familia (wdth 72-90 condensado arriba, 100 en cuerpo), no con una segunda familia.

**The Cuerpo De 17 Rule.** Ningún texto de lectura baja de 17px; lo menor es solo etiqueta, nota o dato dentro de una maqueta.

## Layout

Contenedor de 1200px centrado, con margen lateral de 16px en el celular y 32px desde 900px. Todo nace en una columna y se abre en dos o tres columnas a 900px (rejillas asimétricas como 1.15fr/.85fr, 1.2fr/.8fr, 1fr auto 1fr). Puntos de quiebre usados: 640px (estuche con etiquetas largas, rejillas de dos), 900px (navegación completa, riel lateral, rejillas de escritorio, se esconde el botón fijo), 1280px (el riel se separa más del borde).

Cada paso es una banda a sangre completa con 96px de aire arriba y abajo (128px en escritorio); los bloques de acero (precios, preguntas, contacto) llevan el mismo ritmo. La cabeza de cada paso es su mango al lado del titular, con 22px de separación y 40px antes del contenido. El inicio deja el titular y los dos botones a la izquierda arriba del pliegue, el celular y la foto de las limas a la derecha, y el estuche de seis mangos debajo.

La navegación del recorrido vive en un riel: en el celular, seis mangos pequeños centrados dentro de la barra; en escritorio, una columna fija al costado izquierdo. El riel y el botón fijo inferior aparecen solo cuando el inicio sale de la pantalla.

## Elevation & Depth

Híbrido: los campos de color y el acero son planos; la profundidad la ponen sombras largas, suaves y caídas hacia abajo bajo los objetos que se pueden "levantar" de la charola (hojas blancas, celular, videos, botones), teñidas con el tono del campo donde están.

### Shadow Vocabulary
- **Hoja sobre el campo** (`box-shadow: 0 40px 70px -40px <tono del campo>`): tarjetas blancas grandes; el tono cambia por paso (rgba(80,62,0,.55) en amarillo, rgba(4,20,60,.7) en azul, rgba(3,40,20,.7) en verde, rgba(21,24,28,.45) en acero).
- **Celular** (`box-shadow: 0 50px 80px -40px rgba(21,24,28,.75), inset 0 0 0 1.5px #3b4148`): el teléfono dibujado del inicio.
- **Video** (`box-shadow: 0 30px 60px -30px rgba(0,0,0,.6)`): los videos del producto.
- **Botón** (`box-shadow: 0 14px 30px -14px rgba(21,24,28,.7)`; en hover `0 20px 36px -16px rgba(21,24,28,.75)`): el botón grafito.
- **Plan en hover** (`box-shadow: 0 30px 50px -30px rgba(21,24,28,.45)`): las tarjetas de precio al pasar.
- **Mango** (`inset 0 0 0 1px rgba(0,0,0,.12), 0 8px 14px -8px rgba(21,24,28,.55)`; recorte fotográfico con `drop-shadow(0 8px 10px rgba(21,24,28,.35))`).

### Named Rules
**The Sombra Del Campo Rule.** La sombra de una hoja toma el tono de su campo, nunca un negro genérico sobre color.

**The Vidrio Solo Arriba Y Abajo Rule.** El desenfoque (blur 12-14px sobre acero translúcido) es solo para la barra superior y el botón fijo inferior, que flotan sobre el contenido.

## Shapes

Cuatro radios y la pastilla: 8px para controles chicos y celdas, 12px para botones, campos y cajas, 16px para tarjetas y videos, 28px para las hojas grandes y fotos protagonistas; 999px para estados, sellos y la etiqueta del riel. Los bordes son de 1 a 1.5px en acero 3; 2px en grafito cuando algo debe pesar (botón de línea, Programa Fundador, mensualidad vencida). Los separadores del presupuesto son punteados, como un ticket. La silueta recurrente es el mango: rectángulo de 7px arriba y 9px abajo, con moleteado de franjas horizontales, brillo lateral y vástago de acero de 3px.

## Components

### Buttons
Firmes y grandes, hechos para el pulgar.
- **Shape:** esquinas medianas (12px), altura mínima 54px (44px en la barra), 750 de peso, ícono SVG de 20px.
- **Primary:** grafito con texto blanco, sombra de botón; al pasar sube 2px. Dentro del plan popular se invierte a blanco.
- **Línea:** transparente con borde grafito de 2px; al pasar se llena de grafito.
- **Sobre campo de color:** blanco con texto grafito.
- **WhatsApp:** verde WhatsApp con texto blanco, solo para acciones que abren WhatsApp.
- **Active:** baja 1px y escala a .98. Transiciones de .25s con la curva "sale".

### Chips
- **Estado de cita:** pastilla de 11px, 750 de peso. Por confirmar en acero 2; Recordado en amarillo pálido (#fff4c2 / #6b5300); Confirmó en verde pálido (#dff5e7 / #12663a).
- **Sello del plan popular:** pastilla amarilla ISO 20 con texto grafito, montada sobre el borde de la tarjeta.

### Cards / Containers
- **Corner Style:** 28px las hojas (recordatorio de prueba, odontograma, presupuesto, plan de pagos, formulario); 16px planes, celdas y videos.
- **Background:** blanco sobre cualquier campo; grafito para la celda oscura y el plan popular.
- **Shadow Strategy:** Hoja sobre el campo (ver Elevation & Depth).
- **Border:** planes y avisos con 1px acero 3; Programa Fundador con 2px grafito y sus lugares como círculos de 14px.
- **Internal Padding:** 26px 20px en el celular, 30-32px en escritorio.

### Inputs / Fields
- **Style:** blanco, borde de 1.5px acero 3, 12px de radio, 52px de alto, letra de 17px; etiqueta arriba en 14px/700 grafito 2.
- **Focus:** el borde pasa a grafito con halo amarillo de 4px (rgba(242,196,0,.45)). El foco del resto de la página es un contorno azul ISO 30 de 3px con 3px de separación.
- **Error:** texto de 14px/650 en rojo error bajo el campo; la ayuda en 13.5px grafito 3.

### Navigation
- **Barra:** fija, 68px, acero translúcido con desenfoque; gana un borde acero 3 cuando el inicio sale. Enlaces de 15px/600 en grafito 2 que se subrayan con 2px y separación de 6px al pasar. Debajo de 900px se cambia por un botón de menú de 46px y un panel de acero.
- **Riel de mangos:** el mango del paso en pantalla crece (1.4 en escritorio, 1.3 x 1.2 en el celular, con anillo grafito); en escritorio, al pasar sale su nombre en una pastilla grafito.

### El mango (componente firma)
Plástico moleteado del color de su calibre, con el número arriba (blanco sobre 25-40, grafito sobre 15-20) y el vástago de acero abajo. Donde se ve grande (estuche del inicio y cabeza de cada paso) se usa el recorte fotográfico mango-15..40.png; en tamaños chicos (riel, preguntas, pie) es CSS. En las preguntas gira 90° al abrir.

### Componentes vivos
El celular con la agenda de mañana, el recordatorio que el visitante se manda a su propio WhatsApp, el odontograma real con su presupuesto (cada línea marca su diente en azul) y la escalera de mensualidades. Todos llevan la leyenda "Datos de ejemplo" en el estilo Ejemplo. Movimiento: GSAP con ScrollTrigger; cada campo de color se abre en un círculo que nace en su mango mientras el mango cae con rebote; curva base cubic-bezier(.16, 1, .3, 1). Con prefers-reduced-motion todo queda en su estado final.

## Do's and Don'ts

### Do:
- **Do** asignar a cada paso nuevo uno de los seis calibres solo si es de verdad ese paso; lo demás vive en acero y grafito.
- **Do** poner texto blanco solo sobre los campos 25, 30, 35 y 40, y grafito sobre 15 y 20.
- **Do** marcar con "Datos de ejemplo" todo componente que muestre datos inventados.
- **Do** usar números tabulares en horas, precios y totales.
- **Do** mantener cuerpo de 17px, botones de 48px o más y contraste AA.
- **Do** escribir el CSS de esta página en su propio bloque o en `style=""`, no con utilidades responsive de Tailwind.

### Don't:
- **Don't** usar un color ISO como acento decorativo fuera de su paso.
- **Don't** volver al verde ISO de catálogo (#1E9E57) para el campo 35: no da AA con texto blanco.
- **Don't** usar el verde WhatsApp para algo que no abra WhatsApp ni lo represente.
- **Don't** agregar una segunda familia tipográfica; la jerarquía sale del ancho y el peso de Archivo.
- **Don't** traer a esta página el vidrio teal del panel del doctor, ni llevar este sistema al panel sin una decisión aparte.
