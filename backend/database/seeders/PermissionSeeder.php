<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionNames = [];

        foreach (array_keys(config('modules')) as $module) {
            foreach (['view', 'edit'] as $action) {
                $permissionNames[] = "{$module}.{$action}";
            }
        }

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        Role::findByName('admin')->syncPermissions($permissionNames);
    }
}
