<?php

namespace Tests\Feature;

use App\Models\Clinic;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * El portal del paciente lo incluyen todos los planes de pago, no Free.
 * Antes no estaba en ningún plan, así que VerifyPatientPortalAccess lo
 * bloqueaba para todos.
 */
class PatientPortalAccessTest extends TestCase
{
    public static function planes(): array
    {
        return [
            'free'        => ['free', false],
            'basico'      => ['basico', true],
            'profesional' => ['profesional', true],
            'clinica'     => ['clinica', true],
        ];
    }

    #[DataProvider('planes')]
    public function test_el_portal_va_con_los_planes_de_pago(string $plan, bool $loTiene): void
    {
        $this->assertSame(
            $loTiene,
            in_array('patient_portal', Clinic::featuresForPlan($plan), true),
            "El plan {$plan} quedó con el portal " . ($loTiene ? 'apagado' : 'prendido') . '.'
        );
    }
}
