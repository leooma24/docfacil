<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates a throwaway demo clinic pre-filled with fake data so sales reps
 * can give live demos without touching real customer data.
 * The clinic is auto-deleted after 1 hour.
 */
class DemoModeController extends Controller
{
    public function start(Request $request)
    {
        // Clean up any expired demo clinics first
        $this->cleanupExpiredDemos();

        $clinic = Clinic::create([
            'name' => 'Clínica Demo ' . now()->format('H:i'),
            'slug' => 'demo-' . Str::random(8),
            'email' => 'demo-' . Str::random(6) . '@docfacil.test',
            'phone' => '555 000 0000',
            'address' => 'Av. Demo 123',
            'city' => 'Ciudad de México',
            'state' => 'CDMX',
            'country' => 'México',
            'onboarding_status' => 'completed',
            'is_active' => true,
            'plan' => 'profesional',
            'is_demo' => true,
            'demo_expires_at' => now()->addHour(),
            'trial_ends_at' => now()->addHour(),
        ]);

        $user = User::forceCreate([
            'name' => 'Dr. Demo',
            'email' => 'demo-' . Str::random(6) . '@docfacil.test',
            'password' => Hash::make(Str::random(16)),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
            'email_verified_at' => now(),
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'specialty' => 'Odontología General',
            'license_number' => 'DEMO-' . rand(100000, 999999),
            'phone' => '555 111 1111',
            'is_active' => true,
        ]);

        $this->seedDemoData($clinic, $doctor);

        Auth::login($user);
        session(['demo_mode' => true]);

        return redirect('/doctor')->with('success', 'Modo demo activado. Explora todas las funciones.');
    }

    protected function seedDemoData(Clinic $clinic, Doctor $doctor): void
    {
        // Services
        $services = [
            ['name' => 'Consulta General', 'price' => 500, 'duration_minutes' => 30, 'category' => 'General'],
            ['name' => 'Limpieza Dental', 'price' => 800, 'duration_minutes' => 45, 'category' => 'Preventivo'],
            ['name' => 'Resina', 'price' => 1200, 'duration_minutes' => 60, 'category' => 'Restauración'],
            ['name' => 'Endodoncia', 'price' => 3500, 'duration_minutes' => 90, 'category' => 'Endodoncia'],
            ['name' => 'Corona Dental', 'price' => 5500, 'duration_minutes' => 60, 'category' => 'Prótesis'],
            ['name' => 'Extracción', 'price' => 900, 'duration_minutes' => 30, 'category' => 'Cirugía'],
        ];
        $serviceRecords = [];
        foreach ($services as $s) {
            $serviceRecords[] = Service::create(array_merge($s, ['clinic_id' => $clinic->id, 'is_active' => true]));
        }

        // Patients
        $firstNames = ['María', 'Juan', 'Ana', 'Carlos', 'Sofía', 'Luis', 'Gabriela', 'Roberto', 'Daniela', 'Miguel',
            'Fernanda', 'Alejandro', 'Valeria', 'Diego', 'Laura', 'Ricardo', 'Paola', 'Jorge', 'Claudia', 'Andrés'];
        $lastNames = ['García', 'Martínez', 'López', 'Hernández', 'González', 'Rodríguez', 'Pérez', 'Sánchez',
            'Ramírez', 'Torres', 'Flores', 'Rivera', 'Cruz', 'Reyes', 'Morales', 'Ortiz', 'Gutiérrez', 'Jiménez'];
        $genders = ['male', 'female'];
        $bloods = ['A+', 'B+', 'O+', 'AB+', 'A-', 'O-'];
        $allergies = [null, null, 'Penicilina', 'Látex', 'Ibuprofeno', null];

        $patients = [];
        for ($i = 0; $i < 35; $i++) {
            $patients[] = Patient::create([
                'clinic_id' => $clinic->id,
                'first_name' => $firstNames[array_rand($firstNames)],
                'last_name' => $lastNames[array_rand($lastNames)] . ' ' . $lastNames[array_rand($lastNames)],
                'phone' => '55 ' . rand(1000, 9999) . ' ' . rand(1000, 9999),
                'email' => 'paciente' . $i . '@demo.com',
                'birth_date' => now()->subYears(rand(18, 75))->subDays(rand(0, 365))->toDateString(),
                'gender' => $genders[array_rand($genders)],
                'blood_type' => $bloods[array_rand($bloods)],
                'allergies' => $allergies[array_rand($allergies)],
                'is_active' => true,
            ]);
        }

        // Appointments - mix of past completed, today, and future
        $complaints = [
            'Dolor en molar inferior derecho',
            'Limpieza de rutina',
            'Revisión general',
            'Dolor de muelas al masticar',
            'Control post-tratamiento',
            'Sensibilidad dental',
            'Sangrado de encías',
            'Diente fracturado',
        ];
        $diagnoses = [
            'Caries profunda', 'Gingivitis leve', 'Placa bacteriana',
            'Caries inicial', 'Pulpitis reversible', 'Bruxismo',
            'Sensibilidad dentinaria', 'Absceso periapical',
        ];
        $treatments = [
            'Resina compuesta clase II', 'Profilaxis dental',
            'Aplicación de flúor', 'Obturación con amalgama',
            'Endodoncia en proceso', 'Férula oclusal',
        ];

        // Past appointments (completed, with records)
        for ($i = 0; $i < 60; $i++) {
            $patient = $patients[array_rand($patients)];
            $service = $serviceRecords[array_rand($serviceRecords)];
            $date = now()->subDays(rand(1, 120));

            $appt = Appointment::create([
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'service_id' => $service->id,
                'starts_at' => $date,
                'ends_at' => (clone $date)->addMinutes($service->duration_minutes),
                'status' => 'completed',
            ]);

            MedicalRecord::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_id' => $appt->id,
                'visit_date' => $date->toDateString(),
                'chief_complaint' => $complaints[array_rand($complaints)],
                'diagnosis' => $diagnoses[array_rand($diagnoses)],
                'treatment' => $treatments[array_rand($treatments)],
            ]);

