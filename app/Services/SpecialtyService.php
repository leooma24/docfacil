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
}
