<?php

namespace App\Actions\Sale;

use App\Http\Requests\Sale\UpdateSaleCustomerRequest;
use App\Models\Sale;

class UpdateSaleCustomer
{
    public function execute(UpdateSaleCustomerRequest $request, Sale $sale): Sale
    {
        $sale->update(['customer_id' => $request->validated('customer_id')]);

        return $sale;
    }
}
