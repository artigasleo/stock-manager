<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class SettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.view', only: ['index']),
            new Middleware('permission:settings.edit', only: ['update']),
        ];
    }

    public function index(): View
    {
        return view('settings.index', [
            'usdExchangeRate' => Setting::get('usd_exchange_rate'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'usd_exchange_rate' => ['required', 'numeric', 'min:0.01'],
        ], [
            'usd_exchange_rate.required' => 'A cotação do dólar é obrigatória.',
            'usd_exchange_rate.numeric' => 'A cotação do dólar deve ser um número.',
            'usd_exchange_rate.min' => 'A cotação do dólar deve ser maior que zero.',
        ]);

        Setting::set('usd_exchange_rate', $validated['usd_exchange_rate']);

        return redirect()->route('settings.index')->with('success', 'Configurações atualizadas.');
    }
}
