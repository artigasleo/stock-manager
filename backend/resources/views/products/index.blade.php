@extends('layouts.app')

@section('content')
    @php $activeDefault = $errors->any() ? (bool) old('active') : true; @endphp

    <div
        x-data="{
            modalOpen: {{ $errors->any() ? 'true' : 'false' }},
            editing: {{ old('editing_id') ? json_encode(['id' => (int) old('editing_id')]) : 'null' }},
        }"
    >
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold text-brand-dark">Produtos</h1>

            <button
                type="button"
                @click="editing = null; modalOpen = true"
                class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer"
            >
                Novo produto
            </button>
        </div>

        <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-3">Código</th>
                        <th class="px-4 py-3">Cód. Barras</th>
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">Categoria</th>
                        <th class="px-4 py-3">Fornecedor</th>
                        <th class="px-4 py-3">Qtd.</th>
                        <th class="px-4 py-3">Preço de venda</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 w-32">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-4 py-3">{{ $product->code }}</td>
                            <td class="px-4 py-3">{{ $product->barcode ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $product->name }}</td>
                            <td class="px-4 py-3">{{ $product->category->name }}</td>
                            <td class="px-4 py-3">{{ $product->supplier?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $product->quantity }}</td>
                            <td class="px-4 py-3">{{ number_format($product->sale_price, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $product->active ? 'bg-green-100 text-green-800' : 'bg-stone-200 text-stone-600' }}">
                                    {{ $product->active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    type="button"
                                    @click="editing = @js($product); modalOpen = true"
                                    class="text-brand hover:underline cursor-pointer mr-3"
                                >
                                    Editar
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('products.destroy', $product) }}"
                                    class="inline"
                                    onsubmit="return confirm('Tem certeza que deseja excluir este produto?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline cursor-pointer">
                                        Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-stone-500">
                                Nenhum produto cadastrado.
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
            <div class="w-full max-w-lg bg-brand-paper rounded-lg shadow p-6 my-8" @click.outside="modalOpen = false">
                <h2 class="text-lg font-semibold text-brand-dark mb-4" x-text="editing ? 'Editar produto' : 'Novo produto'"></h2>

                <form
                    method="POST"
                    :action="editing ? `/products/${editing.id}` : '{{ route('products.store') }}'"
                    class="space-y-4"
                >
                    @csrf
                    <input type="hidden" name="_method" :value="editing ? 'PUT' : 'POST'">
                    <input type="hidden" name="editing_id" :value="editing ? editing.id : ''">

                    <div>
                        <label class="block text-sm font-medium mb-1">Nome</label>
                        <input
                            type="text"
                            name="name"
                            :value="editing && editing.name !== undefined ? editing.name : '{{ old('name') }}'"
                            class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Código</label>
                            <input
                                type="text"
                                name="code"
                                :value="editing && editing.code !== undefined ? editing.code : '{{ old('code') }}'"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                            @error('code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Código de barras</label>
                            <input
                                type="text"
                                name="barcode"
                                :value="editing && editing.barcode !== undefined ? editing.barcode : '{{ old('barcode') }}'"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                            @error('barcode')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Categoria</label>
                            <select
                                name="category_id"
                                :value="editing && editing.category_id !== undefined ? editing.category_id : undefined"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                                <option value="" disabled @selected(!old('category_id'))>Selecione...</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Fornecedor</label>
                            <select
                                name="supplier_id"
                                :value="editing && editing.supplier_id !== undefined ? editing.supplier_id : undefined"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                                <option value="" @selected(!old('supplier_id'))>Nenhum</option>
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
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Quantidade</label>
                            <input
                                type="number"
                                min="0"
                                name="quantity"
                                :value="editing && editing.quantity !== undefined ? editing.quantity : '{{ old('quantity', 0) }}'"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Estoque mínimo</label>
                            <input
                                type="number"
                                min="0"
                                name="min_stock"
                                :value="editing && editing.min_stock !== undefined ? editing.min_stock : '{{ old('min_stock', 0) }}'"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                            @error('min_stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Validade</label>
                            <input
                                type="date"
                                name="expiration_date"
                                :value="editing && editing.expiration_date !== undefined ? (editing.expiration_date ? editing.expiration_date.substring(0, 10) : '') : '{{ old('expiration_date') }}'"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                            @error('expiration_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Preço de custo</label>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                name="cost_price"
                                :value="editing && editing.cost_price !== undefined ? editing.cost_price : '{{ old('cost_price') }}'"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                            @error('cost_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Preço de venda</label>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                name="sale_price"
                                :value="editing && editing.sale_price !== undefined ? editing.sale_price : '{{ old('sale_price') }}'"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                            @error('sale_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="active" value="0">
                        <input
                            id="product-active"
                            type="checkbox"
                            name="active"
                            value="1"
                            :checked="editing && editing.active !== undefined ? editing.active : {{ $activeDefault ? 'true' : 'false' }}"
                            class="rounded border-stone-300"
                        >
                        <label for="product-active" class="text-sm">Ativo</label>
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
