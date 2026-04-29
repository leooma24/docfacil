<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use Illuminate\Support\Facades\Cache;

/**
 * Páginas pSEO por ciudad: /software-dental/{slug}
 *
 * El array de ciudades cubre todas las plazas con tracción real (>=5
 * prospectos en BD). Cada página inyecta datos reales (cuántos consultorios
 * hay en BD, especialidad top, distribución) para que cada URL tenga
 * contenido único — no plantilla repetida.
 */
class CityLandingController extends Controller
{
    /**
     * Slug → metadata. Mantiene slug sin acentos (convención SEO mexicana).
     * El nombre con acentos se usa para mostrar y para querying contra BD.
     */
    private array $cities = [
        // --- Originales (top tier) ---
        'culiacan'        => ['name' => 'Culiacán',              'state' => 'Sinaloa',              'db_name' => 'Culiacán'],
        'cdmx'            => ['name' => 'Ciudad de México',      'state' => 'CDMX',                 'db_name' => 'Ciudad de México'],
        'guadalajara'     => ['name' => 'Guadalajara',           'state' => 'Jalisco',              'db_name' => 'Guadalajara'],
        'monterrey'       => ['name' => 'Monterrey',             'state' => 'Nuevo León',           'db_name' => 'Monterrey'],
        'puebla'          => ['name' => 'Puebla',                'state' => 'Puebla',               'db_name' => 'Puebla'],
        'tijuana'         => ['name' => 'Tijuana',               'state' => 'Baja California',      'db_name' => 'Tijuana'],
        'leon'            => ['name' => 'León',                  'state' => 'Guanajuato',           'db_name' => 'León'],
        'merida'          => ['name' => 'Mérida',                'state' => 'Yucatán',              'db_name' => 'Mérida'],
        'cancun'          => ['name' => 'Cancún',                'state' => 'Quintana Roo',         'db_name' => 'Cancún'],
        'queretaro'       => ['name' => 'Querétaro',             'state' => 'Querétaro',            'db_name' => 'Santiago de Querétaro'],
        'aguascalientes'  => ['name' => 'Aguascalientes',        'state' => 'Aguascalientes',       'db_name' => 'Aguascalientes'],
        'chihuahua'       => ['name' => 'Chihuahua',             'state' => 'Chihuahua',            'db_name' => 'Chihuahua'],
        'morelia'         => ['name' => 'Morelia',               'state' => 'Michoacán',            'db_name' => 'Morelia'],
        'toluca'          => ['name' => 'Toluca',                'state' => 'Estado de México',     'db_name' => 'Toluca'],
        'hermosillo'      => ['name' => 'Hermosillo',            'state' => 'Sonora',               'db_name' => 'Hermosillo'],
        'saltillo'        => ['name' => 'Saltillo',              'state' => 'Coahuila',             'db_name' => 'Saltillo'],
        'mazatlan'        => ['name' => 'Mazatlán',              'state' => 'Sinaloa',              'db_name' => 'Mazatlán'],
        'los-mochis'      => ['name' => 'Los Mochis',            'state' => 'Sinaloa',              'db_name' => 'Los Mochis'],

        // --- Nuevas (con tracción real) ---
        'ciudad-obregon'        => ['name' => 'Ciudad Obregón',          'state' => 'Sonora',              'db_name' => 'Ciudad Obregón'],
        'ciudad-juarez'         => ['name' => 'Ciudad Juárez',           'state' => 'Chihuahua',           'db_name' => 'Ciudad Juárez'],
        'torreon'               => ['name' => 'Torreón',                 'state' => 'Coahuila',            'db_name' => 'Torreón'],
        'metepec'               => ['name' => 'Metepec',                 'state' => 'Estado de México',    'db_name' => 'Metepec'],
        'cuernavaca'            => ['name' => 'Cuernavaca',              'state' => 'Morelos',             'db_name' => 'Cuernavaca'],
        'boca-del-rio'          => ['name' => 'Boca del Río',            'state' => 'Veracruz',            'db_name' => 'Boca del Río'],
        'san-andres-cholula'    => ['name' => 'San Andrés Cholula',      'state' => 'Puebla',              'db_name' => 'San Andrés Cholula'],
        'san-luis-potosi'       => ['name' => 'San Luis Potosí',         'state' => 'San Luis Potosí',     'db_name' => 'San Luis Potosí'],
        'playa-del-carmen'      => ['name' => 'Playa del Carmen',        'state' => 'Quintana Roo',        'db_name' => 'Playa del Carmen'],
        'gomez-palacio'         => ['name' => 'Gómez Palacio',           'state' => 'Durango',             'db_name' => 'Gómez Palacio'],
        'jiutepec'              => ['name' => 'Jiutepec',                'state' => 'Morelos',             'db_name' => 'Jiutepec'],
        'san-pedro-garza-garcia'=> ['name' => 'San Pedro Garza García',  'state' => 'Nuevo León',          'db_name' => 'San Pedro Garza García'],
        'zapopan'               => ['name' => 'Zapopan',                 'state' => 'Jalisco',             'db_name' => 'Zapopan'],
        'irapuato'              => ['name' => 'Irapuato',                'state' => 'Guanajuato',          'db_name' => 'Irapuato'],
        'temixco'                => ['name' => 'Temixco',                'state' => 'Morelos',             'db_name' => 'Temixco'],
        'guasave'               => ['name' => 'Guasave',                 'state' => 'Sinaloa',             'db_name' => 'Guasave'],
        'ramos-arizpe'          => ['name' => 'Ramos Arizpe',            'state' => 'Coahuila',            'db_name' => 'Ramos Arizpe'],
        'rosarito'              => ['name' => 'Rosarito',                'state' => 'Baja California',     'db_name' => 'Rosarito'],
        'san-juan-del-rio'      => ['name' => 'San Juan del Río',        'state' => 'Querétaro',           'db_name' => 'San Juan del Río'],
        'san-pedro-cholula'     => ['name' => 'San Pedro Cholula',       'state' => 'Puebla',              'db_name' => 'San Pedro Cholula'],
        'tulum'                 => ['name' => 'Tulum',                   'state' => 'Quintana Roo',        'db_name' => 'Tulum'],
        'silao'                 => ['name' => 'Silao',                   'state' => 'Guanajuato',          'db_name' => 'Silao'],
    ];

