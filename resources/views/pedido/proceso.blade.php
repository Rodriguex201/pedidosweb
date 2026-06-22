@extends('layouts.app')

@section('content')
<section class="space-y-5">
    <div class="app-panel">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-600">Pedidos</p>
        <h1 class="mt-1 text-2xl font-semibold text-slate-950">Pedidos en proceso</h1>
        <p class="text-sm text-slate-500">Pedidos registrados para el vendedor actual.</p>
    </div>

    @if ($errors->has('pedidos'))
        <div class="soft-alert-error">
            {{ $errors->first('pedidos') }}
        </div>
    @endif

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
                    <th class="px-3 py-2">Aprobado</th>
                    <th class="px-3 py-2 text-right">Acciones</th>
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
                        <td>
                            <span class="inline-flex rounded-full {{ (int) data_get($pedido, 'ped_estado', 0) === 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }} px-2.5 py-1 text-xs font-semibold">
                                {{ (int) data_get($pedido, 'ped_estado', 0) === 0 ? 'No' : 'Sí' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('pedido.detalle', data_get($pedido, 'id_vtaped')) }}" class="btn-secondary px-3 py-2 text-xs">Detalle</a>
                                <form method="POST" action="{{ route('pedido.borrar', data_get($pedido, 'id_vtaped')) }}" onsubmit="return confirm('Desea eliminar este pedido?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger px-3 py-2 text-xs">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-slate-500">No hay pedidos en proceso.</td>
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
