<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@stockmanager.test')],
            [
                'name' => 'Admin',
                'password' => env('ADMIN_PASSWORD', 'password'),
            ]
        );

        if (! $admin->hasRole($adminRole)) {
            $admin->assignRole($adminRole);
        }
    }
}
