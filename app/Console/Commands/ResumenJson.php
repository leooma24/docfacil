<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Models\Prospect;
use App\Support\CargaDeTrabajo;
use App\Support\MensajesDeVenta;
use App\Support\SiguientePaso;
use Illuminate\Console\Command;

/**
 * Las ventas de DocFácil en JSON, para el tablero de Omar (cactus-seguimiento).
 *
 * El tablero junta en su Mac todos sus proyectos y trae esto por ssh. Solo
 * lee: lo que se marca en el CRM lo marca Omar desde /ventas.
 */
class ResumenJson extends Command
{
    protected $signature = 'docfacil:resumen-json {--rep= : id del vendedor (si hay uno solo, se toma ese)}';

    protected $description = 'Ventas y activación de DocFácil en JSON para el tablero de Omar (solo lee)';

    public function handle(): int
    {
        $rep = VentasHoy::vendedor($this->option('rep'));
        if (! $rep) {
            $this->error('No sé de qué vendedor. Use --rep=<id>.');

            return self::FAILURE;
        }

        $numeros = CargaDeTrabajo::numeros($rep->id);
        $activacion = CargaDeTrabajo::activacion($rep->id);

        $datos = [
            'generado' => now()->toIso8601String(),
            'vendedor' => $rep->name,
            'numeros' => collect($numeros)->except('embudo')->all(),
            'embudo' => $numeros['embudo'],
            'activacion' => [
                'registrados' => $activacion['registrados'],
                'entraron' => $activacion['entraron'],
                'configurados' => $activacion['configurados'],
                'usan_esta_semana' => $activacion['usanEstaSemana'],
                'sin_entrar' => $activacion['sinEntrar']->map(fn (Clinic $c) => [
                    'nombre' => $c->name,
                    'registrado' => $c->created_at?->toDateString(),
                ])->all(),
            ],
            'contestaron' => CargaDeTrabajo::contestaron($rep->id)
                ->reject(fn (Prospect $p) => in_array($p->status, ['lost', 'converted'], true))
                ->map(fn (Prospect $p) => [
                    'nombre' => $p->name,
                    'consultorio' => $p->clinic_name,
                    'contesto' => $p->replied_at?->toIso8601String(),
                    'dijo' => VentasHoy::notas($p)['dijo'],
                    'siguiente' => SiguientePaso::para($p)['que'],
                ])->values()->all(),
            'seguimientos' => CargaDeTrabajo::seguimientos($rep->id)->count(),
            'primer_contacto' => CargaDeTrabajo::primerContacto($rep->id)->count(),
            'por_verificar' => CargaDeTrabajo::porVerificar($rep->id)->count(),
            'promocion_fundador' => CargaDeTrabajo::promocionFundador($rep->id)->count(),
            'fundadores' => Clinic::lugaresDeFundador(),
            'mensajes' => MensajesDeVenta::resultados($rep->id),
            'tareas' => CargaDeTrabajo::tareasDeHoy($rep->id),
        ];

        $this->line(json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
