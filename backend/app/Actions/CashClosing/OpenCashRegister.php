<?php

namespace App\Actions\CashClosing;

use App\Http\Requests\CashClosing\StoreCashOpeningRequest;
use App\Models\CashClosing;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class OpenCashRegister
{
    public function execute(StoreCashOpeningRequest $request, Unit $unit, User $user): CashClosing
    {
        $date = Carbon::parse($request->validated('date'));
        $openingCashUsd = (float) ($request->validated('opening_cash_usd') ?? 0);
        $exchangeRate = (float) Setting::get('usd_exchange_rate', 0);

        if ($openingCashUsd > 0 && $exchangeRate <= 0) {
            throw ValidationException::withMessages([
                'opening_cash_usd' => 'Configure a cotação do dólar em Configurações antes de registrar dólar na abertura.',
            ]);
        }

        return CashClosing::updateOrCreate(
            ['unit_id' => $unit->id, 'date' => $date->format('Y-m-d')],
            [
                'opened_by_user_id' => $user->id,
                'opened_at' => now(),
                'opening_cash' => $request->validated('opening_cash') ?? 0,
                // Quem conta a gaveta pensa em notas de dólar, não em reais — o
                // valor digitado é em US$, convertido aqui pra R$ (igual o resto
                // do sistema registra tudo), com a cotação do momento da abertura.
                'opening_cash_usd' => round($openingCashUsd * $exchangeRate, 2),
            ],
        );
    }
}
