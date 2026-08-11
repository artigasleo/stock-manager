<?php

namespace App\Actions\Customer;

use App\Models\Customer;

class DeleteCustomer
{
    public function execute(Customer $customer): void
    {
        $customer->delete();
    }
}
