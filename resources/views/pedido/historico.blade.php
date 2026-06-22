@extends('layouts.app')

@section('content')
<section class="space-y-5">
    <div class="app-panel">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-600">Consulta</p>
        <h1 class="mt-1 text-2xl font-semibold text-slate-950">Histórico de pedidos</h1>
        <p class="text-sm text-slate-500">Filtra pedidos por estado, cliente o rango de fechas.</p>
    </div>

    @if ($errors->has('pedidos'))
        <div class="soft-alert-error">
            {{ $errors->first('pedidos') }}
        </div>
    @endif

    <form method="GET" action="{{ route('pedido.historico') }}" class="app-panel">
        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-1 md:col-span-2">
                <span class="app-label">Estado</span>
                <select name="estado" class="app-input">
                    <option value="aprobados" @selected($filtros['estado'] === 'aprobados')>Pedidos aprobados</option>
                    <option value="proceso" @selected($filtros['estado'] === 'proceso')>Pedidos en proceso</option>
                    <option value="todos" @selected($filtros['estado'] === 'todos')>Todos</option>
                </select>
            </label>
            <label class="space-y-1">
                <span class="app-label">NIT/Cédula</span>
                <input name="nit" type="text" value="{{ $filtros['nit'] }}" class="app-input">
            </label>
            <label class="space-y-1">
                <span class="app-label">Nombre cliente</span>
                <input name="nombre" type="text" value="{{ $filtros['nombre'] }}" class="app-input">
            </label>
            <label class="space-y-1">
                <span class="app-label">Fecha desde</span>
                <input name="fecha_desde" type="date" value="{{ $filtros['fecha_desde'] }}" class="app-input">
            </label>
            <label class="space-y-1">
                <span class="app-label">Fecha hasta</span>
                <input name="fecha_hasta" type="date" value="{{ $filtros['fecha_hasta'] }}" class="app-input">
            </label>
        </div>
        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('pedido.historico') }}" class="btn-secondary">Limpiar</a>
            <button type="submit" class="btn-primary">Buscar</button>
        </div>
    </form>

    <div class="table-shell">
        <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="px-3 py-2">Fecha</th>
                    <th class="px-3 py-2 text-right">Total</th>
                    <th class="px-3 py-2">Cliente</th>
                    <th class="px-3 py-2">Nombre</th>
                    <th class="px-3 py-2 text-right">Accion</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pedidos as $pedido)
                    <tr>
                        <td>{{ data_get($pedido, 'fecha') }}</td>
                        <td class="text-right font-semibold text-slate-950">${{ number_format((float) data_get($pedido, 'valor', 0), 0, ',', '.') }}</td>
                        <td>{{ data_get($pedido, 'nit') }}</td>
                        <td class="font-medium text-slate-950">{{ data_get($pedido, 'titular') }}</td>
                        <td class="text-right">
                            <a href="{{ route('pedido.detalle', data_get($pedido, 'id_vtaped')) }}" class="btn-secondary px-3 py-2 text-xs">Detalle</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-slate-500">
                            {{ $busquedaRealizada ? 'No se encontraron pedidos' : 'Use los filtros para buscar pedidos' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @if (is_object($pedidos) && method_exists($pedidos, 'links'))
        <div class="app-panel">
            {{ $pedidos->withQueryString()->links('vendor.pagination.simple-es') }}
        </div>
    @endif
</section>
@endsection
