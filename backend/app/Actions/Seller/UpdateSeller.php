<?php

namespace App\Actions\Seller;

use App\Http\Requests\Seller\UpdateSellerRequest;
use App\Models\Seller;

class UpdateSeller
{
    public function execute(UpdateSellerRequest $request, Seller $seller): Seller
    {
        $seller->fill([
            'name' => $request->validated('name'),
            'active' => $request->validated('active') ?? $seller->active,
        ]);

        $seller->save();

        return $seller;
    }
}
