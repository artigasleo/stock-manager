<?php

namespace App\Actions\Seller;

use App\Models\Seller;

class DeleteSeller
{
    public function execute(Seller $seller): void
    {
        $seller->delete();
    }
}