    public function show(string $slug)
    {
        if (! isset($this->cities[$slug])) {
            abort(404);
        }

        $data = $this->cities[$slug];

        // Métricas reales por ciudad. Cacheamos 1 hora para no martillar BD.
        $stats = Cache::remember("city-landing-stats:{$slug}", 3600, function () use ($data) {
            return $this->computeStats($data['db_name']);
        });

        return view('city-landing', [
            'city'        => $data['name'],
            'state'       => $data['state'],
            'slug'        => $slug,
            'stats'       => $stats,
            'all_cities'  => $this->getCitiesForFooter(),
        ]);
    }

    /**
     * Lista de ciudades publicada (slug + name). Útil para footer/sitemap.
     */
    public function getCitiesForFooter(): array
    {
        return collect($this->cities)
            ->map(fn ($d, $slug) => ['slug' => $slug, 'name' => $d['name'], 'state' => $d['state']])
            ->values()
            ->all();
    }

    /**
     * Insights reales de la ciudad desde BD para hacer pSEO no genérico.
     * Conteo de prospectos = "consultorios identificados", especialidad top,
     * etc. Si no hay data, defaults seguros.
     */
    private function computeStats(string $dbName): array
    {
        $totalProspects = Prospect::where('city', $dbName)->count();

        $topSpecialties = Prospect::where('city', $dbName)
            ->whereNotNull('specialty')
            ->where('specialty', '!=', '')
            ->selectRaw('specialty, count(*) as c')
            ->groupBy('specialty')
            ->orderByDesc('c')
            ->limit(3)
            ->pluck('c', 'specialty')
            ->all();

        // "Más de N consultorios" — redondeamos hacia abajo en decenas
        // para no parecer súper preciso (sale más natural en marketing).
        $roundedConsultorios = $totalProspects >= 10 ? floor($totalProspects / 10) * 10 : max($totalProspects, 0);

        return [
            'total'                => $totalProspects,
            'rounded_consultorios' => $roundedConsultorios,
            'top_specialties'      => $topSpecialties,
            'has_data'             => $totalProspects > 0,
        ];
    }
}
