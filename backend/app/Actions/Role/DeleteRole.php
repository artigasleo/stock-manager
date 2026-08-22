<?php

namespace App\Actions\Role;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class DeleteRole
{
    public function execute(Role $role): void
    {
        if ($role->name === 'admin') {
            throw ValidationException::withMessages([
                'role' => 'A função "admin" não pode ser excluída.',
            ]);
        }

        if (User::role($role->name)->exists()) {
            throw ValidationException::withMessages([
                'role' => 'Esta função está atribuída a pelo menos um usuário e não pode ser excluída.',
            ]);
        }

        $role->delete();
    }
}
