<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Cruza lo que se va a recetar con lo que ya se sabe del paciente: sus
 * alergias y lo que el doctor anotó en sus notas médicas (medicamentos que
 * toma, embarazo, enfermedades).
 *
 * Son avisos para que el doctor revise, no un dictamen: no sabe la historia
 * completa, solo junta dos datos en el momento en que se pueden juntar. Por
 * eso dicen "revise" y nunca "no recete".
 */
class AlertasClinicas
{
    /**
     * Grupos de medicamentos: cómo se le dice al grupo, las palabras con que
     * se anota la alergia, y los medicamentos del grupo (sin acentos).
     */
    private const GRUPOS = [
        'penicilinas' => [
            'nombre' => 'una penicilina',
            'alergia' => ['penicilin', 'amoxicilin', 'ampicilin', 'betalactam', 'beta-lactam', 'dicloxacilin'],
            'medicamentos' => ['penicilin', 'amoxicilin', 'ampicilin', 'dicloxacilin', 'piperacilin', 'clavulan', 'bencetacil'],
        ],
        'cefalosporinas' => [
            'nombre' => 'una cefalosporina',
            'alergia' => ['cefalospor', 'cefalexin', 'cefuroxim', 'ceftriaxon', 'cefadroxil', 'cefixim'],
            'medicamentos' => ['cefalexin', 'cefuroxim', 'ceftriaxon', 'cefadroxil', 'cefixim', 'cefaclor', 'cefotaxim', 'cefepim'],
        ],
        'aines' => [
            'nombre' => 'un antiinflamatorio (AINE)',
            'alergia' => ['aine', 'aspirin', 'acetilsalicil', 'ibuprofen', 'naproxen', 'diclofenac', 'ketorolac', 'metamizol', 'antiinflamator'],
            'medicamentos' => ['aspirin', 'acetilsalicil', 'ibuprofen', 'naproxen', 'diclofenac', 'ketorolac', 'metamizol', 'nimesulid', 'celecoxib', 'meloxicam', 'indometacin', 'piroxicam', 'ketoprofen', 'etoricoxib', 'dexketoprofen'],
        ],
        'sulfas' => [
            'nombre' => 'una sulfa',
            'alergia' => ['sulfa'],
            'medicamentos' => ['sulfametox', 'trimetoprim', 'sulfadiaz', 'bactrim'],
        ],
        'anestesicos' => [
            'nombre' => 'un anestésico local',
            'alergia' => ['lidocain', 'mepivacain', 'articain', 'prilocain', 'bupivacain', 'xilocain', 'anestesi'],
            'medicamentos' => ['lidocain', 'mepivacain', 'articain', 'prilocain', 'bupivacain', 'xilocain'],
        ],
        'macrolidos' => [
            'nombre' => 'un macrólido',
            'alergia' => ['macrolid', 'eritromicin', 'azitromicin', 'claritromicin'],
            'medicamentos' => ['eritromicin', 'azitromicin', 'claritromicin'],
        ],
        'quinolonas' => [
            'nombre' => 'una quinolona',
            'alergia' => ['quinolon', 'ciprofloxacin', 'levofloxacin', 'moxifloxacin'],
            'medicamentos' => ['ciprofloxacin', 'levofloxacin', 'moxifloxacin', 'norfloxacin'],
        ],
        'tetraciclinas' => [
            'nombre' => 'una tetraciclina',
            'alergia' => ['tetraciclin', 'doxiciclin', 'minociclin'],
            'medicamentos' => ['tetraciclin', 'doxiciclin', 'minociclin'],
        ],
    ];

    /** Penicilina y cefalosporina pueden cruzarse; se avisa más suave. */
    private const CRUZADAS = [['penicilinas', 'cefalosporinas'], ['cefalosporinas', 'penicilinas']];

    private const ANTICOAGULANTES = ['warfarin', 'acenocumarol', 'sintrom', 'rivaroxaban', 'apixaban', 'dabigatran', 'edoxaban', 'heparin', 'enoxaparin', 'clopidogrel', 'anticoagul'];

    /** Delicados en el embarazo: se pide revisar, no se prohíbe. */
    private const CUIDADO_EN_EMBARAZO = ['tetraciclin', 'doxiciclin', 'minociclin', 'misoprostol', 'isotretinoin', 'warfarin', 'acenocumarol', 'metotrexat', 'ciprofloxacin', 'levofloxacin'];

