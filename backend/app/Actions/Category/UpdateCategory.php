<?php

namespace App\Actions\Category;

use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;

class UpdateCategory
{
    public function execute(UpdateCategoryRequest $request, Category $category): Category
    {
        $category->fill([
            'name' => $request->validated('name'),
            'active' => $request->validated('active') ?? $category->active,
        ]);

        $category->save();

        return $category;
    }
}
