@extends('layouts.app')

@section('content')
    @php $activeDefault = $errors->any() ? (bool) old('active') : true; @endphp

    <div
        x-data="{
            modalOpen: {{ $errors->any() ? 'true' : 'false' }},
            editing: {{ old('editing_id') ? json_encode(['id' => (int) old('editing_id')]) : 'null' }},
        }"
    >
        <div class="mb-4">
            <p class="text-sm text-stone-600 max-w-2xl">
                Cada unidade tem seu próprio controle de estoque. Hoje o sistema opera com uma única unidade
                padrão — cadastrar uma segunda aqui não faz nada usá-la automaticamente em Vendas/Compras/Estoque
                ainda; isso exige um seletor de loja, que é um passo futuro.
            </p>
        </div>

        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold text-brand-dark">Unidades</h1>

            <button
                type="button"
                @click="editing = null; modalOpen = true"
                class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer"
            >
                Nova unidade
            </button>
        </div>

        <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">Endereço</th>
                        <th class="px-4 py-3">Padrão</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 w-32">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($units as $unit)
                        <tr>
                            <td class="px-4 py-3">{{ $unit->name }}</td>
                            <td class="px-4 py-3">{{ $unit->address ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($unit->is_default)
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800">Padrão</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $unit->active ? 'bg-green-100 text-green-800' : 'bg-stone-200 text-stone-600' }}">
                                    {{ $unit->active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    type="button"
                                    @click="editing = @js($unit); modalOpen = true"
                                    class="text-brand hover:underline cursor-pointer mr-3"
                                >
                                    Editar
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('units.destroy', $unit) }}"
                                    class="inline"
                                    onsubmit="return confirm('Tem certeza que deseja excluir esta unidade?')"
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
                            <td colspan="5" class="px-4 py-6 text-center text-stone-500">
                                Nenhuma unidade cadastrada.
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
                <h2 class="text-lg font-semibold text-brand-dark mb-4" x-text="editing ? 'Editar unidade' : 'Nova unidade'"></h2>

                <form
                    method="POST"
                    :action="editing ? `/units/${editing.id}` : '{{ route('units.store') }}'"
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
                        <input
                            id="is_default"
                            type="checkbox"
                            name="is_default"
                            value="1"
                            :checked="editing && editing.is_default !== undefined ? editing.is_default : false"
                            class="rounded border-stone-300"
                        >
                        <label for="is_default" class="text-sm">Unidade padrão</label>
                    </div>
                    @error('is_default')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="active" value="0">
                        <input
                            id="active"
                            type="checkbox"
                            name="active"
                            value="1"
                            :checked="editing && editing.active !== undefined ? editing.active : {{ $activeDefault ? 'true' : 'false' }}"
                            class="rounded border-stone-300"
                        >
                        <label for="active" class="text-sm">Ativo</label>
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
