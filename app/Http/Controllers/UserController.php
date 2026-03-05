<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'codigo_empresa' => ['required', 'string', 'max:50'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'codigo_empresa' => strtoupper($validated['codigo_empresa']),
            'aprobado' => false,
            'rol' => 'empresa',
        ]);

        return redirect()->route('register.pending');
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