            if (rand(0, 10) > 2) {
                Payment::create([
                    'clinic_id' => $clinic->id,
                    'patient_id' => $patient->id,
                    'appointment_id' => $appt->id,
                    'service_id' => $service->id,
                    'amount' => $service->price,
                    'payment_method' => ['cash', 'card', 'transfer'][array_rand(['cash', 'card', 'transfer'])],
                    'status' => 'paid',
                    'payment_date' => $date->toDateString(),
                ]);
            }
        }

        // Today's appointments
        for ($i = 0; $i < 5; $i++) {
            $patient = $patients[array_rand($patients)];
            $service = $serviceRecords[array_rand($serviceRecords)];
            $hour = 9 + $i * 2;
            $starts = now()->setHour($hour)->setMinute(0)->setSecond(0);

            Appointment::create([
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'service_id' => $service->id,
                'starts_at' => $starts,
                'ends_at' => (clone $starts)->addMinutes($service->duration_minutes),
                'status' => $i === 0 ? 'confirmed' : 'scheduled',
            ]);
        }

        // Future appointments
        for ($i = 0; $i < 15; $i++) {
            $patient = $patients[array_rand($patients)];
            $service = $serviceRecords[array_rand($serviceRecords)];
            $starts = now()->addDays(rand(1, 14))->setHour(rand(9, 17))->setMinute([0, 15, 30, 45][array_rand([0, 15, 30, 45])]);

            Appointment::create([
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'service_id' => $service->id,
                'starts_at' => $starts,
                'ends_at' => (clone $starts)->addMinutes($service->duration_minutes),
                'status' => 'scheduled',
            ]);
        }
    }

    protected function cleanupExpiredDemos(): void
    {
        $expired = Clinic::where('is_demo', true)
            ->where('demo_expires_at', '<', now())
            ->pluck('id');

        if ($expired->isEmpty()) return;

        // Manual cascade delete (to bypass clinic scopes)
        \DB::table('medical_records')->whereIn('clinic_id', $expired)->delete();
        \DB::table('prescriptions')->whereIn('clinic_id', $expired)->delete();
        \DB::table('payments')->whereIn('clinic_id', $expired)->delete();
        \DB::table('appointments')->whereIn('clinic_id', $expired)->delete();
        \DB::table('patients')->whereIn('clinic_id', $expired)->delete();
        \DB::table('services')->whereIn('clinic_id', $expired)->delete();
        \DB::table('doctors')->whereIn('clinic_id', $expired)->delete();
        \DB::table('users')->whereIn('clinic_id', $expired)->delete();
        \DB::table('clinics')->whereIn('id', $expired)->delete();
    }
}
