<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\DoctorInvitationResource;
use App\Filament\Doctor\Resources\DoctorInvitationResource\Pages\CreateDoctorInvitation;
use App\Filament\Doctor\Resources\DoctorInvitationResource\Pages\ListDoctorInvitations;
use App\Filament\Doctor\Widgets\IncomeChart;
use App\Filament\Doctor\Widgets\StatsOverview;
use App\Mail\DoctorInvitationMail;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorInvitation;
use App\Models\User;
use App\Support\LoQueTraeCadaPlan;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La asistente con su propio usuario.
 *
 * En la entrevista del 10-oct-2026 (docs/LO-QUE-LE-DUELE-AL-DENTISTA) la que
 * más usaría el sistema es la asistente: agenda, cobra y manda recordatorios.
 * Pero el dentista no tenía cómo invitarla (solo el administrador creaba
 * usuarios de staff) y, ya adentro, veía todo, ingresos y gastos incluidos.
 *
 * Ahora el dentista la invita desde el Básico, y decide si ve el dinero del
 * consultorio. Lo del consultorio (equipo, configuración, add-ons) es solo
 * del doctor.
 */
class AsistenteDelConsultorioTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->doctor = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        Doctor::create(['user_id' => $this->doctor->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    private function asistente(bool $veDinero = false): User
    {
        return User::forceCreate(['name' => 'Lupita', 'email' => 'lupita@test.com', 'password' => bcrypt('x'), 'role' => 'staff',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id, 've_dinero' => $veDinero]);
    }

    // ── Invitarla ───────────────────────────────────────────────

    public function test_desde_el_basico_el_doctor_invita_a_su_asistente(): void
    {
        $this->actingAs($this->doctor);

        $this->assertTrue(DoctorInvitationResource::canAccess());

        Livewire::test(CreateDoctorInvitation::class)
            ->fillForm(['role' => 'staff', 'name' => 'Lupita', 'email' => 'lupita@test.com', 've_dinero' => false])
            ->call('create')
            ->assertHasNoFormErrors();

        $invitacion = DoctorInvitation::firstOrFail();
        $this->assertSame('staff', $invitacion->role);
        $this->assertFalse($invitacion->ve_dinero);
        Mail::assertSent(DoctorInvitationMail::class);
    }

    public function test_la_pantalla_de_equipo_abre_en_el_basico_aunque_ya_tenga_su_doctor(): void
    {
        // El tope de 1 doctor del Básico no aplica a la asistente: esta prueba
        // pasa por el servidor de verdad, no por Livewire, que se salta las reglas de plan.
        $this->actingAs($this->doctor);

        $this->get('/doctor/invitar-doctores')->assertOk();
        $this->get('/doctor/invitar-doctores/create')->assertOk();
    }

    public function test_en_el_pro_con_tres_doctores_ya_no_invita_otro_pero_si_a_su_asistente(): void
    {
        $this->clinica->update(['plan' => 'profesional']);
        foreach (['b', 'c'] as $l) {
            $u = User::forceCreate(['name' => "Dr. {$l}", 'email' => "{$l}@test.com", 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
            Doctor::create(['user_id' => $u->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Ortodoncia']);
        }
        $this->actingAs($this->doctor);

        $this->get('/doctor/invitar-doctores/create')->assertOk();

        Livewire::test(CreateDoctorInvitation::class)
            ->fillForm(['role' => 'doctor', 'name' => 'Dra. Cuarta', 'email' => 'cuarta@test.com'])
            ->call('create')
            ->assertHasFormErrors(['role']);

        Livewire::test(CreateDoctorInvitation::class)
            ->fillForm(['role' => 'staff', 'name' => 'Lupita', 'email' => 'lupita@test.com'])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_en_el_basico_no_puede_invitar_otro_doctor(): void
    {
        $this->actingAs($this->doctor);

        Livewire::test(CreateDoctorInvitation::class)
            ->fillForm(['role' => 'doctor', 'name' => 'Dra. Paola', 'email' => 'paola@test.com'])
            ->call('create')
            ->assertHasFormErrors(['role']);

        $this->assertSame(0, DoctorInvitation::count());
    }

    public function test_la_liga_se_manda_por_whatsapp(): void
    {
        $this->actingAs($this->doctor);
        $inv = DoctorInvitation::create(['clinic_id' => $this->clinica->id, 'invited_by' => $this->doctor->id, 'name' => 'Lupita', 'email' => 'lupita@test.com', 'role' => 'staff']);

        Livewire::test(ListDoctorInvitations::class)
            ->assertTableActionVisible('whatsapp', $inv)
            ->assertSee(urlencode(route('invitation.accept', $inv->token)), false);
    }

    public function test_al_aceptar_queda_como_asistente_y_no_como_doctor(): void
    {
        $inv = DoctorInvitation::create(['clinic_id' => $this->clinica->id, 'invited_by' => $this->doctor->id, 'name' => 'Lupita', 'email' => 'lupita@test.com', 'role' => 'staff', 've_dinero' => true]);

        $this->get(route('invitation.accept', $inv->token))->assertOk()->assertSee('asistente');
        $this->post(route('invitation.store', $inv->token), ['password' => 'secreta123', 'password_confirmation' => 'secreta123'])->assertRedirect('/doctor');

        $lupita = User::where('email', 'lupita@test.com')->firstOrFail();
        $this->assertSame('staff', $lupita->role);
        $this->assertTrue($lupita->ve_dinero);
        $this->assertSame($this->clinica->id, $lupita->clinic_id);
        $this->assertSame(1, Doctor::count(), 'La asistente no es doctor: no sale en la agenda ni en las recetas.');
    }

    // ── Lo que ve ───────────────────────────────────────────────

    public function test_sin_permiso_no_ve_el_dinero_ni_lo_del_consultorio(): void
    {
        $this->actingAs($this->asistente(false));

        $this->get('/doctor')->assertOk();
        Livewire::test(StatsOverview::class)->assertDontSee('Ingresos del mes')->assertSee('Citas hoy');
        $this->assertFalse(IncomeChart::canView());
        $this->get('/doctor/cobros')->assertOk();
        $this->get('/doctor/citas')->assertOk();

        $this->get('/doctor/corte')->assertForbidden();
        $this->get('/doctor/gastos')->assertForbidden();
        $this->get('/doctor/invitar-doctores')->assertForbidden();
        $this->get('/doctor/clinic-settings')->assertForbidden();
    }

    public function test_con_permiso_ve_el_corte_y_los_gastos(): void
    {
        $this->actingAs($this->asistente(true));

        $this->get('/doctor/corte')->assertOk();
        $this->get('/doctor/gastos')->assertOk();
        $this->get('/doctor/invitar-doctores')->assertForbidden();
    }

    public function test_el_doctor_sigue_viendo_todo(): void
    {
        $this->actingAs($this->doctor);

        Livewire::test(StatsOverview::class)->assertSee('Ingresos del mes');
        $this->get('/doctor/corte')->assertOk();
        $this->get('/doctor/clinic-settings')->assertOk();
    }

    public function test_el_plan_lo_dice(): void
    {
        $this->assertTrue($this->clinica->hasFeature('asistente'));
        $basico = collect(LoQueTraeCadaPlan::planes())->firstWhere('name', 'Básico');
        $this->assertStringContainsString('asistente', mb_strtolower(implode(' ', $basico['features'])));
    }
}
