# Motor de insumos — reglas clínicas

> Decisiones del doctor sobre cómo se gastan los insumos y cómo se cobra.
> Se van acumulando aquí conforme contesta, porque son reglas finas que se
> pierden en el chat y de las que depende que el inventario diga la verdad.
>
> Estado: **en levantamiento**. Faltan 2 de las reglas.

---

## El principio: una entrada, cuatro formas de contarla

Todo lo que el sistema necesita saber es **en qué dientes se trabajó**. De ahí
salen cuatro cuentas distintas, y cada línea de insumo elige la suya:

| Alcance | Qué cuenta | Ejemplo: curetaje de 2 cuadrantes (un lado, 16 dientes) |
|---|---|---|
| `tooth` | cuántos dientes | 16 |
| `quadrant` | cuántos cuadrantes se tocaron | **2** |
| `contiguous_zone` | cuántos grupos de dientes pegados | **2** |
| `visit` | 1 | 1 |

**Ojo con `contiguous_zone`:** no es una regla sola. En el maxilar cuenta
grupos contiguos; en la mandíbula, cuadrantes — porque el bloqueo troncular
duerme el cuadrante entero aunque haya huecos. Ver Regla 1.

Esto importa porque **la misma cuenta sirve para dos cosas**: cuánto se cobra y
cuánto insumo se gasta. Si el curetaje se cobra por cuadrante, sus insumos de
curetaje también se gastan por cuadrante. Calcularlo una vez evita que el cobro
y el inventario se desfasen — que es como empieza un inventario que miente.

---

## Regla 1 — Anestesia: por zona contigua

**Lo que dijo el doctor:** es por zona, pero solo si los dientes están
contiguos. Si están separados, ya no aplica: es una segunda dosis.

O sea que la anestesia **no** escala con el diente ni con la visita: escala con
los **grupos de dientes pegados**.

| Dientes trabajados | Zonas | Dosis |
|---|---|---|
| 16, 15, 14 | 1 (posiciones 4-5-6 en el cuadrante 1) | 1 |
| 16, 14 | 2 (falta el 15) | 2 |
| 16, 26 | 2 (dos cuadrantes) | 2 |
| 16, 15, 26 | 2 (5-6 en Q1, 6 en Q2) | 2 |
| 16, 17, 18, 26, 27 | 2 (6-7-8 en Q1, 6-7 en Q2) | 2 |

En notación FDI el primer dígito es el cuadrante y el segundo la posición, así
que se cuenta agrupando por cuadrante y viendo cuántas veces la posición no es
la anterior más uno:

```php
public static function contiguousZones(array $teeth): int
{
    $porCuadrante = [];
    foreach (array_unique(array_map('intval', $teeth)) as $d) {
        $porCuadrante[intdiv($d, 10)][] = $d % 10;
    }

    $zonas = 0;
    foreach ($porCuadrante as $posiciones) {
        sort($posiciones);
        $anterior = null;
        foreach ($posiciones as $p) {
            if ($anterior === null || $p !== $anterior + 1) {
                $zonas++;
            }
            $anterior = $p;
        }
    }

    return $zonas;
}
```

**Confirmado — la dosis se gradúa:**

**Lo que dijo el doctor:** el refuerzo **no** es siempre un cartucho completo.
Es muy común usar **medio cartucho o menos**:

- **Refuerzo infiltrativo** (cuando el bloqueo no durmió del todo): un cuarto o
  medio cartucho basta para las fibras nerviosas que quedaron.
- **Procedimiento largo** (se está acabando el efecto): medio o un cartucho
  completo para prolongar, respetando el máximo por peso del paciente.
- **Margen de seguridad** (niños, adultos mayores, condiciones especiales):
  solo la fracción estrictamente necesaria, para evitar toxicidad.

Los cartuchos dentales traen **1.8 ml**, así que la dosis se gradúa con
precisión.

