# Plan de los próximos días: DocFácil para el dentista que ya tiene sistema

10-oct-2026. Se basa en tres investigaciones:
- cómo trabajan Dentalink y DaleControl, pantalla por pantalla, con su ayuda oficial y sus tutoriales;
- qué tiene DocFácil hoy, revisando el código.

## Lo que aprendimos

**Dónde ya estamos bien o mejor:**
- **WhatsApp que confirma solo.** Dentalink cobra aparte el WhatsApp automático y su texto no se puede cambiar. DaleControl lo tiene.
  - Nosotros ya lo tenemos: recordatorio con botones «Confirmo» y «Necesito cambiar», y respeto a «ya no me manden».
- **Bitácora de quién abre cada expediente** y **corrección de notas sin borrar la original**. Dentalink no documenta la bitácora y DaleControl no describe cómo se corrige una nota.
- **Laboratorio con fecha de entrega y aviso si no llegó antes de la cita.** Dentalink no documenta fechas de entrega.
- **Lista de espera:** Dentalink no la documenta en su ayuda.
- **Aceptar el presupuesto en línea:** Dentalink no lo documenta.
- **Precio:** Dentalink guarda casi todo para sus planes caros (Pro y Titanium), y nosotros traemos casi todo desde el Básico.

**Dónde ellos se ven más profesionales:**
1. **Cobrar.** El botón de cobro muestra el saldo («Cobrar $300»), con atajos «Paga todo» y «Sin pago hoy». Hay estado de cuenta con un solo saldo, saldo a favor (anticipos) y pago combinado (mitad efectivo, mitad tarjeta).
2. **Corte de caja con conteo.** Se ve lo esperado, lo contado y la diferencia antes de cerrar, y queda quién cerró.
3. **Migración.** Plantilla, vista previa de columnas, aviso de repetidos («¿Es esta persona?») y carga de notas clínicas. Ninguno de los dos documenta que migre citas futuras ni saldos: **ahí les podemos ganar.**
4. **Presupuesto.** Va por etapas, con descuento en porcentaje, varios dientes a la vez y un tratamiento alternativo. Al mandarlo se elige qué tanto detalle lleva y el paciente lo firma.
5. **Seguimiento.** Pendientes automáticos: canceló y no reagendó, presupuesto sin empezar, terminó y le toca revisión, y quedó debiendo. Cada uno se cierra con «Logrado», «Volver a llamar en X días» o «Cerrar caso».
6. **Agenda.** «Buscar espacio», solo huecos reales al dar la cita, una columna por doctor, festivos de México y vacaciones del doctor.
7. **Ortodoncia.** Quién va atrasado en sus controles y quién no tiene su siguiente cita.
8. **Varios doctores.** Comisión de cada doctor sobre lo **ya cobrado**, un semáforo de qué falta y un botón «Explicar» con el porqué de cada monto.
9. **Portal del paciente.** El paciente ve su saldo, su presupuesto por etapas y firma consentimientos.

## El plan, por día

Cada día termina con pruebas, una revisión a mano en la demo y su commit. **Nada se sube sin su «súbelo».**

**Día 1: cobrar como profesional**
- Botón «Cobrar» en la ficha y en la cita. Cuando hay saldo dice «Cobrar $300» y se pone de color.
- Atajos «Paga todo» y «Sin pago hoy». Total, pago de hoy y lo que queda se calculan al escribir.
- Pago combinado: efectivo y tarjeta en el mismo cobro.
- Estado de cuenta del paciente en una línea: «$600 pagado de $800», con su lista de movimientos y PDF.

**Día 2: saldo a favor y corte con conteo**
- Anticipos que quedan como saldo a favor y se aplican solos en el siguiente cobro.
- En el corte del día: lo esperado en efectivo, lo que contó la persona y la diferencia, y quién cerró y a qué hora.
- Los cobros borrados se siguen viendo en el corte, tachados y con quién los borró.

