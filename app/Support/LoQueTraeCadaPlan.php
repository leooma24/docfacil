<?php

namespace App\Support;

/**
 * Lo que trae cada plan, en palabras para el dentista.
 *
 * Antes cada material tenía su lista: el folleto ofrecía en Pro el
 * odontograma (que viene desde el Básico) y en Clínica "multi-sucursal" y
 * "comisiones entre doctores", que no existen. Ahora la página de inicio,
 * el folleto, el brief, la propuesta y la página de planes leen de aquí.
 *
 * Solo va lo que el sistema hace (Clinic::featuresForPlan y los topes de
 * VerifyClinicPlan / Clinic::LIMITE_*). Si cambia un plan, cambia aquí.
 */
class LoQueTraeCadaPlan
{
    /** @return array<int, array<string, mixed>> */
    public static function planes(): array
    {
        return [
            // Los limites (doctores, pacientes) salen de la lista de
            // features y se van a su propio renglon. Antes ocupaban 2
            // de los 4 espacios visibles de cada tarjeta: en el lugar
            // mas caro de la pagina se le decia al dentista lo que NO
            // puede hacer, en vez de por que le conviene pagar.
            [
                'name' => 'Free',
                'price' => '0',
                'annual' => 0,
                'subtitle' => 'Para siempre',
                'ideal' => 'Para conocerlo sin prisa',
                'limits' => '1 doctor · 15 pacientes · 10 citas al mes',
                'lead' => null,
                'features' => [
                    'Agenda y calendario de citas',
                    'Expediente de cada paciente',
                    'Sin tarjeta y sin vencimiento',
                    'Sube de plan cuando quiera',
                ],
                'cta' => 'Empezar gratis',
                'popular' => false,
            ],
            [
                'name' => 'Básico',
                'price' => '499',
                'annual' => 4990,
                'subtitle' => 'por mes · cancela cuando quiera',
                'ideal' => 'Para el dentista que trabaja solo',
                'limits' => '1 doctor · 200 pacientes · citas ilimitadas',
                'lead' => null,
                // De mayor a menor por lo que le mueve la aguja a un
                // dentista: primero lo que le trae o le cuida dinero,
                // luego lo que lo hace verse profesional.
                'features' => [
                    'Recordatorios WhatsApp a 1 clic',
                    'Gastos y corte del mes',
                    'Odontograma FDI interactivo',
                    'Recetas PDF con cédula',
                    'Presupuestos que el paciente acepta en línea',
                    'Cobro por WhatsApp a 1 clic',
                    'Confirmar cita con link',
                    'Check-in con QR',
                    'Su asistente con su propio usuario',
                    'Laboratorio: qué ya llegó y cuánto se le debe',
                    'Escritorio con sus números del día',
                ],
                'cta' => 'Probar 15 días gratis',
                'popular' => false,
            ],
            [
                'name' => 'Pro',
                'price' => '999',
                'annual' => 9990,
                'subtitle' => 'por mes · cancela cuando quiera',
                'ideal' => 'Para consultorios de 2 o 3 doctores',
                'limits' => 'Hasta 3 doctores · pacientes ilimitados',
                'lead' => 'Todo lo del Básico, y además:',
                'features' => [
                    'Sus pacientes agendan solos, a cualquier hora',
                    'Recall: a quién ya le toca volver',
                    'Lista de espera que llena los huecos',
                    'Consentimientos firmados en pantalla',
                    'Inventario de insumos: qué hay, qué se acabó y qué caduca',
                    'Reportes avanzados',
                    'Alertas inteligentes',
                    'Soporte prioritario por WhatsApp',
                ],
                'cta' => 'Probar Pro 15 días gratis',
                'popular' => true,
            ],
            [
                'name' => 'Clínica',
                'price' => '1,999',
                'annual' => 19990,
                'subtitle' => 'por mes · cancela cuando quiera',
                'ideal' => 'Para clínicas con varios doctores',
                'limits' => 'Doctores y pacientes ilimitados',
                'lead' => 'Todo lo del Pro, y además:',
                'features' => [
                    'Producción individual por doctor',
                    'Reportes por doctor',
                    'Onboarding 1 a 1 dedicado',
                    'Soporte prioritario 7 días a la semana',
                ],
                'cta' => 'Contactar ventas',
                'popular' => false,
            ],
        ];
    }

    /** Un plan por su nombre corto: free, basico, profesional, clinica. */
    public static function plan(string $clave): array
    {
        $nombres = ['free' => 'Free', 'basico' => 'Básico', 'profesional' => 'Pro', 'clinica' => 'Clínica'];

        return collect(self::planes())->firstWhere('name', $nombres[$clave] ?? $clave) ?? [];
    }
}
