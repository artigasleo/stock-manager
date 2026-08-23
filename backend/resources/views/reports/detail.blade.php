@extends('layouts.app')

@php
    $paymentLabels = [
        'cash' => 'Dinheiro',
        'pix' => 'PIX',
        'debit_card' => 'Cartão de Débito',
        'credit_card' => 'Cartão de Crédito',
        'other' => 'Outro',
    ];

    $totalSum = $sales->sum('total');
@endphp

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold text-brand-dark">Vendas — {{ $label }}</h1>
        </div>

        <a
            href="{{ route('reports.index', ['granularity' => $granularity]) }}"
            class="rounded-md border border-brand text-brand px-4 py-2 text-sm font-medium hover:bg-brand hover:text-brand-cream cursor-pointer"
        >
            &larr; Voltar aos relatórios
        </a>
    </div>

    <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-stone-100 text-left text-stone-600">
                <tr>
                    <th class="px-4 py-3">Data/Hora</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Forma de pagamento</th>
                    <th class="px-4 py-3">Vendedor</th>
                    <th class="px-4 py-3">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
                @forelse ($sales as $sale)
                    <tr>
                        <td class="px-4 py-3">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $sale->customer?->name ?? 'Não identificado' }}</td>
                        <td class="px-4 py-3">{{ $paymentLabels[$sale->payment_method] ?? 'Não informado' }}</td>
                        <td class="px-4 py-3">{{ $sale->seller?->name ?? '—' }}</td>
                        <td class="px-4 py-3">R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-stone-500">
                            Nenhuma venda registrada nesse período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($sales->isNotEmpty())
                <tfoot>
                    <tr class="bg-stone-100 font-semibold text-brand-dark">
                        <td class="px-4 py-3" colspan="4">Total</td>
                        <td class="px-4 py-3">R$ {{ number_format($totalSum, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
