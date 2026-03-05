<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro | PedidosWeb</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 text-slate-800 antialiased">
    <main class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
        <section class="w-full max-w-lg rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-xl shadow-slate-200/60 backdrop-blur sm:p-8">
            <div class="mb-8 space-y-2 text-center">
                <p class="text-sm font-medium uppercase tracking-[0.18em] text-blue-600">PedidosWeb</p>
                <h1 class="text-2xl font-semibold text-slate-900">Crear cuenta</h1>
                <p class="text-sm text-slate-500">Registra tus datos para acceder al entorno empresarial.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <p class="font-medium">Encontramos algunos errores en el formulario.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}" class="space-y-5" novalidate>
                @csrf
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Nombre completo</label>
                    <input id="name" name="name" type="text" autocomplete="name" value="{{ old('name') }}" required
                        class="block w-full rounded-xl border px-4 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4 {{ $errors->has('name') ? 'border-rose-400 bg-rose-50/50 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 bg-white focus:border-blue-500 focus:ring-blue-100' }}"
                        placeholder="Tu nombre y apellido">
                    @error('name')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Correo electrónico</label>
                    <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required
                        class="block w-full rounded-xl border px-4 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4 {{ $errors->has('email') ? 'border-rose-400 bg-rose-50/50 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 bg-white focus:border-blue-500 focus:ring-blue-100' }}"
                        placeholder="correo@empresa.com">
                    @error('email')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                        class="block w-full rounded-xl border px-4 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4 {{ $errors->has('password') ? 'border-rose-400 bg-rose-50/50 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 bg-white focus:border-blue-500 focus:ring-blue-100' }}"
                        placeholder="Mínimo 8 caracteres">
                    @error('password')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">Confirmar contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                        class="block w-full rounded-xl border px-4 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4 {{ $errors->has('password_confirmation') ? 'border-rose-400 bg-rose-50/50 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 bg-white focus:border-blue-500 focus:ring-blue-100' }}"
                        placeholder="Repite tu contraseña">
                    @error('password_confirmation')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="company_code" class="mb-1.5 block text-sm font-medium text-slate-700">Código de empresa</label>
                    <input id="company_code" name="company_code" type="text" autocomplete="organization" value="{{ old('company_code') }}" required
                        class="block w-full rounded-xl border px-4 py-2.5 text-sm uppercase shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4 {{ $errors->has('company_code') ? 'border-rose-400 bg-rose-50/50 focus:border-rose-500 focus:ring-rose-100' : 'border-slate-300 bg-white focus:border-blue-500 focus:ring-blue-100' }}"
                        placeholder="Ej: EMP-ACME">
                    @error('company_code')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                    Crear cuenta
                </button>
            </form>

            <p class="mt-7 text-center text-sm text-slate-600">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700 hover:underline">Iniciar sesión</a>
            </p>
        </section>
    </main>
</body>
</html>
