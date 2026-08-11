<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        Unit::firstOrCreate(
            ['name' => 'Loja São Paulo'],
            ['is_default' => true, 'active' => true]
        );
    }
}