### La consecuencia para el inventario: se gasta el cartucho ABIERTO

> *"Cualquier anestésico sobrante en el cartucho se desecha inmediatamente
> después de atender al paciente."*

Esto es lo importante, y es contraintuitivo: si el doctor usa **medio** cartucho,
el inventario pierde **un cartucho completo** — el resto se tira.

O sea que hay **dos números distintos** y no se pueden confundir:

| | Qué mide | Para qué sirve |
|---|---|---|
| **Dosis clínica** | 0.5, 0.25, 1 cartucho | expediente y máximo por peso |
| **Consumo de inventario** | cartuchos **abiertos** | kardex y costo |

Convertir mililitros a inventario daría **de menos, siempre**. El kardex
descuenta cartuchos abiertos; la dosis clínica se guarda aparte.

### Una oportunidad que ya está a la mano

El doctor mencionó el límite por peso y la toxicidad en niños — y **el peso ya
se captura** en los signos vitales de la consulta (`consultation_data.weight`,
y en `MedicalRecord.vital_signs`).

Con eso se puede avisar **antes de pasarse de la dosis máxima**. Es seguridad
del paciente, va alineado con la NOM-004, y es de las cosas que ningún
competidor local tiene.

### Confirmado — el sobrante se reusa dentro de la misma visita

**Lo que dijo el doctor:** sí se puede reusar el cartucho sobrante para un
refuerzo **en el mismo paciente y la misma visita**. La jeringa y el cartucho
están montados y son de uso monopaciente; el líquido se mantiene estéril para
sus propios refuerzos.

Lo que **nunca** se hace: guardar un cartucho a medias para otro paciente. Al
terminar la consulta el remanente se tira junto con la aguja usada, y **cada
paciente nuevo estrena cartuchos y agujas**.

### La fórmula que sale de eso

Como el sobrante se aprovecha dentro de la visita, lo que se gasta no son las
dosis aplicadas sino los **cartuchos abiertos**, y se abre uno nuevo solo cuando
se acaba el anterior:

```
cartuchos = ceil( suma de las dosis, medidas en cartuchos )
```

| Dosis aplicadas | Suma | Cartuchos abiertos |
|---|---|---|
| 1 + 0.5 (bloqueo + refuerzo) | 1.5 | **2** |
| 0.5 + 0.5 | 1.0 | **1** |
| 0.25 + 0.25 + 0.25 | 0.75 | **1** |
| 1 + 1 | 2.0 | **2** |

Y como ninguna dosis pasa de un cartucho, los cartuchos abiertos **nunca son más
que las zonas**.

**Lo que el sistema propone:** 1 cartucho por zona. Es una estimación alta a
propósito — si el doctor usó puras medias dosis, él la baja. Y con el tiempo el
sistema aprende su fracción típica (niveles 1 y 2 de la suposición).

### Confirmado — la regla depende de la arcada

**Lo que dijo el doctor:** en la **mandíbula la contigüidad no aplica**. Los
dientes separados se duermen igual, sin importar la distancia.

El porqué es anatómico y explica la asimetría:

- **El bloqueo alveolar inferior es el interruptor principal.** Se deposita la
  anestesia atrás, justo antes de que el nervio entre al hueso. Al bloquear el
  tronco, se corta toda la "línea telefónica": el cuadrante completo, del último
  molar al incisivo central del mismo lado.
- **El hueso mandibular es denso y compacto**, a diferencia del maxilar, que es
  poroso y deja que la infiltración bañe diente por diente. Por eso abajo la
  infiltración local casi no funciona: se requiere el bloqueo troncular.
- **Y duerme tejidos blandos**: medio labio inferior, la barbilla y los dos
  tercios anteriores de la lengua del lado anestesiado. O sea que la "zona" es
  un territorio nervioso, no una fila de dientes.

Su ejemplo: primer premolar y segundo molar del mismo lado inferior → **un solo
bloqueo duerme ambos**, aunque estén separados.

