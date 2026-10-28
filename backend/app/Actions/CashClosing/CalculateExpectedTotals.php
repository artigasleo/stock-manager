<?php

namespace App\Actions\CashClosing;

use App\Models\SalePayment;
use App\Models\Unit;
use Illuminate\Support\Carbon;

class CalculateExpectedTotals
{
    /**
     * Soma, por forma de pagamento, o que as vendas (não canceladas) da unidade
     * geraram naquele dia — é o valor que o caixa deveria ter ao final do dia.
     *
     * @return array<string, float>
     */
    public function execute(Unit $unit, Carbon $date): array
    {
        return SalePayment::query()
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->where('sales.unit_id', $unit->id)
            ->where('sales.status', '!=', 'cancelled')
            ->whereDate('sales.created_at', $date)
            ->selectRaw('sale_payments.payment_method, sum(sale_payments.amount) as total')
            ->groupBy('sale_payments.payment_method')
            ->pluck('total', 'payment_method')
            ->map(fn ($total) => (float) $total)
            ->toArray();
    }
}
