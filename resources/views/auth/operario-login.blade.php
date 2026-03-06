<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Operario | PedidosWeb</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-cyan-50 text-slate-800 antialiased">
    <main class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
        <section class="w-full max-w-md rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-xl shadow-slate-200/60 backdrop-blur sm:p-8">
            <div class="mb-8 space-y-2 text-center">
                <p class="text-sm font-medium uppercase tracking-[0.18em] text-cyan-600">PedidosWeb</p>
                <h1 class="text-2xl font-semibold text-slate-900">Bienvenido</h1>
                <p class="text-sm text-slate-500">Seleccione el operario para continuar.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('operario.login.submit') }}" class="space-y-5" novalidate>
                @csrf

                <div>
                    <label for="operario" class="mb-1.5 block text-sm font-medium text-slate-700">Seleccione el operario</label>
                    <select id="operario" name="operario" required
                        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm shadow-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100">
                        <option value="">-- Seleccione --</option>
                        @foreach ($operarios as $nombreOperario)
                            <option value="{{ $nombreOperario }}" @selected(old('operario') === $nombreOperario)>{{ $nombreOperario }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="operario_password" class="mb-1.5 block text-sm font-medium text-slate-700">Contraseña del operario</label>
                    <input id="operario_password" name="operario_password" type="password" required
                        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm shadow-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                        placeholder="••••••••">
                </div>

                <div>
                    <label for="vendedor" class="mb-1.5 block text-sm font-medium text-slate-700">Nombre del vendedor (opcional)</label>
                    <input id="vendedor" name="vendedor" type="text" value="{{ old('vendedor') }}"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm shadow-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                        placeholder="Nombre del vendedor">
                </div>

                <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-cyan-200 transition hover:bg-cyan-700 focus:outline-none focus:ring-4 focus:ring-cyan-200">
                    Validar operario
                </button>
            </form>
        </section>
    </main>
</body>
</html>
