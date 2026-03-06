@extends('layouts.app')

@section('content')
<div
    x-data="clienteView('{{ route('cliente.buscar') }}', '{{ csrf_token() }}')"
    class="relative min-h-screen pb-20 md:pb-0"
>
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

            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h1 class="mb-5 text-2xl font-bold text-slate-900 md:text-2xl">Cliente</h1>

                <div class="space-y-4 text-sm text-slate-800">
                    <template x-if="mensajeError">
                        <p class="rounded-lg bg-rose-50 px-3 py-2 text-rose-700" x-text="mensajeError"></p>
                    </template>

                    <div class="space-y-3">
                        <div class="grid grid-cols-[64px_minmax(0,1fr)_50px] items-center gap-2">
                            <label class="font-medium">NIT</label>

                            <input
                                x-model="form.nit"
                                @keydown.enter.prevent="buscarCliente"
                                type="text"
                                class="h-11 w-full min-w-0 rounded-xl border border-slate-300 bg-white px-4 outline-none"
                                placeholder="Digite NIT"
                            >

                            <button
                                type="button"
                                @click="buscarCliente"
                                :disabled="cargando"
                                class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-300 bg-slate-100 text-lg hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <svg x-show="!cargando" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                </svg>
                                <svg x-show="cargando" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-[64px_minmax(0,1fr)] items-center gap-2">
                            <label class="font-medium">Tel:</label>
                            <input x-model="form.telefono" type="text" class="h-11 w-full min-w-0 rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-[92px_minmax(0,1fr)] sm:items-center">
                        <label class="font-medium">Nombre:</label>
                        <input x-model="form.nombre" type="text" class="h-11 w-full min-w-0 rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white">
                    </div>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-[92px_minmax(0,1fr)] sm:items-center">
                        <label class="font-medium">Correo:</label>
                        <input x-model="form.correo" type="email" class="h-11 w-full min-w-0 rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white">
                    </div>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-[92px_minmax(0,1fr)] sm:items-center">
                        <label class="font-medium">Celular:</label>
                        <input x-model="form.celular" type="text" class="h-11 w-full min-w-0 rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white">
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label class="block font-medium">Escala</label>
                            <input x-model="form.escala" type="text" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white">
                        </div>

                        <div class="space-y-2">
                            <label class="block font-medium">Saldo Vencido</label>
                            <input :value="formatoMoneda(form.saldo_vencido)" type="text" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label class="block font-medium">Cupo</label>
                            <input :value="formatoMoneda(form.cupo)" type="text" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white">
                        </div>

                        <div class="space-y-2">
                            <label class="block font-medium">Factura X Cobrar</label>
                            <input x-model="form.facturas_por_cobrar" type="text" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white">
                        </div>
                    </div>
                </div>
            </article>

            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-5 text-2xl font-bold text-slate-900 md:text-2xl">Despacho</h2>

                <div class="space-y-4 text-sm text-slate-800">
                    <div class="space-y-2">
                        <label class="block font-medium">Recibe:</label>
                        <input type="text" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white">
                    </div>

                    <div class="space-y-2">
                        <label class="block font-medium">Dirección:</label>
                        <input type="text" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white">
                    </div>

                    <div class="space-y-2">
                        <label class="block font-medium">Ciudad:</label>
                        <input x-model="form.ciudad" type="text" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white">
                    </div>

                    <div class="space-y-2">
                        <label class="block font-medium">Teléfono:</label>
                        <input x-model="form.telefono" type="text" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-100 px-4 outline-none md:bg-white">
                    </div>

                    <div class="space-y-2">
                        <label class="block font-medium">Detalles:</label>
                        <textarea rows="4" class="w-full rounded-xl border border-slate-300 bg-slate-100 px-4 py-3 outline-none md:bg-white"></textarea>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <nav class="fixed bottom-0 left-0 right-0 z-30 flex border-t border-emerald-700 bg-emerald-600 text-base text-white md:hidden">
        <a href="{{ route('cliente.index') }}" class="flex-1 bg-emerald-700 py-3 text-center">Cliente</a>
        <a href="{{ route('pedido.index') }}" class="flex-1 py-3 text-center">Pedido</a>
    </nav>
</div>

<script>
    function clienteView(urlBuscar, csrfToken) {
        return {
            cargando: false,
            mensajeError: '',
            form: {
                nit: '',
                nombre: '',
                ciudad: '',
                telefono: '',
                correo: '',
                cupo: 0,
                escala: '',
                celular: '',
                saldo_vencido: 0,
                facturas_por_cobrar: 0,
            },
            async buscarCliente() {
                if (!this.form.nit.trim()) {
                    this.mensajeError = 'Debes digitar un NIT para buscar.';
                    return;
                }

                this.cargando = true;
                this.mensajeError = '';

                try {
                    const response = await fetch(urlBuscar, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ nit: this.form.nit.trim() }),
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        this.mensajeError = payload.message ?? 'No fue posible consultar el cliente.';
                        return;
                    }

                    const data = payload.data;
                    this.form = {
                        ...this.form,
                        nit: data.nit ?? this.form.nit,
                        nombre: data.nombre ?? '',
                        ciudad: data.ciudad ?? '',
                        telefono: data.telefono ?? '',
                        correo: data.correo ?? '',
                        cupo: Number(data.cupo ?? 0),
                        escala: data.escala ?? '',
                        celular: data.celular ?? '',
                        saldo_vencido: Number(data.saldo_vencido ?? 0),
                        facturas_por_cobrar: Number(data.facturas_por_cobrar ?? 0),
                    };
                } catch (error) {
                    this.mensajeError = 'Error de conexión al buscar el cliente.';
                } finally {
                    this.cargando = false;
                }
            },
            formatoMoneda(valor) {
                return new Intl.NumberFormat('es-CO', {
                    style: 'currency',
                    currency: 'COP',
                    minimumFractionDigits: 0,
                }).format(Number(valor ?? 0));
            },
        };
    }
</script>
@endsection
