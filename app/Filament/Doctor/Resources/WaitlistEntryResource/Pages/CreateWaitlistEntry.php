<?php

namespace App\Filament\Doctor\Resources\WaitlistEntryResource\Pages;

use App\Filament\Doctor\Resources\WaitlistEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWaitlistEntry extends CreateRecord
{
    use \App\Filament\Doctor\Concerns\HasFormHero;

    protected static string $resource = WaitlistEntryResource::class;
}
