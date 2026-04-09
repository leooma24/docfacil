# Guión cronometrado — DocFácil demo screencast

Tiempos coinciden con `record.js`. Total ~1:49 de pantalla.
La idea: grabas voz por separado siguiendo este script y la sincronizas al video en cualquier editor (CapCut, DaVinci, ElevenLabs + ffmpeg, etc.).

Tono: cercano, doctor mexicano. Sin gritar, sin "amigos!". Estilo "déjame mostrarte cómo".

---

## 0:00 – 0:05 · Pantalla de login (5s)
> "Mira. Esto es DocFácil. Le doy enter y ya estoy adentro — sin instalar nada, solo navegador."

## 0:05 – 0:13 · Dashboard (8s)
> "Esta es la pantalla del día. Cuántas citas tengo, cuánto llevo cobrado, qué pacientes están pendientes. Todo en un solo lugar, sin abrir tres programas."

## 0:13 – 0:23 · Lista de citas (10s)
> "Aquí están todas las citas. Confirmadas, sin confirmar, las que faltaron. Filtro por doctor, por día, por estado. Lo que en libreta te toma media hora, aquí lo ves en dos segundos."

## 0:23 – 0:33 · Calendario visual (10s)
> "Y si prefieres verlo en calendario, también. Arrastras una cita para moverla, das clic en un hueco para crear una nueva. Funciona igual de bien en celular."

## 0:33 – 0:41 · Lista de pacientes (8s)
> "Tus pacientes. Buscas por nombre, por teléfono, por lo que sea. Cero papeles, cero archivos sueltos."

## 0:41 – 0:53 · Perfil del paciente (12s)
> "Y cuando entras a un paciente, tienes TODO de él en una sola pantalla. Su historial, sus citas, sus recetas, sus pagos, su odontograma si eres dentista. Esto es lo que de verdad hace diferencia: deja de buscar, ahora ya sabes."

## 0:53 – 1:03 · Recetas (10s)
> "Las recetas. Antes a mano, ilegibles, perdidas. Ahora cada receta queda guardada con el nombre del paciente, los medicamentos, las dosis, todo."

## 1:03 – 1:13 · Detalle de receta (10s)
> "Y se genera un PDF profesional con tu nombre, tu cédula y tu logo. Lo descargas o lo mandas al paciente por WhatsApp directo desde aquí."

## 1:13 – 1:23 · Cobros (10s)
> "Cobros. Registras pago en efectivo, transferencia o tarjeta. El sistema te dice cuánto te deben, quién te debe, y cuánto llevas en el día."

## 1:23 – 1:31 · Expediente clínico (8s)
> "Expediente clínico completo. Diagnóstico, tratamiento, signos vitales. Todo queda registrado con fecha y firma — por si alguna vez lo necesitas."

## 1:31 – 1:39 · Odontograma (8s)
> "Si eres dentista: odontograma interactivo. Marcas el diente, eliges la condición, queda guardado en el historial. Tu paciente ve exactamente qué le hiciste."

## 1:39 – 1:49 · Dashboard de cierre (10s)
> "Esto es DocFácil. Hecho en México, para consultorios mexicanos. 15 días gratis, sin tarjeta. Pruébalo desde tu celular ahorita — el link está abajo. Nos vemos adentro."

---

## Notas para postproducción

- **Voz IA recomendada:** ElevenLabs voz "Mateo" o "Diego" en español neutro. Velocidad 1.0x, estabilidad 50%, claridad 75%.
- **Música:** opcional, instrumental suave a -20dB. Sin esto también funciona.
- **Cortes:** el video grabado tiene transiciones de página naturales (la barra de carga de Filament). Si quedan muy bruscas, mete un fade de 200ms entre escenas.
- **Resolución:** 1280x800 (suficiente para landing). Si quieres 1080p, cambia VIEWPORT en `record.js` a `{width:1920, height:1080}` y vuelves a correr.
- **Si una escena se rompió** (selector falló): el video sigue grabando — solo se va a ver una pantalla en blanco esos 10s. Edita esa parte fuera, o vuelve a correr el script.
