<?php

namespace App\Actions\Seller;

use App\Models\Seller;
use Illuminate\Database\Eloquent\Collection;

class ListSeller
{
    public function execute(): Collection
    {
        return Seller::all();
    }
}
