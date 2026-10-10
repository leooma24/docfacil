# Auditoría de UX y UI (12-oct-2026)

**Cómo se hizo**
- Cinco revisiones del código, una por área: agenda y consulta, pacientes y expediente, dinero, cuenta y menú, y lo visual de todo el panel.
- Recorrido en la demo local: 56 pantallas del doctor, más la medición en el celular (375 px).
- Los hallazgos más graves los comprobé yo en el código o en la demo (marcados ✔).
- Son unos 100 hallazgos. Aquí van juntos y sin repetir, en tres grupos.

**Lo que ya está sano**
- Ninguna pantalla truena: las 56 cargan.
- La IA apagada no se ofrece en ningún lado.
- WhatsApp siempre dice que el doctor da enviar. No hay CFDI.
- Lo que entró se cuenta igual en Caja, Corte y correo.
- Notas y recetas se cierran a las 24 h. Un paciente con expediente no se borra.
- "Sus datos" se pueden bajar. El importador está bien pensado para México.
- Casi todo habla de usted.

---

## A. Arreglar ya: rompe, engaña o pone en riesgo datos o dinero

| # | Qué pasa | Dónde |
|---|---|---|
| A1 | ✔ **Botones y colores que no se ven.** El panel no compila su propio Tailwind: solo existe el CSS de Filament. Los fondos de color no existen, así que el texto blanco queda sobre blanco. Pasa en "Habilitar 2FA" (comprobado en la demo), "Agendar" (les toca volver), "Felicitar", "Copiar"/"Compartir WhatsApp", "Enviar comprobante" (SPEI), "Invitar doctor" y "RECOMENDADO" en Mi plan. Además, rojo, ámbar y verde salen grises: deudores vencidos, Pagado contra Pendiente en el perfil, alergias en el perfil y el % por doctor. | 9 botones + ~10 vistas (ver detalle abajo) |
| A2 | **Modo oscuro roto.** Si el celular está en modo oscuro, en Caja, Recordatorios, Pendientes, Su mes y Corte el nombre del paciente y los montos quedan blancos sobre blanco. | vistas con `background:#fff` |
| A3 | ✔ **Abrir una cita la pone "En curso".** Tocar una cita del calendario solo para verla la marca en consulta y no se puede deshacer. Desaparece de Recordatorios de mañana y la tele de la sala la anuncia. | `CalendarWidget.php:267`, `Consultation.php:227` |
| A4 | **Pasar por el paso "Cobro" lo da por pagado.** Si el doctor da "Siguiente" pensando que recepción cobra después, queda "pagado en efectivo" algo que nadie recibió. | `Consultation.php:797` |
| A5 | ✅ *Arreglado el 12-oct ("Le deben" en tarjetas)* · ✔ **"Cobrar" en Cobros pendientes lo da todo por pagado.** Un clic marca $3,000 como pagados aunque el paciente traiga $500. | `PendingPayments.php:49` |
| A6 | **"Se le regresó el dinero" no descuenta.** La Caja, el Corte y el recibo siguen contando ese dinero como entrado. | `PaymentResource::cuadrarLoQueQuedo` |
| A7 | **Re-agendar no borra "ya se le recordó".** Al paciente nunca se le recuerda la fecha nueva. | `Appointment` (updating) |
| A8 | ✔ **El fundador ve y paga el precio normal.** `getFounderPrice()` existe pero nadie lo usa: Mi plan, Stripe y SPEI cobran $999 por el Pro. | `Upgrade.php:72`, `SpeiCheckout.php:47` |
| A9 | ✔ **El 403 está de tú, no dice que es por el plan y ofrece ligas a "Panel Ventas" y "Administración".** "Volver al inicio" saca al doctor a la landing. Lo ven un Free al terminar la prueba y la asistente cuando su lista de pasos la manda a Configuración. | `errors/403.blade.php` |
| A10 | **No se le puede quitar el acceso a una asistente que se fue.** "Su equipo" lista invitaciones, no personas. Además, "Reenviar" invalida la liga y no manda nada. | `DoctorInvitationResource` |
| A11 | **Borrar en bloque citas o un paciente "sin expediente" se lleva cobros, procedimientos y fotos de la hoja vieja,** sin decir qué se pierde. | `AppointmentResource.php:611`, `Patient::tieneExpediente()` |
| A12 | **El odontograma se puede editar o borrar para siempre** (aun el de hace 3 años), y **las marcas se pierden si sale sin "Guardar"**: no hay autoguardado ni aviso. | `Odontogram`, `EditOdontogram` |
| A13 | **El presupuesto se acepta solo con abrir la liga.** La vista previa de WhatsApp o un antivirus pueden aceptarlo. "Rechazar" puede deshacer un aceptado sin avisar. | `TreatmentPlanController.php:46` |
| A14 | **La asistente sin permiso de dinero ve ingresos** en el encabezado de Cobros y en el aviso "aceptó su presupuesto". También ve Mi plan y puede comprar. | `ListPayments.php:32`, `Upgrade` |
| A15 | **Un plan de pagos mal hecho no se puede corregir ni cancelar.** Si el enganche es mayor al total, la pantalla truena. | `PaymentPlanResource` |
| A16 | ✔ **Confiabilidad al subir:** al borrar archivos, el autoload de Composer y el caché de Filament quedan un momento apuntando a lo borrado. Hoy un doctor vio un error unos segundos; ya se corrigió en el servidor. `deploy.sh` debería regenerar el autoload (`composer dump-autoload -o`). | `deploy.sh` |

