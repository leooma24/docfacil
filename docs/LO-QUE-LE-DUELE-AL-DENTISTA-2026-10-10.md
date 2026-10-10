# Lo que le duele al dentista, y si DocFácil lo resuelve (10-oct-2026)

**De dónde sale.** Es una entrevista de 5 rondas a un dentista interpretado por IA: el "Dr. Javier Ortiz", general con diplomado en endodoncia, en Los Mochis, con 2 sillones, una asistente (Lupita) que también cobra y una ortodoncista que le renta el sillón dos tardes. Esto se cruzó con el mapa de lo que DocFácil hace hoy, sacado del código.

**Ojo:** es una simulación. Sirve para no dejar huecos, pero cada punto hay que confirmarlo con 2 o 3 dentistas reales antes de construir algo grande.

## Lo que dijo, en una línea por tema

- **Recordatorios.** Lupita se tarda de 20 a 30 min cada tarde copiando y pegando. En la mañana no saben quién viene. De cada 10 citas fallan 1 o 2.
- **Entre paciente y paciente.** Se le van 5 min buscando el expediente de papel (qué conductos, qué longitud) y la radiografía entre las fotos de su celular.
- **Abonos.** Dice: "yo ya le había dado 2,000". Hojean la libreta. Se le escapan unos miles al mes en saldos que nadie cobra.
- **Laboratorio.** La corona sale con el mensajero y regresa en 8 a 10 días. Solo saben si llegó cuando aparece la cajita. Ya le pasó que la paciente llegó a su cita y la corona no estaba. Le paga al laboratorio cada quincena sin saber bien cuánto le debe.
- **Presupuestos.** Cuando dicen "lo voy a pensar", ahí se queda. Termina 3 o 4 de cada 10 presupuestos grandes y los tratamientos quedan a medias. Quiere un recordatorio amable al mes, que mande él; nada cada 3 días ("en Los Mochis todo mundo se conoce").
- **Niños.** La mamá agenda y la trae la abuela. La autorización se pide por WhatsApp. Un papá paga a 3 hermanos; papás divorciados pagan cosas distintas ("¿cuánto he dado yo?").
- **Ortodoncista que renta el sillón.** Quiere su agenda dentro de la de él, que no se crucen pacientes, y que ninguno vea los cobros del otro.
- **Pacientes con riesgo.** La historia clínica se llena una vez y queda en el archivero. Ha tenido dos sustos: un hipertenso con epinefrina, y casi le receta amoxicilina a una alérgica. Quiere ver en rojo "alérgico a penicilina / anticoagulado / diabético" y que de vez en cuando le pregunte si sigue igual. No quiere 50 preguntas.
- **Urgencias.** Las decide a ojo, y el paciente de las 12 espera.
- **Huecos.** Casi nunca los llena. La lista de espera está en la cabeza de Lupita.
- **Los que no han venido en un año.** No sabe cuántos son. Una vez les escribieron a 40 y volvieron 10.
- **Dinero.** No sabe cuánto ganó en septiembre ni cuánto le deben. Lupita cuadra la caja y casi nunca cuadra (transferencias no anotadas).
- **Facturas.** Las hace su contador y no quiere que el sistema facture. Sí quiere saber quién pidió factura y si ya se mandó.
- **Lo que pediría Lupita.** No mandar recordatorios uno por uno, y ver cuánto debe cada quien con el paciente enfrente.
- **Miedos.**
  - Que sea complicado y Lupita lo capture todo; ella se maneja en el celular.
  - Borrar algo por error.
  - Que la reemplace.
  - **Que se vaya el internet**: "la agenda de papel nunca se cae".
- **La primera semana decide.**
  - Recordatorios funcionando el primer día; nada de 2 h configurando.
  - No pasaría los 1,500 expedientes: solo los de la semana y los tratamientos a medias.
  - Quiere tomarle foto a la hoja vieja.
- **Dónde lo usaría.** En el celular (él y Lupita) y en la compu de recepción. Tablet en el sillón no, por el aerosol. Anotar en 30 s con botones, no con párrafos.
- **Precio.** $400 a $500 sin pensarlo ("lo que me cuesta un paciente que no llega"). Por $1,000, solo si ve **en números** lo que le recupera. Nada de contratos de un año.
- **"Esto no es para mí":**
  - inglés o palabras raras;
  - 30 menús;
  - pedirle RFC y cédula antes de dejarlo ver nada;
  - mensajes desde otro número o robóticos;
  - no poder sacar sus datos;
  - que no haya una persona en WhatsApp;
  - módulos de clínica grande.

## Contra lo que DocFácil hace hoy

✅ resuelto · 🟡 a medias · ❌ falta

