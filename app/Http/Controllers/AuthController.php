<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmpresaExternaConnectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private readonly EmpresaExternaConnectionService $empresaConnection)
    {
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->with('empresa')->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return back()
                ->withInput(['email' => $validated['email']])
                ->withErrors(['email' => 'Credenciales inválidas.']);
        }

        if (! $user->aprobado) {
            return back()
                ->withInput(['email' => $validated['email']])
                ->withErrors(['email' => 'Tu usuario aún no ha sido aprobado.']);
        }

        if (! $user->empresa || ! $user->empresa->ip_servidor) {
            return back()
                ->withInput(['email' => $validated['email']])
                ->withErrors(['email' => 'No hay configuración de empresa para este usuario.']);
        }

        try {
            $config = $this->empresaConnection->connect($user->empresa);

            $operarios = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME)
                ->table('xxxxciao')
                ->select('nombre')
                ->orderBy('nombre')
                ->pluck('nombre')
                ->filter()
                ->values()
                ->all();
        } catch (Throwable $throwable) {
            report($throwable);

            return back()
                ->withInput(['email' => $validated['email']])
                ->withErrors(['email' => 'No fue posible conectar con la base externa de la empresa.']);
        }

        Auth::login($user);

        session([
            'auth_flow.user_id' => $user->id,
            'auth_flow.empresa_id' => $user->empresa->id,
            'auth_flow.operarios' => $operarios,
            'auth_flow.ip_servidor' => $user->empresa->ip_servidor,
            'auth_flow.database' => $config['database'],
        ]);

        return redirect()->route('operario.login');
    }

    public function showOperarioLogin(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('auth_flow.user_id')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Primero debes iniciar sesión con tu usuario del sistema.',
            ]);
        }

        /** @var array<int, string> $operarios */
        $operarios = $request->session()->get('auth_flow.operarios', []);

        return view('auth.operario-login', [
            'operarios' => $operarios,
        ]);
    }

    public function validateOperario(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'operario' => ['required', 'string'],
            'operario_password' => ['required', 'string'],
            'vendedor' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::query()
            ->with('empresa')
            ->find($request->session()->get('auth_flow.user_id'));

        if (! $user || ! $user->empresa || ! $user->empresa->ip_servidor) {
            return redirect()->route('login')->withErrors([
                'email' => 'La sesión de autenticación expiró. Inicia sesión nuevamente.',
            ]);
        }

        try {
            $config = $this->empresaConnection->connect($user->empresa);

            $operario = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME)
                ->table('xxxxciao')
                ->select('nombre', 'pw')
                ->where('nombre', $validated['operario'])
                ->first();
        } catch (Throwable $throwable) {
            report($throwable);

            return back()->withInput()->withErrors([
                'operario' => 'No fue posible validar el operario en la base externa.',
            ]);
        }

        if (! $operario || $operario->pw !== $validated['operario_password']) {
            return back()->withInput()->withErrors([
                'operario_password' => 'Contraseña de operario incorrecta',
            ]);
        }

        $request->session()->forget('auth_flow');
        $request->session()->regenerate();

        $request->session()->put([
            'user_id' => $user->id,
            'empresa_id' => $user->empresa->id,
            'operario' => $operario->nombre,
            'vendedor' => $validated['vendedor'] ?: null,
            'ip_servidor' => $user->empresa->ip_servidor,
            'database' => $config['database'],
        ]);

        return redirect()->route('cliente.index');
    }
}
