@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-semibold text-brand-dark mb-4">Fechamento de Caixa</h1>

    <form method="GET" action="{{ route('cashClosings.index') }}" class="flex items-end gap-3 mb-6">
        <div>
            <label class="block text-sm font-medium mb-1">Data</label>
            <input
                type="date"
                name="date"
                value="{{ $date }}"
                max="{{ now()->format('Y-m-d') }}"
                onchange="this.form.submit()"
                class="rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
            >
        </div>
    </form>

    <div
        x-data="{ openingCashUsd: {{ $openingCashUsd }}, usdExchangeRate: @js($usdExchangeRate) }"
        class="bg-brand-paper rounded-lg shadow p-6 mb-6 max-w-xl"
    >
        <h2 class="font-semibold text-brand-dark mb-1">Abertura de Caixa</h2>
        <p class="text-sm text-stone-500 mb-4">
            Fundo de caixa no início do dia — soma ao valor esperado de "Dinheiro/Real" e "Dinheiro/Dólar" na hora de fechar.
        </p>

        @if ($existing?->opened_at)
            <p class="text-sm text-stone-500 mb-4">
                Aberto por {{ $existing->openedBy->name }} em {{ $existing->opened_at->format('d/m/Y H:i') }}.
            </p>
        @endif

        <form method="POST" action="{{ route('cashClosings.openCash') }}" class="flex flex-wrap items-start gap-4">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <div>
                <label class="block text-sm font-medium mb-1">Dinheiro/Real (R$)</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="opening_cash"
                    value="{{ $openingCash }}"
                    class="w-32 rounded-md border {{ $errors->has('opening_cash') ? 'border-red-500' : 'border-stone-300' }} px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                >
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Dinheiro/Dólar (US$)</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="opening_cash_usd"
                    x-model="openingCashUsd"
                    class="w-32 rounded-md border {{ $errors->has('opening_cash_usd') ? 'border-red-500' : 'border-stone-300' }} px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                >
                <p x-show="usdExchangeRate && openingCashUsd" x-cloak class="mt-1 text-xs text-stone-500">
                    ≈ R$ <span x-text="(Number(openingCashUsd || 0) * Number(usdExchangeRate)).toFixed(2)"></span>
                </p>
                <p x-show="!usdExchangeRate && openingCashUsd" x-cloak class="mt-1 text-xs text-red-600">
                    Cotação não configurada. Vá em Configurações.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 invisible">Abrir</label>
                <button type="submit" class="rounded-md border border-brand text-brand px-4 py-2 text-sm font-medium hover:bg-brand hover:text-brand-cream cursor-pointer">
                    {{ $existing?->opened_at ? 'Corrigir abertura' : 'Abrir caixa' }}
                </button>
            </div>
        </form>
    </div>

    <div
        x-data="{
            rows: @js($rows),
            usdExchangeRate: @js($usdExchangeRate),
            syncCashUsd(row) {
                row.counted = (Number(row.countedUsd || 0) * Number(this.usdExchangeRate || 0)).toFixed(2);
            },
            totalExpected() {
                return this.rows.reduce((sum, row) => sum + Number(row.expected || 0), 0).toFixed(2);
            },
            totalCounted() {
                return this.rows.reduce((sum, row) => sum + Number(row.counted || 0), 0).toFixed(2);
            },
            totalDifference() {
                return (Number(this.totalCounted()) - Number(this.totalExpected())).toFixed(2);
            },
            rowDifference(row) {
                return (Number(row.counted || 0) - Number(row.expected || 0)).toFixed(2);
            },
        }"
        class="bg-brand-paper rounded-lg shadow p-6"
    >
        @if ($existing?->closed_at)
            <p class="text-sm text-stone-500 mb-4">
                Esse dia já foi fechado por {{ $existing->closedBy->name }} em {{ $existing->closed_at->format('d/m/Y H:i') }}.
                Salvar de novo substitui os valores contados.
            </p>
        @endif

        <form method="POST" action="{{ route('cashClosings.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <table class="w-full text-sm">
                <thead class="text-left text-stone-600">
                    <tr>
                        <th class="py-2">Forma de pagamento</th>
                        <th class="py-2">Esperado (R$)</th>
                        <th class="py-2">Contado (R$)</th>
                        <th class="py-2">Diferença (R$)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    <template x-for="(row, index) in rows" :key="row.method">
                        <tr>
                            <td class="py-2 align-top" x-text="row.label"></td>
                            <td class="py-2 align-top text-stone-500">
                                R$ <span x-text="Number(row.expected).toFixed(2)"></span>
                                <template x-if="row.method === 'cash_usd' && usdExchangeRate">
                                    <span class="block text-xs" x-text="'(≈ US$ ' + (Number(row.expected) / Number(usdExchangeRate)).toFixed(2) + ')'"></span>
                                </template>
                                <input type="hidden" :name="`items[${index}][payment_method]`" :value="row.method">
                            </td>
                            <td class="py-2 align-top">
                                <template x-if="row.method === 'cash_usd'">
                                    <div>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="US$"
                                            x-model="row.countedUsd"
                                            @input="syncCashUsd(row)"
                                            class="w-32 rounded-md border border-stone-300 px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                                        >
                                        <input type="hidden" :name="`items[${index}][counted_amount]`" :value="row.counted">
                                        <p x-show="usdExchangeRate" x-cloak class="mt-0.5 text-xs text-stone-500">
                                            ≈ R$ <span x-text="Number(row.counted || 0).toFixed(2)"></span>
                                        </p>
                                        <p x-show="!usdExchangeRate" x-cloak class="mt-0.5 text-xs text-red-600">
                                            Cotação não configurada.
                                        </p>
                                    </div>
                                </template>
                                <template x-if="row.method !== 'cash_usd'">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        :name="`items[${index}][counted_amount]`"
                                        x-model="row.counted"
                                        class="w-32 rounded-md border border-stone-300 px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                                    >
                                </template>
                            </td>
                            <td
                                class="py-2 align-top font-medium"
                                :class="Number(rowDifference(row)) === 0 ? 'text-stone-500' : (Number(rowDifference(row)) > 0 ? 'text-green-600' : 'text-red-600')"
                            >
                                R$ <span x-text="rowDifference(row)"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr class="border-t border-stone-300 font-semibold">
                        <td class="py-2">Total</td>
                        <td class="py-2" x-text="'R$ ' + totalExpected()"></td>
                        <td class="py-2" x-text="'R$ ' + totalCounted()"></td>
                        <td
                            class="py-2"
                            :class="Number(totalDifference()) === 0 ? 'text-stone-700' : (Number(totalDifference()) > 0 ? 'text-green-600' : 'text-red-600')"
                            x-text="'R$ ' + totalDifference()"
                        ></td>
                    </tr>
                </tfoot>
            </table>

            <div>
                <label class="block text-sm font-medium mb-1">Observações (opcional)</label>
                <textarea
                    name="notes"
                    rows="2"
                    placeholder="Ex: falta de troco, erro de contagem, etc."
                    class="w-full rounded-md border {{ $errors->has('notes') ? 'border-red-500' : 'border-stone-300' }} px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                >{{ old('notes', $existing?->notes) }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer">
                    {{ $existing ? 'Atualizar fechamento' : 'Fechar caixa do dia' }}
                </button>
            </div>
        </form>
    </div>

    <div class="mt-8">
        <h2 class="text-lg font-semibold text-brand-dark mb-3">Histórico</h2>

        <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-3">Data</th>
                        <th class="px-4 py-3">Esperado (R$)</th>
                        <th class="px-4 py-3">Contado (R$)</th>
                        <th class="px-4 py-3">Diferença (R$)</th>
                        <th class="px-4 py-3">Fechado por</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($history as $closing)
                        @php
                            $closingExpected = $closing->items->sum('expected_amount');
                            $closingCounted = $closing->items->sum('counted_amount');
                            $closingDiff = round($closingCounted - $closingExpected, 2);
                        @endphp
                        <tr>
                            <td class="px-4 py-3">{{ $closing->date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">R$ {{ number_format($closingExpected, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">R$ {{ number_format($closingCounted, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 font-medium {{ $closingDiff == 0 ? 'text-stone-600' : ($closingDiff > 0 ? 'text-green-600' : 'text-red-600') }}">
                                R$ {{ number_format($closingDiff, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">{{ $closing->closedBy->name }}</td>
                            <td class="px-4 py-3">
                                <a
                                    href="{{ route('cashClosings.index', ['date' => $closing->date->format('Y-m-d')]) }}"
                                    class="text-brand hover:underline cursor-pointer text-xs"
                                >
                                    Ver / Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-stone-500">
                                Nenhum fechamento registrado ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
