<?php

namespace App\Filament\Doctor\Resources\LabOrderResource\Pages;

use App\Filament\Doctor\Resources\LabOrderResource;
use App\Models\LabOrder;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLabOrders extends ListRecords
{
    protected static string $resource = LabOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Nueva orden')];
    }

    /** Cuánto se le debe al laboratorio (solo quien ve el dinero), y qué falta por llegar. */
    public function getSubheading(): ?string
    {
        $clinicId = auth()->user()->clinic_id;
        $porLlegar = LabOrder::where('clinic_id', $clinicId)->whereNull('llego_at')->count();
        $atrasadas = LabOrder::atrasadasDe($clinicId)->count();

        $partes = [$porLlegar . ($porLlegar === 1 ? ' por llegar' : ' por llegar')];
        if ($atrasadas > 0) {
            $partes[] = $atrasadas . ($atrasadas === 1 ? ' atrasado' : ' atrasados');
        }

        if (auth()->user()->veElDinero()) {
            $deuda = LabOrder::loQueSeDebe($clinicId);
            if ($deuda['total'] > 0) {
                array_unshift($partes, 'Se le debe al laboratorio $' . number_format($deuda['total'], 0)
                    . (count($deuda['porLaboratorio']) > 1
                        ? ' (' . collect($deuda['porLaboratorio'])->map(fn ($v, $lab) => "{$lab} $" . number_format($v, 0))->implode(' · ') . ')'
                        : ''));
            }
        }

        return implode(' · ', $partes);
    }
}
