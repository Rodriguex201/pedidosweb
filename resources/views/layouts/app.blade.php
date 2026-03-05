<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Pedidos Web' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body x-data="{ sidebarOpen: false }" class="min-h-screen w-full bg-slate-100 text-slate-900">
    <div class="min-h-screen w-full md:grid md:grid-cols-[280px_1fr]">
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-black/50 md:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] flex-col bg-white transition-transform duration-200 md:hidden"
        >
            <div class="bg-lime-400 px-4 py-5 text-center">
                <p class="text-3xl font-extrabold text-white">FACTURA TOUCH</p>
                <p class="text-3xl font-bold text-black">Menú de Navegación</p>
            </div>

            <button
                @click="sidebarOpen = false"
                class="self-end p-3 text-slate-500"
                aria-label="Cerrar menú"
            >
                ✕
            </button>

            <nav class="text-2xl">
                <a href="{{ route('cliente.index') }}" class="block border-y border-slate-200 px-6 py-4">Realizar Pedidos</a>
                <a href="#" class="block border-b border-slate-200 px-6 py-4">Pedidos En Proceso</a>
                <a href="#" class="block border-b border-slate-200 px-6 py-4">Pedidos Aprobados</a>
            </nav>

            <div class="mt-auto p-4">
                <button class="w-full rounded bg-rose-900 py-3 text-lg font-bold text-white">SALIR</button>
            </div>
        </aside>

        <aside class="hidden border-r border-slate-200 bg-white md:flex md:flex-col md:h-screen md:sticky md:top-0">
            <div class="bg-lime-400 px-4 py-5 text-center">
                <p class="text-3xl font-extrabold text-white">FACTURA TOUCH</p>
                <p class="text-3xl font-bold text-black">Menú de Navegación</p>
            </div>

            <nav class="text-lg">
                <a href="{{ route('cliente.index') }}" class="block border-y border-slate-200 px-6 py-4">Realizar Pedidos</a>
                <a href="#" class="block border-b border-slate-200 px-6 py-4">Pedidos En Proceso</a>
                <a href="#" class="block border-b border-slate-200 px-6 py-4">Pedidos Aprobados</a>
            </nav>

            <div class="mt-auto p-4">
                <button class="w-full rounded bg-rose-900 py-3 text-lg font-bold text-white">SALIR</button>
            </div>
        </aside>

        <main class="w-full min-h-screen p-4 md:p-6">
            <div class="mx-auto max-w-md md:mx-0 md:max-w-none">
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>
