<?php

namespace App\Filament\Doctor\Resources\ExpenseResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpense extends EditRecord
{
    use HasFormHero;

    protected static string $resource = ExpenseResource::class;

    protected static string $view = 'filament.doctor.resources.edit-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Editar gasto',
            'icon'     => '💸',
            'kicker'   => '✏️ Gasto',
            'subtitle' => 'Corrige el monto, la categoría o la fecha.',
            'gradient' => '#b45309 0%, #d97706 40%, #f59e0b 100%',
            'accent'   => '#d97706',
        ];
    }
}
