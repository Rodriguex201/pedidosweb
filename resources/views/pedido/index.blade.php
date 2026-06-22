@extends('layouts.app')

@section('content')
<div
    class="relative pb-20 lg:pb-0"
    x-data="{
        articulo: null,
        cantidad: 0,
        detalle: '',
        codigo: '',
        valorUnidad: 0,
        enviarCorreo: true,
        listaPrecio: {{ (int) ($cliente['precio'] ?? 1) }},
        showSearchModal: {{ $busquedaRealizada ? 'true' : 'false' }},
        selectedMovimiento: null,
        movimientoBaseUrl: '{{ url('/pedido/movimiento') }}',
        barcodeSearchUrl: '{{ url('/pedido/articulo') }}',
        barcodeReaderId: 'barcode-reader-{{ $pedidoId }}',
        showScanner: false,
        barcodeScanner: null,
        isScanning: false,
        barcodeStatus: '',
        barcodeError: '',
        seleccionar(item) {
            this.selectedMovimiento = null;
            this.articulo = item;
            this.codigo = item.articodigo || '';
            this.cantidad = 1;
            if (this.listaPrecio === 2) {
                this.valorUnidad = Number(item.artivlr2_c || item.artivlr1_c || 0);
            } else if (this.listaPrecio === 3) {
                this.valorUnidad = Number(item.artivlr3_c || item.artivlr1_c || 0);
            } else if (this.listaPrecio === 4) {
                this.valorUnidad = Number(item.artivlr4_c || item.artivlr1_c || 0);
            } else {
                this.valorUnidad = Number(item.artivlr1_c || 0);
            }
            this.showSearchModal = false;
        },
        seleccionarMovimiento(item) {
            this.selectedMovimiento = item;
            this.articulo = null;
            this.codigo = item.codigo || '';
            this.cantidad = Number(item.cantidad || 0);
            this.valorUnidad = Number(item.valor || 0);
            this.detalle = item.detalle || '';
        },
        parcial() {
            return Number(this.cantidad || 0) * Number(this.valorUnidad || 0);
        },
        dinero(valor) {
            return '$' + Number(valor || 0).toLocaleString('es-CO');
        },
        limpiarArticulo() {
            this.articulo = null;
            this.cantidad = 0;
            this.detalle = '';
            this.codigo = '';
            this.valorUnidad = 0;
            this.selectedMovimiento = null;
        },
        async openBarcodeScanner() {
            this.showScanner = true;
            this.barcodeError = '';
            this.barcodeStatus = 'Preparando camara...';
            this.$nextTick(() => this.startBarcodeScanner());
        },
        async startBarcodeScanner() {
            if (!window.Html5Qrcode) {
                this.barcodeError = 'No fue posible cargar el lector de codigo de barras.';
                this.barcodeStatus = '';
                return;
            }

            await this.stopBarcodeScanner();

            try {
                this.barcodeScanner = new window.Html5Qrcode(this.barcodeReaderId);
                this.isScanning = true;
                await this.barcodeScanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 260, height: 160 } },
                    (decodedText) => this.onBarcodeScanned(decodedText),
                    () => {}
                );
                this.barcodeStatus = 'Centre el codigo dentro de la pantalla';
            } catch (error) {
                this.isScanning = false;
                this.barcodeError = error?.message || 'No fue posible iniciar la camara. Verifique permisos del navegador.';
                this.barcodeStatus = '';
            }
        },
        async onBarcodeScanned(decodedText) {
            if (!this.isScanning) {
                return;
            }

            const scannedCode = String(decodedText || '').trim();

            if (!scannedCode) {
                return;
            }

            this.isScanning = false;
            this.barcodeStatus = 'Codigo leido: ' + scannedCode;
            await this.stopBarcodeScanner();
            this.showScanner = false;
            this.codigo = scannedCode;
            await this.buscarArticuloPorCodigo(scannedCode);
        },
        async stopBarcodeScanner() {
            if (!this.barcodeScanner) {
                return;
            }

            try {
                await this.barcodeScanner.stop();
            } catch (error) {
                // El lector puede estar detenido si el navegador cerro la camara.
            }

            try {
                await this.barcodeScanner.clear();
            } catch (error) {
                // Algunos navegadores liberan el video antes de limpiar el lector.
            }

            this.barcodeScanner = null;
            this.isScanning = false;
        },
        async closeBarcodeScanner() {
            await this.stopBarcodeScanner();
            this.showScanner = false;
        },
        async buscarArticuloPorCodigo(scannedCode) {
            this.barcodeError = '';
            this.barcodeStatus = 'Buscando articulo...';

            try {
                const response = await fetch(this.barcodeSearchUrl + '/' + encodeURIComponent(scannedCode), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'No existe un articulo con el codigo ' + scannedCode + '.');
                }

                this.seleccionar(data.articulo);
                this.barcodeStatus = 'Articulo encontrado.';
            } catch (error) {
                this.barcodeError = error?.message || 'No fue posible buscar el articulo leido.';
                this.barcodeStatus = '';
            }
        }
    }"
    x-init="
        enviarCorreo = localStorage.getItem('pedidosweb.enviarCorreo') === null
            ? true
            : localStorage.getItem('pedidosweb.enviarCorreo') === '1';
        $watch('enviarCorreo', value => localStorage.setItem('pedidosweb.enviarCorreo', value ? '1' : '0'));
    "
