<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prospect;
use App\Models\User;
use App\Support\CargaDeTrabajo;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Lo que pasa después de que un doctor se registra.
 *
 * Al 7-oct-2026 el embudo de ventas terminaba en "Cerraron": no había forma
 * de saber si el doctor entró, si dejó su consultorio listo o si lo usa. La
 * Dra. Karla quedó con su cuenta hecha y nunca entró, y nadie se enteró hasta
 * revisarlo a mano. Esto lo deja a la vista de quien vende y del tablero de
 * Omar (docfacil:resumen-json).
 */
class ActivacionTest extends TestCase
{
    use RefreshDatabase;

    private User $omar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(10, 0));
        $this->omar = User::forceCreate(['name' => 'Omar', 'email' => 'ventas@test.com', 'password' => bcrypt('x'), 'role' => 'sales', 'email_verified_at' => now()]);
    }

    private function consultorio(string $nombre, ?int $vendedor = null, string $onboarding = 'pending'): array
    {
        $clinica = Clinic::create(['name' => $nombre, 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => $onboarding]);
        if ($vendedor) {
            $clinica->forceFill(['sold_by_user_id' => $vendedor, 'sold_at' => now()->subDays(5)])->save();
        }
        $doctor = User::forceCreate(['name' => "Dr. {$nombre}", 'email' => str()->slug($nombre) . '@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $clinica->id]);

        return [$clinica, $doctor];
    }

    private function cita(Clinic $clinica, User $usuario): Appointment
    {
        $doctor = Doctor::create(['user_id' => $usuario->id, 'clinic_id' => $clinica->id, 'specialty' => 'Odontología']);
        $paciente = Patient::create(['clinic_id' => $clinica->id, 'first_name' => 'Camila', 'last_name' => 'Ortega', 'phone' => '6681234567']);

        return Appointment::create(['clinic_id' => $clinica->id, 'doctor_id' => $doctor->id, 'patient_id' => $paciente->id,
            'starts_at' => now()->addDay()->setTime(9, 0), 'ends_at' => now()->addDay()->setTime(9, 30), 'status' => 'scheduled']);
    }

    // ── Las señales ─────────────────────────────────────────────

    public function test_entrar_deja_anotado_cuando(): void
    {
        [, $doctor] = $this->consultorio('Sonrisas');

        Auth::guard('web')->login($doctor);

        $this->assertNotNull($doctor->fresh()->last_login_at);
        $this->assertTrue($doctor->fresh()->last_login_at->isSameMinute(now()));
    }

    public function test_el_recordatorio_deja_su_fecha(): void
    {
        [$clinica, $doctor] = $this->consultorio('Sonrisas', null, 'completed');
        $cita = $this->cita($clinica, $doctor);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));

        $this->actingAs($doctor)->get(route('cita.recordar', $cita))->assertRedirect();

        $this->assertTrue($cita->fresh()->reminder_sent_at->isSameMinute(now()));
    }

    // ── El embudo de después de cerrar ──────────────────────────

    public function test_cuenta_registrados_los_que_entraron_y_los_que_lo_usan(): void
    {
        [$nunca] = $this->consultorio('Nunca Entró', $this->omar->id);

        [, $entro] = $this->consultorio('Solo Entró', $this->omar->id);
        $entro->forceFill(['last_login_at' => now()->subDays(2)])->save();

        [$usa, $doctorUsa] = $this->consultorio('Lo Usa', $this->omar->id, 'completed');
        $doctorUsa->forceFill(['last_login_at' => now()->subDay()])->save();
        $this->cita($usa, $doctorUsa);

        // Se registró sin la liga del vendedor, pero el prospecto era suyo.
        [$porProspecto, $doctorP] = $this->consultorio('Por Prospecto', null, 'completed');
        $doctorP->forceFill(['last_login_at' => now()->subDays(3)])->save();
        Prospect::create(['name' => 'Dr. Por Prospecto', 'phone' => '6680000001', 'source' => 'prospecting', 'status' => 'converted',
            'assigned_to_sales_rep_id' => $this->omar->id, 'converted_clinic_id' => $porProspecto->id]);

        $otro = User::forceCreate(['name' => 'Otro', 'email' => 'otro@test.com', 'password' => bcrypt('x'), 'role' => 'sales', 'email_verified_at' => now()]);
        $this->consultorio('De Otro', $otro->id);

        $a = CargaDeTrabajo::activacion($this->omar->id);

        $this->assertSame(4, $a['registrados']);
        $this->assertSame(3, $a['entraron']);
        $this->assertSame(2, $a['configurados']);
        $this->assertSame(1, $a['usanEstaSemana']);
        $this->assertSame(['Nunca Entró'], $a['sinEntrar']->pluck('name')->all());
        $this->assertSame($nunca->id, $a['sinEntrar']->first()->id);
    }

    public function test_el_que_no_ha_entrado_es_tarea_de_hoy(): void
    {
        $this->consultorio('Nunca Entró', $this->omar->id);

        $tareas = collect(CargaDeTrabajo::tareasDeHoy($this->omar->id))->pluck('que')->implode(' | ');

        $this->assertStringContainsString('se registró y no ha entrado', $tareas);
    }

    // ── El resumen para el tablero de Omar ─────────────────────

    public function test_el_resumen_sale_en_json_y_no_cambia_nada(): void
    {
        $this->consultorio('Nunca Entró', $this->omar->id);
        $p = Prospect::create(['name' => 'Dra. Abigail', 'phone' => '6680000002', 'source' => 'prospecting', 'status' => 'contacted',
            'contact_day' => 1, 'has_whatsapp' => true, 'last_contact_method' => 'whatsapp', 'replied_at' => now()->subDay(),
            'assigned_to_sales_rep_id' => $this->omar->id]);
        $antes = $p->fresh()->updated_at;
        $this->travel(5)->minutes();

        Artisan::call('docfacil:resumen-json', ['--rep' => $this->omar->id]);
        $datos = json_decode(Artisan::output(), true);

        $this->assertIsArray($datos, 'Debe ser JSON válido.');
        foreach (['generado', 'numeros', 'embudo', 'activacion', 'contestaron', 'seguimientos', 'mensajes', 'fundadores'] as $llave) {
            $this->assertArrayHasKey($llave, $datos);
        }
        $this->assertSame(1, $datos['activacion']['registrados']);
        $this->assertSame(['Nunca Entró'], array_column($datos['activacion']['sin_entrar'], 'nombre'));
        $this->assertSame('Dra. Abigail', $datos['contestaron'][0]['nombre']);
        $this->assertEquals($antes, $p->fresh()->updated_at);
    }
}
