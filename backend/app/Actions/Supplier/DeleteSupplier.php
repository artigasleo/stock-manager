<?php

namespace App\Actions\Supplier;

use App\Models\Supplier;

class DeleteSupplier
{
    public function execute(Supplier $supplier): void
    {
        $supplier->delete();
    }
}
