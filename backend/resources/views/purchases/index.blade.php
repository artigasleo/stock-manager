@extends('layouts.app')

@section('content')
    <div
        x-data="{
            modalOpen: {{ $errors->any() || old('items') ? 'true' : 'false' }},
            importModalOpen: {{ $errors->has('xml_file') ? 'true' : 'false' }},
            items: {{ old('items') ? json_encode(old('items')) : "[{ product_id: '', quantity: 1, unit_cost: '' }]" }},
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                } else {
                    this.items[0] = { product_id: '', quantity: 1, unit_cost: '' };
                }
            },
        }"
    >
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold text-brand-dark">Compras</h1>

            @can('purchases.edit')
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="importModalOpen = true"
                        class="rounded-md border border-brand text-brand px-3 py-1.5 text-sm font-medium hover:bg-brand hover:text-brand-cream cursor-pointer"
                    >
                        Importar XML
                    </button>

                    <button
                        type="button"
                        @click="modalOpen = true"
                        class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer"
                    >
                        Nova compra
                    </button>
                </div>
            @endcan
        </div>

        <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-3">Data</th>
                        <th class="px-4 py-3">Fornecedor</th>
                        <th class="px-4 py-3">Nota Fiscal</th>
                        <th class="px-4 py-3">Itens</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Usuário</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td class="px-4 py-3">{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $purchase->supplier->name }}</td>
                            <td class="px-4 py-3">{{ $purchase->invoice_number ?? '—' }}</td>
                            <td class="px-4 py-3">
                                {{ $purchase->items->pluck('product.name')->join(', ') }}
                            </td>
                            <td class="px-4 py-3">R$ {{ number_format($purchase->total, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $purchase->user->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-stone-500">
                                Nenhuma compra registrada.
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
                <h2 class="text-lg font-semibold text-brand-dark mb-4">Nova compra</h2>

                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('purchases.store') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Unidade</label>
                            <select
                                name="unit_id"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" @selected(old('unit_id', $defaultUnit->id) == $unit->id)>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Fornecedor</label>
                            <select
                                name="supplier_id"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                                <option value="" disabled @selected(!old('supplier_id'))>Selecione...</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Nota Fiscal (opcional)</label>
                            <input
                                type="text"
                                name="invoice_number"
                                value="{{ old('invoice_number') }}"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium">Itens</label>
                            <button
                                type="button"
                                @click="items.push({ product_id: '', quantity: 1, unit_cost: '' })"
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
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
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
                                        placeholder="Custo unit."
                                        :name="`items[${index}][unit_cost]`"
                                        x-model="item.unit_cost"
                                        class="col-span-3 rounded-md border border-stone-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                                    >

                                    <button
                                        type="button"
                                        @click="removeItem(index)"
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

        @can('purchases.edit')
            <div
                x-show="importModalOpen"
                x-cloak
                class="fixed inset-0 bg-black/40 flex items-center justify-center p-4"
                style="display: none;"
            >
                <div class="w-full max-w-sm bg-brand-paper rounded-lg shadow p-6" @click.outside="importModalOpen = false">
                    <h2 class="text-lg font-semibold text-brand-dark mb-4">Importar XML</h2>

                    <form method="POST" action="{{ route('purchases.import') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input
                            type="file"
                            name="xml_file"
                            accept=".xml"
                            required
                            class="w-full text-sm text-stone-600 rounded-md border border-stone-300 px-2 py-1.5 bg-white cursor-pointer file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-brand file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-brand-cream hover:file:bg-brand-dark"
                        >
                        @error('xml_file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="importModalOpen = false" class="px-4 py-2 text-sm cursor-pointer">
                                Cancelar
                            </button>
                            <button type="submit" class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer">
                                Importar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
@endsection
