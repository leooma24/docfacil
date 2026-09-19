<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * `role` está en $guarded del modelo, así que Eloquent lo descartaba en
     * silencio: el admin elegía "Staff" o "Super admin" en el formulario y el
     * usuario terminaba como `doctor` (el default de la migración), con acceso
     * completo al panel de ese consultorio. Aquí se escribe a propósito.
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $user = new User();
        $user->forceFill($data)->save();

        return $user;
    }
}
