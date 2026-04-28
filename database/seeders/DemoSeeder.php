<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // CLINICA DEMO
        // =============================================
        $clinic = Clinic::create([
            'name' => 'Clínica Dental Sonrisas CDMX',
            'slug' => 'clinica-dental-sonrisas-cdmx',
            'phone' => '55 1234 5678',
            'email' => 'contacto@sonrisascdmx.com',
            'address' => 'Av. Insurgentes Sur 1234, Col. Del Valle',
            'city' => 'Ciudad de México',
            'state' => 'CDMX',
            'zip_code' => '03100',
            'plan' => 'profesional',
            'trial_ends_at' => now()->addDays(30),
            'onboarding_status' => 'completed',
        ]);

        // =============================================
        // DOCTOR DEMO (cuenta para mostrar en ventas)
        // =============================================
        $doctorUser = User::forceCreate([
            'name' => 'Dr. Roberto García',
            'email' => 'demo@docfacil.com',
            'password' => bcrypt('demo2026'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
            // Demo siempre con email verificado (no quemar 1 min cada demo
            // pidiendo verificacion). Se regenera 4 AM diario.
            'email_verified_at' => now(),
            'terms_accepted_at' => now(),
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'clinic_id' => $clinic->id,
            'specialty' => 'Odontología General',
            'license_number' => 'CED-98765432',
            'phone' => '55 9876 5432',
            'appointment_duration' => 30,
            'working_hours' => [
                'monday' => ['start' => '09:00', 'end' => '18:00'],
                'tuesday' => ['start' => '09:00', 'end' => '18:00'],
                'wednesday' => ['start' => '09:00', 'end' => '18:00'],
                'thursday' => ['start' => '09:00', 'end' => '18:00'],
                'friday' => ['start' => '09:00', 'end' => '14:00'],
            ],
        ]);

        // Segundo doctor
        $doctor2User = User::forceCreate([
            'name' => 'Dra. Ana Martínez',
            'email' => 'dra.ana@docfacil.com',
            'password' => bcrypt('demo2026'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
            'email_verified_at' => now(),
            'terms_accepted_at' => now(),
        ]);

        $doctor2 = Doctor::create([
            'user_id' => $doctor2User->id,
            'clinic_id' => $clinic->id,
            'specialty' => 'Ortodoncia',
            'license_number' => 'CED-11223344',
            'phone' => '55 1122 3344',
            'appointment_duration' => 45,
        ]);

        // =============================================
        // SERVICIOS
        // =============================================
        $services = [];
        $serviceData = [
            ['name' => 'Consulta general', 'price' => 300, 'duration_minutes' => 20, 'category' => 'General'],
            ['name' => 'Limpieza dental', 'price' => 500, 'duration_minutes' => 45, 'category' => 'Preventivo'],
            ['name' => 'Extracción simple', 'price' => 800, 'duration_minutes' => 30, 'category' => 'Cirugía'],
            ['name' => 'Extracción de tercer molar', 'price' => 2500, 'duration_minutes' => 60, 'category' => 'Cirugía'],
            ['name' => 'Resina (obturación)', 'price' => 600, 'duration_minutes' => 40, 'category' => 'Restauración'],
            ['name' => 'Endodoncia', 'price' => 3500, 'duration_minutes' => 90, 'category' => 'Endodoncia'],
            ['name' => 'Corona dental porcelana', 'price' => 4500, 'duration_minutes' => 60, 'category' => 'Prótesis'],
            ['name' => 'Corona dental zirconia', 'price' => 6000, 'duration_minutes' => 60, 'category' => 'Prótesis'],
            ['name' => 'Blanqueamiento dental', 'price' => 2500, 'duration_minutes' => 60, 'category' => 'Estética'],
            ['name' => 'Carillas de porcelana (por pieza)', 'price' => 5000, 'duration_minutes' => 45, 'category' => 'Estética'],
            ['name' => 'Radiografía periapical', 'price' => 150, 'duration_minutes' => 10, 'category' => 'Diagnóstico'],
            ['name' => 'Radiografía panorámica', 'price' => 350, 'duration_minutes' => 15, 'category' => 'Diagnóstico'],
            ['name' => 'Guardas dentales', 'price' => 1200, 'duration_minutes' => 30, 'category' => 'Preventivo'],
            ['name' => 'Prótesis parcial removible', 'price' => 3500, 'duration_minutes' => 45, 'category' => 'Prótesis'],
            ['name' => 'Ortodoncia (mensualidad)', 'price' => 800, 'duration_minutes' => 30, 'category' => 'Ortodoncia'],
        ];

        foreach ($serviceData as $s) {
            $services[] = Service::create(array_merge($s, ['clinic_id' => $clinic->id]));
        }

        // =============================================
        // PACIENTES (20 pacientes realistas)
        // =============================================
        $patientData = [
            ['first_name' => 'María Elena', 'last_name' => 'García López', 'phone' => '55 2111 3001', 'email' => 'maria.garcia@email.com', 'birth_date' => '1985-03-15', 'gender' => 'female', 'blood_type' => 'O+'],
            ['first_name' => 'Carlos Eduardo', 'last_name' => 'Hernández Ruiz', 'phone' => '55 2111 3002', 'email' => 'carlos.h@email.com', 'birth_date' => '1990-07-22', 'gender' => 'male', 'blood_type' => 'A+'],
            ['first_name' => 'Ana Sofía', 'last_name' => 'Martínez Soto', 'phone' => '55 2111 3003', 'email' => 'ana.martinez@email.com', 'birth_date' => '1995-11-08', 'gender' => 'female', 'blood_type' => 'B+'],
            ['first_name' => 'Roberto', 'last_name' => 'López Díaz', 'phone' => '55 2111 3004', 'birth_date' => '1978-02-28', 'gender' => 'male', 'allergies' => 'Penicilina, Lidocaína'],
            ['first_name' => 'Laura Patricia', 'last_name' => 'Sánchez Mora', 'phone' => '55 2111 3005', 'email' => 'laura.sanchez@email.com', 'birth_date' => '2000-06-12', 'gender' => 'female'],
            ['first_name' => 'José Manuel', 'last_name' => 'Ramírez Torres', 'phone' => '55 2111 3006', 'birth_date' => '1972-09-03', 'gender' => 'male', 'blood_type' => 'O-', 'medical_notes' => 'Paciente diabético tipo 2. Toma metformina.'],
            ['first_name' => 'Gabriela', 'last_name' => 'Flores Vega', 'phone' => '55 2111 3007', 'email' => 'gaby.flores@email.com', 'birth_date' => '1988-12-20', 'gender' => 'female', 'blood_type' => 'A-'],
            ['first_name' => 'Fernando', 'last_name' => 'Morales Castro', 'phone' => '55 2111 3008', 'birth_date' => '1965-04-17', 'gender' => 'male', 'allergies' => 'Aspirina', 'medical_notes' => 'Hipertensión controlada con Losartán.'],
            ['first_name' => 'Alejandra', 'last_name' => 'Jiménez Reyes', 'phone' => '55 2111 3009', 'email' => 'ale.jimenez@email.com', 'birth_date' => '1993-08-30', 'gender' => 'female'],
            ['first_name' => 'Miguel Ángel', 'last_name' => 'Pérez Guzmán', 'phone' => '55 2111 3010', 'birth_date' => '1982-01-14', 'gender' => 'male', 'blood_type' => 'AB+'],
            ['first_name' => 'Daniela', 'last_name' => 'Cruz Medina', 'phone' => '55 2111 3011', 'email' => 'dani.cruz@email.com', 'birth_date' => '1998-05-25', 'gender' => 'female'],
            ['first_name' => 'Ricardo', 'last_name' => 'Vargas Ortiz', 'phone' => '55 2111 3012', 'birth_date' => '1975-10-09', 'gender' => 'male', 'blood_type' => 'B-'],
            ['first_name' => 'Paola', 'last_name' => 'Mendoza Ríos', 'phone' => '55 2111 3013', 'email' => 'paola.m@email.com', 'birth_date' => '2002-03-18', 'gender' => 'female'],
            ['first_name' => 'Héctor', 'last_name' => 'Aguilar Fuentes', 'phone' => '55 2111 3014', 'birth_date' => '1960-07-07', 'gender' => 'male', 'medical_notes' => 'Prótesis parcial superior. Revisión cada 6 meses.'],
            ['first_name' => 'Valentina', 'last_name' => 'Rojas Luna', 'phone' => '55 2111 3015', 'email' => 'vale.rojas@email.com', 'birth_date' => '2005-09-22', 'gender' => 'female'],
            ['first_name' => 'Andrés', 'last_name' => 'Domínguez Silva', 'phone' => '55 2111 3016', 'birth_date' => '1987-11-30', 'gender' => 'male', 'blood_type' => 'O+'],
            ['first_name' => 'Mónica', 'last_name' => 'Castillo Herrera', 'phone' => '55 2111 3017', 'email' => 'monica.c@email.com', 'birth_date' => '1992-04-05', 'gender' => 'female'],
            ['first_name' => 'Jorge Luis', 'last_name' => 'Guerrero Navarro', 'phone' => '55 2111 3018', 'birth_date' => '1970-06-14', 'gender' => 'male', 'allergies' => 'Latex'],
            ['first_name' => 'Camila', 'last_name' => 'Ortega Palacios', 'phone' => '55 2111 3019', 'email' => 'camila.o@email.com', 'birth_date' => '1997-02-08', 'gender' => 'female', 'blood_type' => 'A+'],
            ['first_name' => 'Diego', 'last_name' => 'Salazar Ibarra', 'phone' => '55 2111 3020', 'birth_date' => '2001-12-01', 'gender' => 'male'],
        ];

        $patients = [];
        foreach ($patientData as $p) {
            $patients[] = Patient::create(array_merge($p, ['clinic_id' => $clinic->id]));
        }

        // =============================================
        // CITAS - Pasadas (últimas 4 semanas) + Futuras
        // =============================================
        $statuses = ['completed', 'completed', 'completed', 'completed', 'completed', 'no_show', 'cancelled'];

        // Citas pasadas (genera historial)
        for ($week = 4; $week >= 1; $week--) {
            for ($day = 1; $day <= 5; $day++) {
                $date = now()->subWeeks($week)->startOfWeek()->addDays($day - 1);
                $slotsUsed = rand(3, 6);

                for ($slot = 0; $slot < $slotsUsed; $slot++) {
                    $hour = 9 + $slot;
                    $patient = $patients[array_rand($patients)];
                    $service = $services[array_rand($services)];
                    $status = $statuses[array_rand($statuses)];

                    $apt = Appointment::create([
                        'clinic_id' => $clinic->id,
                        'doctor_id' => rand(0, 1) ? $doctor->id : $doctor2->id,
                        'patient_id' => $patient->id,
                        'service_id' => $service->id,
                        'starts_at' => $date->copy()->setTime($hour, 0),
                        'ends_at' => $date->copy()->setTime($hour, $service->duration_minutes),
                        'status' => $status,
                        'reminder_sent' => true,
                    ]);

                    // Pagos para citas completadas
                    if ($status === 'completed') {
                        Payment::create([
                            'clinic_id' => $clinic->id,
                            'patient_id' => $patient->id,
                            'appointment_id' => $apt->id,
                            'service_id' => $service->id,
                            'amount' => $service->price,
                            'payment_method' => ['cash', 'card', 'transfer'][rand(0, 2)],
                            'status' => 'paid',
                            'payment_date' => $date->toDateString(),
                        ]);
                    }
                }
            }
        }

        // Citas futuras (esta semana y siguiente)
        $futureStatuses = ['scheduled', 'scheduled', 'confirmed', 'confirmed', 'scheduled'];
        for ($day = 0; $day <= 9; $day++) {
            $date = now()->addDays($day);
            if ($date->isWeekend()) continue;

            $slotsUsed = rand(3, 7);
            for ($slot = 0; $slot < $slotsUsed; $slot++) {
                $hour = 9 + $slot;
                $patient = $patients[array_rand($patients)];
                $service = $services[array_rand($services)];

                Appointment::create([
                    'clinic_id' => $clinic->id,
                    'doctor_id' => rand(0, 1) ? $doctor->id : $doctor2->id,
                    'patient_id' => $patient->id,
                    'service_id' => $service->id,
                    'starts_at' => $date->copy()->setTime($hour, 0),
                    'ends_at' => $date->copy()->setTime($hour, $service->duration_minutes),
                    'status' => $futureStatuses[array_rand($futureStatuses)],
                ]);
            }
        }

        // =============================================
        // EXPEDIENTES CLÍNICOS (10 registros)
        // =============================================
        $records = [
            ['patient' => 0, 'complaint' => 'Dolor molar inferior derecho', 'diagnosis' => 'Caries profunda en pieza 46', 'treatment' => 'Se realiza resina compuesta. Se indica analgésico por 3 días.', 'vital_signs' => ['blood_pressure' => '120/80', 'heart_rate' => '72', 'temperature' => '36.5']],
            ['patient' => 1, 'complaint' => 'Revisión semestral', 'diagnosis' => 'Acumulación de sarro moderada. Encías inflamadas.', 'treatment' => 'Limpieza dental completa. Se instruye técnica de cepillado.', 'vital_signs' => ['blood_pressure' => '130/85', 'heart_rate' => '78']],
            ['patient' => 2, 'complaint' => 'Sensibilidad al frío', 'diagnosis' => 'Retracción gingival en premolares superiores', 'treatment' => 'Aplicación de flúor. Se recomienda pasta para sensibilidad.', 'vital_signs' => ['blood_pressure' => '110/70', 'temperature' => '36.8']],
            ['patient' => 3, 'complaint' => 'Dolor intenso muela del juicio', 'diagnosis' => 'Tercer molar inferior impactado con pericoronitis', 'treatment' => 'Se prescribe antibiótico y antiinflamatorio. Programar extracción.', 'vital_signs' => ['blood_pressure' => '140/90', 'heart_rate' => '88', 'temperature' => '37.2']],
            ['patient' => 5, 'complaint' => 'Control de diabetes - revisión dental', 'diagnosis' => 'Gingivitis leve. Sin lesiones nuevas.', 'treatment' => 'Limpieza suave. Control en 3 meses por condición diabética.', 'vital_signs' => ['blood_pressure' => '135/85', 'heart_rate' => '80', 'temperature' => '36.6', 'weight' => '82']],
            ['patient' => 6, 'complaint' => 'Quiere blanqueamiento', 'diagnosis' => 'Pigmentación por café/vino. Apta para blanqueamiento.', 'treatment' => 'Blanqueamiento en consultorio con peróxido 35%. Resultado satisfactorio.'],
            ['patient' => 8, 'complaint' => 'Fractura de diente frontal', 'diagnosis' => 'Fractura coronaria no complicada pieza 11', 'treatment' => 'Reconstrucción con resina compuesta. Excelente resultado estético.'],
            ['patient' => 10, 'complaint' => 'Consulta ortodoncia', 'diagnosis' => 'Maloclusión clase II. Apiñamiento moderado.', 'treatment' => 'Se toman radiografías y modelos. Se presenta plan de ortodoncia 18 meses.'],
            ['patient' => 13, 'complaint' => 'Revisión de prótesis', 'diagnosis' => 'Prótesis parcial superior con desgaste. Ajuste necesario.', 'treatment' => 'Rebase de prótesis. Se programa nueva prótesis en 2 meses.'],
            ['patient' => 14, 'complaint' => 'Primera visita - revisión general', 'diagnosis' => 'Caries incipiente en pieza 36. Resto sano.', 'treatment' => 'Sellante preventivo. Se programa resina para próxima cita.'],
        ];

        foreach ($records as $i => $rec) {
            MedicalRecord::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $patients[$rec['patient']]->id,
                'doctor_id' => $i % 2 === 0 ? $doctor->id : $doctor2->id,
                'visit_date' => now()->subDays(rand(1, 28)),
                'chief_complaint' => $rec['complaint'],
                'diagnosis' => $rec['diagnosis'],
                'treatment' => $rec['treatment'],
                'vital_signs' => $rec['vital_signs'] ?? null,
            ]);
        }

        // =============================================
        // RECETAS (5 recetas con medicamentos)
        // =============================================
        $prescriptions = [
            [
                'patient' => 0, 'diagnosis' => 'Caries profunda pieza 46 - post resina',
                'items' => [
                    ['medication' => 'Ibuprofeno 400mg', 'dosage' => '1 tableta', 'frequency' => 'Cada 8 horas', 'duration' => '3 días', 'instructions' => 'Tomar con alimentos'],
                    ['medication' => 'Ketorolaco 10mg', 'dosage' => '1 tableta', 'frequency' => 'Cada 8 horas solo si hay dolor intenso', 'duration' => '2 días', 'instructions' => 'No combinar con ibuprofeno'],
                ],
            ],
            [
                'patient' => 3, 'diagnosis' => 'Pericoronitis tercer molar',
                'items' => [
                    ['medication' => 'Amoxicilina 500mg', 'dosage' => '1 cápsula', 'frequency' => 'Cada 8 horas', 'duration' => '7 días', 'instructions' => 'Completar todo el tratamiento'],
                    ['medication' => 'Metronidazol 500mg', 'dosage' => '1 tableta', 'frequency' => 'Cada 8 horas', 'duration' => '7 días', 'instructions' => 'No tomar alcohol durante el tratamiento'],
                    ['medication' => 'Nimesulida 100mg', 'dosage' => '1 tableta', 'frequency' => 'Cada 12 horas', 'duration' => '5 días', 'instructions' => 'Tomar después de alimentos'],
                ],
            ],
            [
                'patient' => 2, 'diagnosis' => 'Sensibilidad dental por retracción gingival',
                'items' => [
                    ['medication' => 'Sensodyne Repair & Protect', 'dosage' => 'Aplicar como pasta dental', 'frequency' => '3 veces al día', 'duration' => 'Uso continuo', 'instructions' => 'No enjuagar inmediatamente después del cepillado'],
                    ['medication' => 'Enjuague bucal con flúor', 'dosage' => '10ml', 'frequency' => '1 vez al día antes de dormir', 'duration' => '30 días'],
                ],
            ],
            [
                'patient' => 7, 'diagnosis' => 'Extracción pieza 38 - post quirúrgico',
                'items' => [
                    ['medication' => 'Clindamicina 300mg', 'dosage' => '1 cápsula', 'frequency' => 'Cada 6 horas', 'duration' => '5 días', 'instructions' => 'Alérgico a penicilina - NO dar amoxicilina'],
                    ['medication' => 'Dexametasona 4mg', 'dosage' => '1 tableta', 'frequency' => 'Cada 12 horas', 'duration' => '3 días', 'instructions' => 'Tomar con alimentos'],
                    ['medication' => 'Paracetamol 500mg', 'dosage' => '1-2 tabletas', 'frequency' => 'Cada 6 horas si hay dolor', 'duration' => '5 días'],
                ],
            ],
            [
                'patient' => 5, 'diagnosis' => 'Gingivitis en paciente diabético',
                'items' => [
                    ['medication' => 'Clorhexidina 0.12% enjuague', 'dosage' => '15ml', 'frequency' => '2 veces al día', 'duration' => '14 días', 'instructions' => 'Enjuagar por 30 segundos después del cepillado'],
                ],
            ],
        ];

        foreach ($prescriptions as $pData) {
            $prescription = Prescription::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $patients[$pData['patient']]->id,
                'doctor_id' => $doctor->id,
                'prescription_date' => now()->subDays(rand(1, 14)),
                'diagnosis' => $pData['diagnosis'],
            ]);

            foreach ($pData['items'] as $item) {
                PrescriptionItem::create(array_merge($item, [
                    'prescription_id' => $prescription->id,
                ]));
            }
        }

        // =============================================
        // PAGOS PENDIENTES (para mostrar en reportes)
        // =============================================
        Payment::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patients[3]->id,
            'service_id' => $services[3]->id,
            'amount' => 2500,
            'payment_method' => 'card',
            'status' => 'pending',
            'payment_date' => now()->toDateString(),
            'notes' => 'Pendiente - extracción de muela del juicio programada',
        ]);

        Payment::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patients[10]->id,
            'service_id' => $services[14]->id,
            'amount' => 800,
            'payment_method' => 'transfer',
            'status' => 'pending',
            'payment_date' => now()->toDateString(),
            'notes' => 'Mensualidad ortodoncia - abril',
        ]);

        Payment::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patients[6]->id,
            'service_id' => $services[8]->id,
            'amount' => 1250,
            'payment_method' => 'card',
            'status' => 'partial',
            'payment_date' => now()->subDays(3)->toDateString(),
            'notes' => 'Blanqueamiento - pagó 50%, resta $1,250',
        ]);
        // =============================================
        // ODONTOGRAMAS (2 pacientes)
        // =============================================
        $odontogram1 = \App\Models\Odontogram::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patients[0]->id,
            'doctor_id' => $doctor->id,
            'evaluation_date' => now()->subDays(7),
            'notes' => 'Evaluación inicial. Paciente con buena higiene general. Requiere atención en zona posterior derecha.',
        ]);

        $teethData = [
            ['tooth_number' => 16, 'condition' => 'filling', 'notes' => 'Resina mesial colocada hace 2 años'],
            ['tooth_number' => 17, 'condition' => 'decay', 'notes' => 'Caries oclusal incipiente'],
            ['tooth_number' => 26, 'condition' => 'crown', 'notes' => 'Corona porcelana - buen estado'],
            ['tooth_number' => 36, 'condition' => 'root_canal', 'notes' => 'Endodoncia realizada 2024, corona pendiente'],
            ['tooth_number' => 38, 'condition' => 'missing', 'notes' => 'Extraído 2023'],
            ['tooth_number' => 46, 'condition' => 'filling', 'notes' => 'Resina reciente - control en 6 meses'],
            ['tooth_number' => 48, 'condition' => 'extraction', 'notes' => 'Programada para extracción - impactado'],
            ['tooth_number' => 18, 'condition' => 'missing', 'notes' => 'Agenesia'],
            ['tooth_number' => 27, 'condition' => 'sealant', 'notes' => 'Sellante preventivo'],
            ['tooth_number' => 14, 'condition' => 'decay', 'notes' => 'Caries interproximal - programar resina'],
        ];

        foreach ($teethData as $t) {
            \App\Models\OdontogramTooth::create(array_merge($t, ['odontogram_id' => $odontogram1->id]));
        }

        $odontogram2 = \App\Models\Odontogram::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patients[13]->id,
            'doctor_id' => $doctor->id,
            'evaluation_date' => now()->subDays(14),
            'notes' => 'Paciente adulto mayor. Prótesis parcial superior. Múltiples restauraciones previas.',
        ]);

        $teethData2 = [
            ['tooth_number' => 11, 'condition' => 'crown'],
            ['tooth_number' => 12, 'condition' => 'bridge'],
            ['tooth_number' => 14, 'condition' => 'missing'],
            ['tooth_number' => 15, 'condition' => 'missing'],
            ['tooth_number' => 21, 'condition' => 'crown'],
            ['tooth_number' => 24, 'condition' => 'missing'],
            ['tooth_number' => 25, 'condition' => 'missing'],
            ['tooth_number' => 36, 'condition' => 'filling'],
            ['tooth_number' => 37, 'condition' => 'decay'],
            ['tooth_number' => 46, 'condition' => 'filling'],
            ['tooth_number' => 47, 'condition' => 'root_canal'],
            ['tooth_number' => 18, 'condition' => 'missing'],
            ['tooth_number' => 28, 'condition' => 'missing'],
            ['tooth_number' => 38, 'condition' => 'missing'],
            ['tooth_number' => 48, 'condition' => 'missing'],
        ];

        foreach ($teethData2 as $t) {
            \App\Models\OdontogramTooth::create(array_merge($t, ['odontogram_id' => $odontogram2->id]));
        }

        // =============================================
        // CONSENTIMIENTOS INFORMADOS (3)
        // =============================================
        \App\Models\ConsentForm::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patients[3]->id,
            'doctor_id' => $doctor->id,
            'title' => 'Consentimiento Informado - Extracción de Tercer Molar',
            'procedure_name' => 'Extracción quirúrgica de tercer molar inferior (pieza 48)',
            'content' => '<p>Yo, <strong>Roberto López Díaz</strong>, declaro que he sido informado(a) de manera clara y comprensible por el <strong>Dr. Roberto García</strong> sobre el procedimiento de extracción quirúrgica del tercer molar inferior.</p><p>Se me ha explicado:</p><ul><li>La naturaleza del procedimiento quirúrgico y en qué consiste</li><li>Los beneficios esperados: eliminación del dolor e infección</li><li>Los riesgos y posibles complicaciones</li><li>Las alternativas de tratamiento disponibles</li></ul><p>Por lo tanto, de manera libre y voluntaria, autorizo la realización del procedimiento.</p>',
            'risks' => 'Sangrado post-operatorio, inflamación, dolor, infección, parestesia temporal del nervio dentario inferior, alveolitis seca.',
            'alternatives' => 'Tratamiento conservador con antibióticos (temporal). No tratar (riesgo de infecciones recurrentes y daño a piezas adyacentes).',
            'signed_at' => now()->subDays(2),
            'signed_ip' => '189.203.45.123',
        ]);

        \App\Models\ConsentForm::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patients[6]->id,
            'doctor_id' => $doctor->id,
            'title' => 'Consentimiento Informado - Blanqueamiento Dental',
            'procedure_name' => 'Blanqueamiento dental en consultorio con peróxido de hidrógeno al 35%',
            'content' => '<p>Yo, <strong>Gabriela Flores Vega</strong>, autorizo el procedimiento de blanqueamiento dental profesional.</p><p>Entiendo que:</p><ul><li>El resultado puede variar según la estructura dental</li><li>Puede haber sensibilidad temporal post-tratamiento</li><li>Se requieren cuidados posteriores para mantener el resultado</li></ul>',
            'risks' => 'Sensibilidad dental temporal (24-48 horas), irritación gingival leve.',
            'alternatives' => 'Blanqueamiento con guardas en casa (más lento, 2-3 semanas). Carillas de porcelana (más invasivo y costoso).',
            'signed_at' => now()->subDays(5),
            'signed_ip' => '189.203.45.124',
        ]);

        \App\Models\ConsentForm::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patients[10]->id,
            'doctor_id' => $doctor2->id,
            'title' => 'Consentimiento Informado - Tratamiento de Ortodoncia',
            'procedure_name' => 'Ortodoncia fija con brackets metálicos - tratamiento 18 meses',
            'content' => '<p>Yo, <strong>Daniela Cruz Medina</strong>, autorizo el inicio de tratamiento de ortodoncia fija.</p><p>He sido informada sobre:</p><ul><li>Duración estimada: 18 meses</li><li>Necesidad de citas mensuales de control</li><li>Importancia de higiene dental durante el tratamiento</li><li>Posibilidad de ajustes en el plan de tratamiento</li></ul>',
            'risks' => 'Descalcificación dental por mala higiene, resorción radicular, recidiva post-tratamiento.',
            'alternatives' => 'Alineadores transparentes (Invisalign). No tratar (maloclusión puede empeorar).',
        ]);
    }
}

