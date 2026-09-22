<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Cómo se cuenta un procedimiento — y, más adelante, un insumo.
 *
 * Es el vocabulario que comparten el cobro y el inventario, y a propósito: la
 * misma cuenta sirve para las dos cosas. Si el curetaje se cobra por cuadrante,
 * sus insumos de curetaje también se gastan por cuadrante. Calcularlo una vez
 * evita que el cobro y el kardex se desfasen, que es como empieza un inventario
 * que miente.
 *
 * Los cuatro alcances salen de una sola entrada —los dientes trabajados— y
 * cada línea elige el suyo:
 *
 *  - `visit`            → 1
 *  - `tooth`            → cuántos dientes
 *  - `quadrant`         → cuántos cuadrantes se tocaron
 *  - `contiguous_zone`  → cuántos grupos de dientes pegados (ver AnesthesiaDose)
 *
 * Ojo con `contiguous_zone`: en el maxilar cuenta grupos contiguos, pero en la
 * mandíbula el bloqueo troncular duerme el cuadrante entero aunque haya
 * huecos. La regla depende de la arcada.
 */
class WorkUnit
{
    public const VISIT = 'visit';
    public const TOOTH = 'tooth';
    public const QUADRANT = 'quadrant';
    public const CONTIGUOUS_ZONE = 'contiguous_zone';

    /** Todas, como las lee el doctor. */
    public const LABELS = [
        self::VISIT => 'Por visita',
        self::TOOTH => 'Por diente',
        self::QUADRANT => 'Por cuadrante',
        self::CONTIGUOUS_ZONE => 'Por zona contigua (anestesia)',
    ];

    /**
     * Las que tienen sentido para COBRAR un servicio.
     *
     * Una zona nerviosa no se cobra: es cómo se gasta la anestesia. Ofrecerla
     * en el catálogo de servicios solo invitaría a elegirla mal.
     */
    public const FOR_BILLING = [
        self::VISIT,
        self::TOOTH,
        self::QUADRANT,
    ];

    /** Cómo se le pregunta la cantidad al doctor, según la unidad. */
    public const QUESTION = [
        self::VISIT => '¿Cuántas visitas?',
        self::TOOTH => '¿Cuántos dientes?',
        self::QUADRANT => '¿Cuántos cuadrantes?',
        self::CONTIGUOUS_ZONE => '¿Cuántas zonas?',
    ];

    /**
     * Lo que en el NOMBRE de un servicio delata su unidad de cobro.
     *
     * Muchos servicios ya se llaman "Curetaje (por cuadrante)" o "Carillas de
     * porcelana (por pieza)" y su precio ES por esa unidad, pero quedaron
     * capturados como "por visita" — que es como se comportaba el sistema
     * antes de que el precio dijera de qué era.
     *
     * Es una sola lista, y la usan dos: la sugerencia en PHP y el filtro en
     * SQL. Si estuvieran separadas, el filtro enseñaría servicios que la
     * sugerencia no reconoce.
     */
    public const NAME_HINTS = [
        self::QUADRANT => ['por cuadrante', 'por 1 cuadrante', 'por cuadrantes'],
        self::TOOTH => ['por diente', 'por dientes', 'por pieza', 'por piezas'],
    ];

    /**
     * La unidad que el nombre deja entrever, si la dice.
     *
     * Solo devuelve algo cuando el nombre lo dice explícitamente. Adivinar la
     * unidad de "Resina (obturación)" sería peor que no sugerir nada: el
     * doctor capturaría mal sin darse cuenta, que es justo lo que estamos
     * tratando de arreglar.
     */
    public static function suggestedFromName(?string $name): ?string
    {
        $nombre = Str::of((string) $name)->lower()->ascii()->squish()->toString();

        foreach (self::NAME_HINTS as $unidad => $pistas) {
            foreach ($pistas as $pista) {
                if (str_contains($nombre, $pista)) {
                    return $unidad;
                }
            }
        }

        return null;
    }

    /** ¿El nombre pide una unidad distinta de la que tiene capturada? */
    public static function differsFromName(?string $currentUnit, ?string $name): bool
    {
        $sugerida = self::suggestedFromName($name);

        return $sugerida !== null && $sugerida !== $currentUnit;
    }

    /**
     * El alcance que le toca a un INSUMO por su categoría.
     *
     * El patrón lo dijo el doctor: la protección se gasta por visita, el
     * material de restauración por diente, la anestesia por zona contigua y el
     * curetaje por cuadrante. Con esto el preset de la receta acierta sin
     * preguntarle insumo por insumo — que es el trabajo que queremos quitarle.
     *
     * Devuelve null cuando la categoría no dice nada. "Instrumental" puede ser
     * cualquier cosa y adivinar sería peor que preguntar.
     */
    public const SCOPE_BY_CATEGORY = [
        'Protección' => self::VISIT,
        'Desechable' => self::VISIT,
        'Anestesia' => self::CONTIGUOUS_ZONE,
        'Restaurador' => self::TOOTH,
        'Endodoncia' => self::TOOTH,
        'Periodoncia' => self::QUADRANT,
    ];

    public static function scopeSuggestedByCategory(?string $category): ?string
    {
        return self::SCOPE_BY_CATEGORY[$category] ?? null;
    }

    /** Solo las unidades de cobro, para el catálogo de servicios. */
    public static function billingLabels(): array
    {
        return array_intersect_key(self::LABELS, array_flip(self::FOR_BILLING));
    }

    public static function isValid(?string $unit): bool
    {
        return $unit !== null && array_key_exists($unit, self::LABELS);
    }

    public static function isBillable(?string $unit): bool
    {
        return $unit !== null && in_array($unit, self::FOR_BILLING, true);
    }

    public static function label(?string $unit): string
    {
        return self::LABELS[$unit] ?? 'Por visita';
    }

    public static function question(?string $unit): string
    {
        return self::QUESTION[$unit] ?? self::QUESTION[self::VISIT];
    }
}
