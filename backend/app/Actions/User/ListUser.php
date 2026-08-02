<?php

namespace App\Actions\User;

use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class ListUser
{
    public function execute(): Collection
    {
        return User::with('roles')->get();
    }
}
