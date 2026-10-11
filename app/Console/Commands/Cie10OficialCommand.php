<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenSpout\Reader\XLSX\Reader;

/**
 * Carga el CIE-10 oficial de la DGIS (11-oct-2026; NOM-024, 6.4.2).
 *
 * La DGIS lo publica como DIAGNOSTICOS_AAAAMMDD.xlsx dentro de un ZIP en
 * gobi.salud.gob.mx (catálogos maestros). Este comando lo convierte al JSON
 * que usa la búsqueda: código con punto (K02.1), nombre en minúsculas y, si
 * el diagnóstico es de un solo sexo, cuál.
 */
class Cie10OficialCommand extends Command
{
    protected $signature = 'docfacil:cie10 {excel : El DIAGNOSTICOS_AAAAMMDD.xlsx de la DGIS} {--salida= : Dónde escribir el JSON}';

    protected $description = 'Convierte el catálogo CIE-10 oficial de la DGIS al JSON de la búsqueda';

    public function handle(): int
    {
        $excel = (string) $this->argument('excel');
        if (! is_file($excel)) {
            $this->error("No existe {$excel}");

            return self::FAILURE;
        }

        $lector = new Reader();
        $lector->open($excel);
        $catalogo = [];
        foreach ($lector->getSheetIterator() as $hoja) {
            $columnas = null;
            foreach ($hoja->getRowIterator() as $fila) {
                $valores = $fila->toArray();
                if ($columnas === null) {
                    $columnas = array_flip(array_map(fn ($c) => strtoupper(trim((string) $c)), $valores));
                    if (! isset($columnas['CATALOG_KEY'], $columnas['NOMBRE'])) {
                        break;
                    }

                    continue;
                }
                $clave = strtoupper(trim((string) ($valores[$columnas['CATALOG_KEY']] ?? '')));
                $nombre = trim((string) ($valores[$columnas['NOMBRE']] ?? ''));
                if (! preg_match('/^[A-Z][0-9][0-9A-Z]{1,3}$/', $clave) || $nombre === '') {
                    continue;
                }
                // La DGIS rellena con X los de 3 caracteres sin subdivisión
                // (R51X = Cefalea): el código es R51.
                if (strlen($clave) === 4 && $clave[3] === 'X') {
                    $clave = substr($clave, 0, 3);
                }
                $entrada = [
                    'code' => strlen($clave) > 3 ? substr($clave, 0, 3) . '.' . substr($clave, 3) : $clave,
                    'name' => self::enMinusculas($nombre),
                ];
                $sexo = strtoupper(trim((string) ($valores[$columnas['LSEX'] ?? -1] ?? 'NO')));
                if (in_array($sexo, ['MUJER', 'HOMBRE'], true)) {
                    $entrada['sexo'] = strtolower($sexo);
                }
                // A09 y A09X son el mismo: se queda el primero.
                $catalogo[$entrada['code']] ??= $entrada;
            }
            if ($catalogo !== []) {
                break;
            }
        }
        $lector->close();

        if ($catalogo === []) {
            $this->error('El archivo no trae las columnas CATALOG_KEY y NOMBRE.');

            return self::FAILURE;
        }

        $catalogo = array_values($catalogo);
        $salida = $this->option('salida') ?: base_path('database/data/cie10-mx.json');
        file_put_contents($salida, "[\n" . implode(",\n", array_map(fn ($e) => '  ' . json_encode($e, JSON_UNESCAPED_UNICODE), $catalogo)) . "\n]\n");
        try {
            cache()->forget('cie10_catalog_v2');
        } catch (\Throwable) {
            // Sin caché a la mano (p. ej. al generarlo en otra máquina): se renueva sola en un día.
        }
        $this->info(count($catalogo) . ' diagnósticos en ' . $salida);

        return self::SUCCESS;
    }

    /** "CARIES DE LA DENTINA" → "Caries de la dentina". */
    private static function enMinusculas(string $texto): string
    {
        $bajo = mb_strtolower($texto);

        return mb_strtoupper(mb_substr($bajo, 0, 1)) . mb_substr($bajo, 1);
    }
}
