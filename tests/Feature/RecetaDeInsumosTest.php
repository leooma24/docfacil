<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Service;
use App\Models\ServiceSupply;
use App\Models\Supply;
use App\Models\User;
use App\Support\SupplyScope;
use App\Support\WorkUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La receta de insumos y las cuentas del motor.
 *
 * Aquí viven las reglas clínicas que el doctor confirmó, y la más fina es la
 * de la anestesia: en el maxilar los dientes separados son dos dosis, en la
 * mandíbula el bloqueo troncular duerme el cuadrante entero y son una.
 */
class RecetaDeInsumosTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-' . uniqid(),
            'onboarding_status' => 'completed',
        ]);

        $user = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);

        $this->actingAs($user);
    }

    private function servicio(string $nombre, string $unidad): Service
    {
        return Service::create([
            'clinic_id' => $this->clinica->id,
            'name' => $nombre,
            'price' => 1000,
            'unit' => $unidad,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
    }

    private function insumo(string $nombre, ?string $categoria = null): Supply
    {
        return Supply::create([
            'clinic_id' => $this->clinica->id,
            'name' => $nombre,
            'category' => $categoria,
            'unit' => 'pieza',
        ]);
    }

    private function linea(Service $servicio, Supply $insumo, float $cantidad, ?string $scope = null, float $merma = 1): ServiceSupply
    {
        return ServiceSupply::create([
            'clinic_id' => $this->clinica->id,
            'service_id' => $servicio->id,
            'supply_id' => $insumo->id,
            'quantity' => $cantidad,
            'scope' => $scope,
            'waste_factor' => $merma,
        ]);
    }

    // ── Contar por alcance ───────────────────────────────────────

    public function test_el_conteo_por_visita_siempre_es_uno(): void
    {
        $this->assertSame(1.0, SupplyScope::count(WorkUnit::VISIT, ['16', '15', '26', '46']));
        $this->assertSame(1.0, SupplyScope::count(WorkUnit::VISIT, []));
    }

    public function test_el_conteo_por_diente_cuenta_los_dientes(): void
    {
        $this->assertSame(3.0, SupplyScope::count(WorkUnit::TOOTH, ['16', '15', '14']));
    }

    public function test_el_conteo_por_cuadrante_cuenta_los_cuadrantes_tocados(): void
    {
        $this->assertSame(1.0, SupplyScope::count(WorkUnit::QUADRANT, ['16', '14']));
        $this->assertSame(2.0, SupplyScope::count(WorkUnit::QUADRANT, ['16', '26']));
        $this->assertSame(2.0, SupplyScope::count(WorkUnit::QUADRANT, ['16', '14', '46']));
    }

    // ── La anestesia: la regla depende de la arcada ──────────────

    public function test_en_el_maxilar_los_dientes_pegados_son_una_dosis(): void
    {
        $this->assertSame(1, SupplyScope::anesthesiaZones(['16', '15', '14']));
    }

    public function test_en_el_maxilar_los_dientes_separados_son_dos_dosis(): void
    {
        // El hueso del maxilar es poroso: la infiltración baña diente por
        // diente, así que un hueco en medio pide su propia dosis.
        $this->assertSame(2, SupplyScope::anesthesiaZones(['16', '14']));
    }

    public function test_en_la_mandibula_los_dientes_separados_son_una_sola_dosis(): void
    {
        // El hueso mandibular es denso y la única vía es el bloqueo troncular,
        // que duerme el cuadrante entero del último molar al incisivo central.
        // Aquí está la diferencia con el maxilar, y es la que había que
        // preguntar: suponerla habría descontado el doble en cada curetaje.
        $this->assertSame(1, SupplyScope::anesthesiaZones(['44', '47']));
        $this->assertSame(1, SupplyScope::anesthesiaZones(['31', '38']));
    }

    public function test_los_mismos_dientes_separados_arriba_y_abajo_dan_distinto(): void
    {
        $this->assertSame(2, SupplyScope::anesthesiaZones(['14', '17']));
        $this->assertSame(1, SupplyScope::anesthesiaZones(['44', '47']));
    }

    public function test_cada_cuadrante_inferior_pide_su_propio_bloqueo(): void
    {
        $this->assertSame(2, SupplyScope::anesthesiaZones(['46', '36']));
    }

    public function test_un_curetaje_de_media_boca_pide_dos_dosis(): void
    {
        // Un lado completo: arriba y abajo del mismo lado. Son dos cuadrantes
        // distintos, así que son dos bloqueos — y la regla de cuadrante y la de
        // contigüidad coinciden, por caminos distintos.
        $this->assertSame(2, SupplyScope::anesthesiaZones(['16', '15', '14', '46', '45', '44']));
    }

    // ── Leer lo que el doctor escribe ────────────────────────────

    public function test_un_rango_fdi_se_expande_dentro_del_cuadrante(): void
    {
        $this->assertSame([46, 47], SupplyScope::teeth(['46-47']));
        // Y como son pegados, son una sola zona.
        $this->assertSame(1, SupplyScope::contiguousZones(['46-47']));
    }

    public function test_un_rango_entre_cuadrantes_no_se_expande(): void
    {
        // Del 16 al 26 no es un rango: son lados distintos de la boca. Se
        // toman los extremos y ya.
        $this->assertSame([16, 26], SupplyScope::teeth(['16-26']));
    }

    public function test_acepta_varios_dientes_separados_por_coma(): void
    {
        $this->assertSame([16, 15, 26], SupplyScope::teeth(['16, 15, 26']));
    }

    public function test_un_diente_inventado_se_ignora(): void
    {
        // 99 y 00 no existen: sin esta guardia, un dedazo contaría como un
        // diente y desviaría el inventario.
        $this->assertSame([16], SupplyScope::teeth(['16', '99', '00', '19']));
    }

    public function test_los_dientes_repetidos_cuentan_una_vez(): void
    {
        $this->assertSame(1.0, SupplyScope::count(WorkUnit::TOOTH, ['16', '16', '16']));
    }

    // ── La receta ────────────────────────────────────────────────

    public function test_el_composite_escala_con_los_dientes(): void
    {
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $this->linea($resina, $this->insumo('Composite', 'Restaurador'), 0.5);

        $receta = $resina->recipeFor(['16', '15', '14']);

        $this->assertCount(1, $receta);
        $this->assertSame(1.5, $receta[0]['quantity']);
    }

    public function test_la_punta_de_aplicacion_es_una_por_paciente(): void
    {
        // No una por diente: se tira al terminar la cita. El preset la habría
        // supuesto por diente y el inventario se desviaría 7×.
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $punta = $this->insumo('Punta de aplicación', 'Protección');
        $this->linea($resina, $punta, 1, WorkUnit::VISIT);

        $receta = $resina->recipeFor(['16', '15', '14', '13', '12', '11', '21']);

        $this->assertSame(1.0, $receta[0]['quantity']);
    }

    public function test_la_merma_se_suma_a_la_cantidad(): void
    {
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $this->linea($resina, $this->insumo('Composite'), 1, null, 1.15);

        $receta = $resina->recipeFor(['16', '15']);

        // 2 dientes × 1.15 = 2.3
        $this->assertSame(2.3, $receta[0]['quantity']);
    }

    public function test_sin_receta_no_se_descuenta_nada(): void
    {
        $servicio = $this->servicio('Limpieza', WorkUnit::VISIT);

        $this->assertSame([], $servicio->recipeFor(['16']));
    }

    // ── La anestesia dentro del curetaje: el caso que pide sobrescritura ──

    public function test_el_anestesico_de_un_curetaje_va_por_zona_y_no_por_cuadrante(): void
    {
        $curetaje = $this->servicio('Curetaje', WorkUnit::QUADRANT);
        $anestesia = $this->insumo('Anestésico', 'Anestesia');
        $linea = $this->linea($curetaje, $anestesia, 1);

        // Dos cuadrantes con los dientes separados en cada uno.
        $dientes = ['16', '14', '46', '44'];

        // Sin decir nada, la línea va como el servicio: por cuadrante. Son 2.
        $this->assertSame(WorkUnit::QUADRANT, $linea->effectiveScope());
        $this->assertSame(2.0, $curetaje->recipeFor($dientes)[0]['quantity']);

        // Pero la anestesia no escala así: arriba son 2 zonas (16 y 14 están
        // separados) y abajo 1 bloqueo por cuadrante. Total 3.
        $linea->update(['scope' => WorkUnit::CONTIGUOUS_ZONE]);

        $this->assertSame(3.0, $curetaje->fresh()->recipeFor($dientes)[0]['quantity']);
    }

    public function test_la_receta_avisa_cuando_un_insumo_necesita_otro_alcance(): void
    {
        $curetaje = $this->servicio('Curetaje', WorkUnit::QUADRANT);
        $anestesia = $this->insumo('Anestésico', 'Anestesia');
        $linea = $this->linea($curetaje, $anestesia, 1);

        // Su categoría dice "por zona contigua" y está como el servicio
        // (por cuadrante): hay que revisarlo.
        $this->assertTrue($linea->needsScopeReview());
        $this->assertSame(WorkUnit::CONTIGUOUS_ZONE, $linea->suggestedScope());
        $this->assertSame(1, $curetaje->recipeLinesNeedingReview());

        $linea->update(['scope' => WorkUnit::CONTIGUOUS_ZONE]);

        $this->assertFalse($linea->fresh()->needsScopeReview());
        $this->assertSame(0, $curetaje->fresh()->recipeLinesNeedingReview());
    }

    public function test_una_categoria_que_no_dice_nada_no_pide_revision(): void
    {
        // "Instrumental" puede ser cualquier cosa. Adivinar sería peor que
        // preguntar, así que no se sugiere nada y no se marca.
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $linea = $this->linea($resina, $this->insumo('Fresa', 'Instrumental'), 1);

        $this->assertNull($linea->suggestedScope());
        $this->assertFalse($linea->needsScopeReview());
    }

    public function test_el_preset_acierta_por_categoria(): void
    {
        $this->assertSame(WorkUnit::VISIT, WorkUnit::scopeSuggestedByCategory('Protección'));
        $this->assertSame(WorkUnit::CONTIGUOUS_ZONE, WorkUnit::scopeSuggestedByCategory('Anestesia'));
        $this->assertSame(WorkUnit::TOOTH, WorkUnit::scopeSuggestedByCategory('Restaurador'));
        $this->assertSame(WorkUnit::QUADRANT, WorkUnit::scopeSuggestedByCategory('Periodoncia'));
        $this->assertNull(WorkUnit::scopeSuggestedByCategory('Instrumental'));
        $this->assertNull(WorkUnit::scopeSuggestedByCategory(null));
    }

    public function test_una_linea_opcional_no_vuelve_opcional_al_insumo(): void
    {
        $resina = $this->servicio('Resina', WorkUnit::TOOTH);
        $composite = $this->insumo('Composite');
        $this->linea($resina, $composite, 0.5);

        $this->assertFalse($resina->recipeFor(['16'])[0]['optional']);
    }

    public function test_dos_servicios_no_comparten_receta(): void
    {
        $uno = $this->servicio('Resina', WorkUnit::TOOTH);
        $dos = $this->servicio('Limpieza', WorkUnit::VISIT);

        $this->linea($uno, $this->insumo('Composite'), 0.5);

        $this->assertCount(1, $uno->recipeFor(['16']));
        $this->assertCount(0, $dos->recipeFor(['16']));
    }
}
