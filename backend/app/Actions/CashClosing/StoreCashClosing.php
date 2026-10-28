<?php

namespace App\Actions\CashClosing;

use App\Http\Requests\CashClosing\StoreCashClosingRequest;
use App\Models\CashClosing;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StoreCashClosing
{
    public function __construct(
        private CalculateExpectedTotals $calculateExpectedTotals
    ) {}

    public function execute(StoreCashClosingRequest $request, Unit $unit, User $user): CashClosing
    {
        return DB::transaction(function () use ($request, $unit, $user) {
            $date = Carbon::parse($request->validated('date'));

            // Mantém o que já foi preenchido na abertura (se houve) — o
            // fechamento só adiciona os dados de quem fechou e os itens.
            $closing = CashClosing::firstOrNew(['unit_id' => $unit->id, 'date' => $date->format('Y-m-d')]);

            // O esperado é travado no momento do fechamento (como o preço na
            // venda) — não recalcula depois, pra não mudar um fechamento já
            // feito se uma venda daquele dia for alterada no futuro. Pra
            // dinheiro/real e dinheiro/dólar, soma o valor de abertura (fundo
            // de caixa) ao que as vendas do dia geraram.
            $expected = $this->calculateExpectedTotals->execute($unit, $date);
            $expected['cash'] = ($expected['cash'] ?? 0) + (float) ($closing->opening_cash ?? 0);
            $expected['cash_usd'] = ($expected['cash_usd'] ?? 0) + (float) ($closing->opening_cash_usd ?? 0);

            $closing->fill([
                'unit_id' => $unit->id,
                'date' => $date->format('Y-m-d'),
                'closed_by_user_id' => $user->id,
                'closed_at' => now(),
                'notes' => $request->validated('notes'),
            ])->save();

            $closing->items()->delete();

            foreach ($request->validated('items') as $item) {
                $closing->items()->create([
                    'payment_method' => $item['payment_method'],
                    'expected_amount' => $expected[$item['payment_method']] ?? 0,
                    'counted_amount' => $item['counted_amount'],
                ]);
            }

            return $closing->load('items');
        });
    }
}
