@extends('layouts.app')

@section('content')
<div x-data="{ activeTab: 'pedido' }" class="relative min-h-screen pb-20">
    <header class="bg-emerald-600 px-4 py-4 text-center text-lg font-semibold text-white">
        Pedido
    </header>

    <section class="p-6">
        <h1 class="mb-2 text-2xl font-bold">Pedido</h1>
        <p class="text-slate-700">Vista placeholder de pedidos. Próximamente se añadirá el detalle del pedido.</p>
    </section>

    <nav class="fixed bottom-0 left-1/2 flex w-full max-w-md -translate-x-1/2 border-t border-emerald-700 bg-emerald-600 text-lg text-white">
        <a href="{{ route('cliente.index') }}" @click="activeTab = 'cliente'" :class="activeTab === 'cliente' ? 'bg-emerald-700' : ''" class="flex-1 py-3 text-center">Cliente</a>
        <a href="{{ route('pedido.index') }}" @click="activeTab = 'pedido'" :class="activeTab === 'pedido' ? 'bg-emerald-700' : ''" class="flex-1 py-3 text-center">Pedido</a>
    </nav>
</div>
@endsection
