@extends('layouts.app')

@section('content')
<section class="space-y-5">
    <div class="app-panel">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-600">Detalle</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950">Pedido #{{ data_get($pedido, 'id_vtaped') }}</h1>
                <p class="text-sm text-slate-600">{{ data_get($pedido, 'nit') }} - {{ data_get($pedido, 'titular') }}</p>
                <p class="text-sm text-slate-500">{{ data_get($pedido, 'fecha') }} {{ data_get($pedido, 'hdigita') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if (in_array((int) data_get($pedido, 'ped_estado', 0), [0, 1], true))
                    <form method="POST" action="{{ route('pedido.retomar', data_get($pedido, 'id_vtaped')) }}">
                        @csrf
                        <button type="submit" class="btn-primary">Continuar</button>
                    </form>
                @endif
                <a href="{{ route('pedido.proceso') }}" class="btn-secondary">Volver</a>
            </div>
        </div>
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
                    <th class="px-3 py-2">Articulo</th>
                    <th class="px-3 py-2 text-right">Cantidad</th>
                    <th class="px-3 py-2 text-right">Val.Unidad</th>
                    <th class="px-3 py-2 text-right">Parcial</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movimientos as $movimiento)
                    <tr>
                        <td class="font-medium text-slate-950">{{ data_get($movimiento, 'detalle') }}</td>
                        <td class="text-right">{{ data_get($movimiento, 'cantidad') }}</td>
                        <td class="text-right">${{ number_format((float) data_get($movimiento, 'valor', 0), 0, ',', '.') }}</td>
                        <td class="text-right font-semibold text-slate-950">${{ number_format((float) data_get($movimiento, 'neto', 0), 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-10 text-center text-slate-500">Este pedido no tiene movimientos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</section>
@endsection
