# NOM-024-SSA3-2012: qué pide y qué tiene DocFácil (12-oct-2026)

Fuente principal: el texto del DOF del 30-nov-2012 (https://dof.gob.mx/nota_detalle.php?codigo=5280847&fecha=30/11/2012) y las páginas de la DGIS. Consultado el 10-oct-2026. Algunas páginas de la DGIS daban error y se leyeron por fragmentos del buscador.

## Estado
- **Vigente.** Entró en vigor el 29-ene-2013.
- **Hay cambios en camino.** El Programa Nacional de Infraestructura de la Calidad 2026 trae su modificación, y también la de la NOM-004 (expediente) y la NOM-013 (salud bucal, "lo referente al expediente clínico estomatológico"). No encontramos un proyecto publicado todavía.
- **A quién aplica.** A los establecimientos del sector público, privado y social "que adopten un SIRES" (registro electrónico para la salud) y a quien tenga los derechos del sistema. No exceptúa a los consultorios chicos.
- **Certificación.** La DGIS la da (7.3.1, "pueden obtenerse"), dura 2 años y es por versión del sistema. Para un consultorio privado chico, en la práctica es voluntaria. Así lo leemos de la redacción; no hay una fuente que lo diga tal cual.
- **Qué se puede decir.**
  - "Certificado NOM-024": solo con un certificado de la DGIS vigente. **DocFácil no lo tiene.**
  - "Pensado para la NOM-024": solo cuando tengamos lo mínimo de abajo.

## Lo mínimo, contra lo que tenemos hoy

| Pide la norma | Numeral | DocFácil hoy |
|---|---|---|
| **CURP** como identificador, más nombre y apellidos | 6.5.1, 6.5.5 | ✅ *12-oct:* CURP validada (formato y dígito verificador, nunca se inventa); no se repite en el consultorio; el perfil avisa si falta. Nombre y apellidos sí (los apellidos van en un solo campo). |
| Fecha de nacimiento, sexo, entidad de nacimiento, nacionalidad, residencia (estado, municipio, localidad) | Tabla 1 | ✅ *12-oct:* fecha, sexo y estado salen solos de la CURP; nacionalidad, estado y municipio donde vive. (Municipio en texto, todavía sin catálogo oficial.) |
| Diagnósticos con el catálogo oficial (CIE-10) | 6.4.2, Apéndice A | ❌ **No es el oficial:** `database/data/cie10-mx.json` tiene 391 códigos de 3 caracteres armados a mano (jun-2026). El de la DGIS tiene miles de códigos de 4 caracteres (p. ej. K02.1). Falta bajarlo y cargarlo. Tampoco hay catálogo de procedimientos. |
| Usuario y contraseña; **se recomienda** un segundo factor | 6.6.3 | ✅ Contraseña y verificación en dos pasos opcional. El texto de Seguridad ("la NOM-024 lo recomienda") es correcto. |
| Permisos por rol | 6.6.4 | 🟡 Doctor y asistente (con o sin dinero). No hay perfiles a la medida. |
| Registros **inalterables** | 6.3.4, 5.9 | ✅ *12-oct:* notas y recetas se bloquean a las 24 h; una nota bloqueada se corrige con «Agregar corrección», que queda ligada a la original sin tocarla y se ve junta en el historial. Odontograma anterior y consentimiento firmado no se editan. |
| Trazabilidad y auditoría (quién vio o cambió qué) | 3.42, 5.8, 6.6.1 | ✅ *12-oct:* queda anotado quién abre cada expediente y quién crea o cambia notas, recetas, consentimientos, odontogramas y datos del paciente; el doctor lo ve en la pestaña «Bitácora» del paciente. |
| Confidencialidad, cifrado | 5.3, 6.6.1 | 🟡 HTTPS y cada consultorio aislado. Los datos clínicos no van cifrados en la base (solo el secreto de dos pasos). Los respaldos van con contraseña. |
| Disponibilidad y conservación en el tiempo (5 años por la NOM-004) | 5.6 | 🟡 Respaldo diario con limpieza y aviso por correo si falla (12-oct). **Están en el mismo servidor:** falta copia fuera (DigitalOcean Spaces). Ojo: la limpieza guarda mensuales 4 meses; para 5 años hace falta conservar uno al año fuera del servidor. |
| El paciente puede llevarse su información; controles de consentimiento | 6.6.6 | ✅ Expediente en PDF, portal del paciente y aviso de privacidad aceptado. |
| Firma electrónica avanzada (solo en lo que el prestador designe e intercambie) | 6.6.2, 6.6.5 | ❌ No hay FIEL. Receta con cédula y espacio para firma autógrafa. |
| Intercambio con HL7 CDA o guías de la DGIS | 6.1.3, 6.3.1 | ❌ No. Aplica solo si el consultorio cae en un escenario de las Guías de Intercambio; uno privado chico casi nunca. |

## Para poder decir "pensado para la NOM-024" sin mentir
- ✅ CURP y datos mínimos (12-oct).
- ✅ Bitácora de quién abre y quién cambia (12-oct).
- ✅ Corrección (addendum) de notas bloqueadas (12-oct).
- ⏳ **CIE-10 oficial de la DGIS:** hay que bajar el catálogo y cargarlo (necesita el visto bueno de Omar para descargarlo).
- ⏳ **Respaldo fuera del servidor** y uno al año conservado 5 años (necesita las llaves de Spaces).
- Pendiente menor: catálogo oficial de municipios (hoy es texto libre).

La certificación ante la DGIS sería un paso aparte, con costo y verificación. No conviene prometerla.
