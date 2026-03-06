@extends('layouts.app')

@section('content')
<div class="relative min-h-screen pb-20 md:pb-0">
    <header class="flex items-center justify-between rounded-t-xl bg-emerald-600 px-4 py-3 text-white md:rounded-none md:px-6 md:py-4">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="rounded-md p-2 hover:bg-emerald-700 md:hidden" aria-label="Abrir menú">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <p class="text-xs font-semibold uppercase tracking-wide md:text-sm">Pedido</p>
        </div>
        <span class="rounded-full bg-emerald-500 px-5 py-2 text-sm font-bold md:rounded-lg">#{{ $pedidoId }}</span>
    </header>

    <section class="mx-auto w-full max-w-md space-y-4 bg-slate-100 p-4 md:mx-0 md:max-w-none md:space-y-5 md:p-6">
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
            <h1 class="text-2xl font-bold text-slate-900 md:text-3xl">Gestión de Pedido</h1>
            <div class="mt-3 grid gap-2 text-sm text-slate-700 md:grid-cols-2 md:gap-4 md:text-base">
                <p><span class="font-semibold">CC/Nit:</span> {{ $cliente['nit'] ?? 'N/A' }}</p>
                <p><span class="font-semibold">Cliente:</span> {{ $cliente['nombre'] ?? 'N/A' }}</p>
                <p><span class="font-semibold">Ciudad:</span> {{ $cliente['ciudad'] ?? 'N/A' }}</p>
                <p><span class="font-semibold">Teléfono:</span> {{ $cliente['telefono'] ?? 'N/A' }}</p>
            </div>

            <p class="mt-4 text-sm text-slate-600">Seleccione productos, agregue cantidades y construya el detalle del pedido desde el catálogo cargado.</p>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
            <h2 class="mb-3 text-lg font-semibold text-slate-800">Grupos de productos</h2>
            <div class="flex flex-wrap gap-2">
                @forelse ($grupos as $grupo)
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ data_get($grupo, 'grupnomb', data_get($grupo, 'grupcodigo', 'Grupo')) }}</span>
                @empty
                    <p class="text-sm text-slate-500">No hay grupos disponibles.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
            <h2 class="mb-3 text-lg font-semibold text-slate-800">Artículos del catálogo</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b bg-slate-100 text-left text-xs uppercase tracking-wide text-slate-600">
                            <th class="px-3 py-2">Código</th>
                            <th class="px-3 py-2">Grupo</th>
                            <th class="px-3 py-2">Artículo</th>
                            <th class="px-3 py-2 text-right">Precio</th>
                            <th class="px-3 py-2 text-right">IVA</th>
                            <th class="px-3 py-2 text-right">Stock</th>
                            <th class="px-3 py-2 text-right">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($articulos as $articulo)
                            <tr class="border-b last:border-0">
                                <td class="px-3 py-2">{{ data_get($articulo, 'articodigo') }}</td>
                                <td class="px-3 py-2">{{ data_get($articulo, 'artigrupo') }}</td>
                                <td class="px-3 py-2">{{ data_get($articulo, 'artinomb') }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format((float) data_get($articulo, 'artivlr1_c', 0), 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right">{{ data_get($articulo, 'artiiva', 0) }}</td>
                                <td class="px-3 py-2 text-right">{{ data_get($articulo, 'articant', 0) }}</td>
                                <td class="px-3 py-2 text-right">
                                    <input type="number" min="0" value="0" class="h-9 w-20 rounded-lg border border-slate-300 px-2 text-right" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-4 text-center text-slate-500">No hay artículos disponibles para este pedido.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <nav class="fixed bottom-0 left-0 right-0 z-30 flex border-t border-emerald-700 bg-emerald-600 text-base text-white md:hidden">
        <a
            href="{{ route('cliente.index') }}"
            class="flex-1 py-3 text-center {{ request()->routeIs('cliente.index') ? 'bg-emerald-700 font-semibold' : '' }}"
        >
            Cliente
        </a>
        <a
            href="{{ route('pedido.index') }}"
            class="flex-1 py-3 text-center {{ request()->routeIs('pedido.index') ? 'bg-emerald-700 font-semibold' : '' }}"
        >
            Pedido
        </a>
    </nav>
</div>
@endsection
