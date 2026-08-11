<?php

namespace App\Actions\Customer;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Models\Customer;

class CreateCustomer
{
    public function execute(StoreCustomerRequest $request): Customer
    {
        return Customer::create([
            'name' => $request->validated('name'),
            'document' => $request->validated('document'),
            'phone' => $request->validated('phone'),
            'email' => $request->validated('email'),
            'address' => $request->validated('address'),
            'active' => $request->validated('active') ?? true,
        ]);
    }
}
