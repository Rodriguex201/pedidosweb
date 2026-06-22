<?php

namespace Tests\Feature;

use App\Services\PedidoFlowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PedidoFlowServiceTenantTest extends TestCase
{
    private PedidoFlowService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PedidoFlowService();

        Schema::dropIfExists('xxxxvpax');
        Schema::dropIfExists('xxxxvpex');

        Schema::create('xxxxvpex', function ($table): void {
            $table->increments('id_vtaped');
            $table->string('vendedor')->nullable();
            $table->integer('ped_estado')->default(0);
            $table->string('numero')->default('');
            $table->string('nit')->nullable();
            $table->string('titular')->nullable();
            $table->date('fdigitar')->nullable();
        });

        Schema::create('xxxxvpax', function ($table): void {
            $table->increments('Id_vpar');
            $table->unsignedInteger('id_vtaped');
        });
    }

    public function test_obtener_pedido_requires_matching_vendedor(): void
    {
        DB::table('xxxxvpex')->insert([
            ['id_vtaped' => 1, 'vendedor' => 'VEN-A', 'fdigitar' => now()->toDateString()],
            ['id_vtaped' => 2, 'vendedor' => 'VEN-B', 'fdigitar' => now()->toDateString()],
        ]);

        $connection = DB::connection();

        $this->assertNotNull($this->service->obtenerPedido($connection, 1, 'VEN-A'));
        $this->assertNull($this->service->obtenerPedido($connection, 2, 'VEN-A'));
        $this->assertSame([], $this->service->listarPedidosVendedor($connection, ''));
    }

    public function test_borrar_pedido_temporal_only_deletes_matching_vendedor(): void
    {
        DB::table('xxxxvpex')->insert([
            ['id_vtaped' => 1, 'vendedor' => 'VEN-A', 'fdigitar' => now()->toDateString()],
            ['id_vtaped' => 2, 'vendedor' => 'VEN-B', 'fdigitar' => now()->toDateString()],
        ]);
        DB::table('xxxxvpax')->insert([
            ['Id_vpar' => 1, 'id_vtaped' => 1],
            ['Id_vpar' => 2, 'id_vtaped' => 2],
        ]);

        $this->service->borrarPedidoTemporal(DB::connection(), 2, 'VEN-A');

        $this->assertDatabaseHas('xxxxvpex', ['id_vtaped' => 2, 'vendedor' => 'VEN-B']);
        $this->assertDatabaseHas('xxxxvpax', ['id_vtaped' => 2]);

        $this->service->borrarPedidoTemporal(DB::connection(), 2, 'VEN-B');

        $this->assertDatabaseMissing('xxxxvpex', ['id_vtaped' => 2]);
        $this->assertDatabaseMissing('xxxxvpax', ['id_vtaped' => 2]);
    }
}
