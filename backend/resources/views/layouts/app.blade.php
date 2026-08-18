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
                        ['label' => 'Dashboard', 'route' => 'dashboard', 'permission' => 'dashboard.view'],
                        ['label' => 'Categorias', 'route' => 'categories.index', 'permission' => 'categories.view'],
                        ['label' => 'Estoque', 'route' => 'products.index', 'permission' => 'products.view'],
                        ['label' => 'Fornecedores', 'route' => 'suppliers.index', 'permission' => 'suppliers.view'],
                        ['label' => 'Clientes', 'route' => 'customers.index', 'permission' => 'customers.view'],
                        ['label' => 'Movimentações', 'route' => 'stock.index', 'permission' => 'stock.view'],
                        ['label' => 'Compras', 'route' => 'purchases.index', 'permission' => 'purchases.view'],
                        ['label' => 'Vendas', 'route' => 'sales.index', 'permission' => 'sales.view'],
                        ['label' => 'Unidades', 'route' => 'units.index', 'permission' => 'units.view'],
                        ['label' => 'Usuários', 'route' => 'users.index', 'permission' => 'users.view'],
                        ['label' => 'Papéis', 'route' => 'roles.index', 'permission' => 'users.view'],
                    ];
                @endphp

                <ul class="space-y-1">
                    @foreach ($navItems as $item)
                        @if ($item['permission'] === null || auth()->user()->can($item['permission']))
                            <li>
                                <a
                                    href="{{ route($item['route']) }}"
                                    class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($item['route']) ? 'bg-brand text-brand-cream' : 'text-stone-700 hover:bg-stone-100' }}"
                                >
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </nav>

            <main class="flex-1 p-6">
                @if (session('success'))
                    <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-4 rounded-md bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm">
                        {{ session('warning') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
