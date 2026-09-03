<?php

namespace App\Filament\Doctor\Resources\ExpenseResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\ExpenseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    use HasFormHero;

    protected static string $resource = ExpenseResource::class;

    protected static string $view = 'filament.doctor.resources.create-with-hero';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Nuevo gasto',
            'icon'     => '💸',
            'kicker'   => '➕ Registrar gasto',
            'subtitle' => 'Renta, materiales, laboratorio, sueldos. Lo que sale del consultorio.',
            'gradient' => '#b45309 0%, #d97706 40%, #f59e0b 100%',
            'accent'   => '#d97706',
        ];
    }
}
