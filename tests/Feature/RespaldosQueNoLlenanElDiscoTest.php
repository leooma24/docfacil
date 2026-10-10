<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

/**
 * Los respaldos se limpian solos (12-oct-2026). El respaldo diario corría
 * desde abril, pero nadie borraba los viejos: 190 respaldos, 7.1 GB, el disco
 * del servidor al 81% y creciendo 64 MB al día. Al llenarse se caían las
 * cinco apps del servidor, no solo DocFácil.
 */
class RespaldosQueNoLlenanElDiscoTest extends TestCase
{
    /** @return array<string, string> comando => expresión cron */
    private function programados(): array
    {
        return collect(app(Schedule::class)->events())
            ->mapWithKeys(fn ($e) => [trim(preg_replace('/^.*artisan["\']?\s+/', '', $e->command)) => $e->expression])
            ->all();
    }

    public function test_el_respaldo_corre_cada_dia(): void
    {
        $this->assertSame('0 3 * * *', $this->programados()['backup:run'] ?? null);
    }

    public function test_los_respaldos_viejos_se_borran_cada_dia_antes_del_nuevo(): void
    {
        $this->assertSame('30 2 * * *', $this->programados()['backup:clean'] ?? null);
    }

    public function test_se_revisa_que_los_respaldos_esten_sanos(): void
    {
        $this->assertArrayHasKey('backup:monitor', $this->programados());
    }

    public function test_la_limpieza_deja_semanas_y_meses_y_no_pasa_de_5_gb(): void
    {
        $limpieza = config('backup.cleanup.default_strategy');

        $this->assertSame(7, $limpieza['keep_all_backups_for_days']);
        $this->assertGreaterThanOrEqual(4, $limpieza['keep_monthly_backups_for_months']);
        $this->assertLessThanOrEqual(5000, $limpieza['delete_oldest_backups_when_using_more_megabytes_than']);
    }
}
