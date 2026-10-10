<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\PaymentPlan;
use App\Models\TreatmentPlan;
use App\Support\LoUltimoDelDiente;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La cuenta demo enseña lo que más le sirvió a los dentistas (12-oct-2026),
 * no módulos vacíos: quien la abre desde el brief tiene que ver alertas en
 * rojo, un trabajo de laboratorio por llegar, presupuestos pendientes, una
 * mensualidad vencida y lo último que se le hizo en el diente.
 */
class ElDemoEnsenaLoNuevoTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 08:00')); // miércoles
        \Illuminate\Support\Facades\Storage::fake('public');
        (new DemoSeeder())->run();
        $this->clinica = Clinic::where('slug', 'clinica-dental-sonrisas-cdmx')->firstOrFail();
    }

    public function test_hay_alergias_y_anticoagulantes_para_ver_en_rojo(): void
    {
        $this->assertTrue(Patient::withoutGlobalScopes()->where('clinic_id', $this->clinica->id)
            ->whereNotNull('allergies')->get()->contains(fn ($p) => in_array('anticoagulado', (array) $p->riesgos, true)));
    }

    public function test_un_trabajo_de_laboratorio_por_llegar_con_cita_cerca(): void
    {
        $this->actingAs(\App\Models\User::where('email', 'demo@docfacil.com')->first());

        $this->assertNotEmpty(LabOrder::enRiesgoDe($this->clinica->id));
    }

    public function test_presupuestos_sin_respuesta_y_aceptados_por_agendar(): void
    {
        $planes = TreatmentPlan::withoutGlobalScopes()->where('clinic_id', $this->clinica->id);

        $this->assertTrue((clone $planes)->where('status', 'sent')->where('sent_at', '<', now()->subDays(7))->exists());
        $this->assertTrue((clone $planes)->where('status', 'accepted')->exists());
    }

    public function test_un_plan_de_pagos_con_mensualidad_vencida(): void
    {
        $plan = PaymentPlan::withoutGlobalScopes()->where('clinic_id', $this->clinica->id)->first();

        $this->assertNotNull($plan);
        $this->assertTrue($plan->vencidas()->exists());
    }

    public function test_la_cita_de_mañana_trae_lo_ultimo_del_diente(): void
    {
        $this->actingAs(\App\Models\User::where('email', 'demo@docfacil.com')->first());
        $manana = Appointment::withoutGlobalScopes()->where('clinic_id', $this->clinica->id)
            ->whereDate('starts_at', today()->addDay())->get();

        $this->assertTrue($manana->contains(fn ($cita) => LoUltimoDelDiente::paraLaCita($cita)->isNotEmpty()));
    }
}
