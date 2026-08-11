@extends('layouts.app')

@section('content')
    <div x-data="{ modalOpen: {{ $errors->any() ? 'true' : 'false' }} }">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold text-brand-dark">Estoque</h1>

            <button
                type="button"
                @click="modalOpen = true"
                class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer"
            >
                Nova movimentação
            </button>
        </div>

        <form method="GET" action="{{ route('stock.index') }}" class="mb-4">
            <select
                name="product_id"
                onchange="this.form.submit()"
                class="rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
            >
                <option value="">Todos os produtos</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected($selectedProductId == $product->id)>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-3">Data</th>
                        <th class="px-4 py-3">Produto</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Quantidade</th>
                        <th class="px-4 py-3">Motivo</th>
                        <th class="px-4 py-3">Usuário</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="px-4 py-3">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $movement->product->name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $movement->type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $movement->type === 'in' ? 'Entrada' : 'Saída' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $movement->type === 'in' ? '+' : '-' }}{{ $movement->quantity }}</td>
                            <td class="px-4 py-3">{{ $movement->reason ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $movement->user->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-stone-500">
                                Nenhuma movimentação registrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div
            x-show="modalOpen"
            x-cloak
            class="fixed inset-0 bg-black/40 flex items-center justify-center p-4"
            style="display: none;"
        >
            <div class="w-full max-w-sm bg-brand-paper rounded-lg shadow p-6" @click.outside="modalOpen = false">
                <h2 class="text-lg font-semibold text-brand-dark mb-4">Nova movimentação</h2>

                <form method="POST" action="{{ route('stock.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium mb-1">Produto</label>
                        <select
                            name="product_id"
                            class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                            <option value="" disabled @selected(!old('product_id'))>Selecione...</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                    {{ $product->name }} (atual: {{ $product->quantity }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Tipo</label>
                        <select
                            name="type"
                            class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                            <option value="in" @selected(old('type', 'in') === 'in')>Entrada</option>
                            <option value="out" @selected(old('type') === 'out')>Saída</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Quantidade</label>
                        <input
                            type="number"
                            min="1"
                            name="quantity"
                            value="{{ old('quantity') }}"
                            class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                        @error('quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Motivo</label>
                        <input
                            type="text"
                            name="reason"
                            value="{{ old('reason') }}"
                            class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                        @error('reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
