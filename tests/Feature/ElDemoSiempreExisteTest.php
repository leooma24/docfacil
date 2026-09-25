<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * La liga que se le manda a los prospectos tiene que llevar a algún lado.
 *
 * El 22 de septiembre se le puso al DemoSeeder un candado para que no sembrara
 * datos de prueba en producción. Suena correcto, salvo por un detalle: el
 * comando `app:demo-reset` corre todos los días a las 4 de la mañana, borra el
 * consultorio de ejemplo y lo vuelve a sembrar. Con el candado, la primera
 * mitad corrió y la segunda no.
 *
 * Resultado: durante dos días /demo entregó un correo y una contraseña de un
 * usuario que ya no existía, y esa liga se le estaba mandando por WhatsApp a
 * los dentistas que pedían verlo. Nadie se entera de algo así hasta que un
 * prospecto lo dice —o hasta que no lo dice y solo se va.
 *
 * Estas pruebas corren con el entorno puesto en producción a propósito.
 */
class ElDemoSiempreExisteTest extends TestCase
{
    use RefreshDatabase;

    private function sembrarComoEnProduccion(): void
    {
        app()['env'] = 'production';

        (new DemoSeeder())->run();
    }

    public function test_el_usuario_del_demo_se_siembra_aunque_sea_produccion(): void
    {
        $this->sembrarComoEnProduccion();

        $demo = User::where('email', 'demo@docfacil.com')->first();

        $this->assertNotNull($demo, 'El demo no existe: la liga de /demo no lleva a ningún lado.');
        $this->assertSame('doctor', $demo->role);
    }

    public function test_la_contrasena_es_la_que_entrega_la_pagina_del_demo(): void
    {
        $this->sembrarComoEnProduccion();

        // Las mismas que muestra la ruta /demo en routes/web.php.
        $this->assertTrue(Hash::check('demo2026', User::where('email', 'demo@docfacil.com')->first()->password));
    }

    public function test_su_consultorio_queda_marcado_como_demo(): void
    {
        $this->sembrarComoEnProduccion();

        $clinica = Clinic::withoutGlobalScopes()->where('slug', 'clinica-dental-sonrisas-cdmx')->first();

        $this->assertNotNull($clinica);
        // Marcado, para que no se cuente como cliente ni ocupe un lugar de fundador.
        $this->assertTrue((bool) $clinica->is_demo);
    }

    public function test_el_demo_tiene_citas_para_manana(): void
    {
        // La pantalla que se enseña en la demo es la de los recordatorios del
        // día siguiente. Con el seeder saltándose los fines de semana, un
        // viernes esa pantalla amanecía vacía: la demo se caía justo en lo
        // único que el dentista pidió ver.
        $this->travelTo(now()->next('friday')->setTime(10, 0));

        $this->sembrarComoEnProduccion();

        $clinicaId = Clinic::withoutGlobalScopes()->where('slug', 'clinica-dental-sonrisas-cdmx')->value('id');

        $manana = \App\Models\Appointment::withoutGlobalScopes()
            ->where('clinic_id', $clinicaId)
            ->whereDate('starts_at', today()->addDay())
            ->count();

        $this->assertGreaterThan(0, $manana, 'Un viernes, la pantalla de recordatorios de mañana sale vacía.');
    }

    public function test_el_demo_tiene_con_que_enseñarse(): void
    {
        $this->sembrarComoEnProduccion();

        $clinicaId = Clinic::withoutGlobalScopes()->where('slug', 'clinica-dental-sonrisas-cdmx')->value('id');

        $this->assertDatabaseCount('patients', \App\Models\Patient::withoutGlobalScopes()->where('clinic_id', $clinicaId)->count());
        $this->assertGreaterThan(0, \App\Models\Patient::withoutGlobalScopes()->where('clinic_id', $clinicaId)->count(), 'Un demo sin pacientes no enseña nada.');
        $this->assertGreaterThan(0, \App\Models\Appointment::withoutGlobalScopes()->where('clinic_id', $clinicaId)->count(), 'Un demo sin citas no enseña la agenda, que es lo que se vende.');
    }
}
