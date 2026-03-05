@extends('layouts.app')

@section('content')
<div x-data="{ drawerOpen: false, activeTab: 'cliente' }" class="relative min-h-screen pb-20">
    <header class="flex items-center justify-between bg-emerald-600 px-4 py-3 text-white">
        <button @click="drawerOpen = true" class="rounded-md p-2 hover:bg-emerald-700" aria-label="Abrir menú">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <p class="text-xs font-semibold uppercase tracking-wide">Cliente</p>
        <button class="rounded-full bg-emerald-500 px-5 py-2 text-sm font-bold">CONTINUAR</button>
    </header>

    <div x-show="drawerOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/50" @click="drawerOpen = false"></div>

    <aside x-show="drawerOpen" x-transition:enter="transition duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-white">
        <div class="bg-lime-400 px-4 py-4 text-center">
            <p class="text-3xl font-extrabold text-white">FACTURA TOUCH</p>
            <p class="text-3xl font-bold text-black">Menú de Navegación</p>
        </div>
        <button @click="drawerOpen = false" class="self-end p-3 text-slate-500" aria-label="Cerrar menú">✕</button>
        <nav class="text-3xl">
            <a href="{{ route('cliente.index') }}" class="block border-y border-slate-200 px-6 py-4">Realizar Pedidos</a>
            <a href="#" class="block border-b border-slate-200 px-6 py-4">Pedidos En Proceso</a>
            <a href="#" class="block border-b border-slate-200 px-6 py-4">Pedidos Aprobados</a>
        </nav>
        <div class="mt-auto p-4">
            <button class="w-full rounded bg-rose-900 py-3 text-lg font-bold text-white">SALIR</button>
        </div>
    </aside>

    <section class="space-y-5 p-4">
        <h1 class="text-4xl font-semibold">Cliente</h1>

        <div class="space-y-3 text-2xl">
            <div class="flex items-center gap-2">
                <label class="w-14">NIT</label>
                <input type="text" value="900123456" class="h-10 flex-1 rounded-full border border-slate-300 bg-white px-4">
                <button type="button" class="h-10 w-10 rounded-md border border-slate-300 bg-slate-200 text-xl">🔍</button>
                <label class="w-10">Tel:</label>
                <input type="text" value="(601) 3210000" class="h-10 w-28 rounded-full bg-slate-300 px-3">
            </div>

            <div class="flex items-center gap-2">
                <label class="w-24">Nombre:</label>
                <input type="text" value="Comercial Ejemplo SAS" class="h-10 flex-1 rounded-full bg-slate-300 px-4">
            </div>
            <div class="flex items-center gap-2">
                <label class="w-24">Correo:</label>
                <input type="email" value="cliente@demo.com" class="h-10 flex-1 rounded-full bg-slate-300 px-4">
            </div>
            <div class="flex items-center gap-2">
                <label class="w-24">Celular:</label>
                <input type="text" value="3001234567" class="h-10 flex-1 rounded-full bg-slate-300 px-4">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-2">
                    <label>Escala</label>
                    <input type="text" value="A" class="h-10 w-full rounded-full bg-slate-300 px-4">
                    <label>Cupo</label>
                    <input type="text" value="$12.000.000" class="h-10 w-full rounded-full bg-slate-300 px-4">
                </div>
                <div class="space-y-2">
                    <label>Saldo Vencido</label>
                    <input type="text" value="$1.200.000" class="h-10 w-full rounded-full bg-slate-300 px-4">
                    <label>Factura X Cobrar</label>
                    <input type="text" value="3" class="h-10 w-full rounded-full bg-slate-300 px-4">
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <h2 class="text-4xl font-semibold">Despacho</h2>
            <div class="space-y-2 text-2xl">
                <label class="block">Recibe:</label>
                <input type="text" value="Bodega Principal" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-100 px-4">
                <label class="block">Dirección:</label>
                <input type="text" value="Cra 10 # 20-30" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-100 px-4">
                <label class="block">Ciudad:</label>
                <input type="text" value="Bogotá" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-100 px-4">
                <label class="block">Teléfono:</label>
                <input type="text" value="6017654321" class="h-11 w-full rounded-2xl border border-slate-300 bg-slate-100 px-4">
                <label class="block">Detalles:</label>
                <textarea rows="2" class="w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 py-2">Entregar en portería de 8am a 5pm.</textarea>
            </div>
        </div>
    </section>

    <nav class="fixed bottom-0 left-1/2 flex w-full max-w-md -translate-x-1/2 border-t border-emerald-700 bg-emerald-600 text-lg text-white">
        <a href="{{ route('cliente.index') }}" @click="activeTab = 'cliente'" :class="activeTab === 'cliente' ? 'bg-emerald-700' : ''" class="flex-1 py-3 text-center">Cliente</a>
        <a href="{{ route('pedido.index') }}" @click="activeTab = 'pedido'" :class="activeTab === 'pedido' ? 'bg-emerald-700' : ''" class="flex-1 py-3 text-center">Pedido</a>
    </nav>
</div>
@endsection
