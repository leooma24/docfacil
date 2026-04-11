<?php

namespace App\Filament\Doctor\Resources\ConsentFormResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\ConsentFormResource;
use App\Models\ConsentForm;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConsentForms extends ListRecords
{
    use HasListHero;

    protected static string $resource = ConsentFormResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuevo Consentimiento'),
        ];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $base = ConsentForm::where('clinic_id', $clinicId);

        $total = (clone $base)->count();
        $signed = (clone $base)->whereNotNull('signed_at')->count();
        $pending = (clone $base)->whereNull('signed_at')->count();
        $thisMonth = (clone $base)->where('created_at', '>=', now()->startOfMonth())->count();

        return [
            'title'    => 'Consentimientos',
            'icon'     => '✍️',
            'kicker'   => '📄 Firma digital',
            'subtitle' => 'Consentimientos informados con firma digital del paciente. Guardan fecha, hora e IP.',
            'gradient' => '#6366f1 0%, #8b5cf6 40%, #a855f7 100%',
            'accent'   => '#6366f1',
            'stats' => [
                ['label' => '📄 Total',           'value' => number_format($total)],
                ['label' => '✅ Firmados',        'value' => number_format($signed)],
                ['label' => '⏳ Pendientes',      'value' => number_format($pending)],
                ['label' => '📅 Este mes',        'value' => number_format($thisMonth)],
            ],
        ];
    }
}
