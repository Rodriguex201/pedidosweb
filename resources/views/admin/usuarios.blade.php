<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios | PedidosWeb</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <main class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Gestión de usuarios</h1>
                <p class="text-sm text-slate-500">Listado general de cuentas registradas en el entorno multiempresa.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Correo</th>
                            <th class="px-4 py-3">ID de empresa</th>
                            <th class="px-4 py-3">Estado de aprobación</th>
                            <th class="px-4 py-3">Dispositivo</th>
                            <th class="px-4 py-3">IP registro</th>
                            <th class="px-4 py-3">User Agent</th>
                            <th class="px-4 py-3">Último acceso</th>
                            <th class="px-4 py-3">Fecha de registro</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($users as $user)
                            <tr class="align-middle">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $user->id }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $user->email }}</td>
                                <td class="px-4 py-3 font-semibold uppercase text-slate-700">{{ $user->empresa?->codigo ?? 'N/D' }}</td>
                                <td class="px-4 py-3">
                                    @if ($user->aprobado)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Aprobado</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Pendiente de aprobación</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $user->device_name ?? 'N/D' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $user->ip_registro ?? 'N/D' }}</td>
                                <td class="px-4 py-3 text-slate-600" title="{{ $user->user_agent ?? 'N/D' }}">
                                    <span class="block max-w-xs break-all">{{ $user->user_agent ?? 'N/D' }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $user->ultimo_acceso?->format('d/m/Y H:i') ?? 'N/D' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    @if (! $user->aprobado)
                                        <div class="flex justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.usuarios.approve', $user) }}" class="inline-flex">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                                                    Aprobar usuario
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.usuarios.reject', $user) }}" class="inline-flex" onsubmit="return confirm('¿Seguro que quieres rechazar este usuario?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-rose-700 focus:outline-none focus:ring-4 focus:ring-rose-200">
                                                    Rechazar usuario
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="text-right">
                                            <span class="text-xs font-medium text-slate-400">Sin acciones</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-8 text-center text-sm text-slate-500">No hay usuarios registrados todavía.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
