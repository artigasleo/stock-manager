<?php

namespace App\Http\Controllers;

use App\Actions\CashClosing\CalculateExpectedTotals;
use App\Actions\CashClosing\OpenCashRegister;
use App\Actions\CashClosing\StoreCashClosing;
use App\Http\Requests\CashClosing\StoreCashClosingRequest;
use App\Http\Requests\CashClosing\StoreCashOpeningRequest;
use App\Models\CashClosing;
use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CashClosingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sales.edit'),
        ];
    }

    public function index(Request $request, CalculateExpectedTotals $calculateExpectedTotals): View
    {
        $unit = Unit::default();
        $date = Carbon::parse($request->query('date', today()->format('Y-m-d')));

        $existing = CashClosing::where('unit_id', $unit->id)
            ->whereDate('date', $date)
            ->with(['items', 'openedBy', 'closedBy'])
            ->first();

        $exchangeRate = (float) Setting::get('usd_exchange_rate', 0);

        $expected = $calculateExpectedTotals->execute($unit, $date);
        $expected['cash'] = ($expected['cash'] ?? 0) + (float) ($existing?->opening_cash ?? 0);
        $expected['cash_usd'] = ($expected['cash_usd'] ?? 0) + (float) ($existing?->opening_cash_usd ?? 0);

        $countedByMethod = $existing
            ? $existing->items->pluck('counted_amount', 'payment_method')
            : collect();

        // Se a última tentativa de salvar falhou na validação, prioriza o que
        // foi digitado em vez de voltar a mostrar o valor salvo/esperado.
        $oldCountedByMethod = collect(old('items', []))->pluck('counted_amount', 'payment_method');

        $rows = collect(config('payments.methods'))->map(function ($label, $method) use ($expected, $countedByMethod, $oldCountedByMethod, $exchangeRate) {
            $expectedAmount = $expected[$method] ?? 0;

            $counted = match (true) {
                $oldCountedByMethod->has($method) => (float) $oldCountedByMethod->get($method),
                $countedByMethod->has($method) => (float) $countedByMethod->get($method),
                default => $expectedAmount,
            };

            return [
                'method' => $method,
                'label' => $label,
                'expected' => $expectedAmount,
                'counted' => $counted,
                // Dinheiro/Dólar é digitado em US$ na tela — mostra o
                // equivalente pra quem está reabrindo um valor já salvo em R$.
                'countedUsd' => $method === 'cash_usd' && $exchangeRate > 0
                    ? round($counted / $exchangeRate, 2)
                    : 0,
            ];
        })->values();

        return view('cash-closings.index', [
            'date' => $date->format('Y-m-d'),
            'rows' => $rows,
            'existing' => $existing,
            'usdExchangeRate' => $exchangeRate,
            'openingCash' => old('opening_cash', $existing?->opening_cash ?? 0),
            'openingCashUsd' => old('opening_cash_usd', $exchangeRate > 0
                ? round((float) ($existing?->opening_cash_usd ?? 0) / $exchangeRate, 2)
                : 0),
            'history' => CashClosing::where('unit_id', $unit->id)
                ->whereNotNull('closed_at')
                ->with(['items', 'closedBy'])
                ->orderByDesc('date')
                ->limit(30)
                ->get(),
        ]);
    }

    public function openCash(StoreCashOpeningRequest $request, OpenCashRegister $action): RedirectResponse
    {
        $unit = Unit::default();

        $action->execute($request, $unit, $request->user());

        return redirect()
            ->route('cashClosings.index', ['date' => $request->validated('date')])
            ->with('success', 'Abertura de caixa registrada.');
    }

    public function store(StoreCashClosingRequest $request, StoreCashClosing $action): RedirectResponse
    {
        $unit = Unit::default();

        $action->execute($request, $unit, $request->user());

        return redirect()
            ->route('cashClosings.index', ['date' => $request->validated('date')])
            ->with('success', 'Fechamento de caixa registrado.');
    }
}
