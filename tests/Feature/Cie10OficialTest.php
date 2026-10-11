<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

/**
 * El CIE-10 oficial de la DGIS (11-oct-2026). El que teníamos eran 391
 * códigos de 3 caracteres armados a mano; la NOM-024 (6.4.2) pide el catálogo
 * oficial. El comando lo convierte del Excel que publica la DGIS
 * (DIAGNOSTICOS_AAAAMMDD.xlsx) al JSON que usa la búsqueda.
 */
class Cie10OficialTest extends TestCase
{
    use RefreshDatabase;

    private function excelDePrueba(): string
    {
        $ruta = sys_get_temp_dir() . '/cie10-prueba-' . uniqid() . '.xlsx';
        $w = new Writer();
        $w->openToFile($ruta);
        $w->addRow(Row::fromValues(['CONSECUTIVO', 'LETRA', 'CATALOG_KEY', 'NO. CARACTERES', 'NOMBRE', 'CODIGOX', 'LSEX']));
        $w->addRow(Row::fromValues([1, 'K', 'K02', 3, 'CARIES DENTAL', 'NO', 'NO']));
        $w->addRow(Row::fromValues([2, 'K', 'K021', 4, 'CARIES DE LA DENTINA', 'NO', 'NO']));
        $w->addRow(Row::fromValues([3, 'O', 'O800', 4, 'PARTO ÚNICO ESPONTÁNEO, PRESENTACIÓN CEFÁLICA DE VÉRTICE', 'NO', 'MUJER']));
        $w->close();

        return $ruta;
    }

    public function test_convierte_el_excel_oficial_al_catalogo(): void
    {
        $salida = sys_get_temp_dir() . '/cie10-' . uniqid() . '.json';

        $this->artisan('docfacil:cie10', ['excel' => $this->excelDePrueba(), '--salida' => $salida])->assertSuccessful();

        $catalogo = json_decode(file_get_contents($salida), true);
        $this->assertSame([
            ['code' => 'K02', 'name' => 'Caries dental'],
            ['code' => 'K02.1', 'name' => 'Caries de la dentina'],
            ['code' => 'O80.0', 'name' => 'Parto único espontáneo, presentación cefálica de vértice', 'sexo' => 'mujer'],
        ], $catalogo);
    }

    public function test_el_catalogo_del_proyecto_es_el_oficial(): void
    {
        $catalogo = json_decode(file_get_contents(base_path('database/data/cie10-mx.json')), true);

        $this->assertGreaterThan(14000, count($catalogo));
        $codigos = array_column($catalogo, 'code');
        $this->assertContains('K02.1', $codigos);
        $this->assertContains('K08.1', $codigos, 'Pérdida de dientes por accidente, extracción o enfermedad periodontal local');
    }

    public function test_al_buscar_salen_primero_los_de_la_boca(): void
    {
        $clinica = Clinic::create(['name' => 'Consultorio', 'onboarding_status' => 'completed']);
        $this->actingAs(User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'clinic_id' => $clinica->id]));

        $codigos = collect($this->get('/api/cie10/search?q=caries')->assertOk()->json())->pluck('code');

        $this->assertStringStartsWith('K02', $codigos->first());
    }
}
