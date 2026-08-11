<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListUser
{
    public function execute(): Collection
    {
        return User::with('roles')->get();
    }
}