| Necesidad | Hoy | Qué falta |
|---|---|---|
| Recordatorios sin copiar y pegar | 🟡 | Hoy hay un clic por cita (abre WhatsApp con el texto y la liga para confirmar) y el filtro "Mañana, sin recordatorio". Falta mandar los de mañana **en fila**: dar enviar, regresar, siguiente, sin buscar. |
| Saber quién viene | ✅ | El paciente confirma o cancela con la liga (Básico) y se ve en la agenda. |
| Cuánto debe cada quien, con el paciente enfrente | ✅ | Cobros con abonos fechados, pestaña Pagos del perfil y deudores vencidos. |
| Cuánto debe una familia o un responsable | ❌ | No hay tutor o responsable ni saldo por familia. |
| Laboratorio (orden, llegó o no, cuánto le debo) | ❌ | Solo existe la categoría de gasto "Laboratorio". |
| Presupuestos "lo voy a pensar" y tratamientos a medias | 🟡 | Hay presupuesto con aceptación en línea y partidas ligadas a citas. Falta **una lista de pendientes** (sin aceptar, aceptados sin terminar) con el recordatorio del mes a 1 clic. |
| Alertas clínicas a la vista | 🟡 | `AlertasClinicas` lee palabras del texto libre y avisa en el perfil y al recetar. Faltan casillas rápidas (diabetes, hipertensión, anticoagulado, embarazo, alergia a penicilina o anestesia), verlas en la cita y la consulta, y "¿sigue igual?" cada tantos meses. |
| Notas de endodoncia (conductos, longitudes) a la mano | 🟡 | Hay historial por paciente. Falta "lo último del diente 36" al abrir la siguiente cita. |
| Nota en 30 segundos desde el celular | 🟡 | Existe "Visita rápida". Faltan botones de lo de siempre y revisar que se use bien en el celular. |
| Fotos de la hoja vieja y radiografías | 🟡 | Hay hasta 10 imágenes por nota de consulta. Falta subirlas directo al paciente, desde el celular, sin crear una nota. |
| Llenar huecos | ✅ | Lista de espera con "Ofrecer a…" al cancelar (Pro). |
| Los que no han venido | ✅ | Recall por servicio (Pro o add-on). Solo cuenta a los que ya están en el sistema. |
| Cuánto gané este mes | ✅ | Corte: entró, salió, quedó (Básico), si se capturan los gastos. |
| Cuadrar la caja del día | ❌ | No hay corte del día por forma de pago (efectivo, tarjeta, transferencia). |
| Quién pidió factura y si ya se mandó | ❌ | No hay marca en el cobro. Sin CFDI, como siempre. |
| Recibo para el paciente ("cuánto he dado") | ❌ | No hay recibo en PDF ni por WhatsApp. |
| Lupita con su propio usuario | ❌ | **El dentista no puede invitar a su asistente.** Solo el super admin crea usuarios staff, y el staff ve todo, cobros incluidos. |
| Ortodoncista que renta el sillón | ❌ | No hay sillones en la agenda (el traslape es por doctor), ni doctor externo, ni cobros separados. |
| Si se va el internet | ❌ | Sin modo offline. Lo práctico: **imprimir la agenda de mañana** como respaldo en papel. |
| Sacar sus datos | 🟡 | Hay expediente en PDF por paciente. Falta bajar todo: pacientes, citas y cobros. |
| Ver en números lo que le recupera | ❌ | Falta una tarjeta del mes con datos reales: citas confirmadas, saldos cobrados, presupuestos aceptados. |
| Precio | ✅ | El Básico ($499) está en el rango de "sin pensarlo". El Pro ($999) necesita la tarjeta de arriba. |

## Propuesta de orden

Va por dolor × frecuencia, con lo más barato de hacer primero.

1. **Que el dentista invite a su asistente**, con un permiso simple: "no ve el corte ni los gastos". Es chico, y hoy bloquea a Lupita, que es quien más usa el sistema.
2. **Recordatorios de mañana en fila.** Es lo primero que pidió Lupita y casi existe.
3. **Alertas clínicas a la vista**: casillas rápidas, en rojo en la cita y en la consulta, y "¿sigue igual?". Es seguridad del paciente.
4. **Laboratorio**: orden de trabajo, aviso "la cita es el jueves y la corona no ha llegado" y lo que se le debe al laboratorio.
5. **Pendientes por paciente**: en qué paso va y cuánto debe, más la lista de presupuestos y tratamientos a medias con su recordatorio del mes.
6. **Caja del día, factura pedida o enviada, y recibo** para el paciente.
7. **Agenda de mañana para imprimir**, por si se va la luz.
8. **Menores**: responsable y saldo por familia.
9. **Foto de la hoja vieja** y archivos del paciente desde el celular.
10. **"Lo que DocFácil le recuperó este mes"**, con datos reales.
11. **Sillón rentado o doctor externo**, con cobros separados. Es lo más grande; va al final, y antes hay que confirmarlo con dentistas reales.
