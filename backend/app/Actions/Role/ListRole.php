<?php

namespace App\Actions\Role;

use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class ListRole
{
    public function execute(): Collection
    {
        return Role::with('permissions')->orderBy('name')->get()
            ->map(function (Role $role) {
                $role->users_count = User::role($role->name)->count();

                return $role;
            });
    }
}
