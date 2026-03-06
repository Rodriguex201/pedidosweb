<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Services\EmpresaExternaConnectionService;
use App\Services\PedidoFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class PedidoController extends Controller
{
    public function __construct(
        private readonly EmpresaExternaConnectionService $empresaConnection,
        private readonly PedidoFlowService $pedidoFlowService,
    ) {
    }

    public function continuar(Request $request): RedirectResponse
    {
        if ($request->session()->has('pedido_actual')) {
            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'Ya tiene un pedido en proceso, para empezar uno nuevo primero debe reiniciarlo.',
            ]);
        }

        /** @var array<string, mixed>|null $cliente */
        $cliente = $request->session()->get('cliente_seleccionado');

        if (! $cliente || empty($cliente['nit'])) {
            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'Seleccione un cliente antes de continuar.',
            ]);
        }

        $empresaId = $request->session()->get('empresa_id');
        $empresa = Empresa::query()->find($empresaId);

        if (! $empresa || ! $empresa->ip_servidor) {
            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'No se encontró configuración de empresa para continuar el pedido.',
            ]);
        }

        try {
            $this->empresaConnection->connect($empresa);
            $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);

            $operario = [
                'clave' => (string) $request->session()->get('operario', ''),
                'obra' => (string) $request->session()->get('obra', ''),
                'vendedor' => (string) $request->session()->get('vendedor', ''),
                'terminal' => (string) $request->session()->get('terminal', ''),
                'sucursal' => (string) $request->session()->get('sucursal', ''),
            ];

            $despacho = $request->session()->get('despacho_actual', []);

            $pedidoId = $this->pedidoFlowService->crearEncabezadoTemporal($connection, $cliente, $operario, is_array($despacho) ? $despacho : []);
            $grupos = $this->pedidoFlowService->cargarGrupos($connection);
            $articulos = $this->pedidoFlowService->cargarArticulos($connection);
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'No fue posible crear el pedido temporal en la base externa.',
            ]);
        }

        $request->session()->put([
            'pedido_actual' => $pedidoId,
            'pedido_catalogo.grupos' => $grupos,
            'pedido_catalogo.articulos' => $articulos,
        ]);

        return redirect()->route('pedido.index');
    }

    public function index(Request $request): View|RedirectResponse
    {
        $pedidoId = $request->session()->get('pedido_actual');

        if (! $pedidoId) {
            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'No hay un pedido activo. Seleccione un cliente y presione Continuar.',
            ]);
        }

        /** @var array<int, object> $grupos */
        $grupos = $request->session()->get('pedido_catalogo.grupos', []);
        /** @var array<int, object> $articulos */
        $articulos = $request->session()->get('pedido_catalogo.articulos', []);
        /** @var array<string, mixed> $cliente */
        $cliente = $request->session()->get('cliente_seleccionado', []);

        return view('pedido.index', [
            'pedidoId' => $pedidoId,
            'cliente' => $cliente,
            'grupos' => $grupos,
            'articulos' => $articulos,
        ]);
    }
}
