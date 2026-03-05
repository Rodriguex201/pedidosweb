@extends('layouts.app')

@section('content')

<div x-data="{ activeTab: 'cliente' }" class="relative min-h-screen pb-20 md:pb-8">
    <header class="flex items-center justify-between bg-emerald-600 px-4 py-3 text-white md:px-8 md:py-4">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="rounded-md p-2 hover:bg-emerald-700 md:hidden" aria-label="Abrir menú">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <p class="text-xs font-semibold uppercase tracking-wide md:text-sm">Cliente</p>
        </div>
        <button class="rounded-full bg-emerald-500 px-5 py-2 text-sm font-bold md:rounded-lg">CONTINUAR</button>
    </header>

    <section class="space-y-4 p-4 md:mx-auto md:max-w-5xl md:space-y-0 md:p-8">
        <div class="grid gap-6 md:grid-cols-2">
            <article class="rounded-2xl bg-slate-100 md:rounded-2xl md:border md:border-slate-200 md:bg-white md:p-5 md:shadow-sm">
                <h1 class="mb-4 text-3xl font-semibold md:text-2xl">Cliente</h1>

                <div class="space-y-3 text-base md:text-sm">
                    <div class="grid grid-cols-[auto_1fr_auto_auto_auto] items-center gap-2 md:grid-cols-[52px_1fr_40px_34px_110px]">
                        <label>NIT</label>
                        <input type="text" value="900123456" class="h-10 rounded-full border border-slate-300 bg-white px-4 md:rounded-lg md:bg-white">
                        <button type="button" class="h-10 w-10 rounded-md border border-slate-300 bg-slate-200 text-lg">🔍</button>
                        <label>Tel:</label>
                        <input type="text" value="(601) 3210000" class="h-10 rounded-full border border-slate-300 bg-slate-200 px-3 md:rounded-lg md:bg-white">
                    </div>

                    <div class="grid grid-cols-[80px_1fr] items-center gap-2 md:grid-cols-[88px_1fr]">
                        <label>Nombre:</label>
                        <input type="text" value="Comercial Ejemplo SAS" class="h-10 rounded-full border border-slate-300 bg-slate-200 px-4 md:rounded-lg md:bg-white">
                    </div>

                    <div class="grid grid-cols-[80px_1fr] items-center gap-2 md:grid-cols-[88px_1fr]">
                        <label>Correo:</label>
                        <input type="email" value="cliente@demo.com" class="h-10 rounded-full border border-slate-300 bg-slate-200 px-4 md:rounded-lg md:bg-white">
                    </div>

                    <div class="grid grid-cols-[80px_1fr] items-center gap-2 md:grid-cols-[88px_1fr]">
                        <label>Celular:</label>
                        <input type="text" value="3001234567" class="h-10 rounded-full border border-slate-300 bg-slate-200 px-4 md:rounded-lg md:bg-white">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-2">
                            <label class="block">Escala</label>
                            <input type="text" value="A" class="h-10 w-full rounded-full border border-slate-300 bg-slate-200 px-4 md:rounded-lg md:bg-white">
                            <label class="block">Cupo</label>
                            <input type="text" value="$12.000.000" class="h-10 w-full rounded-full border border-slate-300 bg-slate-200 px-4 md:rounded-lg md:bg-white">
                        </div>
                        <div class="space-y-2">
                            <label class="block">Saldo Vencido</label>
                            <input type="text" value="$1.200.000" class="h-10 w-full rounded-full border border-slate-300 bg-slate-200 px-4 md:rounded-lg md:bg-white">
                            <label class="block">Factura X Cobrar</label>
                            <input type="text" value="3" class="h-10 w-full rounded-full border border-slate-300 bg-slate-200 px-4 md:rounded-lg md:bg-white">
                        </div>
                    </div>
                </div>
            </article>

            <article class="rounded-2xl bg-slate-100 md:rounded-2xl md:border md:border-slate-200 md:bg-white md:p-5 md:shadow-sm">
                <h2 class="mb-4 text-3xl font-semibold md:text-2xl">Despacho</h2>
                <div class="space-y-2 text-base md:text-sm">
                    <label class="block">Recibe:</label>
                    <input type="text" value="Bodega Principal" class="h-10 w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 md:rounded-lg md:bg-white">

                    <label class="block">Dirección:</label>
                    <input type="text" value="Cra 10 # 20-30" class="h-10 w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 md:rounded-lg md:bg-white">

                    <label class="block">Ciudad:</label>
                    <input type="text" value="Bogotá" class="h-10 w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 md:rounded-lg md:bg-white">

                    <label class="block">Teléfono:</label>
                    <input type="text" value="6017654321" class="h-10 w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 md:rounded-lg md:bg-white">

                    <label class="block">Detalles:</label>
                    <textarea rows="3" class="w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 py-2 md:rounded-lg md:bg-white">Entregar en portería de 8am a 5pm.</textarea>
                </div>
            </article>
        </div>
    </section>

    <nav class="fixed bottom-0 left-0 right-0 z-30 flex border-t border-emerald-700 bg-emerald-600 text-base text-white md:hidden">

        <a href="{{ route('cliente.index') }}" @click="activeTab = 'cliente'" :class="activeTab === 'cliente' ? 'bg-emerald-700' : ''" class="flex-1 py-3 text-center">Cliente</a>
        <a href="{{ route('pedido.index') }}" @click="activeTab = 'pedido'" :class="activeTab === 'pedido' ? 'bg-emerald-700' : ''" class="flex-1 py-3 text-center">Pedido</a>
    </nav>
</div>
@endsection
