<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class ListCustomer
{
    public function execute(): Collection
    {
        return Customer::all();
    }
}
