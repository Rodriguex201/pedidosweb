<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Pedidos Web' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body
    x-data="{ sidebarOpen: window.innerWidth >= 1024, alertOpen: {{ session('status') || session('error') || $errors->any() ? 'true' : 'false' }} }"
    x-init="window.addEventListener('resize', () => { if (window.innerWidth >= 1024) sidebarOpen = true })"
    class="min-h-screen bg-slate-50 text-slate-900 antialiased"
>
    @php
        $headerData = $sessionHeaderData ?? [];
        $userName = $headerData['userName'] ?? null;
        $operarioName = $headerData['operarioName'] ?? null;
        $empresaInfo = $headerData['empresaInfo'] ?? null;
        $navItems = [
            ['label' => 'Realizar pedidos', 'route' => 'cliente.index', 'active' => ['cliente.*', 'pedido.index']],
            ['label' => 'Pedidos en proceso', 'route' => 'pedido.proceso', 'active' => ['pedido.proceso', 'pedido.detalle']],
            ['label' => 'Pedidos aprobados', 'route' => 'pedido.aprobados', 'active' => ['pedido.aprobados']],
            ['label' => 'Histórico', 'route' => 'pedido.historico', 'active' => ['pedido.historico']],
        ];
    @endphp

    <div class="min-h-screen lg:flex">
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-950/45 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            :class="sidebarOpen ? 'translate-x-0 lg:w-72' : '-translate-x-full lg:w-0 lg:translate-x-0 lg:overflow-hidden lg:border-r-0'"
            class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[88vw] flex-col border-r border-slate-200 bg-white shadow-2xl shadow-slate-950/10 transition-all duration-300 lg:sticky lg:top-0 lg:h-screen lg:shadow-none"
        >
            <div class="flex h-full w-72 flex-col">
                <div class="border-b border-slate-200 px-5 py-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">PedidosWeb</p>
                            <h1 class="mt-1 text-xl font-semibold text-slate-950">Factura Touch</h1>
                        </div>
                        <button
                            @click="sidebarOpen = false"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                            aria-label="Cerrar menú"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                            </svg>
                        </button>
                    </div>

                    @if ($empresaInfo || $userName || $operarioName)
                        <div class="mt-5 rounded-xl border border-emerald-100 bg-emerald-50/70 px-3 py-3 text-xs text-slate-700">
                            @if ($empresaInfo)
                                <p class="font-semibold text-slate-900">{{ $empresaInfo }}</p>
                            @endif
                            @if ($userName)
                                <p class="mt-1">Usuario: <span class="font-medium">{{ $userName }}</span></p>
                            @endif
                            @if ($operarioName)
                                <p class="mt-1">Operario: <span class="font-medium">{{ $operarioName }}</span></p>
                            @endif
                        </div>
                    @endif
                </div>

                <nav class="flex-1 space-y-1 px-3 py-4">
                    @foreach ($navItems as $item)
                        @php $isActive = collect($item['active'])->contains(fn ($pattern) => request()->routeIs($pattern)); @endphp
                        <a
                            href="{{ route($item['route']) }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition {{ $isActive ? 'bg-emerald-600 text-white shadow-md shadow-emerald-100' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}"
                            @click="if (window.innerWidth < 1024) sidebarOpen = false"
                        >
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $isActive ? 'bg-white/15' : 'bg-slate-100 text-slate-500 group-hover:bg-white' }}">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10" />
                                </svg>
                            </span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="border-t border-slate-200 p-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 focus:outline-none focus:ring-4 focus:ring-rose-100">
                            Salir
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="min-w-0 flex-1">
            <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur">
                <div class="flex min-h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button
                            @click="sidebarOpen = !sidebarOpen"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-950 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                            aria-label="Alternar menú"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-600">Panel de pedidos</p>
                            <p class="text-sm font-medium text-slate-600">Gestión comercial y operativa</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
    </div>

    @if (session('status') || session('error') || $errors->any())
        <div
            x-show="alertOpen"
            x-transition.opacity
            class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/45 px-4 py-6 backdrop-blur-sm"
        >
            <section class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-2xl shadow-slate-950/20">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ session('status') ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        @if (session('status'))
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                            </svg>
                        @else
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 4.3 2.6 18a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z" />
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-base font-semibold text-slate-950">{{ session('status') ? 'Operación exitosa' : 'Revisa esta información' }}</h2>
                        <div class="mt-2 space-y-2 text-sm leading-6 text-slate-600">
                            @if (session('status'))
                                <p>{{ session('status') }}</p>
                            @endif
                            @if (session('error'))
                                <p>{{ session('error') }}</p>
                            @endif
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-100 transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        @click="alertOpen = false"
                    >
                        Entendido
                    </button>
                </div>
            </section>
        </div>
    @endif
</body>
</html>
