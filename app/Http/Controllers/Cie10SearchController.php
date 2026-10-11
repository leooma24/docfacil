<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class Cie10SearchController extends Controller
{
    private const CATALOG_PATH = 'database/data/cie10-mx.json';

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $catalog = $this->loadCatalog();
        $needle = mb_strtolower($this->stripAccents($query));

        // El catálogo oficial trae 14 mil diagnósticos (11-oct-2026): primero
        // los de la boca (K00 a K14) y los que empiezan con lo que se escribió.
        $matches = [];
        foreach ($catalog as $entry) {
            $code = $entry['code'];
            $haystack = mb_strtolower($code . ' ' . $this->stripAccents($entry['name']));

            if (str_contains($haystack, $needle)) {
                $deLaBoca = preg_match('/^K(0\d|1[0-4])/', $code) === 1;
                $empieza = str_starts_with(mb_strtolower($code), $needle) || str_starts_with(mb_strtolower($this->stripAccents($entry['name'])), $needle);
                $matches[] = [($deLaBoca ? 0 : 2) + ($empieza ? 0 : 1), $entry];
            }
        }
        usort($matches, fn ($a, $b) => $a[0] <=> $b[0]);

        return response()->json(array_map(fn ($m) => $m[1], array_slice($matches, 0, 20)));
    }

    /**
     * Resuelve una lista de códigos a sus entradas completas (code + name).
     * Lo usa el frontend al hidratar el estado guardado del campo cie10_codes.
     */
    public function resolve(Request $request): JsonResponse
    {
        $codesParam = (string) $request->query('codes', '');
        $codes = array_filter(array_map('trim', explode(',', $codesParam)));

        if (empty($codes)) {
            return response()->json([]);
        }

        $catalog = $this->loadCatalog();
        $byCode = collect($catalog)->keyBy('code');

        $resolved = [];
        foreach ($codes as $code) {
            $resolved[] = $byCode->get($code, ['code' => $code, 'name' => '(no encontrado)']);
        }

        return response()->json($resolved);
    }

    private function loadCatalog(): array
    {
        return Cache::remember('cie10_catalog_v2', now()->addDay(), function () {
            $path = base_path(self::CATALOG_PATH);
            if (! File::exists($path)) {
                return [];
            }
            $data = json_decode(File::get($path), true);
            return is_array($data) ? $data : [];
        });
    }

    private function stripAccents(string $text): string
    {
        $from = ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ'];
        $to   = ['a','e','i','o','u','a','e','i','o','u','n','n'];
        return str_replace($from, $to, $text);
    }
}
