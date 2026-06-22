@extends('layouts.app')

@section('content')
<div
    x-data="clienteView('{{ route('cliente.buscar') }}', '{{ csrf_token() }}', @js($errors->first('continuar')))"
    class="relative pb-20 lg:pb-0"
>
    <header class="mb-5 flex flex-col gap-4 rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm shadow-slate-200/60 sm:flex-row sm:items-center sm:justify-between sm:p-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-600">Cliente</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-950">Datos del cliente y despacho</h1>
            <p class="mt-1 text-sm text-slate-500">Consulta por NIT o nombre y confirma la información de entrega.</p>
        </div>

        <form method="POST" action="{{ route('cliente.continuar') }}">
            @csrf
            <input type="hidden" name="despacho_recibe" :value="despacho.recibe">
            <input type="hidden" name="despacho_direccion" :value="despacho.direccion">
            <input type="hidden" name="despacho_ciudad" :value="despacho.ciudad">
            <input type="hidden" name="despacho_telefono" :value="despacho.telefono">
            <input type="hidden" name="despacho_detalle" :value="despacho.detalle">
            <button type="submit" class="btn-primary w-full sm:w-auto">
                Continuar
            </button>
        </form>
    </header>

    <section class="w-full">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <article class="app-panel">
                <h2 class="mb-5 text-lg font-semibold text-slate-950">Información comercial</h2>

                <div class="space-y-4 text-sm text-slate-800">
                    <template x-if="mensajeError">
                        <p class="soft-alert-error" x-text="mensajeError"></p>
                    </template>

                    <div class="grid gap-2 sm:grid-cols-[128px_minmax(0,1fr)_48px] sm:items-center">
                        <label class="app-label">NIT o nombre</label>
                        <input x-model="terminoBusqueda" @keydown.enter.prevent="buscarCliente" type="text" class="app-input min-w-0" placeholder="Digite NIT o nombre del cliente">
                        <button type="button" @click="buscarCliente" :disabled="cargando" class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-950 disabled:cursor-not-allowed disabled:opacity-60">
                            <svg x-show="!cargando" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                            </svg>
                            <svg x-show="cargando" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-[92px_minmax(0,1fr)] sm:items-center">
                        <label class="app-label">NIT</label>
                        <input x-model="form.nit" type="text" class="app-input min-w-0 bg-slate-50" readonly>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-[92px_minmax(0,1fr)] sm:items-center">
                        <label class="app-label">Nombre</label>
                        <input x-model="form.nombre" type="text" class="app-input min-w-0 bg-slate-50" readonly>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-[92px_minmax(0,1fr)] sm:items-center">
                        <label class="app-label">Teléfono</label>
                        <input x-model="form.telefono" type="text" class="app-input min-w-0 bg-slate-50" readonly>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-[92px_minmax(0,1fr)] sm:items-center">
                        <label class="app-label">Correo</label>
                        <input x-model="form.correo" type="email" class="app-input min-w-0 bg-slate-50" readonly>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-[92px_minmax(0,1fr)] sm:items-center">
                        <label class="app-label">Celular</label>
                        <input x-model="form.celular" type="text" class="app-input min-w-0 bg-slate-50" readonly>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label class="app-label block">Escala</label>
                            <input x-model="form.escala" type="text" class="app-input bg-slate-50" readonly>
                        </div>
                        <div class="space-y-2">
                            <label class="app-label block">Saldo vencido</label>
                            <input :value="formatoMoneda(form.saldo_vencido)" type="text" class="app-input bg-slate-50" readonly>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label class="app-label block">Cupo</label>
                            <input :value="formatoMoneda(form.cupo)" type="text" class="app-input bg-slate-50" readonly>
                        </div>
                        <div class="space-y-2">
                            <label class="app-label block">Facturas por cobrar</label>
                            <input x-model="form.facturas_por_cobrar" type="text" class="app-input bg-slate-50" readonly>
                        </div>
                    </div>
                </div>
            </article>

            <article class="app-panel">
                <h2 class="mb-5 text-lg font-semibold text-slate-950">Despacho</h2>

                <div class="space-y-4 text-sm text-slate-800">
                    <div class="space-y-2">
                        <label class="app-label block">Recibe</label>
                        <input x-model="despacho.recibe" type="text" class="app-input">
                    </div>
                    <div class="space-y-2">
                        <label class="app-label block">Dirección</label>
                        <input x-model="despacho.direccion" :disabled="!despacho.recibe" type="text" class="app-input">
                    </div>
                    <div class="space-y-2">
                        <label class="app-label block">Ciudad</label>
                        <input x-model="despacho.ciudad" :disabled="!despacho.recibe" type="text" class="app-input">
                    </div>
                    <div class="space-y-2">
                        <label class="app-label block">Teléfono</label>
                        <input x-model="despacho.telefono" :disabled="!despacho.recibe" type="text" class="app-input">
                    </div>
                    <div class="space-y-2">
                        <label class="app-label block">Detalles</label>
                        <textarea x-model="despacho.detalle" rows="4" class="app-textarea"></textarea>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <div
        x-show="modalClientes"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-6 backdrop-blur-sm"
        style="display: none;"
    >
        <section
            @click.outside="modalClientes = false"
            class="max-h-[90vh] w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/20"
        >
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 p-5">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Clientes similares</h2>
                    <p class="text-sm text-slate-500">Se encontraron varias coincidencias. Selecciona el cliente correcto.</p>
                </div>
                <button type="button" class="btn-secondary h-9 w-9 p-0" @click="modalClientes = false" aria-label="Cerrar selección">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <div class="max-h-[65vh] overflow-y-auto p-3">
                <template x-for="cliente in clientesSimilares" :key="cliente.nit">
                    <button
                        type="button"
                        class="mb-2 w-full rounded-xl border border-slate-200 bg-white p-4 text-left transition hover:border-emerald-200 hover:bg-emerald-50/60"
                        @click="seleccionarCliente(cliente)"
                    >
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-950" x-text="cliente.nombre || 'Cliente sin nombre'"></p>
                                <p class="mt-1 text-sm text-slate-500">
                                    NIT: <span x-text="cliente.nit || 'N/A'"></span>
                                </p>
                            </div>
                            <div class="text-sm text-slate-600 sm:text-right">
                                <p x-text="cliente.ciudad || 'Sin ciudad'"></p>
                                <p x-text="cliente.telefono || 'Sin teléfono'"></p>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-slate-500" x-show="cliente.direccion" x-text="cliente.direccion"></p>
                    </button>
                </template>
            </div>
        </section>
    </div>

    <nav class="fixed bottom-0 left-0 right-0 z-30 grid grid-cols-2 border-t border-slate-200 bg-white/95 p-2 text-sm font-semibold shadow-2xl shadow-slate-950/10 backdrop-blur lg:hidden">
        <a href="{{ route('cliente.index') }}" class="rounded-xl bg-emerald-600 py-3 text-center text-white">Cliente</a>
        <a href="{{ route('pedido.index') }}" class="rounded-xl py-3 text-center text-slate-600">Pedido</a>
    </nav>
