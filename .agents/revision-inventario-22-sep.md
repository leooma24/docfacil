# Revisión del inventario de insumos — 22 de septiembre

Notas de la revisión de los commits `2a4ec5b`, `16d7147` y `75b4c20` antes de subirlos a producción. No es un regaño: el diseño de fondo está bien —el stock derivado del kardex, la propuesta que el doctor confirma, los precios congelados, el `$fillable` completo en los ocho modelos— y por eso vale la pena anotar dónde se rompe, que son casi siempre los mismos tres lugares.

## Las tres trampas que se repiten

### 1. SQLite no es MySQL, y las pruebas corren en SQLite

Lo que pasó local y habría tronado en producción:

- **`tooth_number` a 10 caracteres.** El propio parser (`SupplyScope::teeth()`) lee listas y rangos: "16, 15, 14, 13" son 14 caracteres. SQLite guarda lo que le den sin chistar; MySQL en modo estricto lanza `SQLSTATE[22001] Data too long`. Las pruebas usaban un solo diente por renglón, así que el caso nunca se tocó.
- **Costos en `decimal(10,2)`.** El costo aquí es por unidad de consumo (un mililitro, un guante), no por caja. Un insumo de $0.008 el ml se guarda como $0.01: 25% de error, multiplicado por cada consulta. Y MySQL redondea en silencio.
- **Decimales sin `cast`.** MySQL devuelve un `DECIMAL` como texto (`"1.80"`), SQLite como float (`1.8`). Sin el cast, el mismo cálculo da distinto en producción que en las pruebas. Le faltaban a las tres columnas de anestesia de `clinics`.

**Regla:** cuando una columna sea de texto, preguntarse cuál es el valor más largo que el propio código puede generar. Cuando sea dinero o cantidad, preguntarse cuál es el valor más chico que hay que poder distinguir. Y toda columna nueva va en `$fillable` **y** en `$casts`.

### 2. Lo que se escribe en varios pasos va en una transacción

`saveAndComplete()` creaba, en este orden y sin transacción: expediente → receta → **cobro** → procedimientos → kardex → cita completada.

Con el error del diente, el cobro ya estaba hecho cuando reventaba. El doctor corregía y volvía a darle Finalizar: **segundo expediente y segundo cobro por la misma consulta**, y el corte del mes con el doble.

Lo que delata el problema: el código nuevo sí se cuidaba de duplicados en su propia parte (borra los procedimientos y los movimientos antes de reescribirlos), pero esa guarda no alcanzaba a lo que se había escrito antes. Media idempotencia es peor que ninguna, porque da confianza.

**Regla:** si una acción del usuario escribe en más de una tabla, va completa o no va.

### 3. Una pantalla que pide datos tiene que guardarlos

`ClinicSettings` mostraba los tres campos de anestesia, decía "Configuración guardada" y no escribía ninguno: `mount()` no los cargaba y `save()` armaba su arreglo de cambios con una lista fija de claves que no los incluía.

El efecto no es un error visible, es peor: la vigilancia de dosis —lo único de seguridad del paciente que trae la consulta— no se podía encender por ningún lado, y en la consulta decía para siempre "sin límite configurado". Las pruebas no lo vieron porque sembraban las columnas directo con `Clinic::create([...])`, sin pasar por la página.

**Regla:** la prueba de un formulario se hace contra el formulario, no contra el modelo. Llenar, guardar, **volver a abrir** y verificar que sigue ahí.

## Lo demás que se corrigió

- **`Supply::register()`** validaba el tipo y la cantidad, y después hacía `array_merge([defaults], $data)`, dejando que `$data` pisara justo lo que se acababa de validar. Una salida podía colarse como entrada con cantidad negativa. Lo validado va al final del merge, nunca al principio.
- **Los seeders no tenían candado de producción.** Un `migrate --seed` dejaba un super admin con contraseña `password` y una clínica de mentiras contada como cliente real en los reportes y en los lugares de fundador. Además, la clínica del demo va marcada con `is_demo`.
- **`occurred_at` como `timestamp`** en vez de `dateTime`: TIMESTAMP se convierte con la zona horaria de la sesión de MySQL, y desde este mismo lote cada consultorio tiene su propia hora. DATETIME no se mueve y no topa en 2038.
- **La zona horaria del onboarding** llegaba del navegador y se guardaba tal cual. El campo sea una lista o no, el valor de un `wire:model` se filtra en el servidor.
- **Candado de plan.** Los tres módulos nuevos no verificaban nada: le aparecían hasta al plan Free. Se cierran **las dos puertas**, `shouldRegisterNavigation()` (el menú) y `canAccess()` (la URL directa). Esconder el menú no cierra nada — así lo hace `ExpenseResource` y `Corte`.

## Lo que quedó pendiente, anotado para no perderlo

1. **Borrar un insumo borra su kardex completo** (`cascadeOnDelete`), incluidas las mermas que se deducen. Contradice lo que el propio `SupplyMovementResource` declara: que un movimiento es un hecho histórico. Va `restrictOnDelete` o SoftDeletes, y ya existe el `is_active` para dar de baja sin borrar.
2. **La entrada desde un gasto valúa cada insumo con el monto completo del gasto.** Un gasto de $3,000 que trajo guantes, agujas y anestesia deja el inventario valuado en $9,000. Hay que repartir el monto, o pedir el costo de cada línea.
3. **Los avisos de caducidad no salen** si el consultorio empieza a capturar lotes después de haber estado moviendo inventario: `fefoAllocation()` suma todo el consumo histórico sin filtro de fecha y se lo come de los lotes nuevos. Es el caso normal, no el raro.
4. **La salida por consulta no guarda costo**, así que hoy no existe el dato de cuánto costó el material de cada procedimiento.
5. **N+1 en la lista de insumos**: `nextExpiry()` y `lotsExpiringSoon()` corren por fila y cada uno dispara su propia consulta.

## Del material de venta

Aparte del código, hay frases que este proyecto no puede sostener y que siguen ahí: "Cumplimos con todas las normas mexicanas" en el kit del vendedor, "servidores fuera de México" usado como golpe a la competencia cuando los nuestros están en Estados Unidos, testimonios de doctores que no existen, y cifras de ahorro sin fuente. La landing está bien escrita; el material interno no. Se limpia aparte.
