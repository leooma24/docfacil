<?php

namespace Tests\Feature;

use App\Http\Middleware\UsarHoraDelConsultorio;
use App\Models\Clinic;
use App\Models\User;
use App\Support\ZonaHoraria;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Cada consultorio a su hora.
 *
 * La app corre en hora del centro. En Los Mochis son una hora menos: a las 9
 * de la mañana de allá, la agenda pública ya creía que eran las 10 y
 * escondía el hueco de las 9:30.
 */
class ZonaHorariaPorConsultorioTest extends TestCase
{
    use RefreshDatabase;

    private function consultorio(array $atributos = []): Clinic
    {
        return Clinic::create(array_merge([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-' . uniqid(),
            'plan' => 'profesional',
            'plan_ends_at' => now()->addYear(),
            'onboarding_status' => 'completed',
        ], $atributos));
    }

    // ── Qué zona le toca ─────────────────────────────────────────

    public function test_los_mochis_va_con_la_hora_del_pacifico(): void
    {
        $this->assertSame('America/Mazatlan', ZonaHoraria::sugerida(null, 'Los Mochis'));
    }

    public function test_reconoce_la_ciudad_con_acentos_mayusculas_y_estado_pegado(): void
    {
        $this->assertSame('America/Cancun', ZonaHoraria::sugerida(null, 'CANCÚN, Q. Roo'));
    }

    public function test_si_la_ciudad_es_ambigua_manda_el_estado(): void
    {
        // Nogales hay en Sonora y en Veracruz.
        $this->assertSame('America/Hermosillo', ZonaHoraria::sugerida('Sonora', 'Nogales'));
        $this->assertNull(ZonaHoraria::sugerida('Veracruz', 'Nogales'));
    }

    public function test_baja_california_y_baja_california_sur_no_se_confunden(): void
    {
        $this->assertSame('America/Tijuana', ZonaHoraria::sugerida('Baja California', null));
        $this->assertSame('America/Mazatlan', ZonaHoraria::sugerida('Baja California Sur', null));
    }

    public function test_alvaro_obregon_no_es_ciudad_obregon(): void
    {
        $this->assertNull(ZonaHoraria::sugerida('CDMX', 'Álvaro Obregón'));
        $this->assertSame('America/Hermosillo', ZonaHoraria::sugerida(null, 'Cd. Obregón'));
    }

    public function test_bahia_de_banderas_va_con_el_centro_aunque_sea_nayarit(): void
    {
        $this->assertNull(ZonaHoraria::sugerida('Nayarit', 'Bahía de Banderas'));
    }

    public function test_el_centro_no_necesita_zona(): void
    {
        $consultorio = $this->consultorio(['city' => 'Guadalajara', 'state' => 'Jalisco']);

        $this->assertSame('America/Mexico_City', ZonaHoraria::delConsultorio($consultorio));
    }

    public function test_la_zona_que_elige_el_doctor_gana(): void
    {
        $consultorio = $this->consultorio(['city' => 'Los Mochis', 'timezone' => 'America/Mexico_City']);

        $this->assertSame('America/Mexico_City', ZonaHoraria::delConsultorio($consultorio->fresh()));
    }

    public function test_una_zona_que_no_es_de_mexico_no_se_usa(): void
    {
        $consultorio = $this->consultorio(['timezone' => 'Europe/Madrid']);

        $this->assertSame('America/Mexico_City', ZonaHoraria::delConsultorio($consultorio));
    }

    // ── Dónde se nota ────────────────────────────────────────────

    public function test_la_agenda_publica_de_los_mochis_ofrece_horarios_a_su_hora(): void
    {
        // 16:00 UTC: en el centro son las 10:00 y en Los Mochis las 09:00.
        $this->travelTo(CarbonImmutable::parse('2026-09-15 16:00:00', 'UTC'));

        $consultorio = $this->consultorio(['city' => 'Los Mochis']);

        $dias = $this->getJson("/clinica/{$consultorio->slug}/horarios-libres")->assertOk()->json('dias');

        // Con una hora de anticipación, allá el primer hueco es a las 10:00.
        // Con la hora del centro habría salido hasta las 11:00.
        $this->assertSame('2026-09-15', $dias[0]['fecha']);
        $this->assertSame('10:00', $dias[0]['horas'][0]);
    }

    public function test_la_misma_hora_en_un_consultorio_del_centro(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-15 16:00:00', 'UTC'));

        $consultorio = $this->consultorio(['city' => 'Guadalajara']);

        $dias = $this->getJson("/clinica/{$consultorio->slug}/horarios-libres")->assertOk()->json('dias');

        $this->assertSame('11:00', $dias[0]['horas'][0]);
    }

    public function test_el_panel_del_doctor_usa_la_hora_de_su_consultorio(): void
    {
        $consultorio = $this->consultorio(['city' => 'Cancún']);

        $doctor = User::forceCreate([
            'name' => 'Dra. Ana',
            'email' => 'ana@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $consultorio->id,
        ]);

        $peticion = Request::create('/doctor', 'GET');
        $peticion->setUserResolver(fn () => $doctor);

        (new UsarHoraDelConsultorio())->handle($peticion, fn () => response('ok'));

        $this->assertSame('America/Cancun', date_default_timezone_get());
        $this->assertSame('America/Cancun', config('app.timezone'));
    }

    public function test_los_recordatorios_corren_consultorio_por_consultorio(): void
    {
        $this->consultorio(['city' => 'Los Mochis']);
        $this->consultorio(['city' => 'Mérida']);

        $this->artisan('docfacil:send-reminders')->assertSuccessful();

        // Y al terminar regresa a la hora de antes.
        $this->assertSame('America/Mexico_City', date_default_timezone_get());
    }
}