### La regla final

| Arcada | Cuadrantes | Cómo se cuenta |
|---|---|---|
| **Maxilar** (arriba) | 1 y 2 | Una dosis por **grupo contiguo** |
| **Mandíbula** (abajo) | 3 y 4 | Una dosis por **cuadrante**, sin importar los huecos |

```php
/**
 * Dosis de anestesia: una por zona nerviosa.
 *
 * Arriba el hueso es poroso y la infiltración baña diente por diente, así que
 * cada grupo contiguo lleva su dosis. Abajo el hueso es denso y la única vía
 * es el bloqueo troncular, que duerme el cuadrante completo: los dientes
 * separados van en la misma dosis.
 */
public static function anesthesiaZones(array $teeth): int
{
    $dosis = 0;

    foreach (self::byQuadrant($teeth) as $cuadrante => $posiciones) {
        if ($cuadrante <= 2) {
            sort($posiciones);
            $anterior = null;
            foreach ($posiciones as $p) {
                if ($anterior === null || $p !== $anterior + 1) {
                    $dosis++;   // arranca un grupo nuevo
                }
                $anterior = $p;
            }
        } else {
            $dosis++;   // el bloqueo troncular cubre el cuadrante entero
        }
    }

    return $dosis;
}
```

Ejemplos:

| Dientes | Arriba / abajo | Dosis |
|---|---|---|
| 16, 15, 14 | arriba, contiguos | 1 |
| 16, 14 | arriba, separados | **2** |
| 46, 47 | abajo, contiguos | 1 |
| 44, 47 | abajo, **separados** | **1** ← la diferencia |
| 16, 15, 46 | arriba contiguos + abajo | 2 |

---

## Regla 2 — Curetaje periodontal: por cuadrante

**Lo que dijo el doctor:**

- **Se cobra por cuadrante**, porque el grado de afectación varía por zona. Si
  solo hay sarro severo de un lado, solo se cobran los cuadrantes afectados.
- **Precio de mercado en México: $1,500 a $3,500 MXN por cuadrante.**
- **Se agenda por visita, no por cuadrante**: hacer toda la boca de una vez es
  muy pesado para el paciente y obliga a anestesiar demasiadas zonas.
  - Lo común: **2 cuadrantes por visita** (un lado completo: arriba y abajo del
    mismo lado) → la boca entera en **2 visitas**.
  - Severo o con bolsas profundas: **1 cuadrante por visita** → **4 visitas**.
- Terminado el tratamiento, sigue **mantenimiento periodontal**, y esas citas
  **sí se cobran fijas por visita**.

### Lo bonito: la anestesia cuadra sola

2 cuadrantes por visita = un lado = arriba **y** abajo. Como arriba y abajo son
cuadrantes distintos, son dos grupos contiguos separados → **2 dosis de
anestesia**. La regla de contigüidad y la de cuadrante coinciden, por caminos
distintos.

### Y lo que demuestra que los dos alcances son independientes

Un curetaje en los dientes **16 y 14** (mismo cuadrante, con el 15 de por medio):

- Por cuadrante → **1** (es un solo cuadrante)
- Por zona contigua → **2** (están separados)

Los dos números son correctos al mismo tiempo para el mismo procedimiento. Por
eso cada línea de insumo elige su alcance y no se puede deducir uno del otro.

---

## Consecuencia en el modelo

**La unidad de cobro es del servicio, no del insumo.** Falta en el código:

```
services.unit            → visit | tooth | quadrant | contiguous_zone
service_supplies.scope   → el alcance de esa línea (por defecto, el del servicio)
```

El alcance de la línea tiene que poder **sobrescribirse**, y la anestesia es la
prueba: el curetaje se cobra `quadrant`, pero su línea de anestesia es
`contiguous_zone`. Si se forzara el default, la anestesia se descontaría mal.

---

## Huecos que esto destapó

### 1. Hoy no se puede cobrar por cuadrante

