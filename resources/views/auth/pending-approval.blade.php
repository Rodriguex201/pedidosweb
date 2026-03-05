<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro pendiente | PedidosWeb</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <main class="flex min-h-screen items-center justify-center px-4">
        <section class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-lg">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-600">PedidosWeb</p>
            <h1 class="mt-3 text-2xl font-bold text-slate-900">Cuenta registrada correctamente</h1>
            <p class="mt-4 text-sm leading-relaxed text-slate-600">
                Tu cuenta fue creada y está pendiente de aprobación por parte de un administrador.
                Recibirás acceso al sistema una vez que el estado cambie a <span class="font-semibold text-slate-900">Aprobado</span>.
            </p>
            <a href="{{ route('login') }}"
                class="mt-6 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                Volver al inicio de sesión
            </a>
        </section>
    </main>
</body>
</html>
