@extends('layouts.app')

@section('content')
    <div
        x-data="{
            modalOpen: {{ $errors->any() ? 'true' : 'false' }},
            editing: {{ old('editing_id') ? json_encode(['id' => (int) old('editing_id')]) : 'null' }},
        }"
    >
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold text-brand-dark">Usuários</h1>

            @can('users.edit')
                <button
                    type="button"
                    @click="editing = null; modalOpen = true"
                    class="rounded-md bg-brand text-brand-cream px-4 py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer"
                >
                    Novo usuário
                </button>
            @endcan
        </div>

        <div class="bg-brand-paper rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-stone-600">
                    <tr>
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">E-mail</th>
                        <th class="px-4 py-3">Papéis</th>
                        <th class="px-4 py-3 w-32">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3">{{ $user->name }}</td>
                            <td class="px-4 py-3">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                @forelse ($user->roles as $role)
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-stone-400">—</span>
                                @endforelse
                            </td>
                            <td class="px-4 py-3">
                                @can('users.edit')
                                    <button
                                        type="button"
                                        @click="editing = @js($user); modalOpen = true"
                                        class="text-brand hover:underline cursor-pointer mr-3"
                                    >
                                        Editar
                                    </button>

                                    @if ($user->id !== auth()->id() && ! $user->is_master)
                                        <form
                                            method="POST"
                                            action="{{ route('users.destroy', $user) }}"
                                            class="inline"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')"
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
                                Nenhum usuário cadastrado.
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
                <h2 class="text-lg font-semibold text-brand-dark mb-4" x-text="editing ? 'Editar usuário' : 'Novo usuário'"></h2>

                <form
                    method="POST"
                    :action="editing ? `/users/${editing.id}` : '{{ route('users.store') }}'"
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
                        <label class="block text-sm font-medium mb-1">Senha</label>
                        <input
                            type="password"
                            name="password"
                            class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                        <p class="mt-1 text-xs text-stone-500" x-show="editing">Deixe em branco para manter a senha atual.</p>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Papéis</label>
                        <div class="space-y-1">
                            @foreach ($roles as $role)
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="roles[]"
                                        value="{{ $role->name }}"
                                        :checked="editing && editing.roles !== undefined ? editing.roles.some((r) => r.name === '{{ $role->name }}') : {{ collect(old('roles', []))->contains($role->name) ? 'true' : 'false' }}"
                                        class="rounded border-stone-300"
                                    >
                                    <span class="text-sm">{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('roles')
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
