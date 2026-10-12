<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\PatientResource\Pages\EditPatient;
use App\Filament\Doctor\Resources\PaymentResource\Pages\ListPayments;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

/**
 * La lista para su contador (11-oct-2026). El CFDI va en una segunda fase
 * (decisión de Omar): mientras, el dentista hace lo que ya hace, «se lo digo
 * a mi contador». Aquí baja en Excel, por mes, quién pidió factura, cuánto,
 * cómo pagó y los datos fiscales del paciente, que se capturan en su ficha.
 */
class ListaParaSuContadorTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private User $doctor;

    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-20 10:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'onboarding_status' => 'completed']);
        $this->doctor = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        Doctor::create(['user_id' => $this->doctor->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);
    }

    private function cobro(Patient $p, string $fecha, bool $factura, float $monto = 1500, ?int $clinicaId = null): Payment
    {
        return Payment::withoutGlobalScopes()->create(['clinic_id' => $clinicaId ?? $this->clinica->id, 'patient_id' => $p->id,
            'amount' => $monto, 'amount_paid' => $monto, 'status' => 'paid', 'payment_method' => 'card',
            'payment_date' => $fecha, 'notes' => 'Resina en 36', 'factura_solicitada' => $factura]);
    }

    /** @return array<int, array<int, mixed>> */
    private function filas(string $mes): array
    {
        $respuesta = $this->get(route('cobros.lista-para-su-contador', ['mes' => $mes]));
        $respuesta->assertOk();
        $ruta = tempnam(sys_get_temp_dir(), 'lista') . '.xlsx';
        file_put_contents($ruta, $respuesta->streamedContent());

        $lector = new Reader();
        $lector->open($ruta);
        $filas = [];
        foreach ($lector->getSheetIterator() as $hoja) {
            foreach ($hoja->getRowIterator() as $fila) {
                $filas[] = $fila->toArray();
            }
            break;
        }
        $lector->close();
        unlink($ruta);

        return $filas;
    }

    // ── Los datos fiscales, en la ficha del paciente ────────────

    public function test_los_datos_fiscales_se_guardan_desde_la_ficha(): void
    {
        Livewire::test(EditPatient::class, ['record' => $this->ana->getRouteKey()])
            ->fillForm(['rfc' => 'ruaa900101ab1', 'razon_social' => 'Ana Ruiz Álvarez', 'regimen_fiscal' => '605',
                'cp_fiscal' => '81200', 'uso_cfdi' => 'D01'])
            ->call('save')
            ->assertHasNoFormErrors();

        $ana = $this->ana->fresh();
        $this->assertSame('RUAA900101AB1', $ana->rfc);
        $this->assertSame('Ana Ruiz Álvarez', $ana->razon_social);
        $this->assertSame('605', $ana->regimen_fiscal);
        $this->assertSame('81200', $ana->cp_fiscal);
        $this->assertSame('D01', $ana->uso_cfdi);
    }

    public function test_un_rfc_mal_escrito_no_se_guarda(): void
    {
        Livewire::test(EditPatient::class, ['record' => $this->ana->getRouteKey()])
            ->fillForm(['rfc' => 'ANA123'])
            ->call('save')
            ->assertHasFormErrors(['rfc']);
    }

    // ── La lista ────────────────────────────────────────────────

    public function test_trae_solo_los_cobros_del_mes_que_pidieron_factura_con_sus_datos_fiscales(): void
    {
        $this->ana->update(['rfc' => 'RUAA900101AB1', 'razon_social' => 'Ana Ruiz Álvarez', 'regimen_fiscal' => '605',
            'cp_fiscal' => '81200', 'uso_cfdi' => 'D01', 'email' => 'ana@correo.com']);
        $this->cobro($this->ana, '2026-10-05', true, 1500);
        $this->cobro($this->ana, '2026-10-06', false, 800);   // no pidió factura
        $this->cobro($this->ana, '2026-09-28', true, 2000);   // otro mes

        $filas = $this->filas('2026-10');

        $this->assertCount(2, $filas, 'Encabezado y un cobro');
        $this->assertContains('RFC', $filas[0]);
        $renglon = array_combine($filas[0], $filas[1]);
        $this->assertSame('Ana Ruiz', $renglon['Paciente']);
        $this->assertSame('RUAA900101AB1', $renglon['RFC']);
        $this->assertSame('Ana Ruiz Álvarez', $renglon['Razón social']);
        $this->assertStringContainsString('605', $renglon['Régimen fiscal']);
        $this->assertSame('81200', $renglon['Código postal']);
        $this->assertStringContainsString('D01', $renglon['Uso del CFDI']);
        $this->assertSame('Resina en 36', $renglon['Concepto']);
        $this->assertEquals(1500, $renglon['Pagado']);
        $this->assertSame('Tarjeta', $renglon['Forma de pago']);
        $this->assertSame('ana@correo.com', $renglon['Correo']);
    }

    public function test_avisa_cuando_al_paciente_le_faltan_sus_datos_fiscales(): void
    {
        $this->cobro($this->ana, '2026-10-05', true);

        $renglon = array_combine(...array_slice($this->filas('2026-10'), 0, 2));

        $this->assertSame('Faltan sus datos fiscales', $renglon['Nota']);
        // En un consultorio dental casi siempre es D01: honorarios médicos y dentales.
        $this->assertStringContainsString('D01', $renglon['Uso del CFDI']);
    }

    public function test_no_trae_nada_de_otro_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otro', 'plan' => 'basico', 'onboarding_status' => 'completed']);
        $ajeno = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Luis', 'last_name' => 'Mora']);
        $this->cobro($ajeno, '2026-10-05', true, 999, $otra->id);

        $this->assertCount(1, $this->filas('2026-10'), 'Solo el encabezado');
    }

    public function test_la_asistente_sin_permiso_de_dinero_no_la_baja(): void
    {
        $asistente = User::forceCreate(['name' => 'Asistente', 'email' => 'a@test.com', 'password' => bcrypt('x'), 'role' => 'staff',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);

        $this->actingAs($asistente)->get(route('cobros.lista-para-su-contador', ['mes' => '2026-10']))->assertForbidden();
    }

    public function test_un_mes_mal_escrito_no_truena(): void
    {
        $this->get(route('cobros.lista-para-su-contador', ['mes' => 'cualquier-cosa']))->assertNotFound();
    }

    public function test_el_boton_esta_en_cobros(): void
    {
        Livewire::test(ListPayments::class)->assertActionExists('listaParaSuContador');
    }
}
