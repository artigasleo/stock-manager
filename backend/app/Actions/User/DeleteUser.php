<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeleteUser
{
    public function execute(User $user, User $actingUser): void
    {
        if ($user->id === $actingUser->id) {
            throw ValidationException::withMessages([
                'user' => 'Você não pode excluir seu próprio usuário.',
            ]);
        }

        $user->delete();
    }
}
