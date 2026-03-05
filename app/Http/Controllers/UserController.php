<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'empresa_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'email_verified_at' => null,
                'password' => Hash::make($validated['password']),
                'empresa_id' => $validated['empresa_id'],
                'aprobado' => false,
                'rol' => 'empresa',
            ]);
        } catch (Throwable) {
            return back()
                ->withInput()
                ->with('error', 'No se pudo completar el registro. Inténtalo nuevamente.');
        }

        return redirect()->route('register')->with('status', '¡Registro completado! Tu usuario se guardó correctamente.');
    }

    public function pendingApproval(): View
    {
        return view('auth.pending-approval');
    }

    public function index(): View
    {
        $users = User::query()->latest()->get();

        return view('admin.usuarios', compact('users'));
    }

    public function approve(User $user): RedirectResponse
    {
        $user->update(['aprobado' => true]);

        return redirect()->route('admin.usuarios')->with('status', 'Usuario aprobado correctamente.');
    }

    public function reject(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.usuarios')->with('status', 'Usuario rechazado y eliminado correctamente.');
    }
}
