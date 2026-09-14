<?php

namespace App\Support;

use App\Models\Doctor;
use Illuminate\Support\Str;

/**
 * Lo que la ley pide que lleve una receta, y los medicamentos que no se
 * recetan como cualquier otro.
 *
 * Reglamento de Insumos para la Salud: nombre, domicilio y cédula de quien
 * prescribe, fecha y firma autógrafa (art. 29); dosis, presentación, vía de
 * administración, frecuencia y duración (art. 30); denominación genérica
 * (art. 31). Reglamento de atención médica (arts. 64 y 65) y Ley General de
 * Salud (art. 83): la institución que expidió el título y, del especialista,
 * su cédula de especialidad.
 */
class Receta
{
    public const VIAS = [
        'Oral' => 'Oral',
        'Sublingual' => 'Sublingual',
        'Tópica' => 'Tópica',
        'Enjuague bucal' => 'Enjuague bucal',
        'Intramuscular' => 'Intramuscular',
        'Intravenosa' => 'Intravenosa',
        'Subcutánea' => 'Subcutánea',
        'Oftálmica' => 'Oftálmica',
        'Ótica' => 'Ótica',
        'Nasal' => 'Nasal',
        'Inhalada' => 'Inhalada',
        'Rectal' => 'Rectal',
        'Vaginal' => 'Vaginal',
    ];

    /** Opioides que son estupefacientes: van en el recetario especial de COFEPRIS. */
    private const ESTUPEFACIENTES = ['morfina', 'fentanilo', 'oxicodona', 'metadona', 'hidromorfona', 'petidina', 'meperidina'];

    private const ANTIBIOTICOS = [
        'amoxicilina', 'ampicilina', 'penicilina', 'dicloxacilina', 'cefalexina', 'cefuroxima',
        'ceftriaxona', 'clindamicina', 'metronidazol', 'azitromicina', 'claritromicina',
        'eritromicina', 'ciprofloxacino', 'levofloxacino', 'doxiciclina', 'tetraciclina',
        'trimetoprima', 'sulfametoxazol', 'nitrofurantoina', 'gentamicina',
    ];

    /**
     * Lo que le falta al doctor para que su receta lleve lo que pide la ley.
     *
     * @return array<int, string>  Vacío si ya tiene todo.
     */
    public static function datosQueFaltan(?Doctor $doctor): array
    {
        if (! $doctor) {
            return ['tu perfil de doctor'];
        }

        $faltan = [];

        if (blank($doctor->license_number)) {
            $faltan[] = 'tu cédula profesional';
        }

        if (blank($doctor->institucion_titulo)) {
            $faltan[] = 'la institución que expidió tu título';
        }

        return $faltan;
    }

    /**
     * Un aviso cuando el medicamento no se receta como cualquier otro.
     * Null si no hace falta advertir nada.
     */
    public static function avisoDeControl(?string $medicamento): ?string
    {
        $texto = Str::of((string) $medicamento)->lower()->ascii()->toString();

        if (trim($texto) === '') {
            return null;
        }

        foreach (self::ESTUPEFACIENTES as $estupefaciente) {
            if (str_contains($texto, $estupefaciente)) {
                return 'Es estupefaciente (Fracción I): no va en receta ordinaria. Necesita el recetario especial de COFEPRIS con código de barras.';
            }
        }

        if (str_contains($texto, 'tramadol')) {
            return 'Desde el 14 de julio de 2026 el tramadol es controlado: la receta debe llevar tu cédula, y la farmacia la sella y anota la fecha y la cantidad.';
        }

        foreach (self::ANTIBIOTICOS as $antibiotico) {
            if (str_contains($texto, $antibiotico)) {
                return 'Es antibiótico: la farmacia solo lo vende con receta médica. Anota bien la dosis y cuántos días.';
            }
        }

        return null;
    }
}