>
    <header class="mb-5 app-panel">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-600">Pedido #{{ $pedidoId }}</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950">Gestión de pedido</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $cliente['nit'] ?? 'N/A' }} · {{ $cliente['nombre'] ?? 'Cliente sin nombre' }}
                </p>
            </div>

            <div class="grid grid-cols-3 gap-2 sm:flex">
                <button type="button" class="btn-secondary px-3" @click="openBarcodeScanner()">Cod. barras</button>
                <form method="POST" action="{{ route('pedido.actualizar') }}">
                    @csrf
                    <button type="submit" class="btn-secondary w-full px-3">Actualizar</button>
                </form>
                <form method="POST" action="{{ route('pedido.reiniciar') }}" onsubmit="return confirm('¿Seguro desea borrar todo el pedido actual?')">
                    @csrf
                    <button type="submit" class="btn-danger w-full px-3">Reiniciar</button>
                </form>
                <form method="POST" action="{{ route('pedido.terminar') }}" onsubmit="return confirm('¿Está seguro que quiere finalizar el pedido?')" class="col-span-3 sm:col-span-1">
                    @csrf
                    <input type="checkbox" name="enviar_correo" value="1" class="sr-only" id="enviar-correo" x-model="enviarCorreo">
                    <button type="submit" class="btn-primary w-full px-3">Terminar</button>
                </form>
                <label for="enviar-correo" class="col-span-3 inline-flex items-center gap-2 text-xs font-medium text-slate-600 sm:w-full">
                    <span class="inline-flex h-4 w-4 items-center justify-center rounded border border-slate-300 transition" :class="enviarCorreo ? 'border-emerald-600 bg-emerald-600' : 'bg-white'">
                        <svg x-show="enviarCorreo" class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                        </svg>
                    </span>
                    Enviar correo al finalizar
                </label>
            </div>
        </div>
    </header>

    @if ($errors->has('buscar'))
        <div class="mb-5 soft-alert-error">
            {{ $errors->first('buscar') }}
        </div>
    @endif

    <template x-if="barcodeError && !showScanner">
        <div class="mb-5 soft-alert-error" x-text="barcodeError"></div>
    </template>

    <section class="mb-5 grid gap-3 sm:grid-cols-3">
        <div class="app-card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total</p>
            <p class="mt-2 text-2xl font-semibold text-slate-950">${{ number_format($totalPedido, 0, ',', '.') }}</p>
        </div>
        <div class="app-card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cantidad</p>
            <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $cantidadTotal }}</p>
        </div>
        <div class="app-card p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Peso</p>
            <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $pesoTotal }} kg</p>
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
        <section class="app-panel">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Agregar o editar artículo</h2>
                    <p class="text-sm text-slate-500">Busca un código, ajusta cantidad y guarda el movimiento.</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-12">
                <label class="space-y-2 lg:col-span-5">
                    <span class="app-label block">Código artículo</span>
                    <div class="flex gap-2">
                        <input x-ref="codigoArticulo" x-model="codigo" type="text" class="app-input min-w-0" placeholder="Código o barras">
                        <button type="button" class="btn-secondary h-11 w-11 shrink-0 p-0" @click="showSearchModal = true" aria-label="Buscar artículos">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                            </svg>
                        </button>
                    </div>
                </label>

                <label class="space-y-2 lg:col-span-4">
                    <span class="app-label block">Cantidad</span>
                    <div class="grid grid-cols-[44px_minmax(0,1fr)_44px_44px] gap-2">
                        <button type="button" class="btn-secondary h-11 p-0" @click="cantidad = Math.max(0, Number(cantidad || 0) - 1)">-</button>
                        <input x-model.number="cantidad" type="number" min="0" class="app-input text-center">
                        <button type="button" class="btn-secondary h-11 p-0" @click="cantidad = Number(cantidad || 0) + 1">+</button>
                        <span class="flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-500" x-text="articulo?.artiunidad || ''"></span>
                    </div>
                </label>

                <div class="space-y-2 lg:col-span-3">
                    <span class="app-label block">Valor unidad</span>
                    <div class="flex h-11 items-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-950" x-text="dinero(valorUnidad)"></div>
                </div>

                <label class="space-y-2 lg:col-span-9">
                    <span class="app-label block">Detalle movimiento (opcional)</span>
                    <input x-model="detalle" type="text" class="app-input" placeholder="Detalle visible en el pedido">
                </label>

                <div class="space-y-2 lg:col-span-3">
                    <span class="app-label block">Valor parcial</span>
                    <div class="flex h-11 items-center rounded-xl border border-emerald-200 bg-emerald-50 px-3 text-sm font-semibold text-emerald-800" x-text="dinero(parcial())"></div>
                </div>
            </div>

            <div class="mt-5 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-4">
                <div class="sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Descripción</p>
                    <p class="mt-1 min-h-6 text-sm font-medium text-slate-950" x-text="articulo?.artinomb || 'Sin artículo seleccionado'"></p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Marca</p>
                    <p class="mt-1 min-h-6 text-sm text-slate-700" x-text="articulo?.artimarca || '-'"></p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Stock</p>
                    <p class="mt-1 min-h-6 text-sm text-slate-700" x-text="articulo?.articant || '-'"></p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Peso</p>
                    <p class="mt-1 min-h-6 text-sm text-slate-700" x-text="articulo?.artipeso || '-'"></p>
                </div>
                <div class="sm:col-span-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Código alterno</p>
                    <p class="mt-1 min-h-6 text-sm text-slate-700" x-text="articulo?.articodi2 || '-'"></p>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-2">
                <form method="POST" action="{{ route('pedido.agregar-articulo') }}" x-show="articulo && !selectedMovimiento" x-cloak>
                    @csrf
                    <input type="hidden" name="codigo" :value="codigo">
                    <input type="hidden" name="cantidad" :value="cantidad">
                    <input type="hidden" name="valor_unidad" :value="valorUnidad">
                    <input type="hidden" name="detalle" :value="detalle">
                    <button type="submit" class="btn-primary">Agregar</button>
                </form>
                <form method="POST" :action="selectedMovimiento ? movimientoBaseUrl + '/' + selectedMovimiento.Id_vpar : '#'" x-show="selectedMovimiento" x-cloak>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="detalle" :value="detalle">
                    <input type="hidden" name="cantidad" :value="cantidad">
                    <input type="hidden" name="valor_unidad" :value="valorUnidad">
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                </form>
                <form method="POST" :action="selectedMovimiento ? movimientoBaseUrl + '/' + selectedMovimiento.Id_vpar : '#'" x-show="selectedMovimiento" x-cloak onsubmit="return confirm('¿Seguro que quiere borrar este artículo?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Eliminar</button>
                </form>
                <button type="button" class="btn-secondary" @click="limpiarArticulo()">Limpiar</button>
            </div>
        </section>

        <section class="app-panel">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-950">Movimientos</h2>
                <p class="text-sm text-slate-500">Selecciona un registro para editarlo.</p>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200">
                <div class="grid grid-cols-5 bg-slate-50 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <div class="col-span-2">Artículo</div>
                    <div>Cant.</div>
                    <div>Unidad</div>
                    <div>Parcial</div>
                </div>
                <div class="max-h-[420px] min-h-40 overflow-y-auto bg-white text-sm">
                    @forelse ($movimientos as $movimiento)
                        <button type="button" class="grid w-full grid-cols-5 gap-2 border-t border-slate-100 px-3 py-3 text-left text-slate-700 transition hover:bg-emerald-50" @click='seleccionarMovimiento(@json($movimiento))'>
                            <span class="col-span-2 truncate font-medium text-slate-950">{{ data_get($movimiento, 'detalle') }}</span>
                            <span>{{ data_get($movimiento, 'cantidad') }}</span>
                            <span>${{ number_format((float) data_get($movimiento, 'valor', 0), 0, ',', '.') }}</span>
                            <span>${{ number_format((float) data_get($movimiento, 'neto', 0), 0, ',', '.') }}</span>
                        </button>
                    @empty
                        <div class="flex min-h-40 items-center justify-center px-4 text-center text-sm text-slate-500">
                            No hay movimientos en este pedido.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <div
        x-show="showScanner"
        x-transition.opacity
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm"
        style="display: none;"
    >
        <section
            @click.outside="closeBarcodeScanner()"
            class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl shadow-slate-950/20"
        >
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Codigo de barras</h2>
                    <p class="text-sm text-slate-500" x-text="barcodeStatus || 'Active la camara y enfoque el codigo.'"></p>
                </div>
                <button type="button" class="btn-secondary h-9 w-9 p-0" @click="closeBarcodeScanner()" aria-label="Cerrar lector">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <div :id="barcodeReaderId" class="overflow-hidden rounded-xl border border-slate-200 bg-slate-950"></div>

            <template x-if="barcodeError">
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" x-text="barcodeError"></div>
            </template>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" class="btn-secondary" @click="closeBarcodeScanner()">Cancelar</button>
            </div>
        </section>
    </div>

    <div
        x-show="showSearchModal"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-6 backdrop-blur-sm"
        style="display: none;"
    >
        <section
            @click.outside="showSearchModal = false"
            class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl shadow-slate-950/20"
        >
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Buscar artículos</h2>
                    <p class="text-sm text-slate-500">Filtra por palabras clave o categoría.</p>
                </div>
                <button type="button" class="btn-secondary h-9 w-9 p-0" @click="showSearchModal = false" aria-label="Cerrar búsqueda">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('pedido.buscar-articulos') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="app-label mb-2 block">Palabras clave (máximo 3)</label>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <input name="palabra_1" type="text" value="{{ old('palabra_1', data_get($busqueda, 'palabra_1', '')) }}" placeholder="Palabra 1" class="app-input">
                        <input name="palabra_2" type="text" value="{{ old('palabra_2', data_get($busqueda, 'palabra_2', '')) }}" placeholder="Palabra 2" class="app-input">
                        <input name="palabra_3" type="text" value="{{ old('palabra_3', data_get($busqueda, 'palabra_3', '')) }}" placeholder="Palabra 3" class="app-input">
                    </div>
                </div>

                <label class="block space-y-2">
                    <span class="app-label block">Categoría</span>
                    <select name="grupo" class="app-input">
                        <option value="">Seleccionar categoría</option>
                        @foreach ($grupos as $grupo)
                            @php
                                $grupoCodigo = (string) data_get($grupo, 'grupcodigo', '');
                                $grupoNombre = data_get($grupo, 'grupnomb', $grupoCodigo);
                            @endphp
                            <option value="{{ $grupoCodigo }}" @selected(old('grupo', data_get($busqueda, 'grupo', '')) === $grupoCodigo)>{{ $grupoNombre }}</option>
                        @endforeach
                    </select>
                </label>

                <button type="submit" class="btn-primary w-full">Buscar</button>
            </form>

            <div class="mt-5 border-t border-slate-200 pt-5">
                @if (! $busquedaRealizada || count($articulos) === 0)
                    <div class="flex min-h-44 items-center justify-center rounded-xl bg-slate-50">
                        <p class="text-center text-sm text-slate-500">No se encontraron artículos.</p>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($articulos as $articulo)
                            <button
                                type="button"
                                class="w-full rounded-xl border border-slate-200 bg-white p-3 text-left shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50/50"
                                @click='seleccionar(@json($articulo))'
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-950">{{ data_get($articulo, 'artinomb') }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ data_get($articulo, 'articodigo') }} · {{ data_get($articulo, 'artimarca') }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="font-semibold text-slate-950">${{ number_format((float) data_get($articulo, 'artivlr1_c', 0), 0, ',', '.') }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Stock {{ data_get($articulo, 'articant', 0) }}</p>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>

    <nav class="fixed bottom-0 left-0 right-0 z-30 grid grid-cols-2 border-t border-slate-200 bg-white/95 p-2 text-sm font-semibold shadow-2xl shadow-slate-950/10 backdrop-blur lg:hidden">
        <a href="{{ route('cliente.index') }}" class="rounded-xl py-3 text-center text-slate-600">Cliente</a>
        <a href="{{ route('pedido.index') }}" class="rounded-xl bg-emerald-600 py-3 text-center text-white">Pedido</a>
    </nav>
</div>
@endsection
