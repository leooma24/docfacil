<?php

namespace App\Filament\Doctor\Resources\DoctorInvitationResource\Pages;

use App\Filament\Doctor\Resources\DoctorInvitationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDoctorInvitations extends ListRecords
{
    protected static string $resource = DoctorInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Invitar Doctor'),
        ];
    }
}
