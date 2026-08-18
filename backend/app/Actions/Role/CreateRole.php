<?php

namespace App\Actions\Role;

use App\Http\Requests\Role\StoreRoleRequest;
use Spatie\Permission\Models\Role;

class CreateRole
{
    public function execute(StoreRoleRequest $request): Role
    {
        $role = Role::create([
            'name' => $request->validated('name'),
        ]);

        $role->syncPermissions($request->validated('permissions') ?? []);

        return $role;
    }
}
