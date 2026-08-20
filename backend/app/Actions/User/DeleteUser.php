<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeleteUser
{
    public function execute(User $user, User $actingUser): void
    {
        if ($user->is_master) {
            throw ValidationException::withMessages([
                'user' => 'Este usuário é protegido e não pode ser excluído.',
            ]);
        }

        if ($user->id === $actingUser->id) {
            throw ValidationException::withMessages([
                'user' => 'Você não pode excluir seu próprio usuário.',
            ]);
        }

        $user->delete();
    }
}
