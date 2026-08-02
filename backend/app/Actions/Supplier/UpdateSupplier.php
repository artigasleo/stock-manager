<?php

namespace App\Actions\Supplier;

use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;

class UpdateSupplier
{
    public function execute(UpdateSupplierRequest $request, Supplier $supplier): Supplier
    {
        $supplier->fill([
            'name' => $request->validated('name'),
            'document' => $request->validated('document'),
            'phone' => $request->validated('phone'),
            'email' => $request->validated('email'),
            'address' => $request->validated('address'),
            'active' => $request->validated('active') ?? $supplier->active,
        ]);

        $supplier->save();

        return $supplier;
    }
}
