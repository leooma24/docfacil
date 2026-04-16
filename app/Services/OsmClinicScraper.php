<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OsmClinicScraper
{
    private const OVERPASS_URL = 'https://overpass-api.de/api/interpreter';

    private const CITY_BBOXES = [
        'hermosillo' => [29.00, -111.15, 29.20, -110.85],
        'obregon'    => [27.40, -110.00, 27.55, -109.85],
        'ciudad obregon' => [27.40, -110.00, 27.55, -109.85],
        'culiacan'   => [24.70, -107.50, 24.90, -107.30],
        'mazatlan'   => [23.15, -106.50, 23.30, -106.35],
        'los mochis' => [25.75, -109.05, 25.85, -108.95],
        'guasave'    => [25.52, -108.53, 25.62, -108.40],
    ];

    public function clinicsForCity(string $city, int $limit = 100): array
    {
        $key = $this->normalize($city);
        $bbox = self::CITY_BBOXES[$key] ?? null;

        if (!$bbox) {
            throw new \InvalidArgumentException("Ciudad no soportada: {$city}. Disponibles: " . implode(', ', array_keys(self::CITY_BBOXES)));
        }

        [$south, $west, $north, $east] = $bbox;
        $bboxStr = "{$south},{$west},{$north},{$east}";

        $query = <<<QUERY
[out:json][timeout:60];
(
  node["amenity"="dentist"]({$bboxStr});
  way["amenity"="dentist"]({$bboxStr});
  node["amenity"="clinic"]({$bboxStr});
  way["amenity"="clinic"]({$bboxStr});
  node["amenity"="doctors"]({$bboxStr});
  way["amenity"="doctors"]({$bboxStr});
  node["healthcare"="dentist"]({$bboxStr});
  way["healthcare"="dentist"]({$bboxStr});
  node["healthcare"="doctor"]({$bboxStr});
  way["healthcare"="doctor"]({$bboxStr});
  node["healthcare"="clinic"]({$bboxStr});
  way["healthcare"="clinic"]({$bboxStr});
);
out center tags {$limit};
QUERY;

        try {
            $response = Http::asForm()->timeout(90)->post(self::OVERPASS_URL, [
                'data' => $query,
            ]);
        } catch (\Throwable $e) {
            Log::error('OsmClinicScraper: request failed', ['error' => $e->getMessage()]);
            return [];
        }

        if (!$response->successful()) {
            Log::error('OsmClinicScraper: bad response', ['status' => $response->status()]);
            return [];
        }

        $elements = $response->json('elements') ?? [];
        $clinics = [];

        foreach ($elements as $el) {
            $tags = $el['tags'] ?? [];
            $name = $tags['name'] ?? null;

            if (!$name) continue;

            $lat = $el['lat'] ?? ($el['center']['lat'] ?? null);
            $lon = $el['lon'] ?? ($el['center']['lon'] ?? null);

            $clinics[] = [
                'osm_id'      => ($el['type'] ?? 'node') . '/' . ($el['id'] ?? ''),
                'name'        => $name,
                'phone'       => $this->cleanPhone($tags['phone'] ?? $tags['contact:phone'] ?? null),
                'website'     => $tags['website'] ?? $tags['contact:website'] ?? $tags['url'] ?? null,
                'email'       => $tags['email'] ?? $tags['contact:email'] ?? null,
                'address'     => $this->buildAddress($tags),
                'specialty'   => $this->inferSpecialty($tags),
                'latitude'    => $lat,
                'longitude'   => $lon,
            ];
        }

        return $clinics;
    }

    private function normalize(string $city): string
    {
        $city = mb_strtolower(trim($city));
        $city = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $city);
        return $city;
    }

    private function cleanPhone(?string $phone): ?string
    {
        if (!$phone) return null;
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) < 10) return null;
        return substr($digits, -10);
    }

    private function buildAddress(array $tags): ?string
    {
        $parts = array_filter([
            $tags['addr:street'] ?? null,
            $tags['addr:housenumber'] ?? null,
            $tags['addr:neighbourhood'] ?? null,
            $tags['addr:city'] ?? null,
        ]);
        return $parts ? implode(' ', $parts) : null;
    }

    private function inferSpecialty(array $tags): string
    {
        $amenity = $tags['amenity'] ?? null;
        $healthcare = $tags['healthcare'] ?? null;
        $speciality = $tags['healthcare:speciality'] ?? null;

        if ($amenity === 'dentist' || $healthcare === 'dentist') return 'dental';
        if ($speciality) return $speciality;
        if ($amenity === 'clinic' || $healthcare === 'clinic') return 'clinica';
        if ($amenity === 'doctors' || $healthcare === 'doctor') return 'medico';

        return 'medico';
    }
}
