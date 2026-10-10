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
| **CURP** como identificador, más nombre y apellidos | 6.5.1, 6.5.5 | ❌ No hay campo de CURP. Nombre y apellidos sí. |
| Fecha de nacimiento, sexo, entidad de nacimiento, nacionalidad, residencia (estado, municipio, localidad) | Tabla 1 | 🟡 Fecha y sexo sí; entidad, nacionalidad y residencia no. |
| Diagnósticos con el catálogo oficial (CIE-10) | 6.4.2, Apéndice A | 🟡 Hay CIE-10 (`database/data/cie10-mx.json`); falta confirmar que sea el de la DGIS. No hay catálogo de procedimientos. |
| Usuario y contraseña; **se recomienda** un segundo factor | 6.6.3 | ✅ Contraseña y verificación en dos pasos opcional. El texto de Seguridad ("la NOM-024 lo recomienda") es correcto. |
| Permisos por rol | 6.6.4 | 🟡 Doctor y asistente (con o sin dinero). No hay perfiles a la medida. |
| Registros **inalterables** | 6.3.4, 5.9 | 🟡 Notas y recetas se bloquean a las 24 h; el odontograma anterior ya se conserva. Consentimientos firmados no se editan. Falta el addendum formal. |
| Trazabilidad y auditoría (quién vio o cambió qué) | 3.42, 5.8, 6.6.1 | 🟡 La bitácora cubre notas, pacientes, citas y gastos, y la descarga del expediente y del ZIP. Falta: recetas, consentimientos, odontogramas y **quién abrió cada expediente**. |
| Confidencialidad, cifrado | 5.3, 6.6.1 | 🟡 HTTPS y cada consultorio aislado. Los datos clínicos no van cifrados en la base (solo el secreto de dos pasos). Los respaldos van con contraseña. |
| Disponibilidad y conservación en el tiempo (5 años por la NOM-004) | 5.6 | 🟡 Respaldo diario con limpieza (desde el 12-oct). **Están en el mismo servidor:** falta copia fuera del servidor. |
| El paciente puede llevarse su información; controles de consentimiento | 6.6.6 | ✅ Expediente en PDF, portal del paciente y aviso de privacidad aceptado. |
| Firma electrónica avanzada (solo en lo que el prestador designe e intercambie) | 6.6.2, 6.6.5 | ❌ No hay FIEL. Receta con cédula y espacio para firma autógrafa. |
| Intercambio con HL7 CDA o guías de la DGIS | 6.1.3, 6.3.1 | ❌ No. Aplica solo si el consultorio cae en un escenario de las Guías de Intercambio; uno privado chico casi nunca. |

## Para poder decir "pensado para la NOM-024" sin mentir (en orden)
1. CURP en el paciente, con validación de formato y sin inventarla, más entidad de nacimiento, nacionalidad y residencia (estado y municipio con catálogo oficial).
2. Bitácora de quién abre cada expediente, y bitácora de recetas, consentimientos y odontogramas.
3. Copia de los respaldos fuera del servidor.
4. Confirmar que el CIE-10 es el catálogo de la DGIS.
5. Addendum para corregir una nota bloqueada sin tocar la original.

La certificación ante la DGIS sería un paso aparte, con costo y verificación. No conviene prometerla.
