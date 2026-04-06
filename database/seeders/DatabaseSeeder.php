<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $admin = User::forceCreate([
            'name' => 'Admin DocFácil',
            'email' => 'admin@docfacil.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        // Clínica de prueba
        $clinic = Clinic::create([
            'name' => 'Consultorio Dental Sonrisas',
            'slug' => 'consultorio-dental-sonrisas',
            'phone' => '555-123-4567',
            'email' => 'contacto@sonrisas.com',
            'address' => 'Av. Reforma 100, Col. Centro',
            'city' => 'CDMX',
            'state' => 'CDMX',
            'zip_code' => '06600',
            'plan' => 'profesional',
            'trial_ends_at' => now()->addDays(15),
        ]);

        // Doctor de prueba
        $doctorUser = User::forceCreate([
            'name' => 'Dr. Juan Pérez',
            'email' => 'doctor@docfacil.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'clinic_id' => $clinic->id,
            'specialty' => 'Odontología General',
            'license_number' => 'CED-12345678',
            'phone' => '555-987-6543',
            'appointment_duration' => 30,
            'working_hours' => [
                'monday' => ['start' => '09:00', 'end' => '18:00'],
                'tuesday' => ['start' => '09:00', 'end' => '18:00'],
                'wednesday' => ['start' => '09:00', 'end' => '18:00'],
                'thursday' => ['start' => '09:00', 'end' => '18:00'],
                'friday' => ['start' => '09:00', 'end' => '14:00'],
            ],
        ]);

        // Servicios
        $services = [
            ['name' => 'Limpieza dental', 'price' => 500, 'duration_minutes' => 45, 'category' => 'Preventivo'],
            ['name' => 'Extracción simple', 'price' => 800, 'duration_minutes' => 30, 'category' => 'Cirugía'],
            ['name' => 'Resina (obturación)', 'price' => 600, 'duration_minutes' => 40, 'category' => 'Restauración'],
            ['name' => 'Endodoncia', 'price' => 3500, 'duration_minutes' => 90, 'category' => 'Endodoncia'],
            ['name' => 'Corona dental', 'price' => 4000, 'duration_minutes' => 60, 'category' => 'Prótesis'],
            ['name' => 'Consulta general', 'price' => 300, 'duration_minutes' => 20, 'category' => 'General'],
            ['name' => 'Radiografía dental', 'price' => 200, 'duration_minutes' => 10, 'category' => 'Diagnóstico'],
            ['name' => 'Blanqueamiento dental', 'price' => 2500, 'duration_minutes' => 60, 'category' => 'Estética'],
        ];

        foreach ($services as $service) {
            Service::create(array_merge($service, ['clinic_id' => $clinic->id]));
        }

        // Pacientes de prueba
        $patients = [
            ['first_name' => 'María', 'last_name' => 'García López', 'phone' => '555-111-2222', 'email' => 'maria@email.com', 'birth_date' => '1990-05-15', 'gender' => 'female', 'blood_type' => 'O+'],
            ['first_name' => 'Carlos', 'last_name' => 'Hernández Ruiz', 'phone' => '555-333-4444', 'email' => 'carlos@email.com', 'birth_date' => '1985-03-20', 'gender' => 'male', 'blood_type' => 'A+'],
            ['first_name' => 'Ana', 'last_name' => 'Martínez Soto', 'phone' => '555-555-6666', 'email' => 'ana@email.com', 'birth_date' => '1995-11-08', 'gender' => 'female'],
            ['first_name' => 'Roberto', 'last_name' => 'López Díaz', 'phone' => '555-777-8888', 'birth_date' => '1978-07-12', 'gender' => 'male', 'allergies' => 'Penicilina'],
            ['first_name' => 'Laura', 'last_name' => 'Sánchez Mora', 'phone' => '555-999-0000', 'email' => 'laura@email.com', 'birth_date' => '2000-01-25', 'gender' => 'female'],
        ];

        $patientModels = [];
        foreach ($patients as $patient) {
            $patientModels[] = Patient::create(array_merge($patient, ['clinic_id' => $clinic->id]));
        }

        // Citas de prueba
        $consultaService = Service::where('name', 'Consulta general')->first();
        $limpiezaService = Service::where('name', 'Limpieza dental')->first();

        Appointment::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patientModels[0]->id,
            'service_id' => $limpiezaService->id,
            'starts_at' => now()->addDay()->setTime(9, 0),
            'ends_at' => now()->addDay()->setTime(9, 45),
            'status' => 'scheduled',
        ]);

        Appointment::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patientModels[1]->id,
            'service_id' => $consultaService->id,
            'starts_at' => now()->addDay()->setTime(10, 0),
            'ends_at' => now()->addDay()->setTime(10, 20),
            'status' => 'confirmed',
        ]);

        Appointment::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patientModels[2]->id,
            'service_id' => $consultaService->id,
            'starts_at' => now()->addDays(2)->setTime(11, 0),
            'ends_at' => now()->addDays(2)->setTime(11, 20),
            'status' => 'scheduled',
        ]);
    }
}
