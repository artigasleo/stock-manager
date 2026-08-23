@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold text-brand-dark">Vendas Detalhadas</h1>

        <a
            href="{{ route('reports.index') }}"
            class="rounded-md border border-brand text-brand px-4 py-2 text-sm font-medium hover:bg-brand hover:text-brand-cream cursor-pointer"
        >
            &larr; Voltar aos relatórios
        </a>
    </div>

    <form method="GET" action="{{ route('reports.sales.index') }}" class="flex items-end gap-3 mb-6">
        <div>
            <label class="block text-sm font-medium mb-1">De</label>
            <input
                type="date"
                name="from"
                value="{{ $from }}"
                class="rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
            >
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Até</label>
            <input
                type="date"
                name="to"
                value="{{ $to }}"
                class="rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
            >
        </div>

        <button
            type="submit"
            class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer"
        >
            Atualizar vendas
        </button>

        <button
            type="submit"
            formaction="{{ route('reports.sales.export') }}"
            class="rounded-md border border-brand text-brand px-4 py-2 text-sm font-medium hover:bg-brand hover:text-brand-cream cursor-pointer"
        >
            Exportar CSV
        </button>
    </form>

    <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-stone-100 text-left text-stone-600">
                <tr>
                    <th class="px-4 py-3">Data/Hora</th>
                    <th class="px-4 py-3">Produto(s)</th>
                    <th class="px-4 py-3">Forma de pagamento</th>
                    <th class="px-4 py-3">Vendedor</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
                @forelse ($sales as $sale)
                    <tr>
                        <td class="px-4 py-3">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            {{ $sale->items->map(fn ($item) => ($item->product?->name ?? 'Produto removido').' (x'.$item->quantity.')')->implode(', ') }}
                        </td>
                        <td class="px-4 py-3">{{ $paymentLabels[$sale->payment_method] ?? 'Não informado' }}</td>
                        <td class="px-4 py-3">{{ $sale->seller?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $sale->customer?->name ?? 'Não identificado' }}</td>
                        <td class="px-4 py-3">{{ $statusLabels[$sale->status] ?? $sale->status }}</td>
                        <td class="px-4 py-3">R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-stone-500">
                            Nenhuma venda registrada nesse período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
