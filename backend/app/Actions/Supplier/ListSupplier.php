<?php

namespace App\Actions\Supplier;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

class ListSupplier
{
    public function execute(): Collection
    {
        return Supplier::all();
    }
}
