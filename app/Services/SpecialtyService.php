<?php

namespace App\Services;

class SpecialtyService
{
    public const SPECIALTIES = [
        'odontologia' => 'Odontología General',
        'ortodoncia' => 'Ortodoncia',
        'endodoncia' => 'Endodoncia',
        'periodoncia' => 'Periodoncia',
        'cirugia_maxilofacial' => 'Cirugía Maxilofacial',
        'odontopediatria' => 'Odontopediatría',
        'medicina_general' => 'Medicina General',
        'pediatria' => 'Pediatría',
        'dermatologia' => 'Dermatología',
        'ginecologia' => 'Ginecología',
        'cardiologia' => 'Cardiología',
        'traumatologia' => 'Traumatología',
        'oftalmologia' => 'Oftalmología',
        'otorrinolaringologia' => 'Otorrinolaringología',
        'nutricion' => 'Nutrición',
        'psicologia' => 'Psicología',
        'fisioterapia' => 'Fisioterapia',
        'otro' => 'Otra especialidad',
    ];

    // Which specialty groups get which modules
    private const DENTAL_SPECIALTIES = [
        'odontologia', 'ortodoncia', 'endodoncia', 'periodoncia',
        'cirugia_maxilofacial', 'odontopediatria',
    ];

    private const PEDIATRIC_SPECIALTIES = ['pediatria', 'odontopediatria'];

    private const DERMA_SPECIALTIES = ['dermatologia'];

    public static function isDental(?string $specialty): bool
    {
        return in_array(self::normalizeSpecialty($specialty), self::DENTAL_SPECIALTIES);
    }

    public static function isPediatric(?string $specialty): bool
    {
        return in_array(self::normalizeSpecialty($specialty), self::PEDIATRIC_SPECIALTIES);
    }

    public static function isDermatology(?string $specialty): bool
    {
        return in_array(self::normalizeSpecialty($specialty), self::DERMA_SPECIALTIES);
    }

    public static function shouldShowModule(string $module, ?string $specialty): bool
    {
        // Universal modules - everyone sees these
        $universal = ['appointments', 'patients', 'medical_records', 'prescriptions', 'payments', 'services', 'invitations'];

        if (in_array($module, $universal)) {
            return true;
        }

        return match ($module) {
            'odontogram' => self::isDental($specialty),
            'consent_forms' => true, // All specialties can use consent forms
            'growth_charts' => self::isPediatric($specialty),
            'vaccination' => self::isPediatric($specialty),
            'dermatoscope' => self::isDermatology($specialty),
            default => true,
        };
    }

    public static function getSpecialtyLabel(?string $specialty): string
    {
        $key = self::normalizeSpecialty($specialty);
        return self::SPECIALTIES[$key] ?? $specialty ?? 'Sin especialidad';
    }

    public static function normalizeSpecialty(?string $specialty): string
    {
        if (!$specialty) {
            return 'medicina_general';
        }

        $lower = mb_strtolower(trim($specialty));

        // Try to match common terms
        foreach (self::SPECIALTIES as $key => $label) {
            if (mb_strtolower($label) === $lower || $key === $lower) {
                return $key;
            }
        }

        // Fuzzy matching for common inputs
        if (str_contains($lower, 'odonto') || str_contains($lower, 'dental') || str_contains($lower, 'dient')) {
            return 'odontologia';
        }
        if (str_contains($lower, 'ortodon')) {
            return 'ortodoncia';
        }
        if (str_contains($lower, 'pediatr')) {
            return 'pediatria';
        }
        if (str_contains($lower, 'dermato') || str_contains($lower, 'piel')) {
            return 'dermatologia';
        }
        if (str_contains($lower, 'gineco') || str_contains($lower, 'obstet')) {
            return 'ginecologia';
        }
        if (str_contains($lower, 'cardio') || str_contains($lower, 'coraz')) {
            return 'cardiologia';
        }
        if (str_contains($lower, 'trauma') || str_contains($lower, 'ortoped')) {
            return 'traumatologia';
        }
        if (str_contains($lower, 'nutri') || str_contains($lower, 'dieta')) {
            return 'nutricion';
        }
        if (str_contains($lower, 'psicol') || str_contains($lower, 'mental')) {
            return 'psicologia';
        }
        if (str_contains($lower, 'fisio') || str_contains($lower, 'rehab')) {
            return 'fisioterapia';
        }
        if (str_contains($lower, 'general') || str_contains($lower, 'medic')) {
            return 'medicina_general';
        }

        return 'otro';
    }

    public static function getCurrentDoctorSpecialty(): ?string
    {
        $user = auth()->user();
        if (!$user) return null;

        $doctor = $user->doctor;
        return $doctor?->specialty;
    }

