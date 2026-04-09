<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Commission;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder para el vendedor demo del panel /ventas.
 *
 * Crea:
 * - 1 usuario demo (ventas-demo@docfacil.com / ventas2026)
 * - 12 prospectos en distintos estados (nuevo, contactado, interesado, trial, lost)
 * - 3 clínicas "ficticias" vendidas por el demo (con diferentes estados de pago)
 * - 6 comisiones (pending, paid, clawback) para mostrar todos los estados
 *
 * Este seeder es idempotente: borra y recrea cada vez que corre.
 */
class DemoSalesSeeder extends Seeder
{
    public const DEMO_EMAIL = 'ventas-demo@docfacil.com';
    public const DEMO_PASSWORD = 'ventas2026';

    public function run(): void
    {
        // Limpiar datos previos del vendedor demo
        $existing = User::where('email', self::DEMO_EMAIL)->first();
        if ($existing) {
            // Borrar clínicas ficticias creadas por este vendedor
            $clinicIds = Clinic::where('sold_by_user_id', $existing->id)
                ->where('slug', 'like', 'demo-sales-%')
                ->pluck('id');
            if ($clinicIds->isNotEmpty()) {
                \DB::table('commissions')->whereIn('clinic_id', $clinicIds)->delete();
                \DB::table('clinics')->whereIn('id', $clinicIds)->delete();
            }
            // Borrar prospectos asignados
            Prospect::where('assigned_to_sales_rep_id', $existing->id)
                ->where('email', 'like', 'demo-prospect-%@example.com')
                ->delete();
            $existing->delete();
        }

        // =============================================
        // CREAR VENDEDOR DEMO
        // =============================================
        $rep = new User();
        $rep->forceFill([
            'name' => 'Juan Ventas (Demo)',
            'email' => self::DEMO_EMAIL,
            'password' => bcrypt(self::DEMO_PASSWORD),
            'role' => 'sales',
            'is_active_sales_rep' => true,
            'commission_rate_percent' => 50,
        ])->save();

        // =============================================
        // PROSPECTOS (12 con distintos estados)
        // =============================================
        $prospects = [
            ['Dr. Mario González', 'Clínica Dental Mazatlán', 'Mazatlán', 'Odontología', '6691234567', 'new', null],
            ['Dra. Laura Torres', 'Consultorio Pediátrico Norte', 'Culiacán', 'Pediatría', '6671112233', 'new', null],
            ['Dr. Roberto Núñez', 'Sonrisas Perfectas', 'Los Mochis', 'Odontología', '6684445566', 'contacted', now()->subDays(2)],
            ['Dra. Ana Beltrán', 'Clínica Dermatológica', 'Culiacán', 'Dermatología', '6677778899', 'contacted', now()->subDays(5)],
            ['Dr. Héctor Ríos', 'Consultorio Familiar', 'Mazatlán', 'Medicina General', '6692223344', 'interested', now()->subDays(7)],
            ['Dra. Patricia Vargas', 'Ortodoncia Vargas', 'Culiacán', 'Ortodoncia', '6675556677', 'interested', now()->subDays(3)],
            ['Dr. Carlos Medina', 'Implantes Dentales Medina', 'Guasave', 'Odontología', '6878889900', 'trial', now()->subDays(10)],
            ['Dra. Sofía Rodríguez', 'Consultorio Ginecológico', 'Los Mochis', 'Ginecología', '6681112244', 'trial', now()->subDays(8)],
            ['Dr. Andrés Peña', 'Clínica Oftalmológica', 'Culiacán', 'Oftalmología', '6673334455', 'new', null],
            ['Dra. Mónica Salazar', 'Endodoncia Especializada', 'Mazatlán', 'Odontología', '6696667788', 'lost', now()->subDays(15)],
            ['Dr. Javier Contreras', 'Medicina Familiar', 'Culiacán', 'Medicina General', '6678889911', 'contacted', now()->subDays(1)],
            ['Dra. Elena Ortiz', 'Pediatría del Valle', 'Los Mochis', 'Pediatría', '6681234455', 'interested', now()->subDays(4)],
        ];

        foreach ($prospects as $i => [$name, $clinic, $city, $specialty, $phone, $status, $contactedAt]) {
            Prospect::create([
                'name' => $name,
                'clinic_name' => $clinic,
                'city' => $city,
                'specialty' => $specialty,
                'phone' => $phone,
                'email' => "demo-prospect-{$i}@example.com",
                'source' => 'prospecting',
                'status' => $status,
                'contacted_at' => $contactedAt,
                'last_followup_at' => $contactedAt,
                'next_followup_at' => in_array($status, ['contacted', 'interested']) ? now()->addDays(rand(1, 5)) : null,
                'assigned_to_sales_rep_id' => $rep->id,
                'notes' => match ($status) {
                    'new' => 'Prospecto nuevo — contactar esta semana',
                    'contacted' => 'Llamada inicial realizada. Mostró interés preliminar.',
                    'interested' => 'Agendada demo. Preguntó por precios y funcionalidad de recetas.',
                    'trial' => 'En periodo de prueba. Revisar seguimiento.',
                    'lost' => 'No le interesó. Prefiere seguir con su sistema actual.',
                    default => null,
                },
            ]);
        }

        // =============================================
        // CLÍNICAS VENDIDAS (3 con distintos estados de pago)
        // =============================================

        // Helper: crea clínica + marca pagos vía update() para que el observer dispare
        $makeClinic = function (string $name, string $plan, int $daysSold, ?int $daysFirstPayment, ?int $daysSecondPayment) use ($rep) {
            $clinic = new Clinic();
            $clinic->forceFill([
                'name' => $name,
                'slug' => 'demo-sales-' . strtolower(preg_replace('/[^a-z0-9]/i', '-', $name)) . '-' . rand(1000, 9999),
                'plan' => $plan,
                'trial_ends_at' => now()->addDays(15),
                'onboarding_status' => 'completed',
                'sold_by_user_id' => $rep->id,
                'sold_at' => now()->subDays($daysSold),
            ])->save();

            // Los updates separados SÍ disparan el observer
            if ($daysFirstPayment !== null) {
                $clinic->update(['first_payment_received_at' => now()->subDays($daysFirstPayment)]);
            }
            if ($daysSecondPayment !== null) {
                $clinic->update(['second_payment_received_at' => now()->subDays($daysSecondPayment)]);
            }

            return $clinic;
        };

        // Clínica 1: Ambos pagos recibidos + comisiones marcadas como pagadas (histórico positivo)
        $clinic1 = $makeClinic('Clínica Dental Ejemplo 1', 'profesional', 45, 40, 15);
        Commission::where('clinic_id', $clinic1->id)->update([
            'status' => 'paid',
            'paid_at' => now()->subDays(10),
        ]);

        // Clínica 2: Solo primer pago → 1 comisión pending
        $makeClinic('Consultorio Médico Ejemplo 2', 'profesional', 12, 8, null);

        // Clínica 3: Vendida pero sin pagos todavía → 0 comisiones (esperando pago)
        $makeClinic('Ortodoncia Especializada Ejemplo 3', 'profesional', 3, null, null);

        // Clínica 4: Plan Clínica con ambos pagos → 2 comisiones grandes ($898.50 c/u)
        $makeClinic('Centro Médico Grande Ejemplo 4', 'clinica', 20, 15, 2);
    }
}
