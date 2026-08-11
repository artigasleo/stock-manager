<?php

namespace App\Actions\Sale;

use App\Actions\Stock\CreateStockMovement;
use App\Http\Requests\Sale\UpdateSaleStatusRequest;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateSaleStatus
{
    public function __construct(
        private CreateStockMovement $createStockMovement
    ) {}

    public function execute(UpdateSaleStatusRequest $request, Sale $sale, User $user): Sale
    {
        return DB::transaction(function () use ($request, $sale, $user) {
            $newStatus = $request->validated('status');

            if ($sale->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Esta venda já está cancelada.',
                ]);
            }

            if ($newStatus === 'cancelled') {
                foreach ($sale->items as $item) {
                    $this->createStockMovement->execute(
                        $item->product_id,
                        'in',
                        $item->quantity,
                        "Cancelamento da venda #{$sale->id}",
                        $user,
                    );
                }
            }

            $sale->update(['status' => $newStatus]);

            return $sale;
        });
    }
}
