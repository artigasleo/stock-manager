<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Manager - Go &amp; Do Emporium</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-cream text-stone-800 antialiased">
    <div class="min-h-screen flex flex-col">
        <header class="bg-brand text-brand-cream shadow">
            <div class="flex items-center justify-between px-6 py-4">
                <span class="text-lg font-semibold">Stock Manager</span>

                @auth
                    <div class="flex items-center gap-4">
                        <span class="text-sm">{{ auth()->user()->name }}</span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm underline hover:no-underline cursor-pointer">
                                Sair
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </header>

        <div class="flex flex-1">
            <nav class="w-60 bg-brand-paper border-r border-stone-200 p-4">
                @php
                    $navItems = [
                        ['label' => 'Dashboard', 'route' => 'dashboard'],
                        ['label' => 'Categorias', 'route' => 'categories.index'],
                        ['label' => 'Produtos', 'route' => 'products.index'],
                        ['label' => 'Fornecedores', 'route' => 'suppliers.index'],
                        ['label' => 'Estoque', 'route' => 'stock.index'],
                    ];
                @endphp

                <ul class="space-y-1">
                    @foreach ($navItems as $item)
                        <li>
                            <a
                                href="{{ route($item['route']) }}"
                                class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($item['route']) ? 'bg-brand text-brand-cream' : 'text-stone-700 hover:bg-stone-100' }}"
                            >
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <main class="flex-1 p-6">
                @if (session('success'))
                    <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
