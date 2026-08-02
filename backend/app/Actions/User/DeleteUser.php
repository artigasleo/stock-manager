<?php

namespace App\Actions\User;

use App\Models\User;

class DeleteUser
{
    public function execute(User $user): void
    {
        $user->delete();
    }
}
