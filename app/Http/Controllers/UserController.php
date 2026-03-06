<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;
use Throwable;

class UserController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'codigo_empresa' => ['required', 'string', 'max:255'],
        ]);

        $codigoEmpresa = trim($validated['codigo_empresa']);
        $codigoEmpresaNormalizado = strtoupper($codigoEmpresa);

        $empresa = Empresa::query()
            ->whereRaw('TRIM(LOWER(codigo)) = ?', [strtolower($codigoEmpresa)])
            ->first();

        if (! $empresa) {
            $empresa = Empresa::query()->create([
                'nombre' => 'Empresa '.$codigoEmpresaNormalizado,
                'codigo' => $codigoEmpresaNormalizado,
                'activa' => false,
            ]);
        }

        try {
            $agent = new Agent();
            $deviceName = $agent->platform().' - '.$agent->browser();
            $ip = $request->ip();
            $userAgent = $request->userAgent() ?? '';
            $deviceHash = hash('sha256', $ip.$userAgent);

            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'email_verified_at' => null,
                'password' => Hash::make($validated['password']),
                'empresa_id' => $empresa->id,
                'aprobado' => false,
                'rol' => 'empresa',
                'device_hash' => $deviceHash,
                'device_name' => $deviceName,
                'ip_registro' => $ip,
                'user_agent' => $userAgent,
                'ultimo_acceso' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['registro' => 'Error al registrar: '.$exception->getMessage()]);
        }

        return redirect()->route('register')->with('status', '¡Registro completado! Tu usuario se guardó correctamente y está pendiente de aprobación.');
    }

    public function pendingApproval(): View
    {
        return view('auth.pending-approval');
    }

    public function index(): View
    {
        $users = User::query()->with('empresa')->latest()->get();

        return view('admin.usuarios', compact('users'));
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'ip_servidor' => ['required', 'ip'],
        ]);

        if (! $user->empresa) {
            return redirect()->route('admin.usuarios')->withErrors([
                'aprobacion' => 'No se encontró la empresa asociada al usuario.',
            ]);
        }

        $user->empresa->update([
            'ip_servidor' => $validated['ip_servidor'],
        ]);

        $user->update(['aprobado' => true]);

        return redirect()->route('admin.usuarios')->with('status', 'Usuario aprobado correctamente e IP de empresa registrada.');
    }

    public function reject(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.usuarios')->with('status', 'Usuario rechazado y eliminado correctamente.');
    }
}