    public static function currentDoctorCanSee(string $module): bool
    {
        return self::shouldShowModule($module, self::getCurrentDoctorSpecialty());
    }

    // ─────────────────────────────────────────────────────────────────
    //  Consultation field configuration (per-specialty defaults +
    //  clinic/doctor overrides). See:
    //    - ClinicConsultationSettings
    //    - DoctorConsultationSettings
    //    - app/Filament/Doctor/Pages/Consultation.php
    // ─────────────────────────────────────────────────────────────────

    /**
     * Catálogo maestro de campos configurables de la pantalla de consulta.
     * Cada campo es una key estable que se persiste en enabled_fields JSON.
     */
    public const FIELD_CATALOG = [
        // Signos vitales
        'blood_pressure' => ['group' => 'vitals', 'label' => 'Presión arterial', 'help' => 'Sistólica/diastólica (ej. 120/80)'],
        'heart_rate' => ['group' => 'vitals', 'label' => 'Frecuencia cardíaca', 'help' => 'Pulsaciones por minuto'],
        'temperature' => ['group' => 'vitals', 'label' => 'Temperatura', 'help' => '°C'],
        'respiratory_rate' => ['group' => 'vitals', 'label' => 'Frecuencia respiratoria', 'help' => 'Respiraciones por minuto'],
        'oxygen_saturation' => ['group' => 'vitals', 'label' => 'Saturación de oxígeno', 'help' => '% SpO2'],

        // Somatometría
        'weight' => ['group' => 'somatometry', 'label' => 'Peso', 'help' => 'kg'],
        'height' => ['group' => 'somatometry', 'label' => 'Talla', 'help' => 'cm'],
        'bmi' => ['group' => 'somatometry', 'label' => 'IMC', 'help' => 'Calculado de peso y talla'],
        'head_circumference' => ['group' => 'somatometry', 'label' => 'Perímetro cefálico', 'help' => 'cm (pediatría)'],

        // Diagnóstico
        'cie10_codes' => ['group' => 'diagnosis', 'label' => 'Diagnóstico CIE-10', 'help' => 'Catálogo OMS con autocompletado'],

        // Extras / banners
        'allergies_alert' => ['group' => 'extras', 'label' => 'Alerta de alergias', 'help' => 'Banner que muestra alergias del paciente'],
        'anticoagulants_alert' => ['group' => 'extras', 'label' => 'Alerta de anticoagulantes', 'help' => 'Banner si el paciente toma anticoagulantes'],
    ];

    /**
     * Campos habilitados por defecto agrupados por familia clínica.
     * Si la clínica/doctor no tienen config explícita, se usan estos.
     */
    public static function defaultConsultationFields(?string $specialty): array
    {
        if (self::isDental($specialty)) {
            return [
                'blood_pressure',
                'heart_rate',
                'allergies_alert',
                'anticoagulants_alert',
            ];
        }

        if (self::isPediatric($specialty)) {
            return [
                'blood_pressure',
                'heart_rate',
                'temperature',
                'respiratory_rate',
                'oxygen_saturation',
                'weight',
                'height',
                'bmi',
                'head_circumference',
                'cie10_codes',
                'allergies_alert',
            ];
        }

        // Médico general y resto de especialidades médicas no-dentales
        return [
            'blood_pressure',
            'heart_rate',
            'temperature',
            'respiratory_rate',
            'oxygen_saturation',
            'weight',
            'height',
            'bmi',
            'cie10_codes',
            'allergies_alert',
        ];
    }

    /**
     * Resuelve qué campos debe ver UN doctor en SU pantalla de consulta.
     * Cascada: doctor override → clinic override → defaults por especialidad.
     *
     * @return array<string> Lista de keys del FIELD_CATALOG habilitadas.
     */
    public static function resolveEnabledFields(?\App\Models\Doctor $doctor): array
    {
        if (! $doctor) {
            return self::defaultConsultationFields(null);
        }

        // 1. ¿Tiene override personal y NO hereda?
        $doctorSettings = $doctor->consultationSettings;
        if ($doctorSettings && ! $doctorSettings->inherits_clinic_config && is_array($doctorSettings->enabled_fields)) {
            return $doctorSettings->enabled_fields;
        }

        // 2. ¿La clínica tiene config?
        $clinicSettings = $doctor->clinic?->consultationSettings;
        if ($clinicSettings && is_array($clinicSettings->enabled_fields)) {
            return $clinicSettings->enabled_fields;
        }

        // 3. Fallback: defaults por especialidad
        return self::defaultConsultationFields($doctor->specialty);
    }
}
