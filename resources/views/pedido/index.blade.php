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
        <span class="rounded-full bg-emerald-500 px-5 py-2 text-sm font-bold md:rounded-lg">GESTIÓN</span>
    </header>

    <section class="mx-auto w-full max-w-md space-y-4 bg-slate-100 p-4 md:mx-0 md:max-w-none md:space-y-5 md:p-6">
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
            <h1 class="text-2xl font-bold text-slate-900 md:text-3xl">Gestión de Pedido</h1>
            <div class="mt-3 grid gap-2 text-sm text-slate-700 md:grid-cols-2 md:gap-4 md:text-base">
                <p><span class="font-semibold">CC/Nit:</span> 900123456-7</p>
                <p><span class="font-semibold">Cliente:</span> Comercial Ejemplo SAS</p>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2 md:grid-cols-4">
                <button type="button" class="rounded-lg bg-violet-600 px-3 py-2 text-sm font-semibold text-white">Cod.Barras</button>
                <button type="button" class="rounded-lg bg-slate-500 px-3 py-2 text-sm font-semibold text-white">Reiniciar</button>
                <button type="button" class="rounded-lg bg-amber-400 px-3 py-2 text-sm font-semibold text-slate-900">Actualizar</button>
                <button type="button" class="rounded-lg bg-cyan-600 px-3 py-2 text-sm font-semibold text-white">Terminar</button>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-2 md:gap-3">
                <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2">
                    <p class="text-xs uppercase text-slate-500">Total $</p>
                    <p class="text-lg font-bold text-emerald-700">0</p>
                </div>
                <div class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-2">
                    <p class="text-xs uppercase text-slate-500">Cantidad</p>
                    <p class="text-lg font-bold text-blue-700">0</p>
                </div>
                <div class="rounded-xl border border-violet-100 bg-violet-50 px-3 py-2">
                    <p class="text-xs uppercase text-slate-500">kg</p>
                    <p class="text-lg font-bold text-violet-700">0</p>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
            <div class="grid gap-3 md:grid-cols-12">
                <div class="md:col-span-5">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Código Artículo</label>
                    <div class="flex gap-2">
                        <input type="text" value="" placeholder="Ej. ART-001" class="h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-emerald-500 md:h-10" />
                        <button type="button" class="h-11 w-11 rounded-lg border border-slate-300 bg-slate-100 text-lg md:h-10 md:w-10">🔍</button>
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Cantidad</label>
                    <div class="flex items-center gap-2">
                        <button type="button" class="h-11 w-10 rounded-lg border border-slate-300 bg-slate-100 text-lg md:h-10">-</button>
                        <input type="text" value="0" class="h-11 w-full rounded-lg border border-slate-300 px-3 text-center text-sm outline-none focus:border-emerald-500 md:h-10" />
                        <button type="button" class="h-11 w-10 rounded-lg border border-slate-300 bg-slate-100 text-lg md:h-10">+</button>
                    </div>
                </div>

                <div class="md:col-span-4">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Valor Unidad</label>
                    <input type="text" value="0" class="h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-emerald-500 md:h-10" />
                </div>

                <div class="md:col-span-8">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Detalle Movimiento (Opcional)</label>
                    <input type="text" value="" placeholder="Detalle adicional del artículo" class="h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-emerald-500 md:h-10" />
                </div>

                <div class="md:col-span-4">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Valor Parcial</label>
                    <input type="text" value="0" class="h-11 w-full rounded-lg border border-slate-300 bg-slate-50 px-3 text-sm font-semibold outline-none md:h-10" />
                </div>

                <div class="md:col-span-8">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Descripción Artículo</label>
                    <input type="text" value="" class="h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-emerald-500 md:h-10" />
                </div>

                <div class="md:col-span-4">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Marca</label>
                    <input type="text" value="" class="h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-emerald-500 md:h-10" />
                </div>

                <div class="grid grid-cols-3 gap-2 md:col-span-6">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Peso</label>
                        <input type="text" value="0" class="h-11 w-full rounded-lg border border-slate-300 px-3 text-sm md:h-10" />
                    </div>
                    <div class="col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Código Alter</label>
                        <input type="text" value="" class="h-11 w-full rounded-lg border border-slate-300 px-3 text-sm md:h-10" />
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Stock</label>
                    <input type="text" value="0" class="h-11 w-full rounded-lg border border-slate-300 bg-slate-50 px-3 text-sm font-semibold md:h-10" />
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
            <div class="grid grid-cols-4 rounded-t-lg bg-slate-200 text-xs font-semibold uppercase tracking-wide text-slate-700 md:text-sm">
                <div class="border-r border-slate-300 px-2 py-2">Artículo</div>
                <div class="border-r border-slate-300 px-2 py-2 text-center">Cantidad</div>
                <div class="border-r border-slate-300 px-2 py-2 text-center">Val.Unidad</div>
                <div class="px-2 py-2 text-center">Parcial</div>
            </div>
            <div class="h-40 rounded-b-lg border border-t-0 border-slate-300 bg-slate-100 md:h-48"></div>
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
