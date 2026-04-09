<?php

namespace App\Policies;

use App\Models\Prospect;
use App\Models\User;

/**
 * Protege prospectos contra IDOR horizontal: un vendedor solo puede
 * ver/editar/borrar sus propios prospectos asignados. Admin ve todo.
 */
class ProspectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'super_admin' || $user->role === 'sales';
    }

    public function view(User $user, Prospect $prospect): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }
        if ($user->role === 'sales') {
            return $prospect->assigned_to_sales_rep_id === $user->id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'sales']);
    }

    public function update(User $user, Prospect $prospect): bool
    {
        return $this->view($user, $prospect);
    }

    public function delete(User $user, Prospect $prospect): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }
        if ($user->role === 'sales') {
            return $prospect->assigned_to_sales_rep_id === $user->id;
        }
        return false;
    }
}
