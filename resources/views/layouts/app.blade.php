<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Pedidos Web' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body x-data="{ sidebarOpen: false }" class="min-h-screen bg-slate-200 text-slate-900">
    <div class="mx-auto min-h-screen w-full max-w-md bg-slate-100 shadow-xl md:max-w-6xl md:bg-transparent md:shadow-none">
        <div class="min-h-screen md:grid md:grid-cols-[260px_1fr] md:gap-0">
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 z-40 bg-black/50 md:hidden"
                @click="sidebarOpen = false"
            ></div>

            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] flex-col bg-white transition-transform duration-200 md:static md:z-10 md:w-full md:max-w-none md:translate-x-0 md:border-r md:border-slate-200"
            >
                <div class="bg-lime-400 px-4 py-5 text-center">
                    <p class="text-3xl font-extrabold text-white">FACTURA TOUCH</p>
                    <p class="text-3xl font-bold text-black">Menú de Navegación</p>
                </div>

                <button
                    @click="sidebarOpen = false"
                    class="self-end p-3 text-slate-500 md:hidden"
                    aria-label="Cerrar menú"
                >
                    ✕
                </button>

                <nav class="text-2xl md:text-lg">
                    <a href="{{ route('cliente.index') }}" class="block border-y border-slate-200 px-6 py-4">Realizar Pedidos</a>
                    <a href="#" class="block border-b border-slate-200 px-6 py-4">Pedidos En Proceso</a>
                    <a href="#" class="block border-b border-slate-200 px-6 py-4">Pedidos Aprobados</a>
                </nav>

                <div class="mt-auto p-4">
                    <button class="w-full rounded bg-rose-900 py-3 text-lg font-bold text-white">SALIR</button>
                </div>
            </aside>

            <main class="min-h-screen bg-slate-100 md:bg-slate-100">
                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>
