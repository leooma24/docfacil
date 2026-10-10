# Prueba con 20 doctores simulados (12-oct-2026)

**Cómo se hizo**
- Se tomó el texto real de 44 pantallas de DocFácil (plan Pro, datos inventados, consultorio en Los Mochis).
- 20 perfiles distintos lo revisaron haciendo las tareas de su día. Los interpretó una IA (Sonnet).
- Los perfiles: general de 63 años en Guasave, recién egresada, ortodoncista que renta sillón, recepcionista que solo usa celular, endodoncista, dueño de clínica con 3 doctores, odontopediatra, implantólogo, dentista de pueblo con mal internet, estética en Polanco, periodoncista, alto volumen en Tijuana, cirujana maxilofacial, desconfiado de la privacidad, rehabilitadora, doctora de 68 años, uno que ya usa otro sistema, dueño de dos consultorios, la que atiende diabéticos y embarazadas, y recepcionista de clínica.

**Cuidado:**
- Son doctores simulados. Sirven para no dejar huecos, no sustituyen a dentistas reales.
- Al armar el recorrido, la prueba corrió las horas y algunas fechas, y 18 de los 20 lo reportaron. **En el servidor real se comprobó que sí cuadran**, así que eso quedó fuera.

## Errores reales (lo que hay que arreglar)

| # | Qué | Cuántos | Dónde |
|---|---|---|---|
| 1 | Se mezcla **tú y usted** en todo el panel ("te deben", "te quedaron", "Puedes ajustarlo", "Agenda una nueva cita") | 20 | Toda la app |
| 2 | **Palabras en inglés o raras**: Check-in, Add-ons, Roadmap, Recall, Slot mágico, Dx/Rx, FDI | 16 | Menú, consulta |
| 3 | **"Pidió factura"** no dice qué hacer (la hace su contador) | 15 | Caja del día |
| 4 | **"Slot mágico"** se ve aunque la IA está apagada y dice "La IA encuentra los mejores horarios" | 12 | Calendario |
| 5 | **Menú de 35 opciones**: Insumos, Residuos, Roadmap, Add-ons, Servicios premium, Invitar colegas… | 11 | Menú |
| 6 | **Corte sin gastos capturados**: "De cada $100 te quedaron $100" y "Salió $0" | 9 | Corte |
| 7 | **Lo que se debe no cuadra entre pantallas**: el Corte cuenta lo de este mes y el Escritorio todo, y ninguno lo dice | 6 | Corte / Escritorio |
| 8 | **Formulario de cobro confuso**: total contra abonado, fecha obligatoria en un pendiente | 6 | Nuevo cobro |
| 9 | **Plan y precios**: el límite de 200 pacientes del Básico no se ve antes de registrarse; "Ver 8 funciones más" está escondido | 6 | Registro / Mi plan |
| 10 | **Plurales y mayúsculas**: "1 pagos vencidos", "Crear Invitación" | 6 | Varios |
| 11 | **"Nueva cita" promete** "recordatorio WhatsApp 24h y 2h antes", y no sale solo | 5 | Nueva cita |
| 12 | **Mateo (niño sin teléfono)** sale con "Mandar" sin decir que le llega a su mamá | 5 | Recordatorios |
| 13 | **La consulta repite** las alergias o los antecedentes en dos lugares | 6 | Consulta |
| 14 | **"hace 1 semana" en una pantalla y "hace 12 días" en otra** para el mismo presupuesto | varios | Presupuestos / Pendientes |

## Lo que les falta, por tipo de doctor

- **Ortodoncia:** mensualidades con calendario y atraso en meses. Ya existe en "Planes de pago", pero no la encontraron. Ajuste cada 4 semanas que se repita y separar el sillón rentado.
- **Endodoncia:** ficha por conducto (longitud, lima, irrigante), pruebas de vitalidad y radiografías por diente.
- **Perio:** periodontograma por visita y mantenimiento cada 3-4 meses.
- **Implantes:** caso por fases con fechas y lote del implante.
- **Cirugía y pediatría:** consentimiento por procedimiento (existe en Pro y no lo encontraron), peso del niño, dos responsables y cobro a nombre de quien paga.
- **Alto volumen:** cobro en dólares y recordatorios en tanda.
- **Clínica:** producción por doctor, permisos finos con bitácora y filtro de agenda por doctor.
- **Recepcionista:** cierre de caja contando el efectivo, lista de "faltaron" con teléfono y lista para el contador con RFC.
- **De todos:** pasar sus datos de otro sistema o de Excel, **sacar todos sus datos** si se van y qué pasa si dejan de pagar (4).

## Lo que más les gustó

1. Alertas en rojo (alergia, anticoagulantes, "Lab: no ha llegado"): 14.
2. Recordatorios con el mensaje escrito, que el doctor manda desde su WhatsApp: 10.
3. Pendientes por paciente con recordatorio: 10.
4. Laboratorio (aviso, preguntar por WhatsApp, cuánto se debe): 10.
5. Agenda para imprimir: 8.

## ¿Lo pagarían?

Ninguno dijo que sí sin condiciones: 13 dicen "hoy no" y 5 "probablemente", con prueba o garantía.
- **Por plan:** Básico 9, Pro 5, Clínica 2 y Free 1.
- **Qué condiciona:**
  - que exista lo de su especialidad;
  - poder probar un mes;
  - poder llevarse sus datos;
  - el límite de 200 pacientes, que empuja al Pro.

## Contra lo que dijo el dentista de la entrevista

**Cubierto:**
- recordatorios en fila y saber quién viene;
- saldo y recibo;
- familia y responsable;
- laboratorio;
- pendientes;
- alertas;
- asistente con su usuario;
- caja y factura pedida;
- agenda impresa;
- "Su mes";
- archivos;
- huecos y quién debe volver (Pro).

**Falta:**
- lo último hecho en el diente al abrir la siguiente cita;
- nota de 30 segundos con botones;
- sillón rentado;
- exportar todos sus datos.

Y el aviso que dio él mismo se confirmó: **"30 menús" y palabras raras lo harían decir "esto no es para mí"**.
