@extends('layouts.app')

@section('content')
    <div
        x-data="{
            modalOpen: {{ $errors->any() ? 'true' : 'false' }},
            editing: {{ old('editing_id') ? json_encode(['id' => (int) old('editing_id')]) : 'null' }},
        }"
    >
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold text-brand-dark">Papéis</h1>

            @can('users.edit')
                <button
                    type="button"
                    @click="editing = null; modalOpen = true"
                    class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer"
                >
                    Novo papel
                </button>
            @endcan
        </div>

        <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">Usuários</th>
                        <th class="px-4 py-3">Permissões</th>
                        <th class="px-4 py-3 w-32">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($roles as $role)
                        <tr>
                            <td class="px-4 py-3">{{ $role->name }}</td>
                            <td class="px-4 py-3">{{ $role->users_count }}</td>
                            <td class="px-4 py-3">{{ $role->permissions->count() }} de {{ $permissions->count() }}</td>
                            <td class="px-4 py-3">
                                @can('users.edit')
                                    <button
                                        type="button"
                                        @click="editing = @js($role); modalOpen = true"
                                        class="text-brand hover:underline cursor-pointer mr-3"
                                    >
                                        Editar
                                    </button>

                                    @if ($role->name !== 'admin')
                                        <form
                                            method="POST"
                                            action="{{ route('roles.destroy', $role) }}"
                                            class="inline"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este papel?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline cursor-pointer">
                                                Excluir
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-stone-500">
                                Nenhum papel cadastrado.
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
                <h2 class="text-lg font-semibold text-brand-dark mb-4" x-text="editing ? 'Editar papel' : 'Novo papel'"></h2>

                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form
                    method="POST"
                    :action="editing ? `/roles/${editing.id}` : '{{ route('roles.store') }}'"
                    class="space-y-4"
                >
                    @csrf
                    <input type="hidden" name="_method" :value="editing ? 'PUT' : 'POST'">
                    <input type="hidden" name="editing_id" :value="editing ? editing.id : ''">

                    <div>
                        <label class="block text-sm font-medium mb-1">Nome do papel</label>
                        <input
                            type="text"
                            name="name"
                            :value="editing && editing.name !== undefined ? editing.name : '{{ old('name') }}'"
                            class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Permissões</label>
                        <table class="w-full text-sm border border-stone-200 rounded-md overflow-hidden">
                            <thead class="bg-stone-100 text-stone-600">
                                <tr>
                                    <th class="px-3 py-2 text-left">Módulo</th>
                                    <th class="px-3 py-2 text-center w-20">Ver</th>
                                    <th class="px-3 py-2 text-center w-20">Editar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200">
                                @foreach ($modules as $slug => $module)
                                    <tr>
                                        <td class="px-3 py-2">{{ $module['label'] }}</td>
                                        <td class="px-3 py-2 text-center">
                                            @if (in_array('view', $module['actions']))
                                                <input
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    value="{{ $slug }}.view"
                                                    :checked="editing && editing.permissions !== undefined ? editing.permissions.some((p) => p.name === '{{ $slug }}.view') : {{ collect(old('permissions', []))->contains($slug.'.view') ? 'true' : 'false' }}"
                                                    class="rounded border-stone-300"
                                                >
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            @if (in_array('edit', $module['actions']))
                                                <input
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    value="{{ $slug }}.edit"
                                                    :checked="editing && editing.permissions !== undefined ? editing.permissions.some((p) => p.name === '{{ $slug }}.edit') : {{ collect(old('permissions', []))->contains($slug.'.edit') ? 'true' : 'false' }}"
                                                    class="rounded border-stone-300"
                                                >
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
