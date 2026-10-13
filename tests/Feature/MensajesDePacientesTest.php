<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MensajeDePaciente;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Lo que el paciente le escribe al número de DocFácil (12-oct-2026).
 *
 * Los recordatorios automáticos salen de un solo número, el de DocFácil. Si
 * el paciente contesta con texto («¿puede ser a las 5?») en lugar de tocar un
 * botón, el mensaje se perdía: con la IA apagada nadie lo veía. Ahora le llega
 * al consultorio en la campanita, con un botón para contestarle desde su
 * propio WhatsApp, y al paciente se le dice una vez que ya se lo pasamos.
 */
class MensajesDePacientesTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $doctor;
    private User $asistente;
    private Doctor $perfil;
    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 14:00'));
        config(['services.whatsapp.token' => 'tok', 'services.whatsapp.phone_number_id' => '123',
            'services.whatsapp.app_secret' => 'secreto']);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.r']]])]);
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'onboarding_status' => 'completed']);
        $this->doctor = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->asistente = User::forceCreate(['name' => 'Laura', 'email' => 'a@test.com', 'password' => bcrypt('x'), 'role' => 'staff',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->perfil = Doctor::create(['user_id' => $this->doctor->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '668 123 4567']);
    }

    private function recordada(Patient $p, ?Clinic $clinica = null, string $cuando = '2026-10-13 18:00'): Appointment
    {
        $clinica ??= $this->clinica;

        return Appointment::withoutGlobalScopes()->create(['clinic_id' => $clinica->id, 'doctor_id' => $this->perfil->id, 'patient_id' => $p->id,
            'starts_at' => '2026-10-15 11:00', 'ends_at' => '2026-10-15 11:30', 'status' => 'scheduled',
            'reminder_24h_sent_at' => $cuando, 'reminder_sent' => true]);
    }

    private function escribe(string $texto, string $desde = '5216681234567')
    {
        $cuerpo = json_encode(['entry' => [['changes' => [['value' => ['messages' => [[
            'from' => $desde, 'id' => 'wamid.' . md5($texto . microtime()), 'type' => 'text', 'text' => ['body' => $texto],
        ]]]]]]]]);

        return $this->call('POST', '/webhooks/whatsapp', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => 'sha256=' . hash_hmac('sha256', $cuerpo, 'secreto'),
        ], $cuerpo);
    }

    /** @return array<int, string> lo que DocFácil le contestó por WhatsApp */
    private function respuestas(): array
    {
        return collect(Http::recorded())->filter(fn ($par) => str_contains($par[0]->url(), '/messages'))
            ->map(fn ($par) => $par[0]->data()['text']['body'] ?? '')->values()->all();
    }

    public function test_el_texto_del_paciente_le_llega_al_consultorio(): void
    {
        $cita = $this->recordada($this->ana);

        $this->escribe('¿Puede ser a las 5 en lugar de las 11?')->assertOk();

        foreach ([$this->doctor, $this->asistente] as $usuario) {
            $aviso = $usuario->fresh()->notifications()->sole();
            $this->assertSame('Ana Ruiz le escribió por WhatsApp', $aviso->data['title']);
            $this->assertStringContainsString('¿Puede ser a las 5 en lugar de las 11?', $aviso->data['body']);
            $ligas = collect($aviso->data['actions'])->pluck('url')->all();
            $this->assertContains('https://wa.me/526681234567', $ligas, 'Contestarle desde el WhatsApp del consultorio');
        }

        $mensaje = MensajeDePaciente::sole();
        $this->assertSame($this->clinica->id, $mensaje->clinic_id);
        $this->assertSame($this->ana->id, $mensaje->patient_id);
        $this->assertSame($cita->id, $mensaje->appointment_id);
    }

    public function test_al_paciente_se_le_dice_una_sola_vez_que_ya_se_paso(): void
    {
        $this->recordada($this->ana);

        $this->escribe('¿Puede ser a las 5?');
        $this->escribe('Es que salgo tarde del trabajo');

        $respuestas = $this->respuestas();
        $this->assertCount(1, $respuestas, 'No le contesta lo mismo a cada mensaje');
        $this->assertStringContainsString('Consultorio Sonrisas', $respuestas[0]);
        $this->assertSame(2, $this->doctor->fresh()->notifications()->count(), 'Pero cada mensaje sí le llega al consultorio');
    }

    public function test_si_es_la_mama_del_nino_tambien_se_reconoce(): void
    {
        $mama = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'Pérez', 'phone' => '6689990000']);
        $nino = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Luisito', 'last_name' => 'Pérez', 'responsable_id' => $mama->id]);
        $this->recordada($nino);

        $this->escribe('Luisito va a llegar 10 minutos tarde', '5216689990000');

        $this->assertSame('Luisito Pérez le escribió por WhatsApp', $this->doctor->fresh()->notifications()->sole()->data['title']);
    }

    public function test_va_al_consultorio_que_le_mando_el_ultimo_recordatorio(): void
    {
        $otra = Clinic::create(['name' => 'Otro consultorio', 'plan' => 'basico', 'onboarding_status' => 'completed']);
        $otroDoctor = User::forceCreate(['name' => 'Dra. Otra', 'email' => 'o@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $otra->id]);
        $anaAlla = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        $this->recordada($anaAlla, $otra, '2026-10-10 18:00');
        $this->recordada($this->ana, $this->clinica, '2026-10-13 18:00');

        $this->escribe('Gracias');

        $this->assertSame(1, $this->doctor->fresh()->notifications()->count());
        $this->assertSame(0, $otroDoctor->fresh()->notifications()->count(), 'Nada a otro consultorio');
    }

    public function test_un_numero_sin_recordatorio_reciente_recibe_donde_escribir(): void
    {
        $this->escribe('Hola, ¿qué es esto?', '5215550001111');

        $this->assertSame(0, $this->doctor->fresh()->notifications()->count());
        $respuestas = $this->respuestas();
        $this->assertCount(1, $respuestas);
        $this->assertStringContainsString('solo manda recordatorios', $respuestas[0]);
    }

    public function test_un_recordatorio_de_hace_mas_de_una_semana_ya_no_cuenta(): void
    {
        $this->recordada($this->ana, $this->clinica, '2026-10-01 18:00');

        $this->escribe('Hola');

        $this->assertSame(0, $this->doctor->fresh()->notifications()->count());
    }
}
