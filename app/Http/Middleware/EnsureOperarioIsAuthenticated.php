<?php

namespace App\Http\Middleware;

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
                'email' => 'Debes completar la autenticación de operario para ingresar al sistema.',
            ]);
        }

        return $next($request);
    }
}
