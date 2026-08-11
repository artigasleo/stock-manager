<?php

namespace App\Actions\Unit;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Collection;

class ListUnit
{
    public function execute(): Collection
    {
        return Unit::all();
    }
}