    private const ANTECEDENTES = [
        'Diabetes' => ['diabet'],
        'Hipertensión' => ['hipertens', 'presion alta'],
        'Embarazo' => ['embaraz'],
        'Anticoagulantes' => self::ANTICOAGULANTES,
        'Asma' => ['asma', 'asmatic'],
        'Epilepsia' => ['epilep', 'convuls'],
        'Cardiopatía' => ['cardiopat', 'infarto', 'marcapasos', 'arritmi'],
        'Enfermedad renal' => ['renal', 'rinon', 'dialisis'],
        'Enfermedad hepática' => ['hepat', 'cirrosis'],
    ];

    private static function limpio(?string $texto): string
    {
        return Str::of((string) $texto)->lower()->ascii()->toString();
    }

    private static function tiene(string $texto, array $palabras): bool
    {
        foreach ($palabras as $palabra) {
            if (str_contains($texto, $palabra)) {
                return true;
            }
        }

        return false;
    }

    /** Los grupos a los que pertenece el medicamento. */
    private static function gruposDe(string $medicamento): array
    {
        return array_keys(array_filter(self::GRUPOS, fn ($g) => self::tiene($medicamento, $g['medicamentos'])));
    }

    /**
     * El aviso más importante al recetar este medicamento a este paciente, o
     * null. ['tipo' => alergia|cruzada|sangrado|embarazo, 'texto' => '...'].
     */
    public static function alRecetar(?string $medicamento, ?string $alergias, ?string $notas): ?array
    {
        $med = self::limpio($medicamento);
        if (trim($med) === '') {
            return null;
        }

        $alergia = self::limpio($alergias);
        $nota = self::limpio($notas);
        $nombreMed = trim(Str::before((string) $medicamento, ' ')) ?: (string) $medicamento;

        if ($alergia !== '') {
            foreach (self::gruposDe($med) as $grupo) {
                if (self::tiene($alergia, self::GRUPOS[$grupo]['alergia'])) {
                    return ['tipo' => 'alergia', 'texto' => "Alergia registrada: {$alergias}. {$nombreMed} es " . self::GRUPOS[$grupo]['nombre'] . '. Revise antes de recetar.'];
                }
            }

            foreach (self::CRUZADAS as [$alergico, $recetado]) {
                if (in_array($recetado, self::gruposDe($med), true) && self::tiene($alergia, self::GRUPOS[$alergico]['alergia'])) {
                    return ['tipo' => 'cruzada', 'texto' => "Alergia registrada: {$alergias}. {$nombreMed} es " . self::GRUPOS[$recetado]['nombre'] . ': puede haber reacción cruzada. Revise antes de recetar.'];
                }
            }

            // Lo que el doctor escribió tal cual: "Clindamicina" y recetan clindamicina.
            foreach (preg_split('/[^a-z0-9]+/', $alergia) as $palabra) {
                if (strlen($palabra) >= 6 && str_contains($med, rtrim($palabra, 'as'))) {
                    return ['tipo' => 'alergia', 'texto' => "Alergia registrada: {$alergias}. Revise antes de recetar {$nombreMed}."];
                }
            }
        }

        if ($nota !== '') {
            if (in_array('aines', self::gruposDe($med), true) && self::tiene($nota, self::ANTICOAGULANTES)) {
                return ['tipo' => 'sangrado', 'texto' => "Toma anticoagulantes (según sus notas). {$nombreMed} aumenta el riesgo de sangrado. Revise antes de recetar."];
            }
            if (self::tiene($nota, ['embaraz']) && self::tiene($med, self::CUIDADO_EN_EMBARAZO)) {
                return ['tipo' => 'embarazo', 'texto' => "Embarazo anotado en sus notas. {$nombreMed} requiere cuidado en el embarazo. Revise antes de recetar."];
            }
        }

        return null;
    }

    /** Lo que se lee en las notas médicas: ['Diabetes', 'Hipertensión', ...]. */
    public static function antecedentes(?string $notas): array
    {
        $nota = self::limpio($notas);
        if ($nota === '') {
            return [];
        }

        return array_keys(array_filter(self::ANTECEDENTES, fn ($palabras) => self::tiene($nota, $palabras)));
    }

    public static function tomaAnticoagulantes(?string $notas): bool
    {
        return self::tiene(self::limpio($notas), self::ANTICOAGULANTES);
    }
}
