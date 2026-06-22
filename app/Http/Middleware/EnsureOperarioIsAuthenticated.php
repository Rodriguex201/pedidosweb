<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOperarioIsAuthenticated
{
    /**
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has(['user_id', 'empresa_id', 'operario', 'ip_servidor', 'database'])) {
            return redirect()->route('login')->withErrors([
                'email' => 'Debes completar la autenticacion de operario para ingresar al sistema.',
            ]);
        }

        $user = User::query()
            ->with('empresa')
            ->find($request->session()->get('user_id'));

        if (
            ! $user
            || ! $user->aprobado
            || ! $user->empresa
            || ! $user->empresa->activa
            || $user->empresa_id !== (int) $request->session()->get('empresa_id')
        ) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu sesion ya no tiene una empresa activa valida.',
            ]);
        }

        return $next($request);
    }
}
