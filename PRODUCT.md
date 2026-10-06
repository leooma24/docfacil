# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Dentistas con consultorio propio en México, de 1 a 3 sillones, que atienden y son dueños a la vez. Muchos llevan la agenda en una libreta o en Excel y confirman citas por WhatsApp, a mano, uno por uno. En consultorios de 2 o 3 sillones a veces la asistente influye en la decisión ("yo lo voy a usar más"). La mayoría llega a la página desde un mensaje de WhatsApp de Omar, en el celular. Solo dentistas: no médicos generales ni IMSS/ISSSTE.

## Product Purpose

DocFácil pasa el consultorio dental del papel al celular: agenda, recordatorios por WhatsApp a un clic, odontograma FDI, presupuestos que el paciente acepta en línea, recetas PDF con cédula, cobros con abonos y mensualidades, check-in con QR y pantalla de la sala de espera. El éxito es que el dentista deje la libreta y que no se le olviden citas, cobros ni tratamientos a medias.

## Positioning

Hecho para el dentista mexicano que va empezando y lo lleva directo el fundador: Omar Lerma, de Los Mochis, le contesta por WhatsApp y le carga sus pacientes. Los mensajes salen del WhatsApp del propio doctor (DocFácil abre su WhatsApp con el texto escrito y él da enviar), sin costo por mensaje. El odontograma ya es el presupuesto.

## Operating Context

- El doctor revisa la agenda entre pacientes, en el celular o en la computadora de recepción.
- WhatsApp es el canal de todo: con los pacientes y con Omar (668 249 3398).
- Prueba de 15 días con todo, sin tarjeta. Garantía de 30 días sobre el primer pago (`/terminos#garantia`).
- Planes (fuente de verdad: `App\Support\LoQueTraeCadaPlan`, `Commission::monthlyPriceForPlan()`): Free $0, Básico $499, Pro $999, Clínica $1,999 al mes; el anual sale en 10 meses.
- Programa Fundador (`config/founders.php`): los lugares que quedan salen de contar fundadores reales en la base.

## Capabilities and Constraints

- Solo se dice lo que el sistema hace hoy. Lo que NO hace y no se promete: WhatsApp que se manda solo, recordatorios automáticos, links de pago, recetas por WhatsApp o correo, varios consultorios en una cuenta, CFDI/factura, IA, trabajar sin internet.
- Pruebas que cuidan la página: `LandingSinCifrasInventadasTest`, `MaterialesDeVentaHonestosTest`, `GarantiaEnTerminosTest`, `LandingVideosTest`, `LandingTest`, `LugaresDeFundadorTest`.
- Tailwind responsive no siempre compila en producción: lo crítico de la página va en CSS propio o `style=""`.
- El texto es de usted, en español de México.

## Brand Commitments

- El logo de DocFácil (`public/images/logo_doc_facil.png`, `logo_doc_facil_white.png`, `solo_logo.png`) se queda. Colores, letra y estilo de la página son libres.
- Omar es la cara y la voz: primera persona, cercano, sin exagerar.
- Acción principal: "Probar 15 días gratis" (registro). Segunda, visible: "Escribirle a Omar" por WhatsApp.

## Evidence on Hand

- Foto de Omar: `public/images/founder-omar.jpg` (original grande) y `founder-omar-320.jpg`. Autorizada en tamaño protagonista.
- Videos reales del producto: `public/videos/v1-consulta`, `v1-corto`, `v2-presupuesto`, `v3-ortodoncia`, `v4-recordatorios`, `v5-sala` (mp4 con su póster).
- Capturas del producto: `public/images/landing/` y se pueden tomar nuevas con la cuenta demo (`/demo`).
- Imágenes generadas con el servicio de la Mac de Omar, autorizadas.
- No hay clientes, testimonios, casos de éxito ni cifras con fuente. No se inventan.

## Product Principles

1. Lo que se enseña ya existe: cada pantalla, video y frase corresponde al sistema de hoy.
2. Todo a pocos clics y conectado: la página demuestra el flujo, no una lista de funciones.
3. El dentista habla con una persona, no con una empresa: Omar está a la vista.
4. Primero el celular: casi todos llegan desde WhatsApp.
5. Quitar el miedo antes que vender: sin tarjeta, garantía y ayuda para empezar.

## Accessibility & Inclusion

Lectores de 35 a 65 años en el celular: texto de cuerpo de al menos 17px, botones de al menos 48px de alto y contraste AA. Respetar `prefers-reduced-motion`.
