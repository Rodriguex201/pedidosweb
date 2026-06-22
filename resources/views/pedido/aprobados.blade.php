@extends('layouts.app')

@section('content')
<section class="space-y-5">
    <div class="app-panel">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-600">Pedidos</p>
        <h1 class="mt-1 text-2xl font-semibold text-slate-950">Pedidos aprobados</h1>
        <p class="text-sm text-slate-500">Pedidos con estado diferente de 0.</p>
    </div>

    <div class="table-shell">
        <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="px-3 py-2">Id</th>
                    <th class="px-3 py-2">Fecha</th>
                    <th class="px-3 py-2">Nit</th>
                    <th class="px-3 py-2">Cliente</th>
                    <th class="px-3 py-2 text-right">Total</th>
                    <th class="px-3 py-2">Estado</th>
                    <th class="px-3 py-2 text-right">Accion</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pedidos as $pedido)
                    <tr>
                        <td>{{ data_get($pedido, 'id_vtaped') }}</td>
                        <td>{{ data_get($pedido, 'fecha') }}</td>
                        <td>{{ data_get($pedido, 'nit') }}</td>
                        <td class="font-medium text-slate-950">{{ data_get($pedido, 'titular') }}</td>
                        <td class="text-right font-semibold text-slate-950">${{ number_format((float) data_get($pedido, 'valor', 0), 0, ',', '.') }}</td>
                        <td><span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ data_get($pedido, 'ped_estado') }}</span></td>
                        <td class="text-right">
                            <a href="{{ route('pedido.detalle', data_get($pedido, 'id_vtaped')) }}" class="btn-secondary px-3 py-2 text-xs">Detalle</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-slate-500">No hay pedidos aprobados.</td>
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