`Service` solo tiene `price` — **no tiene unidad de cobro**. Y la consulta
registra un solo `payment_service_id` con un `payment_amount` que el doctor
teclea a mano.

O sea: un curetaje de 2 cuadrantes hoy se cobra **una** vez al precio de uno, o
el doctor lo captura a mano. La pantalla no sabe que el precio es por cuadrante.

Se arregla con la misma pieza que hace falta para la anestesia: registrar los
procedimientos (servicio + dientes + cantidad) al cerrar la consulta.

### 2. El onboarding está enseñando a cobrar barato

En `Onboarding.php`, la sugerencia de periodoncia dice:

```php
['name' => 'Curetaje (por cuadrante)', 'price' => 800, ...]
```

El doctor dice que el mercado mexicano va de **$1,500 a $3,500 por cuadrante**.
La sugerencia está **2 a 4 veces por debajo**, y es lo que el doctor ve
precargado al configurar su catálogo. Vale subirla.

*(El de mantenimiento, $600 fijo por visita, sí es coherente con lo que
describió.)*

---

## Regla 3 — Punta de aplicación: por paciente

**Lo que dijo el doctor:** la punta de aplicación se cambia **por paciente**.

O sea: **una por consulta**, no una por diente. Si se trabajan 7 dientes con
resina, es **1 punta**, no 7. (En una consulta de un solo paciente, "por
paciente" y "por visita" son lo mismo: el insumo se tira al terminar la cita,
no se guarda para la siguiente.)

Esto es justo lo que la confirmación del doctor tiene que atrapar. El preset
habría supuesto **7 puntas** para 7 dientes — un inventario desviado 7× en el
insumo que más se usa. Sin el visto bueno del doctor, ese error se acumula en
silencio y a las tres semanas el número no sirve.

---

## El atajo que hace bueno al preset: la categoría predice el alcance

Con cuatro reglas ya se ve un patrón. **El tipo de insumo dice cómo se gasta**,
y eso permite que el preset acierte sin preguntar:

| Categoría del insumo | Alcance típico |
|---|---|
| Protección e higiene (guantes, babero, punta, algodón) | `visit` |
| Material de restauración (composite, matriz, grabador) | `tooth` |
| Anestesia | `contiguous_zone` |
| Curetaje y periodoncia | `quadrant` |

Así, el nivel 0 del preset puede asignar el alcance **solo por la categoría**, y
el doctor únicamente corrige las excepciones. Que es mucho más barato que
preguntarle el alcance de cada insumo, uno por uno.

---

## Regla 4 — Merma: sí, y con motivo

**Lo que dijo el doctor:** los dentistas sí registran mermas. Las causas
operativas que nombró:

| Causa | Qué es |
|---|---|
| **Caducidad** | Resinas, anestésicos, blanqueadores o cementos que expiran sin usarse |
| **Mal proceso** | Alginato o silicona mal mezclados, vaciado fallido de modelos |
| **Normativa** | Sobrante de amalgama y restos extraídos — no pueden ir al drenaje (Convenio de Minamata) |
| **Robo hormiga** | Pérdida menor de desechables y material de alta rotación |

Y dio el motivo por el que esto le importa al consultorio: **los dentistas
deducen fiscalmente estas mermas** como pérdidas permitidas por la ley.

### Dos mermas distintas que no se pueden mezclar

Aquí hay una trampa de doble conteo:

| | Merma de inventario | Merma normativa |
|---|---|---|
| Ejemplos | caducidad, mal mezclado, robo | sobrante de amalgama, restos extraídos |
| ¿Descuenta stock? | **Sí** — el material está en el anaquel y se pierde | **No** — ya salió del inventario cuando se mezcló |
| Para qué sirve | costo real y punto de reorden | cumplimiento y trazabilidad |

Registrar el sobrante de amalgama como salida de inventario **descontaría dos
veces lo mismo**. Es un registro de cumplimiento, no un movimiento de stock.

