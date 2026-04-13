<?php

namespace App\Http\Controllers;

class CityLandingController extends Controller
{
    private array $cities = [
        'culiacan' => ['name' => 'Culiacán', 'state' => 'Sinaloa'],
        'cdmx' => ['name' => 'Ciudad de México', 'state' => 'CDMX'],
        'guadalajara' => ['name' => 'Guadalajara', 'state' => 'Jalisco'],
        'monterrey' => ['name' => 'Monterrey', 'state' => 'Nuevo León'],
        'puebla' => ['name' => 'Puebla', 'state' => 'Puebla'],
        'tijuana' => ['name' => 'Tijuana', 'state' => 'Baja California'],
        'leon' => ['name' => 'León', 'state' => 'Guanajuato'],
        'merida' => ['name' => 'Mérida', 'state' => 'Yucatán'],
        'cancun' => ['name' => 'Cancún', 'state' => 'Quintana Roo'],
        'queretaro' => ['name' => 'Querétaro', 'state' => 'Querétaro'],
        'aguascalientes' => ['name' => 'Aguascalientes', 'state' => 'Aguascalientes'],
        'chihuahua' => ['name' => 'Chihuahua', 'state' => 'Chihuahua'],
        'morelia' => ['name' => 'Morelia', 'state' => 'Michoacán'],
        'toluca' => ['name' => 'Toluca', 'state' => 'Estado de México'],
        'hermosillo' => ['name' => 'Hermosillo', 'state' => 'Sonora'],
        'saltillo' => ['name' => 'Saltillo', 'state' => 'Coahuila'],
        'mazatlan' => ['name' => 'Mazatlán', 'state' => 'Sinaloa'],
        'los-mochis' => ['name' => 'Los Mochis', 'state' => 'Sinaloa'],
    ];

    public function show(string $city)
    {
        if (!isset($this->cities[$city])) {
            abort(404);
        }

        $data = $this->cities[$city];

        return view('city-landing', [
            'city' => $data['name'],
            'state' => $data['state'],
            'slug' => $city,
        ]);
    }
}