## B. Siguiente: confunde o hace perder tiempo

- **Tocar al paciente en la lista abre "Editar", no su perfil.** Notas, recetas y consentimientos viejos rebotan con "ya no se puede editar" en lugar de abrirse para leer.
- **"Mi plan" sin explicación:** al llegar al paciente 16 o a la cita 11 en Free, lo manda a precios sin decir por qué. "Su prueba ha terminado" suena a que ya no puede usar nada.
- **WhatsApp a la persona equivocada o con liga rota:**
  - Varios lugares usan `phone` en lugar de `telefonoDeContacto()`: el niño contra la mamá.
  - Otros pegan "52" fijo, y un número con "+52" queda "5252…".
  - Va en consulta, perfil, deudores, presupuestos y lista de espera. Hace falta un solo ayudante para todos.
- **Selectores que mezclan pacientes:** "Cita asociada" en cobro y en nota, y "Consulta asociada" en receta, enseñan citas y diagnósticos de otros pacientes.
- **Dos botones para cerrar la consulta**, sin confirmar y sin "Guardando…".
- **Calendario en celular:** abre en semana (7 columnas en 375 px) y un dedazo mueve la cita.
- **"Nueva cita" desde el calendario** no revisa choques ni el tope del plan.
- **En Citas, el filtro "Próximas"** esconde la cita de las 9:00 a las 10:00, así que no se puede cobrar.
- **Imprimir agenda** siempre da la de mañana, y el botón no lo dice.
- **Extras:** se "activan" en Free sin hacer nada, prometen un cobro de $49 que no existe y la prueba se reinicia.
- **El arranque tiene 8 pasos repetidos** (tarjeta de bienvenida y lista). El paso 4 vende Extras a quien está en la prueba con todo. Son unos 10 clics a la primera cita.
- **Menú:**
  - "Calendario" y "Citas" son dos entradas.
  - Los títulos no coinciden con el menú: "Llegada con QR" abre "Check-in por QR", "Extras" abre "Add-ons" y "Mi plan" abre "Actualizar Plan".
  - El horario del consultorio está escondido en Mi cuenta.
- **No se puede cambiar la contraseña** estando dentro.
- **La landing y el panel dicen cosas distintas:** "sus datos quedan 30 días" contra "nada se borra", y "yo le subo su Excel", que no aparece adentro.
- **Lo que la recepcionista escribe en la cita lo lee el paciente en su portal** (notas).
- **El tablero se va a poner lento** con muchos pacientes: les toca volver hace ~3 consultas por paciente, y la columna de laboratorio, los planes de pago y los insumos hacen consultas por renglón.
- **Recordatorios de mañana** dice "no hay citas" y abajo enlista a los que no tienen teléfono. Desde ahí no se puede agregar el teléfono.
- **Lista de espera:** "Ofrecer slot" está en inglés y el estado "Notificado" suena a que el sistema avisó solo.
- **"Cobrar" desde Citas no confirma** que se guardó.

## C. Pulido

- **Tú que se coló:**
  - Unos 40 textos (Editar paciente, nota, receta, odontograma, calendario, check-in, 403).
  - Correos de acceso y bienvenida ("Confirma tu correo", "Como beta tester").
  - La página pública del presupuesto y Mi plan ("Paga anual y ahorra", "Trial").
  - La prueba guardiana no conoce "Verifica", "Intenta", "Arrastra", "Marca" ni "Actualiza".
- **Inglés y jerga:** dashboard, Check-in, link, Email, 2FA/TOTP, trial, cascade, Dx/Tx, "med.", "Transf.".
- **Formatos:** dinero con y sin centavos y "MXN"; "$1500.00" sin coma en los servicios; fechas `d/m` contra `d/m/Y`; "Cobrar", "Pagar abono" y "Registrar pago" para lo mismo.
- **Botones chicos para el dedo** (menos de 40 px): Recordatorios ("Mandar"), Caja ("Recibo", "Pidió factura", que además no se puede deshacer), les toca volver, cumpleaños, deudores y alergias.
- **Escritorio en celular:** la tarjeta de bienvenida llena la primera pantalla y sus cuadros se salen del borde.
- **Colores:** WhatsApp sale en 5 verdes distintos, y peligro, aviso y éxito en 4 tonos cada uno.
- **Contraste:** textos grises claros (#94a3b8) en información que importa.
- **13 pantallas sin la cabecera de color:** Laboratorio, Planes de pago, Presupuestos y Lista de espera.
- **Detalles:**
  - Iniciales rotas con acento ("Á" sale �).
  - El QR se sale de la tarjeta en celulares chicos.
  - Letra de 11 px.
  - "contrasena" sin ñ.
  - El top de embajadores muestra el nombre de doctores de otros consultorios.

---

## Orden que propongo

1. **A1 + A2, los colores y el modo oscuro.** Es una sola causa y toca muchas pantallas:
   - lo esencial a estilos en línea;
   - apagar el modo oscuro hasta que esté bien;
   - una prueba que avise si una vista usa una clase que no existe en el CSS.
2. **El dinero, A4–A6, A14 y A15:** que lo que dice la caja sea lo que hay en el cajón.
3. **Agenda y datos, A3, A7 y A11–A13:** que nada se pierda ni se marque solo.
4. **Cuenta, A8–A10 y A16:** precio de fundador, 403 de usted con la salida a Mi plan, quitar acceso a la asistente y `deploy.sh`.
5. Después el grupo B, de a poco, y el C junto con cada pantalla que se toque.