### Lo que esto le pide al modelo

1. **El motivo tiene que ser una categoría, no texto libre.** El contador suma
   por causa; "merma del mes por caducidad" no se puede sacar de un campo de
   texto. → `waste_reason`: `expired | spoiled | lost | other`

   **`regulatory` NO está en la lista, y es a propósito**: la merma normativa no
   es un movimiento de inventario, así que no puede ser un motivo del kardex.
   Tiene su propia tabla (`hazardous_wastes`). Hay un test que fija que no esté,
   para que nadie la agregue después sin pensarlo.

2. **La merma necesita costo para poder deducirse.** Hoy `unit_cost` solo se
   llena en las entradas. Una merma tiene que congelar el costo del insumo al
   momento, o no hay nada que reportarle al contador.

3. **Caducidad necesita lotes, y eso todavía no existe.** El kardex actual no
   rastrea lote ni fecha de vencimiento, así que **no se puede avisar "esto
   caduca en 15 días"**. Para eso hacen falta `supply_lots` (lote, fecha,
   cantidad) y consumir por FEFO — primero lo que caduca antes. Es una fase
   aparte, no un campo que se agrega y ya.

4. **El "robo hormiga" pide auditoría, no captura.** Se detecta cuando el
   consumo por procedimiento se desvía del promedio. Eso es análisis sobre el
   historial, no un movimiento más.

### Una aclaración de alcance

El doctor también mencionó la *"merma de dentistas"* — la fuga de profesionales
de ciertas zonas por tarifas bajas de aseguradoras y competencia informal. Eso
es un concepto gremial y de salud pública, no de inventario. No entra en este
motor.

---

## Cómo se va a ver al cerrar la consulta

El sistema propone los descuentos y el doctor corrige. No se trata de acertar
solo: con reglas clínicas que varían por doctor, la confirmación es parte del
diseño, no una concesión.

```
Se descontará:
  Composite A2          3.5 jeringa   (7 dientes)
  Puntas de aplicación  1 pieza       (1 paciente)
  Anestésico            2 cartucho    (2 zonas contiguas: 16-15 y 26)
  Guantes               2 pieza       (1 visita)
  Babero                1 pieza       (1 visita)
                                     [ Ajustar ]  [ Confirmar ]
```

Cuando el doctor ajusta, el sistema aprende la regla real — y ese ajuste es la
señal de que el default estaba mal, no un estorbo.

---

## Cómo se propone y cómo se confirma (acordado)

**Decisión:** al cerrar la consulta, el sistema **supone** los insumos y el
doctor **verifica y da el visto bueno**. No se captura desde cero, y no se
descuenta a escondidas.

### El trabajo del sistema no es acertar: es ser revisable

Una propuesta que acierta el 80% y se corrige en cinco segundos vale más que
una automatización que acierta el 95% y no se ve. Lo primero construye
confianza; lo segundo la destruye la primera vez que se equivoca, porque nadie
se enteró.

### Los cuatro niveles de la suposición, de menos a más listo

| Nivel | De dónde sale la propuesta | Cuándo aplica |
|---|---|---|
| **0. Preset** | Catálogo de recetas por especialidad, ya cargado | Consultorio nuevo, sin historial |
| **1. Su historial** | Las veces que él ya confirmó ese servicio | Después de confirmar ~3 veces, deja de preguntar |
| **2. Sus ajustes** | Si siempre agrega algo que el preset no traía, se propone agregarlo | Cuando el ajuste se repite |
| **3. Su catálogo** | Solo se proponen insumos que él tiene dados de alta | Siempre — no proponer lo que no compra |

El nivel 1 es el que decide si la función vive o muere: **se confirma una vez
por servicio, no una vez por visita.** Si el doctor tuviera que confirmar en
cada consulta para siempre, a la tercera semana deja de leer y aprueba en
automático — o abandona.

