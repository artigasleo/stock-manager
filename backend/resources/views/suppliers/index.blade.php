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
            <h1 class="text-2xl font-semibold text-brand-dark">Fornecedores</h1>

            <button
                type="button"
                @click="editing = null; modalOpen = true"
                class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer"
            >
                Novo fornecedor
            </button>
        </div>

        <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">CNPJ/CPF</th>
                        <th class="px-4 py-3">Telefone</th>
                        <th class="px-4 py-3">E-mail</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 w-32">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="px-4 py-3">{{ $supplier->name }}</td>
                            <td class="px-4 py-3">{{ $supplier->document ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $supplier->phone ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $supplier->email ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $supplier->active ? 'bg-green-100 text-green-800' : 'bg-stone-200 text-stone-600' }}">
                                    {{ $supplier->active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    type="button"
                                    @click="editing = @json($supplier); modalOpen = true"
                                    class="text-brand hover:underline cursor-pointer mr-3"
                                >
                                    Editar
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('suppliers.destroy', $supplier) }}"
                                    class="inline"
                                    onsubmit="return confirm('Tem certeza que deseja excluir este fornecedor?')"
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
                            <td colspan="6" class="px-4 py-6 text-center text-stone-500">
                                Nenhum fornecedor cadastrado.
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
            <div class="w-full max-w-md bg-brand-paper rounded-lg shadow p-6" @click.outside="modalOpen = false">
                <h2 class="text-lg font-semibold text-brand-dark mb-4" x-text="editing ? 'Editar fornecedor' : 'Novo fornecedor'"></h2>

                <form
                    method="POST"
                    :action="editing ? `/suppliers/${editing.id}` : '{{ route('suppliers.store') }}'"
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

                    <div>
                        <label class="block text-sm font-medium mb-1">CNPJ/CPF</label>
                        <input
                            type="text"
                            name="document"
                            :value="editing && editing.document !== undefined ? editing.document : '{{ old('document') }}'"
                            class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                        @error('document')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Telefone</label>
                        <input
                            type="text"
                            name="phone"
                            :value="editing && editing.phone !== undefined ? editing.phone : '{{ old('phone') }}'"
                            class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">E-mail</label>
                        <input
                            type="email"
                            name="email"
                            :value="editing && editing.email !== undefined ? editing.email : '{{ old('email') }}'"
                            class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Endereço</label>
                        <input
                            type="text"
                            name="address"
                            :value="editing && editing.address !== undefined ? editing.address : '{{ old('address') }}'"
                            class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="active" value="0">
                        <input
                            id="supplier-active"
                            type="checkbox"
                            name="active"
                            value="1"
                            :checked="editing && editing.active !== undefined ? editing.active : {{ $activeDefault ? 'true' : 'false' }}"
                            class="rounded border-stone-300"
                        >
                        <label for="supplier-active" class="text-sm">Ativo</label>
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
