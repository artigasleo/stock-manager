@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-semibold text-brand-dark mb-4">Configurações</h1>

    <div class="bg-brand-paper rounded-lg shadow p-6 max-w-md">
        <h2 class="font-semibold text-brand-dark mb-1">Cotação do Dólar</h2>
        <p class="text-sm text-stone-500 mb-4">
            Usada pra converter o total da venda quando o pagamento for em dinheiro/dólar.
            Atualize sempre que a cotação mudar.
        </p>

        <form method="POST" action="{{ route('settings.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">1 USD = R$</label>
                <input
                    type="number"
                    name="usd_exchange_rate"
                    min="0.01"
                    step="0.01"
                    value="{{ old('usd_exchange_rate', $usdExchangeRate) }}"
                    class="w-full rounded-md border {{ $errors->has('usd_exchange_rate') ? 'border-red-500' : 'border-stone-300' }} px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                >
            </div>

            @can('settings.edit')
                <button type="submit" class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer">
                    Salvar
                </button>
            @endcan
        </form>
    </div>
@endsection
