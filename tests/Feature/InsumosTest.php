<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Expense;
use App\Models\Supply;
use App\Models\SupplyMovement;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El kardex de insumos.
 *
 * Lo que esta fase tiene que garantizar es que el número que el doctor ve sea
 * cierto. Por eso el stock no es una columna: se suma de los movimientos, y
 * las pruebas están para que siga siendo así cuando la fase 2 agregue el
 * descuento automático por consulta.
 */
class InsumosTest extends TestCase
{
    use RefreshDatabase;

    private function consultorio(string $nombre = 'Consultorio Test'): Clinic
    {
        return Clinic::create([
            'name' => $nombre,
            'slug' => 'consultorio-' . uniqid(),
            'plan' => 'profesional',
            'plan_ends_at' => now()->addYear(),
            'onboarding_status' => 'completed',
        ]);
    }

    private function doctor(Clinic $clinica): User
    {
        return User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doc' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $clinica->id,
        ]);
    }

    private function insumo(Clinic $clinica, array $atributos = []): Supply
    {
        return Supply::create(array_merge([
            'clinic_id' => $clinica->id,
            'name' => 'Guantes de nitrilo',
            'unit' => 'pieza',
            'purchase_unit' => 'caja',
            'units_per_purchase' => 50,
        ], $atributos));
    }

    // ── El stock sale del kardex ─────────────────────────────────

    public function test_una_entrada_suma_al_stock(): void
    {
        $insumo = $this->insumo($this->consultorio());

        $insumo->register('in', 100);

        $this->assertSame(100.0, $insumo->fresh()->currentStock());
    }

    public function test_una_salida_resta_del_stock(): void
    {
        $insumo = $this->insumo($this->consultorio());

        $insumo->register('in', 100);
        $insumo->register('out', 30);

        $this->assertSame(70.0, $insumo->fresh()->currentStock());
    }

    public function test_la_merma_tambien_resta(): void
    {
        // La merma no es una venta ni un uso: es material que se perdió. Resta
        // igual, pero se cuenta aparte para poder ver el desperdicio.
        $insumo = $this->insumo($this->consultorio());

        $insumo->register('in', 100);
        $insumo->register('waste', 12);

        $this->assertSame(88.0, $insumo->fresh()->currentStock());
    }

    public function test_el_stock_es_la_suma_de_todo_el_kardex(): void
    {
        $insumo = $this->insumo($this->consultorio());

        $insumo->register('in', 50);
        $insumo->register('in', 30);
        $insumo->register('out', 20);
        $insumo->register('waste', 5);
        $insumo->register('out', 15);

        $this->assertSame(40.0, $insumo->fresh()->currentStock());
    }

    public function test_el_stock_de_la_tabla_coincide_con_el_del_modelo(): void
    {
        // scopeConStock() existe para no hacer una consulta por fila. Si se
        // equivoca, la tabla enseña un número y el detalle otro — que es la
        // forma más rápida de que nadie vuelva a confiar en el inventario.
        $insumo = $this->insumo($this->consultorio());
        $insumo->register('in', 120);
        $insumo->register('out', 45);

        $enTabla = Supply::withStock()->find($insumo->id);

        $this->assertSame($insumo->fresh()->currentStock(), $enTabla->stockOnHand());
        $this->assertSame(75.0, $enTabla->stockOnHand());
        // Y lo demás de la fila sigue viniendo completo.
        $this->assertSame('Guantes de nitrilo', $enTabla->name);
        $this->assertNotNull($enTabla->id);
    }

    // ── Las guardias de la puerta de escritura ───────────────────

    public function test_una_cantidad_negativa_se_rechaza(): void
    {
        // El signo lo pone el tipo, nunca el número: una salida de -5 sumaría
        // cinco al inventario y no se vería en ningún reporte.
        $insumo = $this->insumo($this->consultorio());

        $this->expectException(\InvalidArgumentException::class);

        $insumo->register('out', -5);
    }

    public function test_una_cantidad_de_cero_se_rechaza(): void
    {
        $insumo = $this->insumo($this->consultorio());

        $this->expectException(\InvalidArgumentException::class);

        $insumo->register('in', 0);
    }

    public function test_un_tipo_de_movimiento_inventado_se_rechaza(): void
    {
        $insumo = $this->insumo($this->consultorio());

        $this->expectException(\InvalidArgumentException::class);

        $insumo->register('prestamo', 10);
    }

    public function test_el_mismo_gasto_no_puede_entrar_dos_veces(): void
    {
        // La guardia de idempotencia: en la fase 2 esto es lo que impide que
        // una consulta reabierta y vuelta a cerrar descuente doble.
        $insumo = $this->insumo($this->consultorio());
        $referencia = ['reference_type' => Expense::class, 'reference_id' => 99];

        $insumo->register('in', 10, $referencia);

        $this->expectException(QueryException::class);

        $insumo->register('in', 10, $referencia);
    }

    public function test_dos_capturas_manuales_sin_referencia_no_chocan(): void
    {
        // Los NULL son distintos entre sí en un índice único: dos ajustes
        // manuales el mismo día son legítimos.
        $insumo = $this->insumo($this->consultorio());

        $insumo->register('in', 10);
        $insumo->register('in', 10);

        $this->assertSame(20.0, $insumo->fresh()->currentStock());
    }

    // ── Comprar por caja, gastar por pieza ───────────────────────

    public function test_una_caja_de_guantes_son_cincuenta_guantes(): void
    {
        $insumo = $this->insumo($this->consultorio(), ['units_per_purchase' => 50]);

        $this->assertSame(100.0, $insumo->toConsumptionUnits(2));
    }

    public function test_el_costo_por_pieza_sale_del_precio_de_la_caja(): void
    {
        // Caja de 50 guantes a $250: cada guante sale en $5.
        $insumo = $this->insumo($this->consultorio(), ['units_per_purchase' => 50]);

        $this->assertSame(5.0, $insumo->costPerConsumptionUnit(250, 1));
    }

    public function test_un_insumo_que_se_compra_y_se_gasta_igual_no_se_divide(): void
    {
        $insumo = $this->insumo($this->consultorio(), [
            'unit' => 'cartucho',
            'purchase_unit' => 'cartucho',
            'units_per_purchase' => 1,
        ]);

        $this->assertSame(3.0, $insumo->toConsumptionUnits(3));
        $this->assertSame(90.0, $insumo->costPerConsumptionUnit(270, 3));
    }

    public function test_el_costo_de_una_entrada_sale_del_total_pagado(): void
    {
        // Caja de 50 guantes a $250: entraron 50 guantes, cada uno a $5. Aquí
        // la cantidad ya viene en unidad de consumo, que es como la captura
        // el doctor — no en la unidad en que compró.
        $insumo = $this->insumo($this->consultorio(), ['units_per_purchase' => 50]);

        $this->assertSame(5.0, $insumo->entryCost(250, 50));
        $this->assertSame(3.5, $insumo->entryCost(175, 50));
    }

    public function test_un_costo_de_entrada_sin_cantidad_no_revienta(): void
    {
        $insumo = $this->insumo($this->consultorio());

        $this->assertSame(0.0, $insumo->entryCost(250, 0));
    }

    // ── La precisión del costo ───────────────────────────────────
    //
    // El costo es por unidad de consumo (un mililitro, un guante), no por
    // caja. Un insumo de $0.008 el ml es normal —el hipoclorito, por
    // ejemplo— y a dos decimales se guarda como $0.01: 25% de error,
    // multiplicado por cada consulta.
    //
    // Para que el número llegue entero hacen falta dos cosas: el cast a
    // `decimal:4` y el redondeo a 4 en el cálculo. El cast redondea al leer y
    // al escribir, pero no sirve de nada si el cálculo ya recortó antes.
    //
    // Ojo con lo que estas pruebas alcanzan a cubrir: el redondeo y el cast,
    // que son PHP. El ancho de la columna no se prueba aquí porque SQLite no
    // recorta los decimales —eso solo se ve en MySQL, como avisa el CLAUDE.md
    // del repo—; de eso se encarga la migración
    // 2026_09_23_100000_widen_inventory_columns.

    public function test_el_costo_por_mililitro_conserva_sus_cuatro_decimales(): void
    {
        // Bidón de $3,000 con 375,000 ml: $0.008 el ml.
        $insumo = $this->insumo($this->consultorio(), [
            'unit' => 'ml',
            'purchase_unit' => 'ml',
            'units_per_purchase' => 1,
        ]);

        $this->assertSame(0.008, $insumo->costPerConsumptionUnit(3000, 375000));
        $this->assertSame(0.008, $insumo->entryCost(3000, 375000));
    }

    public function test_un_costo_barato_sobrevive_el_viaje_a_la_base(): void
    {
        $insumo = $this->insumo($this->consultorio(), [
            'unit' => 'ml',
            'purchase_unit' => 'ml',
            'units_per_purchase' => 1,
        ]);

        $movimiento = $insumo->register('in', 375000, ['unit_cost' => 0.008]);

        // Se relee de la base, que es donde el cast hace su trabajo.
        $this->assertEqualsWithDelta(0.008, (float) $movimiento->fresh()->unit_cost, 0.00001);
    }

    public function test_el_costo_del_catalogo_tambien_guarda_cuatro_decimales(): void
    {
        $insumo = $this->insumo($this->consultorio(), [
            'unit' => 'ml',
            'cost_per_unit' => 0.008,
        ]);

        $this->assertEqualsWithDelta(0.008, (float) $insumo->fresh()->cost_per_unit, 0.00001);
    }

    public function test_el_costo_de_un_lote_tambien_guarda_cuatro_decimales(): void
    {
        $insumo = $this->insumo($this->consultorio(), ['unit' => 'ml']);

        $lote = $insumo->lots()->create([
            'clinic_id' => $insumo->clinic_id,
            'lot_number' => 'HIP-0001',
            'expires_on' => now()->addYear(),
            'quantity' => 375000,
            'unit_cost' => 0.008,
        ]);

        $this->assertEqualsWithDelta(0.008, (float) $lote->fresh()->unit_cost, 0.00001);
    }

    // ── No se borra la historia ──────────────────────────────────

    public function test_un_insumo_con_kardex_no_se_borra(): void
    {
        // Un movimiento es un hecho: pasó, alguien lo capturó, y el stock que
        // hay hoy depende de él. Borrar el insumo no puede reescribir eso.
        $insumo = $this->insumo($this->consultorio());
        $insumo->register('in', 10);

        $insumo->delete();

        $this->assertDatabaseHas('supplies', ['id' => $insumo->id]);
        $this->assertSame(1, SupplyMovement::where('supply_id', $insumo->id)->count());
    }

    public function test_un_insumo_sin_kardex_si_se_borra(): void
    {
        // El que se capturó mal y nunca se usó no tiene historia que cuidar.
        $insumo = $this->insumo($this->consultorio());

        $insumo->delete();

        $this->assertDatabaseMissing('supplies', ['id' => $insumo->id]);
    }

    public function test_borrar_un_insumo_tampoco_se_lleva_sus_lotes(): void
    {
        $insumo = $this->insumo($this->consultorio());
        $insumo->register('in', 10);

        $lote = $insumo->lots()->create([
            'clinic_id' => $insumo->clinic_id,
            'lot_number' => 'L-1',
            'expires_on' => now()->addYear(),
            'quantity' => 10,
        ]);

        $insumo->delete();

        $this->assertDatabaseHas('supply_lots', ['id' => $lote->id]);
    }

    // ── El punto de reorden ──────────────────────────────────────

    public function test_sin_punto_de_reorden_no_hay_alerta(): void
    {
        // Un insumo sin min_stock definido no debe llenar la pantalla de avisos.
        $insumo = $this->insumo($this->consultorio(), ['min_stock' => 0]);
        $insumo->register('in', 1);
        $insumo->register('out', 1);

        $this->assertFalse($insumo->fresh()->belowMinimum());
    }

    public function test_avisa_al_pegar_en_el_punto_de_reorden(): void
    {
        $insumo = $this->insumo($this->consultorio(), ['min_stock' => 20]);

        $insumo->register('in', 100);
        $this->assertFalse($insumo->fresh()->belowMinimum());

        // Exactamente en el mínimo ya avisa: el punto de reorden es "pide
        // ahora", no "ya casi".
        $insumo->register('out', 80);
        $this->assertTrue($insumo->fresh()->belowMinimum());

        $insumo->register('out', 10);
        $this->assertTrue($insumo->fresh()->belowMinimum());
    }

    // ── Cada consultorio ve lo suyo ──────────────────────────────

    public function test_un_consultorio_no_ve_los_insumos_de_otro(): void
    {
        $uno = $this->consultorio('Uno');
        $dos = $this->consultorio('Dos');

        $this->insumo($uno, ['name' => 'Composite']);
        $this->insumo($dos, ['name' => 'Anestésico']);

        $this->actingAs($this->doctor($uno));

        $this->assertSame(1, Supply::count());
        $this->assertSame('Composite', Supply::first()->name);
    }

    public function test_el_kardex_tambien_queda_aislado_por_consultorio(): void
    {
        $uno = $this->consultorio('Uno');
        $dos = $this->consultorio('Dos');

        $this->insumo($uno)->register('in', 10);
        $this->insumo($dos)->register('in', 99);

        $this->actingAs($this->doctor($uno));

        $this->assertSame(1, SupplyMovement::count());
        $this->assertSame(10.0, (float) SupplyMovement::first()->quantity);
    }

    public function test_el_movimiento_guarda_quien_lo_capturo(): void
    {
        $clinica = $this->consultorio();
        $insumo = $this->insumo($clinica);

        $this->actingAs($this->doctor($clinica));
        $movimiento = $insumo->register('in', 25);

        $this->assertNotNull($movimiento->user_id);
        $this->assertSame($clinica->id, $movimiento->clinic_id);
        $this->assertSame($insumo->id, $movimiento->supply_id);
    }

    // ── La merma y su motivo ─────────────────────────────────────

    public function test_la_merma_guarda_su_motivo_y_baja_el_stock(): void
    {
        $insumo = $this->insumo($this->consultorio());
        $insumo->register('in', 10);

        $merma = $insumo->register('waste', 2, ['waste_reason' => 'expired']);

        $this->assertTrue($merma->isWaste());
        $this->assertSame('Caducó', $merma->wasteReasonLabel());
        $this->assertSame(8.0, $insumo->fresh()->currentStock());
    }

    public function test_un_motivo_de_merma_inventado_se_rechaza(): void
    {
        $insumo = $this->insumo($this->consultorio());

        $this->expectException(\InvalidArgumentException::class);

        $insumo->register('waste', 1, ['waste_reason' => 'se me antojó']);
    }

    public function test_la_merma_congela_el_costo_del_catalogo(): void
    {
        // Sin costo no hay nada que deducir. Se congela el del insumo al
        // momento, para que el reporte del contador cuadre.
        $insumo = $this->insumo($this->consultorio(), ['cost_per_unit' => 12.5]);
        $insumo->register('in', 10);

        $merma = $insumo->register('waste', 3, ['waste_reason' => 'lost']);

        // Se compara el número, no el texto: la columna lleva 4 decimales
        // porque el costo es por unidad de consumo (un ml, un guante).
        $this->assertEquals(12.50, (float) $merma->unit_cost);
        $this->assertSame(37.5, $merma->value());
    }

    public function test_el_costo_congelado_no_se_mueve_si_sube_el_catalogo(): void
    {
        // Lo que se fue en marzo vale lo que valía en marzo.
        $insumo = $this->insumo($this->consultorio(), ['cost_per_unit' => 10]);
        $insumo->register('in', 5);

        $merma = $insumo->register('waste', 1, ['waste_reason' => 'other']);
        $insumo->update(['cost_per_unit' => 99]);

        $this->assertSame(10.0, $merma->fresh()->value());
    }

    public function test_una_merma_puede_venir_sin_motivo(): void
    {
        // Las mermas que ya existían no lo tienen, y el campo no puede
        // romperlas. En la pantalla sí se pide: sin motivo no se deduce.
        $insumo = $this->insumo($this->consultorio());
        $insumo->register('in', 5);

        $merma = $insumo->register('waste', 1);

        $this->assertNull($merma->waste_reason);
        $this->assertSame('Sin motivo', $merma->wasteReasonLabel());
    }

    public function test_la_merma_normativa_no_es_un_movimiento_de_inventario(): void
    {
        // El sobrante de amalgama y los restos extraídos ya salieron del
        // inventario cuando se mezclaron. Registrarlos como salida descontaría
        // dos veces lo mismo: son cumplimiento (Minamata), no kardex.
        $this->assertArrayNotHasKey('normativa', SupplyMovement::WASTE_REASONS);
        $this->assertCount(4, SupplyMovement::WASTE_REASONS);
    }

    public function test_el_valor_tambien_se_calcula_en_una_entrada(): void
    {
        $insumo = $this->insumo($this->consultorio());
        $entrada = $insumo->register('in', 4, ['unit_cost' => 25]);

        $this->assertSame(100.0, $entrada->value());
    }
}
