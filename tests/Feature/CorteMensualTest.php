<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Corte;
use App\Mail\CorteMensualMail;
use App\Models\Clinic;
use App\Models\Expense;
use App\Models\LifecycleEmail;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use App\Services\NumerosDelCorte;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El corte del mes, por correo el día 1.
 *
 * Casi nadie le dice a un dentista cuánto le quedó. El correo se lo dice sin
 * que tenga que entrar — pero solo si hay algo que decir, una vez por mes, y
 * con los mismos números que ve en la pantalla.
 */
class CorteMensualTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private User $doctor;

    private Patient $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        // Primero de septiembre en la mañana: el comando manda agosto.
        $this->travelTo(CarbonImmutable::parse('2026-09-01 08:00'));

        Mail::fake();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-test',
            'plan' => 'basico',
            'plan_ends_at' => now()->addMonth(),
            'onboarding_status' => 'completed',
        ]);

        $this->doctor = User::forceCreate([
            'name' => 'Dr. Roberto García',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);

        $this->paciente = Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Fernando',
            'last_name' => 'Morales',
            'phone' => '5512345678',
        ]);
    }

    private function cobro(float $monto, string $fecha, string $estado = 'paid', float $abonado = 0): void
    {
        Payment::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'amount' => $monto,
            'amount_paid' => $abonado,
            'status' => $estado,
            'payment_date' => $fecha,
        ]);
    }

    private function gasto(float $monto, string $fecha, string $categoria = 'renta'): void
    {
        Expense::create([
            'clinic_id' => $this->clinica->id,
            'created_by' => $this->doctor->id,
            'category' => $categoria,
            'concept' => 'Gasto de prueba',
            'amount' => $monto,
            'expense_date' => $fecha,
        ]);
    }

    private function correr(array $opciones = []): void
    {
        $this->artisan('docfacil:corte-mensual', $opciones)->assertSuccessful();
    }

    // ── Qué le llega ─────────────────────────────────────────────

    public function test_le_manda_al_doctor_el_corte_del_mes_pasado(): void
    {
        $this->cobro(20000, '2026-08-10');
        $this->gasto(8000, '2026-08-05');
        // De septiembre: todavía no es de este corte.
        $this->cobro(99999, '2026-09-01');

        $this->correr();

        Mail::assertSent(CorteMensualMail::class, fn (CorteMensualMail $correo) => $correo->hasTo('doctor@test.com')
            && $correo->mes->format('Y-m') === '2026-08'
            && $correo->numeros['ingresos'] === 20000.0
            && $correo->numeros['gastos'] === 8000.0
            && $correo->numeros['utilidad'] === 12000.0);
    }

    public function test_el_asunto_ya_dice_cuanto_le_quedo(): void
    {
        // Mucha gente no abre el correo. Aun así se queda con el número.
        $this->cobro(20000, '2026-08-10');
        $this->gasto(8000, '2026-08-05');

        $this->correr();

        Mail::assertSent(CorteMensualMail::class, fn (CorteMensualMail $correo) => $correo->asunto() === 'Tu corte de agosto: te quedaron $12,000');
    }

    public function test_sin_gastos_anotados_no_dice_que_le_quedo_todo(): void
    {
        // "Te quedaron $20,000" sería mentira: nada más no anotó sus gastos.
        $this->cobro(20000, '2026-08-10');

        $this->correr();

        Mail::assertSent(CorteMensualMail::class, fn (CorteMensualMail $correo) => $correo->asunto() === 'Tu corte de agosto: entraron $20,000');
    }

    public function test_si_salio_mas_de_lo_que_entro_lo_dice_sin_rodeos(): void
    {
        $this->cobro(5000, '2026-08-10');
        $this->gasto(12000, '2026-08-05', 'equipo');

        $this->correr();

        Mail::assertSent(CorteMensualMail::class, fn (CorteMensualMail $correo) => $correo->asunto() === 'Tu corte de agosto: salió más de lo que entró');
    }

    public function test_el_correo_dice_lo_mismo_que_la_pantalla_del_corte(): void
    {
        // Si cada uno hiciera su cuenta, tarde o temprano dirían cifras distintas.
        $this->cobro(20000, '2026-08-10');
        $this->cobro(6000, '2026-08-20', 'partial', abonado: 2500);
        $this->gasto(8000, '2026-08-05');
        $this->gasto(3100, '2026-08-15', 'laboratorio');

        $this->correr();

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);

        $pantalla = Livewire::withQueryParams(['desde' => '2026-08-01', 'hasta' => '2026-08-31'])
            ->test(Corte::class)
            ->instance()
            ->getNumeros();

        Mail::assertSent(CorteMensualMail::class, function (CorteMensualMail $correo) use ($pantalla) {
            foreach (['ingresos', 'gastos', 'utilidad', 'por_cobrar', 'categorias'] as $clave) {
                if ($correo->numeros[$clave] !== $pantalla[$clave]) {
                    return false;
                }
            }

            return true;
        });
    }

    public function test_el_correo_se_arma_con_todo_lo_que_tiene_que_decir(): void
    {
        $this->cobro(20000, '2026-08-10');
        $this->cobro(3000, '2026-08-11', 'pending');
        $this->gasto(8000, '2026-08-05', 'renta');
        $this->gasto(3100, '2026-08-05', 'laboratorio');
        $this->gasto(1200, '2026-08-05', 'materiales');
        $this->gasto(500, '2026-08-05', 'publicidad');

        $mes = CarbonImmutable::parse('2026-08-01');
        $numeros = NumerosDelCorte::calcular(
            $this->clinica->id, $mes, $mes->endOfMonth(),
            $mes->subMonth()->startOfMonth(), $mes->subMonth()->endOfMonth(),
        );

        $html = (new CorteMensualMail($this->clinica, $this->doctor, $mes, $numeros))->render();

        $this->assertStringContainsString('Tu corte de agosto', $html);
        $this->assertStringContainsString('$7,200', $html);
        $this->assertStringContainsString('Te quedaron a deber', $html);
        $this->assertStringContainsString('Y 1 categoría más', $html);
        // La liga abre el corte en agosto, no en el mes que va corriendo.
        $this->assertStringContainsString('desde=2026-08-01', $html);
        $this->assertStringContainsString('corte/sin-correo', $html);
    }

    // ── Cuándo NO le llega ───────────────────────────────────────

    public function test_sin_movimientos_en_el_mes_no_manda_nada(): void
    {
        // "Entró $0, salió $0" no le sirve a nadie y enseña a ignorar los que siguen.
        $this->correr();

        Mail::assertNothingSent();
    }

    public function test_correrlo_dos_veces_no_le_manda_dos(): void
    {
        $this->cobro(20000, '2026-08-10');

        $this->correr();
        $this->correr();

        Mail::assertSentCount(1);
        $this->assertSame(1, LifecycleEmail::where('type', 'corte_mensual_2026-08')->count());
    }

    public function test_sin_gastos_y_corte_en_su_plan_no_le_llega(): void
    {
        $this->clinica->update([
            'plan' => 'free',
            'plan_ends_at' => null,
            'trial_ends_at' => now()->subDay(),
        ]);
        $this->cobro(20000, '2026-08-10');

        $this->correr();

        Mail::assertNothingSent();
    }

    public function test_si_lo_apago_ya_no_le_llega(): void
    {
        $this->clinica->update(['corte_por_correo' => false]);
        $this->cobro(20000, '2026-08-10');

        $this->correr();

        Mail::assertNothingSent();
    }

    public function test_el_demo_publico_no_recibe_correo(): void
    {
        // Su correo no es un buzón real: rebotaría cada mes.
        $this->clinica->update(['slug' => 'clinica-dental-sonrisas-cdmx']);
        $this->cobro(20000, '2026-08-10');

        $this->correr();

        Mail::assertNothingSent();
    }

    // ── Apagarlo ─────────────────────────────────────────────────

    public function test_la_liga_del_correo_lo_apaga(): void
    {
        $this->get(URL::signedRoute('corte.sin-correo', ['clinic' => $this->clinica->id]))
            ->assertOk()
            ->assertSee('Ya no te mandaremos el corte del mes por correo');

        $this->assertFalse($this->clinica->fresh()->corte_por_correo);
    }

    public function test_sin_firma_nadie_le_apaga_el_correo_a_otro_consultorio(): void
    {
        $this->get('/corte/sin-correo/' . $this->clinica->id)->assertForbidden();

        $this->assertTrue($this->clinica->fresh()->corte_por_correo);
    }

    // ── Para verlo antes ─────────────────────────────────────────

    public function test_la_vista_previa_va_al_correo_que_se_pida_y_no_cuenta_como_enviado(): void
    {
        $this->cobro(20000, '2026-08-10');

        $this->correr(['--a' => 'omar@test.com']);

        Mail::assertSent(CorteMensualMail::class, fn (CorteMensualMail $correo) => $correo->hasTo('omar@test.com') && ! $correo->hasTo('doctor@test.com'));
        $this->assertSame(0, LifecycleEmail::count());
    }

    public function test_un_mes_mal_escrito_no_manda_nada(): void
    {
        $this->artisan('docfacil:corte-mensual', ['--mes' => 'agosto'])->assertFailed();

        Mail::assertNothingSent();
    }

    // ── La liga que abre el corte ────────────────────────────────

    public function test_la_liga_abre_el_corte_en_el_mes_del_correo(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);

        Livewire::withQueryParams(['desde' => '2026-08-01', 'hasta' => '2026-08-31'])
            ->test(Corte::class)
            ->assertSet('data.periodo', 'personalizado')
            // El selector de fecha le pega la hora: lo que importa es el día.
            ->assertSet('data.desde', fn ($fecha) => str_starts_with((string) $fecha, '2026-08-01'))
            ->assertSet('data.hasta', fn ($fecha) => str_starts_with((string) $fecha, '2026-08-31'));
    }

    public function test_una_fecha_que_no_existe_abre_el_mes_en_curso(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);

        Livewire::withQueryParams(['desde' => '2026-02-31', 'hasta' => 'mañana'])
            ->test(Corte::class)
            ->assertSet('data.periodo', 'este_mes');
    }

    // ── Volverlo a prender ───────────────────────────────────────

    public function test_el_doctor_lo_vuelve_a_prender_en_su_configuracion(): void
    {
        $this->clinica->update(['corte_por_correo' => false]);

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);

        Livewire::test(\App\Filament\Doctor\Pages\ClinicSettings::class)
            ->assertSet('data.corte_por_correo', false)
            ->set('data.corte_por_correo', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue($this->clinica->fresh()->corte_por_correo);
    }

    public function test_guardar_la_configuracion_sin_el_corte_en_su_plan_no_le_prende_el_correo(): void
    {
        // Lo apagó desde la liga y después se le venció el plan: guardar su
        // horario no debe volver a prenderle un correo que ya no quiso.
        $this->clinica->update([
            'corte_por_correo' => false,
            'plan' => 'free',
            'plan_ends_at' => null,
            'trial_ends_at' => now()->subDay(),
        ]);

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);

        Livewire::test(\App\Filament\Doctor\Pages\ClinicSettings::class)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse($this->clinica->fresh()->corte_por_correo);
    }
}
