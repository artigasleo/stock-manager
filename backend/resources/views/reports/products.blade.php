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

        <div>
            <label class="block text-sm font-medium mb-1">Buscar</label>
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Produto ou fornecedor..."
                class="rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
            >
        </div>

        <input type="hidden" name="per_page" value="{{ $perPage }}">

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
                    <tr>
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

    <div class="flex items-center justify-between mt-4">
        <form method="GET" action="{{ route('reports.products.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="from" value="{{ $from }}">
            <input type="hidden" name="to" value="{{ $to }}">
            <input type="hidden" name="search" value="{{ $search }}">

            <label class="text-sm text-stone-600">Mostrar</label>
            <select
                name="per_page"
                onchange="this.form.submit()"
                class="rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
            >
                @foreach (['5' => '5 por página', '10' => '10 por página', '25' => '25 por página', '50' => '50 por página', 'all' => 'Ver tudo'] as $value => $label)
                    <option value="{{ $value }}" @selected($perPage === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>

        @if ($rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            {{ $rows->links() }}
        @endif
    </div>
@endsection
