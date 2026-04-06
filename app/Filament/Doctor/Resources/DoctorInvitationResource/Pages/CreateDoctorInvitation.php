<?php

namespace App\Filament\Doctor\Resources\DoctorInvitationResource\Pages;

use App\Filament\Doctor\Resources\DoctorInvitationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDoctorInvitation extends CreateRecord
{
    protected static string $resource = DoctorInvitationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;
        $data['invited_by'] = auth()->id();

        return $data;
    }
}
