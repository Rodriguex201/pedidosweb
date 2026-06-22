<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuarios_requires_login(): void
    {
        $this->get('/usuarios')->assertRedirect('/login');
    }

    public function test_usuarios_rejects_non_admin_users(): void
    {
        $user = User::factory()->create([
            'aprobado' => true,
            'rol' => 'empresa',
        ]);

        $this->actingAs($user)
            ->get('/usuarios')
            ->assertForbidden();
    }

    public function test_usuarios_allows_admin_users(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create([
            'aprobado' => true,
            'rol' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/usuarios')
            ->assertOk();
    }
}
