<?php

namespace App\Actions\Seller;

use App\Http\Requests\Seller\StoreSellerRequest;
use App\Models\Seller;

class CreateSeller
{
    public function execute(StoreSellerRequest $request): Seller
    {
        return Seller::create([
            'name' => $request->validated('name'),
            'active' => $request->validated('active') ?? true,
        ]);
    }
}
