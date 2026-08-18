<?php

namespace App\Http\Controllers;

use App\Actions\Category\CreateCategory;
use App\Actions\Category\DeleteCategory;
use App\Actions\Category\ListCategory;
use App\Actions\Category\UpdateCategory;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class CategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:categories.view', only: ['index']),
            new Middleware('permission:categories.edit', only: ['store', 'update', 'destroy']),
        ];
    }

    public function index(ListCategory $action): View
    {
        return view('categories.index', [
            'categories' => $action->execute(),
        ]);
    }

    public function store(
        StoreCategoryRequest $request,
        CreateCategory $action
    ): RedirectResponse {
        $action->execute($request);

        return redirect()->route('categories.index')->with('success', 'Categoria criada.');
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        UpdateCategory $action
    ): RedirectResponse {
        $action->execute($request, $category);

        return redirect()->route('categories.index')->with('success', 'Categoria atualizada.');
    }

    public function destroy(
        Category $category,
        DeleteCategory $action
    ): RedirectResponse {
        $action->execute($category);

        return redirect()->route('categories.index')->with('success', 'Categoria excluída.');
    }
}
