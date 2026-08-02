<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class ListCategory
{
    public function execute(): Collection
    {
        return Category::all();
    }
}
