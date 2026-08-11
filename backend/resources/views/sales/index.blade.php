@extends('layouts.app')

@php
    $statusLabels = [
        'awaiting_payment' => 'Aguardando Pagamento',
        'paid' => 'Paga',
        'invoiced' => 'Faturada',
        'cancelled' => 'Cancelada',
    ];

    $statusColors = [
        'awaiting_payment' => 'bg-amber-100 text-amber-800',
        'paid' => 'bg-green-100 text-green-800',
        'invoiced' => 'bg-blue-100 text-blue-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];

    $paymentLabels = [
        'cash' => 'Dinheiro',
        'pix' => 'PIX',
        'debit_card' => 'Cartão de Débito',
        'credit_card' => 'Cartão de Crédito',
        'other' => 'Outro',
    ];
@endphp

@section('content')
    <div
        x-data="{
            modalOpen: {{ $errors->any() ? 'true' : 'false' }},
            items: {{ old('items') ? json_encode(old('items')) : "[{ product_id: '', quantity: 1, unit_price: '' }]" }},
        }"
    >
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold text-brand-dark">Vendas</h1>

            <button
                type="button"
                @click="modalOpen = true"
                class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer"
            >
                Nova venda
            </button>
        </div>

        <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-3">Data</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Itens</th>
                        <th class="px-4 py-3">Pagamento</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Vendedor</th>
                        <th class="px-4 py-3 w-44">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="px-4 py-3">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $sale->customer?->name ?? 'Não identificado' }}</td>
                            <td class="px-4 py-3">{{ $sale->items->pluck('product.name')->join(', ') }}</td>
                            <td class="px-4 py-3">{{ $paymentLabels[$sale->payment_method] ?? '—' }}</td>
                            <td class="px-4 py-3">R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $sale->user->name }}</td>
                            <td class="px-4 py-3">
                                @if ($sale->status === 'cancelled')
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $statusColors[$sale->status] }}">
                                        {{ $statusLabels[$sale->status] }}
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('sales.updateStatus', $sale) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select
                                            name="status"
                                            onchange="this.form.submit()"
                                            class="rounded-md border border-stone-300 px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-brand {{ $statusColors[$sale->status] }}"
                                        >
                                            @foreach ($statusLabels as $value => $label)
                                                <option value="{{ $value }}" @selected($sale->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-stone-500">
                                Nenhuma venda registrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div
            x-show="modalOpen"
            x-cloak
            class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 overflow-y-auto"
            style="display: none;"
        >
            <div class="w-full max-w-2xl bg-brand-paper rounded-lg shadow p-6 my-8" @click.outside="modalOpen = false">
                <h2 class="text-lg font-semibold text-brand-dark mb-4">Nova venda</h2>

                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('sales.store') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Cliente (opcional)</label>
                            <select
                                name="customer_id"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                                <option value="" @selected(!old('customer_id'))>Não identificado</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Forma de pagamento</label>
                            <select
                                name="payment_method"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                                <option value="" @selected(!old('payment_method'))>Selecione...</option>
                                @foreach ($paymentLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium">Itens</label>
                            <button
                                type="button"
                                @click="items.push({ product_id: '', quantity: 1, unit_price: '' })"
                                class="text-sm text-brand hover:underline cursor-pointer"
                            >
                                + Adicionar item
                            </button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="grid grid-cols-12 gap-2 items-center">
                                    <select
                                        :name="`items[${index}][product_id]`"
                                        x-model="item.product_id"
                                        class="col-span-6 rounded-md border border-stone-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                                    >
                                        <option value="">Selecione...</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} (disp.: {{ $product->quantity }})</option>
                                        @endforeach
                                    </select>

                                    <input
                                        type="number"
                                        min="1"
                                        placeholder="Qtd."
                                        :name="`items[${index}][quantity]`"
                                        x-model="item.quantity"
                                        class="col-span-2 rounded-md border border-stone-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                                    >

                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="Preço unit."
                                        :name="`items[${index}][unit_price]`"
                                        x-model="item.unit_price"
                                        class="col-span-3 rounded-md border border-stone-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                                    >

                                    <button
                                        type="button"
                                        @click="items.length > 1 ? items.splice(index, 1) : null"
                                        class="col-span-1 text-red-600 hover:underline cursor-pointer text-sm"
                                    >
                                        Remover
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 text-sm cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
