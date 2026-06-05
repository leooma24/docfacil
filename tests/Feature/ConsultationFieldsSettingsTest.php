<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\ClinicConsultationSettings;
use App\Models\Doctor;
use App\Models\DoctorConsultationSettings;
use App\Models\User;
use App\Services\SpecialtyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationFieldsSettingsTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinic;
    private User $user;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::create([
            'name' => 'Test Clinic',
            'onboarding_status' => 'completed',
        ]);

        $this->user = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $this->user->id,
            'clinic_id' => $this->clinic->id,
            'specialty' => 'odontologia',
        ]);
    }

    public function test_dentist_default_fields_exclude_temperature_weight_height(): void
    {
        $fields = SpecialtyService::defaultConsultationFields('odontologia');

        $this->assertContains('blood_pressure', $fields);
        $this->assertContains('heart_rate', $fields);
        $this->assertContains('allergies_alert', $fields);
        $this->assertContains('anticoagulants_alert', $fields);

        $this->assertNotContains('temperature', $fields);
        $this->assertNotContains('weight', $fields);
        $this->assertNotContains('height', $fields);
        $this->assertNotContains('bmi', $fields);
        $this->assertNotContains('respiratory_rate', $fields);
        $this->assertNotContains('oxygen_saturation', $fields);
        $this->assertNotContains('cie10_codes', $fields);
    }

    public function test_general_doctor_default_fields_include_full_vitals_and_somatometry(): void
    {
        $fields = SpecialtyService::defaultConsultationFields('medicina_general');

        $this->assertContains('blood_pressure', $fields);
        $this->assertContains('heart_rate', $fields);
        $this->assertContains('temperature', $fields);
        $this->assertContains('respiratory_rate', $fields);
        $this->assertContains('oxygen_saturation', $fields);
        $this->assertContains('weight', $fields);
        $this->assertContains('height', $fields);
        $this->assertContains('bmi', $fields);
        $this->assertContains('cie10_codes', $fields);

        $this->assertNotContains('head_circumference', $fields, 'head_circumference es solo pediatría');
    }

    public function test_pediatric_default_fields_include_head_circumference(): void
    {
        $fields = SpecialtyService::defaultConsultationFields('pediatria');

        $this->assertContains('head_circumference', $fields);
        $this->assertContains('weight', $fields);
        $this->assertContains('height', $fields);
        $this->assertContains('bmi', $fields);
    }

    public function test_cascade_falls_back_to_specialty_defaults_when_no_overrides(): void
    {
        // Sin clinic config, sin doctor config → defaults por especialidad
        $resolved = SpecialtyService::resolveEnabledFields($this->doctor->fresh());

        $this->assertEquals(
            SpecialtyService::defaultConsultationFields('odontologia'),
            $resolved
        );
    }

    public function test_clinic_override_replaces_specialty_defaults(): void
    {
        ClinicConsultationSettings::create([
            'clinic_id' => $this->clinic->id,
            'enabled_fields' => ['blood_pressure', 'temperature', 'weight'],
        ]);

        $resolved = SpecialtyService::resolveEnabledFields($this->doctor->fresh());

        $this->assertEquals(['blood_pressure', 'temperature', 'weight'], $resolved);
    }

    public function test_doctor_override_wins_over_clinic_and_defaults(): void
    {
        ClinicConsultationSettings::create([
            'clinic_id' => $this->clinic->id,
            'enabled_fields' => ['blood_pressure', 'temperature'],
        ]);

        DoctorConsultationSettings::create([
            'doctor_id' => $this->doctor->id,
            'enabled_fields' => ['cie10_codes', 'weight'],
            'inherits_clinic_config' => false,
        ]);

        $resolved = SpecialtyService::resolveEnabledFields($this->doctor->fresh());

        $this->assertEquals(['cie10_codes', 'weight'], $resolved);
    }

    public function test_doctor_with_inherits_true_uses_clinic_config_even_if_has_enabled_fields(): void
    {
        ClinicConsultationSettings::create([
            'clinic_id' => $this->clinic->id,
            'enabled_fields' => ['blood_pressure', 'temperature'],
        ]);

        // Doctor tiene enabled_fields guardados PERO inherits=true → debe ignorar los suyos
        DoctorConsultationSettings::create([
            'doctor_id' => $this->doctor->id,
            'enabled_fields' => ['cie10_codes'],
            'inherits_clinic_config' => true,
        ]);

        $resolved = SpecialtyService::resolveEnabledFields($this->doctor->fresh());

        $this->assertEquals(['blood_pressure', 'temperature'], $resolved);
    }

    public function test_changing_clinic_config_affects_inheriting_doctors_immediately(): void
    {
        $settings = ClinicConsultationSettings::create([
            'clinic_id' => $this->clinic->id,
            'enabled_fields' => ['blood_pressure'],
        ]);

        $this->assertEquals(['blood_pressure'], SpecialtyService::resolveEnabledFields($this->doctor->fresh()));

        // Cambiamos la config de la clínica
        $settings->update(['enabled_fields' => ['blood_pressure', 'heart_rate', 'temperature']]);

        $this->assertEquals(
            ['blood_pressure', 'heart_rate', 'temperature'],
            SpecialtyService::resolveEnabledFields($this->doctor->fresh())
        );
    }

    public function test_changing_clinic_config_does_not_affect_doctor_with_override(): void
    {
        $clinicSettings = ClinicConsultationSettings::create([
            'clinic_id' => $this->clinic->id,
            'enabled_fields' => ['blood_pressure'],
        ]);

        DoctorConsultationSettings::create([
            'doctor_id' => $this->doctor->id,
            'enabled_fields' => ['cie10_codes', 'weight'],
            'inherits_clinic_config' => false,
        ]);

        // Cambiamos la config de la clínica
        $clinicSettings->update(['enabled_fields' => ['heart_rate', 'temperature', 'oxygen_saturation']]);

        // El doctor con override NO debe verse afectado
        $this->assertEquals(
            ['cie10_codes', 'weight'],
            SpecialtyService::resolveEnabledFields($this->doctor->fresh())
        );
    }

    public function test_field_catalog_groups_all_known_fields(): void
    {
        $catalog = SpecialtyService::FIELD_CATALOG;

        // Sanity: catálogo no vacío
        $this->assertNotEmpty($catalog);

        // Todos los grupos esperados existen
        $groups = collect($catalog)->pluck('group')->unique()->values();
        $this->assertContains('vitals', $groups);
        $this->assertContains('somatometry', $groups);
        $this->assertContains('diagnosis', $groups);
        $this->assertContains('extras', $groups);

        // Cada entrada tiene label y help
        foreach ($catalog as $key => $meta) {
            $this->assertArrayHasKey('label', $meta, "Falta label para {$key}");
            $this->assertArrayHasKey('help', $meta, "Falta help para {$key}");
            $this->assertArrayHasKey('group', $meta, "Falta group para {$key}");
        }
    }

    public function test_resolver_returns_defaults_when_doctor_is_null(): void
    {
        $resolved = SpecialtyService::resolveEnabledFields(null);
        $this->assertIsArray($resolved);
        $this->assertNotEmpty($resolved);
    }
}
