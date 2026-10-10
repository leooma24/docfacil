<?php

namespace App\Support;

/**
 * La CURP del paciente (NOM-024-SSA3-2012, 6.5).
 *
 * Se valida el formato y el dígito verificador (el último carácter, que se
 * calcula con los otros 17): así se detecta una CURP mal tecleada. El sistema
 * nunca la genera (6.5.1); solo lee lo que trae: fecha de nacimiento, sexo y
 * estado donde nació.
 */
class Curp
{
    /** Entidades de nacimiento de la CURP (RENAPO). NE = nacido en el extranjero. */
    public const ENTIDADES = [
        'AS' => 'Aguascalientes', 'BC' => 'Baja California', 'BS' => 'Baja California Sur', 'CC' => 'Campeche',
        'CL' => 'Coahuila', 'CM' => 'Colima', 'CS' => 'Chiapas', 'CH' => 'Chihuahua', 'DF' => 'Ciudad de México',
        'DG' => 'Durango', 'GT' => 'Guanajuato', 'GR' => 'Guerrero', 'HG' => 'Hidalgo', 'JC' => 'Jalisco',
        'MC' => 'Estado de México', 'MN' => 'Michoacán', 'MS' => 'Morelos', 'NT' => 'Nayarit', 'NL' => 'Nuevo León',
        'OC' => 'Oaxaca', 'PL' => 'Puebla', 'QT' => 'Querétaro', 'QR' => 'Quintana Roo', 'SP' => 'San Luis Potosí',
        'SL' => 'Sinaloa', 'SR' => 'Sonora', 'TC' => 'Tabasco', 'TS' => 'Tamaulipas', 'TL' => 'Tlaxcala',
        'VZ' => 'Veracruz', 'YN' => 'Yucatán', 'ZS' => 'Zacatecas', 'NE' => 'Nacido en el extranjero',
    ];

    private const DICCIONARIO = '0123456789ABCDEFGHIJKLMNÑOPQRSTUVWXYZ';

    public static function limpia(?string $curp): ?string
    {
        $curp = mb_strtoupper(trim((string) $curp));

        return $curp === '' ? null : $curp;
    }

    public static function valida(?string $curp): bool
    {
        $curp = self::limpia($curp);
        $entidades = implode('|', array_keys(self::ENTIDADES));

        if (! $curp || ! preg_match('/^[A-Z][AEIOUX][A-Z]{2}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[HMX](' . $entidades . ')[B-DF-HJ-NP-TV-Z]{3}[0-9A-Z]\d$/u', $curp)) {
            return false;
        }

        [$anio, $mes, $dia] = self::fecha($curp);
        if (! checkdate($mes, $dia, $anio)) {
            return false;
        }

        return self::digitoVerificador(mb_substr($curp, 0, 17)) === (int) mb_substr($curp, 17, 1);
    }

    /**
     * Lo que se lee de una CURP válida, con los nombres de las columnas del
     * paciente. null si no es válida.
     *
     * @return array{birth_date: string, gender: string, entidad_nacimiento: string}|null
     */
    public static function datos(?string $curp): ?array
    {
        if (! self::valida($curp)) {
            return null;
        }

        $curp = self::limpia($curp);
        [$anio, $mes, $dia] = self::fecha($curp);

        return [
            'birth_date' => sprintf('%04d-%02d-%02d', $anio, $mes, $dia),
            'gender' => ['H' => 'male', 'M' => 'female', 'X' => 'other'][$curp[10]],
            'entidad_nacimiento' => substr($curp, 11, 2),
        ];
    }

    /** @return array{0: int, 1: int, 2: int} año, mes, día */
    private static function fecha(string $curp): array
    {
        // El carácter 17 distingue el siglo: número antes del 2000, letra después.
        $siglo = ctype_digit($curp[16]) ? 1900 : 2000;

        return [$siglo + (int) substr($curp, 4, 2), (int) substr($curp, 6, 2), (int) substr($curp, 8, 2)];
    }

    private static function digitoVerificador(string $diecisiete): int
    {
        $suma = 0;
        foreach (mb_str_split($diecisiete) as $i => $caracter) {
            $suma += mb_strpos(self::DICCIONARIO, $caracter) * (18 - $i);
        }

        return (10 - $suma % 10) % 10;
    }
}
