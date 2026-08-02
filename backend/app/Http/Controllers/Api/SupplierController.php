<?php

namespace App\Http\Controllers\Api;

use App\Actions\Supplier\CreateSupplier;
use App\Actions\Supplier\DeleteSupplier;
use App\Actions\Supplier\ListSupplier;
use App\Actions\Supplier\UpdateSupplier;
use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SupplierController extends Controller
{
    public function index(ListSupplier $action): AnonymousResourceCollection
    {
        return SupplierResource::collection($action->execute());
    }

    public function store(
        StoreSupplierRequest $request,
        CreateSupplier $action
    ): SupplierResource
    {
        $supplier = $action->execute($request);

        return new SupplierResource($supplier);
    }

    public function show(Supplier $supplier): SupplierResource
    {
        return new SupplierResource($supplier);
    }

    public function update(
        UpdateSupplierRequest $request,
        Supplier $supplier,
        UpdateSupplier $action
    ): SupplierResource
    {
        $supplier = $action->execute($request, $supplier);

        return new SupplierResource($supplier);
    }

    public function destroy(
        Supplier $supplier,
        DeleteSupplier $action
    ): Response
    {
        $action->execute($supplier);

        return response()->noContent();
    }
}
