<?php

namespace Tests\Unit;

use App\Models\Commission;
use App\Models\FeatureRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * La escalera del poll del Roadmap ("¿Cuánto pagarías al mes por esta
 * feature?") tiene que cubrir el rango real de un add-on: desde algo barato
 * hasta algo caro, con el último escalón abierto.
 *
 * El bug que estos tests previenen: el techo quedó en $299/mes, por debajo
 * del plan más barato (Básico, $499/mes). Ningún doctor podía expresar que
 * pagaría más, así que la señal de pricing salía sesgada a la baja y no
 * servía para decidir cuánto cobrar por un add-on.
 */
class FeatureRequestPriceTiersTest extends TestCase
{
    public function test_el_ultimo_escalon_es_abierto(): void
    {
        $ultimo = array_key_last(FeatureRequest::PRICE_TIERS);

        $this->assertStringEndsWith(
            'plus',
            $ultimo,
            'El último escalón debe ser abierto ("+") para no volver a ponerle un muro al techo.'
        );
    }

    public function test_el_techo_alcanza_al_menos_el_precio_del_plan_mas_barato(): void
    {
        $precioBasico = Commission::monthlyPriceForPlan('basico');
        $ultimo = array_key_last(FeatureRequest::PRICE_TIERS);
        $techo = FeatureRequest::PRICE_TIER_VALUES[$ultimo];

        $this->assertGreaterThanOrEqual(
            $precioBasico,
            $techo,
            "El techo del poll (\${$techo}) no puede quedar por debajo del plan más barato (\${$precioBasico})."
        );
    }

    public function test_la_escalera_es_creciente_y_empieza_en_free(): void
    {
        $valores = [];

        foreach (array_keys(FeatureRequest::PRICE_TIERS) as $tier) {
            $this->assertArrayHasKey(
                $tier,
                FeatureRequest::PRICE_TIER_VALUES,
                "El escalón '{$tier}' no tiene valor numérico."
            );
            $valores[] = FeatureRequest::PRICE_TIER_VALUES[$tier];
        }

        $this->assertSame(0, $valores[0], 'El primer escalón ("free") debe valer 0.');

        for ($i = 1; $i < count($valores); $i++) {
            $this->assertGreaterThan(
                $valores[$i - 1],
                $valores[$i],
                'Los escalones deben ser estrictamente crecientes.'
            );
        }
    }

    public function test_las_reglas_in_del_roadmap_aceptan_los_escalones_vigentes(): void
    {
        $regla = 'in:' . implode(',', array_keys(FeatureRequest::PRICE_TIERS));

        foreach (array_keys(FeatureRequest::PRICE_TIERS) as $tier) {
            $validator = Validator::make(['tier' => $tier], ['tier' => 'required|' . $regla]);

            $this->assertFalse(
                $validator->fails(),
                "La regla '{$regla}' debería aceptar el escalón '{$tier}'."
            );
        }

        // Un valor fuera de la escalera vigente se sigue rechazando.
        $validator = Validator::make(['tier' => '7777'], ['tier' => 'required|' . $regla]);
        $this->assertTrue($validator->fails());
    }

    public function test_los_escalones_del_pasado_no_quedan_huerfanos(): void
    {
        // Datos viejos ('49', '199', '299plus'): deben seguir resolviendo su
        // valor numérico (score) y su etiqueta (UI), aunque ya no se ofrezcan.
        foreach (FeatureRequest::LEGACY_PRICE_TIERS as $tier => $label) {
            $this->assertArrayHasKey($tier, FeatureRequest::PRICE_TIER_VALUES);
            $this->assertSame($label, FeatureRequest::tierLabel($tier));
        }

        $this->assertNull(FeatureRequest::tierLabel(null));
        $this->assertNull(FeatureRequest::tierLabel('no-existe'));
    }
}