### Por qué el preset es obligatorio, no un extra

Sin nivel 0, el doctor tendría que armar la receta de cada servicio desde cero.
Son decenas de servicios, y **nadie lo va a hacer**. Con el preset, solo llena
lo que no conocemos: unos 10 servicios, no 80.

### El precedente ya existe en el código

`Onboarding::SERVICE_SUGGESTIONS` hace exactamente esto con el catálogo de
servicios: propone por especialidad (`general`, `ortodoncia`, `periodoncia`…) y
el doctor **edita y borra** antes de continuar. Es el mismo patrón, y ya está
probado con doctores reales.

La receta de insumos debe usar esa misma forma.

### Las dos reglas que no se pueden romper

1. **Nunca descontar en silencio un servicio sin receta.** Se marca como "sin
   configurar" y se ve en una lista, ordenada por frecuencia de uso. Un
   servicio que no descuenta nada desvía el inventario sin que nadie lo note.
2. **Nunca descontar sin que se vea.** La propuesta muestra cantidades y de
   dónde salen. La confirmación del doctor es lo que crea el movimiento.

### La propuesta se ve como lista de verificación, no como formulario

Con las cantidades ya puestas y todo palomeado. La acción del doctor es
**desmarcar lo que no usó**, que es como la gente verifica de verdad.

---

## Las reglas clínicas están completas

Las **cuatro reglas** quedaron confirmadas por el doctor, incluida la asimetría
entre arcadas, que era la pieza más fina. El motor ya tiene con qué calcular.

Lo que sigue no es clínico sino de construcción — ver la tabla del final.

---

## Lo que el motor le pide al código (resumen)

| Pieza | Estado |
|---|---|
| Insumos, kardex y compras | **Construido** |
| `services.unit` — unidad de cobro del servicio | **Construido** |
| `consultation_procedures` — servicio + diente + cantidad | **Construido** |
| `service_supplies.scope` — alcance por línea, con sobrescritura | **Construido** |
| Calculadores de alcance (zonas contiguas, anestesia por arcada) | **Construido** |
| Preset por categoría + lista de revisión | **Construido** |
| Motor de agregación de la propuesta | **Construido** |
| Propuesta + confirmación al cerrar la consulta | **Construido** |
| Descuento con reversa idempotente | **Construido** |
| `waste_reason` como categoría + costo congelado | **Construido** |
| `supply_lots` + reparto FEFO (caducidades) | **Construido** |
| Registro de residuos peligrosos (cumplimiento) | **Construido** |
| Alerta de alergias + dosis máxima por peso | **Construido** |

### Notas de lo último que se construyó

**La merma normativa vive en su propia tabla** (`hazardous_wastes`), no en el
kardex. Ese material ya salió del inventario cuando se mezcló, así que
registrarlo como salida descontaría dos veces lo mismo. Hay un test que fija que
registrar un residuo **no mueve el stock**.

**El límite de dosis viene VACÍO a propósito.** La dosis máxima depende del
anestésico —no es lo mismo la lidocaína con epinefrina que sin ella—, y un
número equivocado en una app clínica es peor que no dar ninguno: el doctor
confiaría en él. El mecanismo existe, y sin configurar **no compara nada**, que
es distinto de decir que está bien. Se configura en Ajustes del consultorio.

**Los lotes son opcionales.** Los guantes no caducan y capturarlos sería trabajo
sin provecho. Un insumo sin lotes simplemente no avisa de caducidades. El
consumo se reparte FEFO (primero lo que caduca antes) y **se calcula al leer**:
un `restante` guardado se desincroniza al primer ajuste, igual que un
`current_stock`.

### Lo que sigue faltando

- **Las mermas anteriores al cambio no tienen costo** y reportan $0. Hay que
  decidir si se rellenan con el costo actual del insumo o se dejan así.
- **Nada de esto está en producción todavía**: todo esto vive en el repo sin
  commitear.

