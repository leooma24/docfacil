<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Supply;
use App\Models\SupplyLot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los lotes y el aviso de caducidad.
 *
 * La caducidad es de las mermas que más duele, porque se pudo evitar. Pero
 * avisar mal es peor que no avisar: un lote que ya se consumió no puede seguir
 * apareciendo como "por caducar", y uno sin fecha no se puede suponer vencido.
 */
class LotesYCaducidadesTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Supply $insumo;

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

        $this->insumo = Supply::create([
            'clinic_id' => $this->clinica->id,
            'name' => 'Composite A2',
            'category' => 'Restaurador',
            'unit' => 'jeringa',
        ]);
    }

    /** Un lote con su entrada correspondiente. */
    private function lote(float $cantidad, ?string $caducaEl, ?string $nombre = null): SupplyLot
    {
        $lote = SupplyLot::create([
            'clinic_id' => $this->clinica->id,
            'supply_id' => $this->insumo->id,
            'lot_number' => $nombre,
            'expires_on' => $caducaEl,
            'quantity' => $cantidad,
        ]);

        $this->insumo->register('in', $cantidad, ['supply_lot_id' => $lote->id]);

        return $lote;
    }

    // ── El reparto FEFO ──────────────────────────────────────────

    public function test_sin_lotes_todo_el_stock_es_sin_lote(): void
    {
        $this->insumo->register('in', 10);

        $reparto = $this->insumo->fefoAllocation();

        $this->assertCount(0, $reparto['lots']);
        $this->assertSame(10.0, $reparto['untracked']);
    }

    public function test_el_consumo_se_come_primero_lo_que_caduca_antes(): void
    {
        // En el anaquel se usa primero lo que vence primero. El reparto tiene
        // que decir lo mismo, o el aviso señalaría el lote equivocado.
        $tarde = $this->lote(10, now()->addYear()->toDateString(), 'TARDE');
        $pronto = $this->lote(10, now()->addMonth()->toDateString(), 'PRONTO');

        $this->insumo->register('out', 6);

        $reparto = $this->insumo->fefoAllocation();
        $porLote = collect($reparto['lots'])->keyBy('lot_number');

        $this->assertSame(4.0, $porLote['PRONTO']->remaining);
        $this->assertSame(10.0, $porLote['TARDE']->remaining);
    }

    public function test_el_consumo_que_sobra_pasa_al_siguiente_lote(): void
    {
        $this->lote(10, now()->addMonth()->toDateString(), 'PRONTO');
        $this->lote(10, now()->addYear()->toDateString(), 'TARDE');

        $this->insumo->register('out', 14);

        $porLote = collect($this->insumo->fefoAllocation()['lots'])->keyBy('lot_number');

        $this->assertSame(0.0, $porLote['PRONTO']->remaining);
        $this->assertSame(6.0, $porLote['TARDE']->remaining);
    }

    public function test_el_stock_sin_lote_se_consume_al_final(): void
    {
        // Lo que no tiene fecha no caduca para el sistema, así que va después.
        $this->lote(10, now()->addMonth()->toDateString(), 'CON FECHA');
        $this->insumo->register('in', 20);

        $this->insumo->register('out', 12);

        $reparto = $this->insumo->fefoAllocation();

        $this->assertSame(0.0, $reparto['lots']->first()->remaining);
        $this->assertSame(18.0, $reparto['untracked']);
    }

    public function test_el_consumo_de_antes_no_vacia_los_lotes_nuevos(): void
    {
        // El caso normal, no el raro: el consultorio lleva meses moviendo
        // inventario y apenas ahora empieza a capturar caducidades. Ese
        // consumo de antes no pudo salir de un lote que todavía no existía,
        // así que no puede vaciarlo. Si lo vacía, el lote nace con cero
        // existencia y el aviso de caducidad no sale nunca.
        $this->insumo->register('in', 500);
        $this->insumo->register('out', 500);

        $this->lote(100, now()->addDays(10)->toDateString(), 'NUEVO');

        $reparto = $this->insumo->fefoAllocation();

        $this->assertSame(100.0, (float) $reparto['lots']->first()->remaining);
        $this->assertCount(1, $this->insumo->lotsExpiringSoon(30));
    }

    public function test_la_merma_tambien_consume_lotes(): void
    {
        $this->lote(10, now()->addMonth()->toDateString(), 'CON FECHA');

        $this->insumo->register('waste', 3, ['waste_reason' => 'expired']);

        $this->assertSame(7.0, $this->insumo->fefoAllocation()['lots']->first()->remaining);
    }

    public function test_el_reparto_cuadra_con_el_stock_total(): void
    {
        // La invariante que hace confiable todo lo demás: lo que queda en los
        // lotes más lo que no tiene lote tiene que ser el stock.
        $this->lote(10, now()->addMonth()->toDateString());
        $this->lote(5, now()->addYear()->toDateString());
        $this->insumo->register('in', 20);
        $this->insumo->register('out', 12);
        $this->insumo->register('waste', 3, ['waste_reason' => 'other']);

        $reparto = $this->insumo->fefoAllocation();

        $enLotes = collect($reparto['lots'])->sum('remaining');

        $this->assertSame(
            $this->insumo->fresh()->currentStock(),
            round($enLotes + $reparto['untracked'], 3),
        );
    }

    // ── El aviso ─────────────────────────────────────────────────

    public function test_avisa_de_un_lote_por_caducar(): void
    {
        $this->lote(10, now()->addDays(10)->toDateString(), 'PRONTO');

        $porCaducar = $this->insumo->lotsExpiringSoon(30);

        $this->assertCount(1, $porCaducar);
        $this->assertSame('PRONTO', $porCaducar->first()->lot_number);
    }

    public function test_un_lote_lejano_no_avisa(): void
    {
        $this->lote(10, now()->addYear()->toDateString());

        $this->assertCount(0, $this->insumo->lotsExpiringSoon(30));
    }

    public function test_un_lote_sin_fecha_no_avisa(): void
    {
        // No se sabe cuándo caduca, y suponerlo llenaría la pantalla de avisos
        // falsos. Callarse es lo correcto.
        $this->lote(10, null);

        $this->assertCount(0, $this->insumo->lotsExpiringSoon(365));
    }

    public function test_un_lote_ya_consumido_no_avisa(): void
    {
        // Es el aviso que más estorbaría: mandar a revisar un frasco que ya se
        // acabó. El reparto FEFO es justo lo que lo evita.
        $lote = $this->lote(10, now()->addDays(10)->toDateString(), 'PRONTO');

        $this->assertCount(1, $this->insumo->lotsExpiringSoon(30));

        $this->insumo->register('out', 10);

        $this->assertCount(0, $this->insumo->lotsExpiringSoon(30));
        $this->assertSame(0.0, $lote->fresh() ? collect($this->insumo->fefoAllocation()['lots'])->first()->remaining : -1);
    }

    public function test_un_lote_vencido_no_cuenta_como_por_caducar(): void
    {
        // Ya caducó: eso es una merma, no un aviso. El aviso es para alcanzar
        // a usarlo.
        $this->lote(10, now()->subDays(5)->toDateString());

        $this->assertCount(0, $this->insumo->lotsExpiringSoon(30));

        $lote = $this->insumo->lots()->first();
        $this->assertTrue($lote->isExpired());
        $this->assertFalse($lote->expiresWithin(30));
    }

    public function test_la_caducidad_mas_proxima_es_la_del_lote_que_queda(): void
    {
        $this->lote(10, now()->addDays(5)->toDateString(), 'A');
        $this->lote(10, now()->addDays(50)->toDateString(), 'B');

        $this->assertSame(
            now()->addDays(5)->toDateString(),
            $this->insumo->nextExpiry()->toDateString(),
        );

        // Se acaba el primero: ahora la próxima es la del segundo.
        $this->insumo->register('out', 10);

        $this->assertSame(
            now()->addDays(50)->toDateString(),
            $this->insumo->nextExpiry()->toDateString(),
        );
    }

    public function test_la_etiqueta_del_lote_dice_lo_que_hace_falta(): void
    {
        $conTodo = $this->lote(1, '2027-03-15', 'ABC-123');
        $sinNada = $this->lote(1, null);

        $this->assertStringContainsString('Lote ABC-123', $conTodo->label());
        $this->assertStringContainsString('caduca 15/03/2027', $conTodo->label());
        $this->assertSame('Sin lote ni caducidad', $sinNada->label());
    }

    public function test_los_lotes_quedan_aislados_por_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otro', 'slug' => 'otro-' . uniqid(), 'onboarding_status' => 'completed']);

        $this->lote(10, now()->addDays(5)->toDateString());

        SupplyLot::create([
            'clinic_id' => $otra->id,
            'supply_id' => $this->insumo->id,
            'quantity' => 5,
            'expires_on' => now()->addDays(5),
        ]);

        $this->assertSame(1, SupplyLot::count());
    }
}
