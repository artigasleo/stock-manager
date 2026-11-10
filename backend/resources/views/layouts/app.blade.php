<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Manager - Go &amp; Do Emporium</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-cream text-stone-800 antialiased">
    <div class="min-h-screen flex flex-col" x-data="{ sidebarOpen: false }">
        <header class="bg-brand text-brand-cream shadow">
            <div class="flex items-center justify-between px-4 sm:px-6 py-1">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="md:hidden rounded-md p-1.5 hover:bg-brand-dark cursor-pointer"
                        aria-label="Abrir menu"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </button>

                    <img src="{{ asset('images/logo.png') }}" alt="Go &amp; Do Emporium" class="h-10 sm:h-16 w-auto">
                </div>

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

        <div class="flex flex-1 relative">
            <div
                x-show="sidebarOpen"
                x-cloak
                @click="sidebarOpen = false"
                class="fixed inset-0 bg-black/40 z-30 md:hidden"
            ></div>

            <nav
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                class="fixed md:static inset-y-0 left-0 z-40 w-60 shrink-0 bg-brand-paper border-r border-stone-200 p-4 overflow-y-auto transition-transform duration-200 md:translate-x-0"
            >
                @php
                    $navItems = [
                        ['label' => 'Dashboard', 'route' => 'dashboard', 'permission' => 'dashboard.view'],
                        ['label' => 'Relatórios', 'route' => 'reports.index', 'permission' => 'reports.view'],
                        ['label' => 'Categorias', 'route' => 'categories.index', 'permission' => 'categories.view'],
                        ['label' => 'Estoque', 'route' => 'products.index', 'permission' => 'products.view'],
                        ['label' => 'Fornecedores', 'route' => 'suppliers.index', 'permission' => 'suppliers.view'],
                        ['label' => 'Clientes', 'route' => 'customers.index', 'permission' => 'customers.view'],
                        ['label' => 'Vendedores', 'route' => 'sellers.index', 'permission' => 'sellers.view'],
                        ['label' => 'Movimentações', 'route' => 'stock.index', 'permission' => 'stock.view'],
                        ['label' => 'Compras', 'route' => 'purchases.index', 'permission' => 'purchases.view'],
                        ['label' => 'Vendas', 'route' => 'sales.index', 'permission' => 'sales.view'],
                        ['label' => 'Fechamento de Caixa', 'route' => 'cashClosings.index', 'permission' => 'sales.edit'],
                        ['label' => 'Unidades', 'route' => 'units.index', 'permission' => 'units.view'],
                        ['label' => 'Usuários', 'route' => 'users.index', 'permission' => 'users.view'],
                        ['label' => 'Funções', 'route' => 'roles.index', 'permission' => 'users.view'],
                        ['label' => 'Configurações', 'route' => 'settings.index', 'permission' => 'settings.view'],
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

            <main class="flex-1 min-w-0 p-6">
                @if (session('success'))
                    <div
                        x-data="{ show: true }"
                        x-init="setTimeout(() => show = false, 4000)"
                        x-show="show"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed top-4 right-4 z-50 max-w-sm rounded-md border border-green-200 bg-green-50 text-green-800 px-4 py-3 text-sm shadow-lg flex items-start gap-3"
                    >
                        <span class="flex-1">{{ session('success') }}</span>
                        <button
                            type="button"
                            @click="show = false"
                            class="text-green-800/60 hover:text-green-800 cursor-pointer leading-none"
                        >
                            &times;
                        </button>
                    </div>
                @endif

                @if ($errors->any() && ! $errors->hasAny(['product', 'xlsx_file', 'xml_file']))
                    <div
                        x-data="{ show: true }"
                        x-init="setTimeout(() => show = false, 8000)"
                        x-show="show"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed top-4 right-4 z-50 max-w-sm rounded-md border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm shadow-lg flex items-start gap-3"
                    >
                        <div class="flex-1 space-y-1">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                        <button
                            type="button"
                            @click="show = false"
                            class="text-red-800/60 hover:text-red-800 cursor-pointer leading-none"
                        >
                            &times;
                        </button>
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
