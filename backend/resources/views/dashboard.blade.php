@extends('layouts.app')

@section('content')
    @php
        $lowStockCount = $lowStockStocks->count();

        $paymentLabels = [
            'cash' => 'Dinheiro',
            'pix' => 'PIX',
            'debit_card' => 'Cartão de Débito',
            'credit_card' => 'Cartão de Crédito',
            'other' => 'Outro',
        ];
    @endphp

    <h1 class="text-2xl font-semibold text-brand-dark mb-4">Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-brand-paper rounded-lg shadow p-5">
            <p class="text-sm text-stone-500">Vendas hoje</p>
            <p class="text-2xl font-semibold text-brand-dark mt-1">{{ $salesTodayCount }}</p>
            <p class="text-sm text-stone-600 mt-1">R$ {{ number_format($salesTodayTotal, 2, ',', '.') }}</p>
        </div>

        <div class="bg-brand-paper rounded-lg shadow p-5">
            <p class="text-sm text-stone-500">Vendas no mês</p>
            <p class="text-2xl font-semibold text-brand-dark mt-1">{{ $salesMonthCount }}</p>
            <p class="text-sm text-stone-600 mt-1">R$ {{ number_format($salesMonthTotal, 2, ',', '.') }}</p>
        </div>

        <div class="bg-brand-paper rounded-lg shadow p-5">
            <p class="text-sm text-stone-500">Produtos com estoque baixo</p>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-2xl font-semibold text-brand-dark">{{ $lowStockCount }}</span>
                @if ($lowStockCount > 0)
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-red-100 text-red-800">Atenção</span>
                @else
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-green-100 text-green-800">Tudo certo</span>
                @endif
            </div>
        </div>

        <div class="bg-brand-paper rounded-lg shadow p-5">
            <p class="text-sm text-stone-500">Valor em estoque</p>
            <p class="text-2xl font-semibold text-brand-dark mt-1">R$ {{ number_format($stockValue, 2, ',', '.') }}</p>
            <p class="text-sm text-stone-600 mt-1">Custo × quantidade, produtos ativos</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
            <div class="px-4 py-3 border-b border-stone-200">
                <h2 class="font-semibold text-brand-dark">Produtos com estoque baixo</h2>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-2">Produto</th>
                        <th class="px-4 py-2">Qtd.</th>
                        <th class="px-4 py-2">Mínimo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($lowStockStocks as $stock)
                        <tr>
                            <td class="px-4 py-2">{{ $stock->product->name }}</td>
                            <td class="px-4 py-2 text-red-700 font-medium">{{ $stock->quantity }}</td>
                            <td class="px-4 py-2">{{ $stock->min_stock }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-stone-500">
                                Nenhum produto com estoque baixo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
            <div class="px-4 py-3 border-b border-stone-200">
                <h2 class="font-semibold text-brand-dark">Formas de pagamento (mês)</h2>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-2">Forma de pagamento</th>
                        <th class="px-4 py-2">Vendas</th>
                        <th class="px-4 py-2">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($paymentMethodBreakdown as $row)
                        <tr>
                            <td class="px-4 py-2">{{ $paymentLabels[$row->payment_method] ?? 'Não informado' }}</td>
                            <td class="px-4 py-2">{{ $row->count }}</td>
                            <td class="px-4 py-2">R$ {{ number_format($row->total, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-stone-500">
                                Nenhuma venda registrada no mês.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
            <div class="px-4 py-3 border-b border-stone-200">
                <h2 class="font-semibold text-brand-dark">Últimas movimentações</h2>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-2">Data</th>
                        <th class="px-4 py-2">Produto</th>
                        <th class="px-4 py-2">Tipo</th>
                        <th class="px-4 py-2">Qtd.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($recentMovements as $movement)
                        <tr>
                            <td class="px-4 py-2">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2">{{ $movement->product->name }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $movement->type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $movement->type === 'in' ? 'Entrada' : 'Saída' }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $movement->type === 'in' ? '+' : '-' }}{{ $movement->quantity }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-stone-500">
                                Nenhuma movimentação registrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
