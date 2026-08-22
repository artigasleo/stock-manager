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
            'zip_code' => $request->validated('zip_code'),
            'street' => $request->validated('street'),
            'number' => $request->validated('number'),
            'complement' => $request->validated('complement'),
            'neighborhood' => $request->validated('neighborhood'),
            'city' => $request->validated('city'),
            'state' => $request->validated('state'),
            'country' => $request->validated('country'),
            'instagram' => $request->validated('instagram'),
            'state_registration' => $request->validated('state_registration'),
            'type' => $request->validated('type'),
            'active' => $request->validated('active') ?? $supplier->active,
        ]);

        $supplier->save();

        return $supplier;
    }
}
