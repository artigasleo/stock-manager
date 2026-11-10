@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold text-brand-dark">Relatório por Produto</h1>

        <a
            href="{{ route('reports.index') }}"
            class="rounded-md border border-brand text-brand px-4 py-2 text-sm font-medium hover:bg-brand hover:text-brand-cream cursor-pointer"
        >
            &larr; Voltar aos relatórios
        </a>
    </div>

    <form method="GET" action="{{ route('reports.products.index') }}" class="flex items-end gap-3 mb-4">
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
            Atualizar
        </button>

        <button
            type="submit"
            formaction="{{ route('reports.products.export') }}"
            class="rounded-md border border-brand text-brand px-4 py-2 text-sm font-medium hover:bg-brand hover:text-brand-cream cursor-pointer"
        >
            Exportar CSV
        </button>
    </form>

    <div
        x-data="{
            search: '',
            matches(name, supplier) {
                if (!this.search) return true;
                const q = this.search.toLowerCase();
                return name.toLowerCase().includes(q) || (supplier && supplier.toLowerCase().includes(q));
            },
        }"
    >
        <div class="mb-4">
            <input
                type="text"
                x-model="search"
                placeholder="Buscar por nome do produto ou fornecedor..."
                class="w-full max-w-md rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
            >
        </div>

        <div class="bg-brand-paper rounded-lg shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-3">Produto</th>
                        <th class="px-4 py-3">Fornecedor</th>
                        <th class="px-4 py-3">Quantidade vendida</th>
                        <th class="px-4 py-3">Faturamento (R$)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($rows as $row)
                        <tr x-show="matches(@js($row->product?->name ?? 'Produto removido'), @js($row->product?->supplier?->name))">
                            <td class="px-4 py-3">{{ $row->product?->name ?? 'Produto removido' }}</td>
                            <td class="px-4 py-3">{{ $row->product?->supplier?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $row->total_quantity }}</td>
                            <td class="px-4 py-3">R$ {{ number_format($row->total_revenue, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-stone-500">
                                Nenhuma venda registrada nesse período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
