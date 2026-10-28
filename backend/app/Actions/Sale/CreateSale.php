<?php

namespace App\Actions\Sale;

use App\Actions\Stock\CreateStockMovement;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateSale
{
    public function __construct(
        private CreateStockMovement $createStockMovement
    ) {}

    public function execute(StoreSaleRequest $request, Unit $unit, User $user): Sale
    {
        return DB::transaction(function () use ($request, $unit, $user) {
            $items = $this->resolveItems($request->validated('items'), $unit);
            $total = array_sum(array_column($items, 'total'));
            $payments = $request->validated('payments');

            $this->assertPaymentsMatchTotal($payments, $total);

            $sale = Sale::create([
                'unit_id' => $unit->id,
                'customer_id' => $request->validated('customer_id'),
                'seller_id' => $request->validated('seller_id'),
                'user_id' => $user->id,
                'status' => 'awaiting_payment',
                'total' => $total,
            ]);

            foreach ($items as $item) {
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'],
                ]);

                $this->createStockMovement->execute(
                    $unit->id,
                    $item['product_id'],
                    'out',
                    $item['quantity'],
                    "Venda #{$sale->id}",
                    $user,
                );
            }

            foreach ($payments as $payment) {
                $sale->payments()->create([
                    'payment_method' => $payment['payment_method'],
                    'amount' => $payment['amount'],
                ]);
            }

            return $sale->load('items.product', 'customer', 'payments');
        });
    }

    /**
     * A soma dos pagamentos precisa bater com o total calculado dos itens — o
     * valor vem do que o vendedor digitou pra cada forma de pagamento, então
     * precisa ser conferido contra o total "de verdade" (preço travado no
     * produto), não contra o que o item mandou.
     *
     * @param  array<int, array{payment_method: string, amount: float}>  $payments
     */
    private function assertPaymentsMatchTotal(array $payments, float $total): void
    {
        $sum = round(array_sum(array_column($payments, 'amount')), 2);

        if (abs($sum - round($total, 2)) > 0.01) {
            $sumLabel = number_format($sum, 2, ',', '.');
            $totalLabel = number_format($total, 2, ',', '.');

            throw ValidationException::withMessages([
                'payments' => "A soma dos pagamentos (R$ {$sumLabel}) não bate com o total da venda (R$ {$totalLabel}).",
            ]);
        }
    }

    /**
     * Resolve each item against the current product record — the unit price always
     * comes from the product's own sale_price, never from the request, so a sale
     * can't be registered at a tampered price.
     *
     * @param  array<int, array{product_id: int, quantity: int, discount_percent?: float|null}>  $rawItems
     * @return array<int, array{product_id: int, quantity: int, unit_price: float, discount_percent: float, total: float}>
     */
    private function resolveItems(array $rawItems, Unit $unit): array
    {
        $resolved = [];

        foreach (array_values($rawItems) as $index => $rawItem) {
            $product = Product::findOrFail($rawItem['product_id']);

            $available = ProductStock::where('unit_id', $unit->id)
                ->where('product_id', $product->id)
                ->first()?->quantity ?? 0;

            if ($rawItem['quantity'] > $available) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => "Estoque insuficiente para {$product->name}. Disponível: {$available}.",
                ]);
            }

            $discountPercent = (float) ($rawItem['discount_percent'] ?? 0);
            $unitPrice = (float) $product->sale_price;

            $resolved[] = [
                'product_id' => $product->id,
                'quantity' => $rawItem['quantity'],
                'unit_price' => $unitPrice,
                'discount_percent' => $discountPercent,
                'total' => round($rawItem['quantity'] * $unitPrice * (1 - $discountPercent / 100), 2),
            ];
        }

        return $resolved;
    }
}
