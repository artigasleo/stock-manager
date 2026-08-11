<?php

namespace App\Actions\Customer;

use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;

class UpdateCustomer
{
    public function execute(UpdateCustomerRequest $request, Customer $customer): Customer
    {
        $customer->fill([
            'name' => $request->validated('name'),
            'document' => $request->validated('document'),
            'phone' => $request->validated('phone'),
            'email' => $request->validated('email'),
            'address' => $request->validated('address'),
            'active' => $request->validated('active') ?? $customer->active,
        ]);

        $customer->save();

        return $customer;
    }
}
