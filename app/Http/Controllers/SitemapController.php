<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    private array $cities = [
        'cdmx' => 'Ciudad de México',
        'guadalajara' => 'Guadalajara',
        'monterrey' => 'Monterrey',
        'puebla' => 'Puebla',
        'tijuana' => 'Tijuana',
        'leon' => 'León',
        'merida' => 'Mérida',
        'cancun' => 'Cancún',
        'queretaro' => 'Querétaro',
        'aguascalientes' => 'Aguascalientes',
        'chihuahua' => 'Chihuahua',
        'morelia' => 'Morelia',
        'toluca' => 'Toluca',
        'hermosillo' => 'Hermosillo',
        'saltillo' => 'Saltillo',
    ];

    public function index()
    {
        $urls = [
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => url('/doctor/login'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => url('/doctor/register'), 'priority' => '0.9', 'changefreq' => 'monthly'],
        ];

        foreach ($this->cities as $slug => $name) {
            $urls[] = ['loc' => url("/software-dental/{$slug}"), 'priority' => '0.7', 'changefreq' => 'monthly'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= "<loc>{$url['loc']}</loc>";
            $xml .= "<lastmod>" . now()->toDateString() . "</lastmod>";
            $xml .= "<changefreq>{$url['changefreq']}</changefreq>";
            $xml .= "<priority>{$url['priority']}</priority>";
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
