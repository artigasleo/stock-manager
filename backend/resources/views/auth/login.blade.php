<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar - Stock Manager</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-cream text-stone-800 antialiased">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-sm bg-brand-paper rounded-lg shadow p-8">
            <h1 class="text-xl font-semibold text-brand-dark mb-6">Stock Manager</h1>

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium mb-1">E-mail</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autofocus
                        class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium mb-1">Senha</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full rounded-md bg-brand text-brand-cream py-2 text-sm font-medium hover:bg-brand-dark cursor-pointer"
                >
                    Entrar
                </button>
            </form>
        </div>
    </div>
</body>
</html>