**Día 3: cambiarse a DocFácil sin perder nada**
- Importar pacientes, mejorado:
  - plantilla descargable;
  - separa el nombre completo;
  - lee la CURP;
  - reporte de qué filas no entraron y por qué.
- **Importar citas futuras y saldos pendientes.** Ninguno de los dos lo documenta.
- «¿Es esta persona?» al dar de alta un paciente con nombre o teléfono repetido.
- Ofrecer la importación en el primer paso de la configuración inicial.

**Día 4: presupuesto por etapas**
- Etapas (por ejemplo «1. Urgencia», «2. Rehabilitación») que se pueden reordenar.
- Descuento en porcentaje y varios dientes en un renglón.
- Tratamiento alternativo («Opción B») que no se cobra hasta elegirlo.
- Al mandarlo, elegir el detalle: completo, solo total o sin precios.
- El paciente **firma** al aceptar, con la misma firma de los consentimientos, y queda en su expediente.

**Día 5: seguimiento que no se olvida**
- Una sola lista de «A quién hay que llamar» que se arma sola:
  - canceló o no vino y no tiene otra cita;
  - presupuesto aceptado sin empezar;
  - le toca su revisión;
  - quedó debiendo;
  - 6 meses sin venir.
- Cada pendiente se cierra con «Logrado», «Volver a llamar en X días» o «Cerrar caso».
- Al final del mes, cuánto se recuperó.

**Día 6: agenda más rápida**
- «Buscar espacio»: el siguiente hueco de N minutos con ese doctor.
- Al dar la cita, solo los huecos libres, en mañana y tarde.
- Vista del día con una columna por doctor.
- Vacaciones del doctor y festivos de México propuestos, sin cerrar nada solo.

**Día 7: ortodoncia**
- Tablero de pacientes en tratamiento:
  - quién va atrasado en su control;
  - quién no tiene su siguiente cita;
  - a quién se le venció la mensualidad.
- La mensualidad queda ligada al control del mes.

**Día 8: clínicas con varios doctores**
- Comisión de cada doctor: un porcentaje general y montos fijos para tratamientos concretos. Se paga solo lo ya cobrado.
- Liquidación con semáforo (pagado, parcial, sin pagar) y botón «Explicar».
- Opción «Exigir verificación en dos pasos a todo el equipo».
- Pantalla de bitácora general para el doctor dueño.

**Día 9: portal del paciente**
- El paciente ve su saldo, su presupuesto por etapas y sus recibos.
- Firma consentimientos antes de su cita.
- Encuesta corta después de la visita: estrellas y un comentario que solo ve el consultorio.

**Día 10: cierre y venta**
- CIE-10 oficial, si usted da el visto bueno para bajarlo.
- Historia clínica en PDF con folio en cada hoja.
- Alertas médicas visibles en todas las pantallas del paciente.
- La agenda marca a los pacientes con datos incompletos.
- Poner al día brief, landing y demo **solo con lo que ya quedó hecho.**

## Preguntas para Omar

Contésteme por correo con el número y su respuesta, por ejemplo: «1 sí, 2 Pro».

1. **Orden.** ¿Arranco por el Día 1 (cobrar) o por el Día 3 (migración), que es lo que más pesa para que alguien se cambie?
2. **Comisiones por doctor.** ¿Van en el plan Clínica, o también en el Pro?
3. **Ortodoncia.** ¿Básico o Pro?
4. **Caja.** ¿Basta con contar el efectivo al cerrar, o quiere que cada persona «abra caja» con su clave? Yo propongo contar al cerrar, que son menos clics.
5. **Migración asistida.** ¿Ofrecemos «mándenos su Excel y se lo cargamos gratis»? La haríamos nosotros con la herramienta del Día 3.
6. **CIE-10 oficial.** ¿Me deja bajar el catálogo de la DGIS?
7. **Respaldos fuera del servidor.** ¿Me consigue las llaves de DigitalOcean Spaces?
8. **Correo de contacto.** ¿`contacto@docfacil.com` existe y le llega a usted? Si no, ¿qué correo ponemos en la página?

