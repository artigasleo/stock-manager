<?php

namespace App\Actions\Role;

use App\Http\Requests\Role\UpdateRoleRequest;
use Spatie\Permission\Models\Role;

class UpdateRole
{
    public function execute(UpdateRoleRequest $request, Role $role): Role
    {
        $role->fill([
            'name' => $request->validated('name'),
        ]);

        $role->save();

        $role->syncPermissions($request->validated('permissions') ?? []);

        return $role;
    }
}
