@extends('layouts.app')

@section('content')

<div x-data="{ activeTab: 'pedido' }" class="relative min-h-screen pb-20 md:pb-0">
    <header class="flex items-center justify-between rounded-t-xl bg-emerald-600 px-4 py-3 text-white md:rounded-none md:px-6 md:py-4">

        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="rounded-md p-2 hover:bg-emerald-700 md:hidden" aria-label="Abrir menú">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <p class="text-xs font-semibold uppercase tracking-wide md:text-sm">Pedido</p>
        </div>
        <button class="rounded-full bg-emerald-500 px-5 py-2 text-sm font-bold md:rounded-lg">CONTINUAR</button>
    </header>


    <section class="w-full max-w-md p-4 mx-auto md:max-w-none md:mx-0 md:p-6">
        <div class="w-full rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h1 class="mb-2 text-2xl font-bold">Pedido</h1>
            <p class="text-slate-700">Vista placeholder de pedidos. Próximamente se añadirá el detalle del pedido.</p>
        </div>
    </section>

    <nav class="fixed bottom-0 left-0 right-0 z-30 flex border-t border-emerald-700 bg-emerald-600 text-base text-white md:hidden">

        <a href="{{ route('cliente.index') }}" @click="activeTab = 'cliente'" :class="activeTab === 'cliente' ? 'bg-emerald-700' : ''" class="flex-1 py-3 text-center">Cliente</a>
        <a href="{{ route('pedido.index') }}" @click="activeTab = 'pedido'" :class="activeTab === 'pedido' ? 'bg-emerald-700' : ''" class="flex-1 py-3 text-center">Pedido</a>
    </nav>
</div>
@endsection