## Avance

- **Días 1 a 3 hechos el 10-oct-2026** (Omar: «Dale a 1 al 3»). Con pruebas y revisados en la demo local. **Sin subir a producción.**
  - **Día 1:**
    - En la ficha, «Cobrar $X» con lo que debe y «Cobrar» / «Nuevo cobro» para algo nuevo, con Paga todo / Una parte / Sin pago hoy.
    - Pago combinado (efectivo + tarjeta o transferencia) con un recibo por forma.
    - «Cobrar» de la cita acepta un abono.
    - Estado de cuenta en PDF, en «Descargar».
  - **Día 2:**
    - Anticipos: queda a su favor y se descuenta solo en el siguiente cobro, sin contarse dos veces en la caja.
    - Cierre de caja con conteo y diferencia: Cuadra / Faltan / Sobran, quién cerró y a qué hora.
    - Un cobro con dinero ya no se borra.
  - **Día 3:**
    - Pantalla «Traer sus datos» (Mi cuenta): pacientes (ahora también con CURP), citas que vienen y saldos. Reconoce al paciente por teléfono o nombre y lo da de alta si no existe; subir dos veces no duplica.
    - «¿Es esta persona?» al dar de alta un teléfono o nombre repetido.
    - La lista de primeros pasos lleva a «Traer sus datos».
- Pendiente del Día 3: ofrecerlo dentro del asistente de configuración inicial. Por ahora sale en la lista de primeros pasos del escritorio, que es lo primero que ve al terminar.

- **Días 1 a 3 en producción** desde el 11-oct-2026.
- **Días 4 a 10 hechos el 11-oct-2026.** Tienen pruebas y se revisaron en la demo local. **Sin subir.**
  - **Día 4:** presupuesto por etapas, varios dientes por renglón, opcionales que no suman, descuento en %, qué ve el paciente (todo, solo total, sin precios) y firma del paciente al aceptar.
  - **Día 5:** «A quién hay que llamar» en Pendientes: canceló o no vino, debe, revisión, 6 meses sin venir. Se cierra con Logrado, Volver a llamar o Cerrar caso, y muestra lo logrado y lo cobrado en el mes.
  - **Día 6:** lupa «Buscar espacio» y huecos libres al dar la cita; «El día por doctor»; vacaciones de un solo doctor; «Proponer festivos».
  - **Día 7:** tablero de Ortodoncia (control atrasado, sin próxima cita, mensualidad vencida). Al cobrar el control se cobra la mensualidad del plan.
  - **Día 8:**
    - Comisiones por doctor: % y montos fijos, solo sobre lo cobrado, con semáforo y «Explicar». Se marca pagada y queda como gasto.
    - Verificación en dos pasos para todo el equipo.
    - Bitácora del consultorio para el dueño.
  - **Día 9:** portal del paciente con próxima cita, saldo, presupuestos, firma de consentimientos, recibos y «¿Cómo le fue?». El consultorio ve las respuestas en «Opiniones».
  - **Día 10:**
    - Alertas médicas también en odontograma, consentimientos, presupuestos y el día por doctor.
    - «Faltan datos» en la agenda.
    - Historia clínica en PDF con folio y número de hoja.
    - Landing, brief y demo al día.
- **Sigue pendiente:**
  - CIE-10 oficial: necesita el visto bueno de Omar.
  - Respaldo fuera del servidor: faltan las llaves de Spaces.
  - Ofrecer «Traer sus datos» dentro del asistente de configuración inicial.
  - Preguntas 2 a 5 del correo. Sin respuesta, se decidió así:
    - comisiones desde Pro;
    - ortodoncia desde Básico;
    - la caja se cuenta al cerrar;
    - no se ofreció la carga asistida de citas y saldos más allá de lo que ya dice la landing.
