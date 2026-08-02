<?php

namespace App\Http\Controllers\Api;

use App\Actions\Product\CreateProduct;
use App\Actions\Product\DeleteProduct;
use App\Actions\Product\ListProduct;
use App\Actions\Product\UpdateProduct;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index(ListProduct $action): AnonymousResourceCollection
    {
        return ProductResource::collection($action->execute());
    }

    public function store(
        StoreProductRequest $request,
        CreateProduct $action
    ): ProductResource
    {
        $product = $action->execute($request);

        return new ProductResource($product);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load(['category', 'supplier']));
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        UpdateProduct $action
    ): ProductResource
    {
        $product = $action->execute($request, $product);

        return new ProductResource($product);
    }

    public function destroy(
        Product $product,
        DeleteProduct $action
    ): Response
    {
        $action->execute($product);

        return response()->noContent();
    }
}
