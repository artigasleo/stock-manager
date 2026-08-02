<?php

namespace App\Http\Controllers\Api;

use App\Actions\Category\CreateCategory;
use App\Actions\Category\DeleteCategory;
use App\Actions\Category\ListCategory;
use App\Actions\Category\UpdateCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function index(ListCategory $action): AnonymousResourceCollection
    {
        return CategoryResource::collection($action->execute());
    }

    public function store(
        StoreCategoryRequest $request,
        CreateCategory $action
    ): CategoryResource
    {
        $category = $action->execute($request);

        return new CategoryResource($category);
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        UpdateCategory $action
    ): CategoryResource
    {
        $category = $action->execute($request, $category);

        return new CategoryResource($category);
    }

    public function destroy(
        Category $category,
        DeleteCategory $action
    ): Response
    {
        $action->execute($category);

        return response()->noContent();
    }
}
