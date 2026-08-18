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
                'role' => 'O papel "admin" não pode ser excluído.',
            ]);
        }

        if (User::role($role->name)->exists()) {
            throw ValidationException::withMessages([
                'role' => 'Este papel está atribuído a pelo menos um usuário e não pode ser excluído.',
            ]);
        }

        $role->delete();
    }
}
