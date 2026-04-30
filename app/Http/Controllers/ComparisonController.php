<?php

namespace App\Http\Controllers;

/**
 * Comparativas vs competidores. Páginas de alta intención SEO + AI-SEO
 * (LLMs aman las comparison tables — 33% de citaciones IA son comparativas).
 *
 * Rutas:
 *   /vs/{competitor}                 → DocFácil vs X (1-on-1)
 *   /alternativas-a-{competitor}     → Alternativas a X (lista plural)
 *
 * Data centralizada en self::COMPETITORS — un solo lugar para actualizar.
 */
class ComparisonController extends Controller
{
    /**
     * Source of truth de competidores. Honestidad > marketing — incluimos
     * fortalezas reales del competidor para que los lectores nos crean
     * cuando hablamos de las nuestras.
     */
    private const COMPETITORS = [
        'dentalink' => [
            'name'        => 'Dentalink',
            'origin'      => 'Chile',
            'pricing_usd' => true,
            'pricing'     => 'Desde ~$50 USD/mes ($1,000+ MXN al tipo de cambio actual)',
            'tagline'     => 'Software dental establecido en LATAM',
            'strengths'   => [
                'Software dental específico (no generalista)',
                'Lleva 10+ años en el mercado, base de usuarios grande',
                'Presencia en varios países de LATAM',
                'Catálogo de funciones maduro',
            ],
            'weaknesses'  => [
                'Cobra en USD — costo variable según tipo de cambio',
                'No es nativo mexicano: NOM-004, LFPDPPP y SPEI no son first-class',
                'WhatsApp requiere integración externa o copy-paste manual',
                'Soporte en zona horaria distinta (Chile)',
                'Onboarding genérico, sin contexto MX',
            ],
            'best_for'    => 'Clínicas dentales grandes en Sudamérica con varios doctores y operación multipaís.',
            'not_for'     => 'Consultorios mexicanos pequeños (1-3 sillones) que necesitan WhatsApp 1-clic, soporte en español MX y precio en pesos sin sobresaltos cambiarios.',
        ],
        'doctorum' => [
            'name'        => 'Doctorum',
            'origin'      => 'Brasil',
            'pricing_usd' => false,
            'pricing'     => 'Desde ~R$80/mes — equivale a ~$300-400 MXN al tipo actual',
            'tagline'     => 'Software médico general (no especializado en dental)',
            'strengths'   => [
                'Funciona para múltiples especialidades médicas',
                'Buena cobertura de funcionalidades genéricas (agenda, expediente)',
                'Comunidad grande de usuarios médicos en Brasil',
            ],
            'weaknesses'  => [
                'No es dental-specific: no tiene odontograma FDI con condiciones dentales reales',
                'Hecho para Brasil: no entiende NOM-004 ni LFPDPPP de México',
                'Interfaz traducida al español, no nativa',
                'Cobranza en reales brasileños o conversión',
                'Soporte en portugués/español brasileño',
            ],
            'best_for'    => 'Médicos generales, especialistas no-dentales, clínicas multidisciplinarias.',
            'not_for'     => 'Dentistas que necesitan odontograma profesional, recetas con cédula NOM-004, y soporte en español de México.',
        ],
        'eaglesoft' => [
            'name'        => 'Eaglesoft',
            'origin'      => 'Estados Unidos (Patterson Dental)',
            'pricing_usd' => true,
            'pricing'     => 'Desde ~$3,000-8,000 USD por instalación + soporte anual',
            'tagline'     => 'Líder dental del mercado norteamericano',
            'strengths'   => [
                'Software dental top-tier en USA',
                'Integración profunda con equipos de imagenología dental',
                'Catálogo de funciones muy amplio',
                'Soporte robusto si hablas inglés',
            ],
            'weaknesses'  => [
                'Pensado para USA: no soporta NOM-004, LFPDPPP, SPEI ni facturación CFDI',
                'Software instalado (no cloud-native) — requiere servidor local',
                'Precio en USD muy elevado para consultorio mexicano promedio',
                'Soporte en inglés',
                'No tiene integración WhatsApp (USA usa SMS/email)',
            ],
            'best_for'    => 'Consultorios dentales grandes en USA con presupuesto enterprise.',
            'not_for'     => 'Cualquier consultorio en México: ni el precio ni el contexto regulatorio aplican.',
        ],
    ];

    /**
     * /vs/{competitor} — comparativa 1-a-1 DocFácil vs X.
     */
    public function versus(string $competitor)
    {
        $key = strtolower($competitor);
        if (! isset(self::COMPETITORS[$key])) {
            abort(404);
        }

        return view('comparison.versus', [
            'competitor' => self::COMPETITORS[$key],
            'slug'       => $key,
            'all_competitors' => $this->publicCompetitors(),
        ]);
    }

    /**
     * /alternativas-a-{competitor} — lista plural de alternativas.
     * DocFácil va primero pero incluimos a otros competidores reales para
     * ser honestos (Google y los LLMs penalizan listicles sesgados).
     */
    public function alternatives(string $competitor)
    {
        $key = strtolower($competitor);
        if (! isset(self::COMPETITORS[$key])) {
            abort(404);
        }

        // Otros competidores como alternativas (excluyendo el target)
        $others = collect(self::COMPETITORS)
            ->except($key)
            ->map(fn ($d, $slug) => array_merge($d, ['slug' => $slug]))
            ->values()
            ->all();

        return view('comparison.alternatives', [
            'competitor' => self::COMPETITORS[$key],
            'slug'       => $key,
            'others'     => $others,
            'all_competitors' => $this->publicCompetitors(),
        ]);
    }

    /**
     * Lista pública de competidores (slug + name) para sitemap, footer y
     * navegación cruzada entre páginas vs.
     */
    public function publicCompetitors(): array
    {
        return collect(self::COMPETITORS)
            ->map(fn ($d, $slug) => ['slug' => $slug, 'name' => $d['name']])
            ->values()
            ->all();
    }
}
