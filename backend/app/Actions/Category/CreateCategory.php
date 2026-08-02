<?php

namespace App\Actions\Category;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Models\Category;

class CreateCategory
{
    public function execute(StoreCategoryRequest $request): Category
    {
        return Category::create([
            'name'   => $request->validated('name'),
            'active' => $request->validated('active') ?? true,
        ]);
    }
}