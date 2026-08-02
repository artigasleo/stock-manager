<?php

namespace App\Actions\Supplier;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Models\Supplier;

class CreateSupplier
{
    public function execute(StoreSupplierRequest $request): Supplier
    {
        return Supplier::create([
            'name' => $request->validated('name'),
            'document' => $request->validated('document'),
            'phone' => $request->validated('phone'),
            'email' => $request->validated('email'),
            'address' => $request->validated('address'),
            'active' => $request->validated('active') ?? true,
        ]);
    }
}
