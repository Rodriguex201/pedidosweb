@extends('layouts.app')

@section('content')
<div x-data="{ activeTab: 'cliente' }" class="relative min-h-screen pb-20 md:pb-0">
    <header class="flex items-center justify-between rounded-t-xl bg-emerald-600 px-4 py-3 text-white md:rounded-none md:px-6 md:py-4">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="rounded-md p-2 hover:bg-emerald-700 md:hidden" aria-label="Abrir menú">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <p class="text-xs font-semibold uppercase tracking-wide md:text-sm">Cliente</p>
        </div>

        <a href="{{ route('pedido.index') }}" class="rounded-full bg-emerald-500 px-5 py-2 text-sm font-bold md:rounded-lg">
            CONTINUAR
        </a>
    </header>

    <section class="mx-auto w-full max-w-md bg-slate-100 p-4 md:mx-0 md:max-w-none md:p-6">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 md:gap-6">

            {{-- Card Cliente --}}
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h1 class="mb-5 text-2xl font-bold text-slate-900 md:text-2xl">Cliente</h1>

                <div class="space-y-4 text-sm text-slate-800">

                    {{-- NIT + lupa + Tel responsive --}}
<div class="space-y-3">

                    {{-- NIT + lupa --}}
                <div class="grid grid-cols-[64px_minmax(0,1fr)_50px] items-center gap-2">
                    <label class="font-medium">NIT</label>

                    <input
                        type="text"
                        value="900123456"
                        class="h-11 w-full min-w-0 rounded-xl border border-slate-300 bg-white px-4 outline-none"
                    >

                    <button
                        type="button"
                        class="h-11 w-11 rounded-xl border border-slate-300 bg-slate-100 text-lg hover:bg-slate-200"
                    >
                        🔍
                    </button>
                </div>

                {{-- Teléfono --}}
                <div class="grid grid-cols-[64px_minmax(0,1fr)] items-center gap-2">
                    <label class="font-medium">Tel:</label>

                    <input
                        type="text"
                        value="(601) 3210000"
                        class="h-11 w-full min-w-0 rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white"
                    >
                </div>

            </div>

                    {{-- Nombre --}}
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-[92px_minmax(0,1fr)] sm:items-center">
                        <label class="font-medium">Nombre:</label>
                        <input
                            type="text"
                            value="Comercial Ejemplo SAS"
                            class="h-11 w-full min-w-0 rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white"
                        >
                    </div>

                    {{-- Correo --}}
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-[92px_minmax(0,1fr)] sm:items-center">
                        <label class="font-medium">Correo:</label>
                        <input
                            type="email"
                            value="cliente@demo.com"
                            class="h-11 w-full min-w-0 rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white"
                        >
                    </div>

                    {{-- Celular --}}
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-[92px_minmax(0,1fr)] sm:items-center">
                        <label class="font-medium">Celular:</label>
                        <input
                            type="text"
                            value="3001234567"
                            class="h-11 w-full min-w-0 rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white"
                        >
                    </div>

                    {{-- Escala / Saldo --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label class="block font-medium">Escala</label>
                            <input
                                type="text"
                                value="A"
                                class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white"
                            >
                        </div>

                        <div class="space-y-2">
                            <label class="block font-medium">Saldo Vencido</label>
                            <input
                                type="text"
                                value="$1.200.000"
                                class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white"
                            >
                        </div>
                    </div>

                    {{-- Cupo / Factura --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label class="block font-medium">Cupo</label>
                            <input
                                type="text"
                                value="$12.000.000"
                                class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white"
                            >
                        </div>

                        <div class="space-y-2">
                            <label class="block font-medium">Factura X Cobrar</label>
                            <input
                                type="text"
                                value="3"
                                class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white"
                            >
                        </div>
                    </div>
                </div>
            </article>

            {{-- Card Despacho --}}
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-5 text-2xl font-bold text-slate-900 md:text-2xl">Despacho</h2>

                <div class="space-y-4 text-sm text-slate-800">
                    <div class="space-y-2">
                        <label class="block font-medium">Recibe:</label>
                        <input
                            type="text"
                            value="Bodega Principal"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white"
                        >
                    </div>

                    <div class="space-y-2">
                        <label class="block font-medium">Dirección:</label>
                        <input
                            type="text"
                            value="Cra 10 # 20-30"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white"
                        >
                    </div>

                    <div class="space-y-2">
                        <label class="block font-medium">Ciudad:</label>
                        <input
                            type="text"
                            value="Bogotá"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white"
                        >
                    </div>

                    <div class="space-y-2">
                        <label class="block font-medium">Teléfono:</label>
                        <input
                            type="text"
                            value="6017654321"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white"
                        >
                    </div>

                    <div class="space-y-2">
                        <label class="block font-medium">Detalles:</label>
                        <textarea
                            rows="4"
                            class="w-full rounded-xl border border-slate-300 bg-slate-100 px-4 py-3 outline-none md:bg-white"
                        >Entregar en portería de 8am a 5pm.</textarea>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <nav class="fixed bottom-0 left-0 right-0 z-30 flex border-t border-emerald-700 bg-emerald-600 text-base text-white md:hidden">
        <a
            href="{{ route('cliente.index') }}"
            @click="activeTab = 'cliente'"
            :class="activeTab === 'cliente' ? 'bg-emerald-700' : ''"
            class="flex-1 py-3 text-center"
        >
            Cliente
        </a>

        <a
            href="{{ route('pedido.index') }}"
            @click="activeTab = 'pedido'"
            :class="activeTab === 'pedido' ? 'bg-emerald-700' : ''"
            class="flex-1 py-3 text-center"
        >
            Pedido
        </a>
    </nav>
</div>
@endsection