</div>

<script>
    function clienteView(urlBuscar, csrfToken, errorContinuar = '') {
        return {
            cargando: false,
            mensajeError: errorContinuar ?? '',
            terminoBusqueda: '',
            modalClientes: false,
            clientesSimilares: [],
            form: {
                nit: '',
                nombre: '',
                ciudad: '',
                direccion: '',
                telefono: '',
                correo: '',
                cupo: 0,
                escala: '',
                celular: '',
                saldo_vencido: 0,
                facturas_por_cobrar: 0,
            },
            despacho: {
                recibe: '',
                direccion: '',
                ciudad: '',
                telefono: '',
                detalle: '',
            },
            async buscarCliente() {
                const termino = this.terminoBusqueda.trim();

                if (!termino) {
                    this.mensajeError = 'Digite un NIT o nombre para buscar.';
                    return;
                }

                await this.consultarCliente(termino, true);
            },
            async consultarCliente(termino, mostrarCoincidencias = true) {
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
                        body: JSON.stringify({ termino }),
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        this.mensajeError = payload.message ?? Object.values(payload.errors ?? {})?.flat()?.[0] ?? 'No fue posible consultar el cliente.';
                        return;
                    }

                    if (Array.isArray(payload.matches) && payload.matches.length > 0) {
                        this.clientesSimilares = payload.matches;
                        this.modalClientes = mostrarCoincidencias;
                        return;
                    }

                    if (payload.data) {
                        this.aplicarCliente(payload.data);
                        this.modalClientes = false;
                    }
                } catch (error) {
                    this.mensajeError = 'Error de conexión al buscar el cliente.';
                } finally {
                    this.cargando = false;
                }
            },
            async seleccionarCliente(cliente) {
                this.aplicarCliente(cliente);
                this.modalClientes = false;
                await this.consultarCliente(String(cliente.nit ?? ''), false);
            },
            aplicarCliente(data) {
                this.form = {
                    ...this.form,
                    nit: data.nit ?? '',
                    nombre: data.nombre ?? '',
                    ciudad: data.ciudad ?? '',
                    direccion: data.direccion ?? '',
                    telefono: data.telefono ?? '',
                    correo: data.correo ?? '',
                    cupo: Number(data.cupo ?? 0),
                    escala: data.escala ?? '',
                    celular: data.celular ?? '',
                    saldo_vencido: Number(data.saldo_vencido ?? 0),
                    facturas_por_cobrar: Number(data.facturas_por_cobrar ?? 0),
                };
                this.terminoBusqueda = data.nombre ?? data.nit ?? '';
                this.despacho = {
                    ...this.despacho,
                    direccion: data.direccion ?? '',
                    ciudad: data.ciudad ?? '',
                    telefono: data.telefono ?? '',
                };
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
