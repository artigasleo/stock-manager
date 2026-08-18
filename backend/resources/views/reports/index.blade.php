@extends('layouts.app')

@php
    $granularityLabels = [
        'day' => 'Diário',
        'month' => 'Mensal',
        'quarter' => 'Trimestral',
        'semester' => 'Semestral',
        'year' => 'Anual',
    ];

    $totalCount = $rows->sum('count');
    $totalSum = $rows->sum('total');
@endphp

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold text-brand-dark">Relatórios de Vendas</h1>

        <a
            href="{{ route('reports.export', ['granularity' => $granularity]) }}"
            class="rounded-md border border-brand text-brand px-4 py-2 text-sm font-medium hover:bg-brand hover:text-brand-cream cursor-pointer"
        >
            Exportar CSV
        </a>
    </div>

    <div class="flex rounded-md border border-stone-300 overflow-hidden text-sm w-fit mb-4">
        @foreach ($granularityLabels as $value => $label)
            <a
                href="{{ route('reports.index', ['granularity' => $value]) }}"
                class="px-4 py-2 {{ $granularity === $value ? 'bg-brand text-brand-cream' : 'bg-white text-stone-600 hover:bg-stone-100' }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-stone-100 text-left text-stone-600">
                <tr>
                    <th class="px-4 py-3">Período</th>
                    <th class="px-4 py-3">Vendas</th>
                    <th class="px-4 py-3">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
                @forelse ($rows as $row)
                    <tr>
                        <td class="px-4 py-3">{{ $row->label }}</td>
                        <td class="px-4 py-3">{{ $row->count }}</td>
                        <td class="px-4 py-3">R$ {{ number_format($row->total, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-stone-500">
                            Nenhuma venda registrada nesse período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr class="bg-stone-100 font-semibold text-brand-dark">
                        <td class="px-4 py-3">Total</td>
                        <td class="px-4 py-3">{{ $totalCount }}</td>
                        <td class="px-4 py-3">R$ {{ number_format($totalSum, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
