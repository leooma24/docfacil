<?php

namespace App\Filament\Paciente\Resources\MyAppointmentResource\Pages;

use App\Filament\Paciente\Resources\MyAppointmentResource;
use Filament\Resources\Pages\ListRecords;

class ListMyAppointments extends ListRecords
{
    protected static string $resource = MyAppointmentResource::class;
}
