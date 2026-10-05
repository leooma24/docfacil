<?php

namespace Tests\Feature;

use App\Livewire\OdontogramEditor;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Odontogram;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El odontograma en el celular, una arcada a la vez.
 *
 * Antes se dibujaba a 720 px y en el celular había que deslizar de lado
 * ("Desliza para ver toda la boca"): cada diente quedaba de unos 20 px,
 * chico para el dedo. Ahora en pantallas angostas se escoge Superior o
 * Inferior y cada lado del paciente va en su renglón de 8 dientes. En la
 * computadora y al imprimir se ve igual que antes, las dos arcadas juntas.
 */
class OdontogramaEnElCelularTest extends TestCase
{
    use RefreshDatabase;

    private function arcadas(string $denticion = 'permanente'): string
    {
        return Blade::render('<x-odontograma.arcadas :dientes="[]" :denticion="$d" />', ['d' => $denticion]);
    }

    public function test_trae_los_botones_para_escoger_la_arcada(): void
    {
        $html = $this->arcadas();

        $this->assertStringContainsString('odo-escoge', $html);
        $this->assertMatchesRegularExpression('/<button[^>]*>\s*Superior\s*<\/button>/', $html);
        $this->assertMatchesRegularExpression('/<button[^>]*>\s*Inferior\s*<\/button>/', $html);
    }

    public function test_cada_fila_dice_de_que_arcada_es(): void
    {
        $html = $this->arcadas('mixta');

        // Superior: permanentes y temporales de arriba; inferior: los de abajo.
        $this->assertSame(2, substr_count($html, 'odo-fila odo-sup'));
        $this->assertSame(2, substr_count($html, 'odo-fila odo-inf'));
    }

    public function test_empieza_en_la_superior(): void
    {
        $this->assertStringContainsString('odo-ver-sup', $this->arcadas());
    }

    public function test_ya_no_obliga_a_deslizar_de_lado(): void
    {
        $html = $this->arcadas();

        $this->assertStringNotContainsString('Desliza para ver toda la boca', $html);
        $this->assertStringNotContainsString('min-width:720px', $html);
    }

    public function test_al_imprimir_se_ven_las_dos_arcadas(): void
    {
        // Lo de una arcada a la vez es solo para pantalla angosta, nunca al imprimir.
        $html = $this->arcadas();

        $this->assertMatchesRegularExpression('/@media screen and \(max-width:\s*760px\)/', $html);
    }

    public function test_el_diente_seleccionado_en_grande_tambien_se_toca_por_cara(): void
    {
        // En el celular las caras del diente chico miden ~9 px; en el diente
        // grande de abajo sí caben el dedo. Las dos llaman a lo mismo.
        $clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $clinica->id]);
        $doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $clinica->id, 'specialty' => 'Odontología']);
        $paciente = Patient::create(['clinic_id' => $clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz']);
        $odontograma = Odontogram::create(['clinic_id' => $clinica->id, 'patient_id' => $paciente->id, 'doctor_id' => $doctor->id, 'evaluation_date' => today()]);
        $this->actingAs($user);

        $html = Livewire::test(OdontogramEditor::class, ['odontogramId' => $odontograma->id])
            ->call('selectTooth', 36)
            ->html();

        $this->assertSame(2, substr_count($html, "applySurface(36, 'oclusal')"));
    }
}
