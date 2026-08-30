<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * No se pueden encimar dos citas del mismo doctor.
 *
 * Antes no había ninguna validación: ni el formulario, ni el modelo, ni el
 * agendamiento público. Dos citas a las 10:00 y 10:30 de una hora cada una
 * se guardaban sin decir nada, y el traslape se descubría cuando llegaban
 * los dos pacientes a la sala de espera.
 */
class TraslapeDeCitasTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private Doctor $doctor;

    private Patient $paciente;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-test',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addMonth(),
            'onboarding_status' => 'completed',
        ]);

        $this->usuario = User::forceCreate([
            'name' => 'Dr. Roberto García',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $this->usuario->id,
            'clinic_id' => $this->clinica->id,
            'specialty' => 'Odontología',
        ]);

        $this->paciente = Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'María Elena',
            'last_name' => 'García',
            'phone' => '5512345678',
        ]);
    }

    private function cita(string $inicio, string $fin, string $estado = 'scheduled'): Appointment
    {
        return Appointment::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'starts_at' => $inicio,
            'ends_at' => $fin,
            'status' => $estado,
        ]);
    }

    private function hayTraslape(string $inicio, string $fin, ?int $excepto = null): bool
    {
        return Appointment::traslapes(
            $this->clinica->id,
            $this->doctor->id,
            new \DateTimeImmutable($inicio),
            new \DateTimeImmutable($fin),
            $excepto,
        )->isNotEmpty();
    }

    // ── Qué cuenta como traslape ─────────────────────────────────

    public function test_una_cita_que_arranca_a_media_cita_choca(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $this->assertTrue($this->hayTraslape('2026-09-10 10:30', '2026-09-10 11:30'));
    }

    public function test_una_cita_que_envuelve_a_otra_choca(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $this->assertTrue($this->hayTraslape('2026-09-10 09:30', '2026-09-10 12:00'));
    }

    public function test_una_cita_metida_dentro_de_otra_choca(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $this->assertTrue($this->hayTraslape('2026-09-10 10:15', '2026-09-10 10:45'));
    }

    public function test_una_cita_pegada_a_la_anterior_no_choca(): void
    {
        // Que una acabe a las 11:00 y la siguiente empiece a las 11:00 es
        // agenda apretada, no traslape.
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $this->assertFalse($this->hayTraslape('2026-09-10 11:00', '2026-09-10 12:00'));
    }

    public function test_otro_dia_no_choca(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $this->assertFalse($this->hayTraslape('2026-09-11 10:00', '2026-09-11 11:00'));
    }

    // ── Lo que libera el horario ─────────────────────────────────

    public function test_una_cita_cancelada_libera_el_horario(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00', 'cancelled');

        $this->assertFalse($this->hayTraslape('2026-09-10 10:00', '2026-09-10 11:00'));
    }

    public function test_una_inasistencia_libera_el_horario(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00', 'no_show');

        $this->assertFalse($this->hayTraslape('2026-09-10 10:00', '2026-09-10 11:00'));
    }

    public function test_otro_doctor_puede_atender_a_la_misma_hora(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $otroUsuario = User::forceCreate([
            'name' => 'Dra. Ana Martínez',
            'email' => 'ana@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $this->clinica->id,
        ]);
        $otroDoctor = Doctor::create([
            'user_id' => $otroUsuario->id,
            'clinic_id' => $this->clinica->id,
            'specialty' => 'Ortodoncia',
        ]);

        $this->assertTrue(Appointment::traslapes(
            $this->clinica->id,
            $otroDoctor->id,
            new \DateTimeImmutable('2026-09-10 10:00'),
            new \DateTimeImmutable('2026-09-10 11:00'),
        )->isEmpty(), 'Dos doctores pueden atender a la misma hora en sillones distintos.');
    }

    public function test_una_cita_no_choca_consigo_misma_al_editarla(): void
    {
        $cita = $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $this->assertFalse(
            $this->hayTraslape('2026-09-10 10:00', '2026-09-10 11:00', $cita->id),
            'Editar una cita sin moverla no debería marcarla como traslape.'
        );
    }

    // ── El mensaje que ve el doctor ──────────────────────────────

    public function test_el_mensaje_dice_con_quien_y_a_que_hora_choca(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $mensaje = Appointment::mensajeDeTraslape(
            $this->clinica->id,
            $this->doctor->id,
            new \DateTimeImmutable('2026-09-10 10:30'),
            new \DateTimeImmutable('2026-09-10 11:30'),
        );

        $this->assertStringContainsString('10:00', $mensaje);
        $this->assertStringContainsString('11:00', $mensaje);
        $this->assertStringContainsString('María Elena', $mensaje);
    }

    public function test_sin_choque_no_hay_mensaje(): void
    {
        $this->assertNull(Appointment::mensajeDeTraslape(
            $this->clinica->id,
            $this->doctor->id,
            new \DateTimeImmutable('2026-09-10 10:00'),
            new \DateTimeImmutable('2026-09-10 11:00'),
        ));
    }

    // ── El formulario del doctor ─────────────────────────────────

    public function test_el_formulario_no_deja_guardar_una_cita_encimada(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);

        Livewire::test(AppointmentResource\Pages\CreateAppointment::class)
            ->fillForm([
                'patient_id' => $this->paciente->id,
                'doctor_id' => $this->doctor->id,
                'starts_at' => '2026-09-10 10:30',
                'ends_at' => '2026-09-10 11:30',
                'status' => 'scheduled',
            ])
            ->call('create')
            ->assertHasFormErrors(['starts_at']);

        $this->assertSame(1, Appointment::count(), 'No debió guardarse la segunda cita.');
    }

    public function test_el_formulario_si_deja_guardar_en_un_hueco_libre(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);

        Livewire::test(AppointmentResource\Pages\CreateAppointment::class)
            ->fillForm([
                'patient_id' => $this->paciente->id,
                'doctor_id' => $this->doctor->id,
                'starts_at' => '2026-09-10 11:00',
                'ends_at' => '2026-09-10 12:00',
                'status' => 'scheduled',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(2, Appointment::count());
    }

    // ── El agendamiento público ──────────────────────────────────

    public function test_el_paciente_no_puede_apartar_un_horario_ocupado(): void
    {
        $this->cita('2026-09-10 10:00', '2026-09-10 11:00');

        $this->post("/clinica/{$this->clinica->slug}/agendar", [
            'first_name' => 'Nuevo',
            'last_name' => 'Paciente',
            'phone' => '5599887766',
            'doctor_id' => $this->doctor->id,
            'preferred_at' => '2026-09-10 10:30',
        ])->assertSessionHasErrors('preferred_at');

        $this->assertSame(1, Appointment::count());
    }

    public function test_el_horario_publico_respeta_la_duracion_del_servicio(): void
    {
        // Antes se apartaban 30 minutos fijos: una endodoncia de 90 dejaba
        // el sillon ocupado con media hora en la agenda.
        $servicio = Service::create([
            'clinic_id' => $this->clinica->id,
            'name' => 'Endodoncia',
            'price' => 3500,
            'duration_minutes' => 90,
        ]);

        $this->post("/clinica/{$this->clinica->slug}/agendar", [
            'first_name' => 'Nuevo',
            'last_name' => 'Paciente',
            'phone' => '5599887766',
            'doctor_id' => $this->doctor->id,
            'service_id' => $servicio->id,
            'preferred_at' => '2026-09-10 10:00',
        ])->assertSessionHasNoErrors();

        $cita = Appointment::firstOrFail();

        $this->assertSame(90, (int) $cita->starts_at->diffInMinutes($cita->ends_at));
    }

    // ── A quién meter cuando se libera un hueco ──────────────────

    private function enEspera(array $atributos = []): \App\Models\WaitlistEntry
    {
        $paciente = Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => $atributos['nombre'] ?? 'Paciente',
            'last_name' => 'En Espera',
            'phone' => '5500000000',
        ]);

        unset($atributos['nombre']);

        return \App\Models\WaitlistEntry::create(array_merge([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $paciente->id,
            'desired_from' => '2026-09-01',
            'desired_to' => '2026-09-30',
            'priority' => 0,
            'status' => 'waiting',
        ], $atributos));
    }

    public function test_sugiere_a_quien_pedia_ese_dia(): void
    {
        $cabe = $this->enEspera(['nombre' => 'Sí Cabe']);
        $noCabe = $this->enEspera([
            'nombre' => 'No Cabe',
            'desired_from' => '2026-10-01',
            'desired_to' => '2026-10-31',
        ]);

        $hueco = $this->cita('2026-09-10 10:00', '2026-09-10 11:00', 'cancelled');
        $candidatos = \App\Models\WaitlistEntry::candidatosPara($hueco);

        $this->assertTrue($candidatos->contains('id', $cabe->id));
        $this->assertFalse($candidatos->contains('id', $noCabe->id));
    }

    public function test_los_urgentes_van_primero(): void
    {
        $this->enEspera(['nombre' => 'Normal']);
        $urgente = $this->enEspera(['nombre' => 'Urgente', 'priority' => 1]);

        $hueco = $this->cita('2026-09-10 10:00', '2026-09-10 11:00', 'cancelled');
        $candidatos = \App\Models\WaitlistEntry::candidatosPara($hueco);

        $this->assertSame($urgente->id, $candidatos->first()->id);
    }

    public function test_quien_pidio_otro_doctor_no_aparece(): void
    {
        $otroUsuario = User::forceCreate([
            'name' => 'Dra. Otra',
            'email' => 'otra@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $this->clinica->id,
        ]);
        $otroDoctor = Doctor::create([
            'user_id' => $otroUsuario->id,
            'clinic_id' => $this->clinica->id,
            'specialty' => 'Ortodoncia',
        ]);

        $conOtroDoctor = $this->enEspera(['nombre' => 'Con Otra', 'doctor_id' => $otroDoctor->id]);
        $leDaIgual = $this->enEspera(['nombre' => 'Le Da Igual']);

        $hueco = $this->cita('2026-09-10 10:00', '2026-09-10 11:00', 'cancelled');
        $candidatos = \App\Models\WaitlistEntry::candidatosPara($hueco);

        $this->assertFalse($candidatos->contains('id', $conOtroDoctor->id));
        $this->assertTrue(
            $candidatos->contains('id', $leDaIgual->id),
            'Quien no pidió doctor en particular sirve para cualquiera.'
        );
    }

    public function test_a_quien_ya_se_le_aviso_no_vuelve_a_salir(): void
    {
        $yaAvisado = $this->enEspera(['nombre' => 'Ya Avisado', 'status' => 'notified']);

        $hueco = $this->cita('2026-09-10 10:00', '2026-09-10 11:00', 'cancelled');

        $this->assertFalse(
            \App\Models\WaitlistEntry::candidatosPara($hueco)->contains('id', $yaAvisado->id)
        );
    }

    // ── Horario de atención ──────────────────────────────────────

    public function test_por_defecto_el_domingo_esta_cerrado(): void
    {
        // Un consultorio nuevo no debe aceptar citas a cualquier hora solo
        // porque todavía no configuró su horario.
        $this->assertFalse($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-06 11:00'))); // domingo
        $this->assertTrue($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-08 11:00')));  // martes
    }

    public function test_de_madrugada_esta_cerrado(): void
    {
        $this->assertFalse($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-08 03:00')));
    }

    public function test_a_la_hora_de_cierre_ya_no_atiende(): void
    {
        // Cierran a las 19:00: a las 18:45 todavía puedes entrar, a las 19:00 no.
        $this->assertTrue($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-08 18:45')));
        $this->assertFalse($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-08 19:00')));
    }

    public function test_una_cita_que_no_termina_antes_de_cerrar_no_cabe(): void
    {
        // Empezar dentro del horario no basta: una limpieza de una hora a las
        // 18:30 deja al paciente a media consulta cuando cierran a las 19:00.
        $this->assertFalse($this->clinica->cabeLaCita(
            new \DateTimeImmutable('2026-09-08 18:30'),
            new \DateTimeImmutable('2026-09-08 19:30'),
        ));

        $this->assertTrue($this->clinica->cabeLaCita(
            new \DateTimeImmutable('2026-09-08 18:00'),
            new \DateTimeImmutable('2026-09-08 19:00'),
        ));
    }

    public function test_el_sabado_cierra_mas_temprano(): void
    {
        $this->assertTrue($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-12 10:00')));
        $this->assertFalse($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-12 16:00')));
    }

    public function test_el_consultorio_puede_cambiar_su_horario(): void
    {
        $this->clinica->update(['working_hours' => [
            'domingo' => ['abre' => '10:00', 'cierra' => '14:00'],
            'lunes' => null,
        ]]);

        $this->assertTrue($this->clinica->fresh()->atiendeEn(new \DateTimeImmutable('2026-09-06 11:00')));
        $this->assertFalse($this->clinica->fresh()->atiendeEn(new \DateTimeImmutable('2026-09-07 11:00')));
    }

    public function test_el_paciente_no_puede_pedir_cita_en_domingo(): void
    {
        $this->post("/clinica/{$this->clinica->slug}/agendar", [
            'first_name' => 'Nuevo',
            'last_name' => 'Paciente',
            'phone' => '5599887766',
            'doctor_id' => $this->doctor->id,
            'preferred_at' => '2026-09-06 11:00',   // domingo
        ])->assertSessionHasErrors('preferred_at');

        $this->assertSame(0, Appointment::count());
    }

    public function test_el_paciente_no_puede_pedir_cita_de_madrugada(): void
    {
        $this->post("/clinica/{$this->clinica->slug}/agendar", [
            'first_name' => 'Nuevo',
            'last_name' => 'Paciente',
            'phone' => '5599887766',
            'doctor_id' => $this->doctor->id,
            'preferred_at' => '2026-09-08 03:00',
        ])->assertSessionHasErrors('preferred_at');

        $this->assertSame(0, Appointment::count());
    }

    public function test_el_paciente_si_puede_pedir_dentro_del_horario(): void
    {
        $this->post("/clinica/{$this->clinica->slug}/agendar", [
            'first_name' => 'Nuevo',
            'last_name' => 'Paciente',
            'phone' => '5599887766',
            'doctor_id' => $this->doctor->id,
            'preferred_at' => '2026-09-08 11:00',   // martes
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Appointment::count());
    }

    public function test_el_mensaje_le_dice_al_paciente_cuando_si_puede_venir(): void
    {
        $this->assertStringContainsString(
            'no abre',
            $this->clinica->horarioDelDia(new \DateTimeImmutable('2026-09-06 11:00'))
        );

        $este = $this->clinica->horarioDelDia(new \DateTimeImmutable('2026-09-08 03:00'));
        $this->assertStringContainsString('09:00', $este);
        $this->assertStringContainsString('19:00', $este);
    }

    // ── Descanso de comida ───────────────────────────────────────

    private function conComida(): void
    {
        $this->clinica->update(['working_hours' => [
            'martes' => ['abre' => '09:00', 'cierra' => '19:00', 'descanso_de' => '14:00', 'descanso_a' => '16:00'],
        ]]);
        $this->clinica = $this->clinica->fresh();
    }

    public function test_a_la_hora_de_comida_no_atiende(): void
    {
        $this->conComida();

        $this->assertTrue($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-08 13:00')));
        $this->assertFalse($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-08 15:00')));
        $this->assertTrue($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-08 16:30')));
    }

    public function test_una_cita_no_puede_cruzar_la_comida(): void
    {
        // Empezar a las 13:30 una consulta de una hora, comiendo de 14:00 a
        // 16:00, deja al paciente esperando en el sillón.
        $this->conComida();

        $this->assertFalse($this->clinica->cabeLaCita(
            new \DateTimeImmutable('2026-09-08 13:30'),
            new \DateTimeImmutable('2026-09-08 14:30'),
        ));

        $this->assertTrue($this->clinica->cabeLaCita(
            new \DateTimeImmutable('2026-09-08 13:00'),
            new \DateTimeImmutable('2026-09-08 14:00'),
        ));
    }

    public function test_el_mensaje_menciona_los_dos_bloques(): void
    {
        $this->conComida();

        $mensaje = $this->clinica->horarioDelDia(new \DateTimeImmutable('2026-09-08 15:00'));

        $this->assertStringContainsString('09:00 a 14:00', $mensaje);
        $this->assertStringContainsString('16:00 a 19:00', $mensaje);
    }

    public function test_el_paciente_no_puede_pedir_cita_a_la_hora_de_comida(): void
    {
        $this->conComida();

        $this->post("/clinica/{$this->clinica->slug}/agendar", [
            'first_name' => 'Nuevo',
            'last_name' => 'Paciente',
            'phone' => '5599887766',
            'doctor_id' => $this->doctor->id,
            'preferred_at' => '2026-09-08 15:00',
        ])->assertSessionHasErrors('preferred_at');

        $this->assertSame(0, Appointment::count());
    }

    // ── Vacaciones y días cerrados ───────────────────────────────

    private function cerrarDel(string $del, string $al, ?string $motivo = null): \App\Models\ClinicClosure
    {
        return \App\Models\ClinicClosure::create([
            'clinic_id' => $this->clinica->id,
            'starts_on' => $del,
            'ends_on' => $al,
            'reason' => $motivo,
        ]);
    }

    public function test_en_vacaciones_no_atiende_aunque_sea_su_horario(): void
    {
        $this->cerrarDel('2026-09-14', '2026-09-18', 'Vacaciones');

        // Martes normal: abierto. Martes de vacaciones: cerrado.
        $this->assertTrue($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-08 11:00')));
        $this->assertFalse($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-15 11:00')));
    }

    public function test_el_cierre_cubre_todo_el_rango_incluidos_los_extremos(): void
    {
        $this->cerrarDel('2026-09-14', '2026-09-18');

        foreach (['2026-09-14', '2026-09-16', '2026-09-18'] as $dia) {
            $this->assertFalse(
                $this->clinica->atiendeEn(new \DateTimeImmutable("{$dia} 11:00")),
                "El {$dia} debería estar cerrado."
            );
        }

        // El día siguiente ya no.
        $this->assertTrue($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-21 11:00')));
    }

    public function test_un_dia_feriado_suelto_se_puede_cerrar(): void
    {
        $this->cerrarDel('2026-09-16', '2026-09-16', 'Día de la Independencia');

        $this->assertFalse($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-16 11:00')));
        $this->assertTrue($this->clinica->atiendeEn(new \DateTimeImmutable('2026-09-17 11:00')));
    }

    public function test_el_mensaje_dice_el_motivo_del_cierre(): void
    {
        $this->cerrarDel('2026-09-16', '2026-09-16', 'Día de la Independencia');

        $mensaje = $this->clinica->horarioDelDia(new \DateTimeImmutable('2026-09-16 11:00'));

        $this->assertStringContainsString('cerrado', $mensaje);
        $this->assertStringContainsString('Día de la Independencia', $mensaje);
    }

    public function test_el_paciente_no_puede_pedir_cita_en_vacaciones(): void
    {
        $this->cerrarDel('2026-09-14', '2026-09-18', 'Vacaciones');

        $this->post("/clinica/{$this->clinica->slug}/agendar", [
            'first_name' => 'Nuevo',
            'last_name' => 'Paciente',
            'phone' => '5599887766',
            'doctor_id' => $this->doctor->id,
            'preferred_at' => '2026-09-15 11:00',
        ])->assertSessionHasErrors('preferred_at');

        $this->assertSame(0, Appointment::count());
    }
}
