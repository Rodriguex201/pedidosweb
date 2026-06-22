<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Services\EmpresaExternaConnectionService;
use App\Services\PedidoFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

        $despacho = [
            'recibe' => (string) $request->input('despacho_recibe', ''),
            'direccion' => (string) $request->input('despacho_direccion', ''),
            'ciudad' => (string) $request->input('despacho_ciudad', ''),
            'telefono' => (string) $request->input('despacho_telefono', ''),
            'detalle' => (string) $request->input('despacho_detalle', ''),
        ];

        $request->session()->put('despacho_actual', $despacho);

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

            $operarioRegistro = null;
            $operarioNombre = (string) $request->session()->get('operario', '');
            $operarioClave = (string) $request->session()->get('operario_clave', '');

            if ($operarioNombre !== '') {
                $operarioRegistro = $connection->table('xxxxciao')
                    ->select('clave', 'obra', 'ciao_vend', 'terminal', 'sucursal')
                    ->where('nombre', $operarioNombre)
                    ->first();
            }

            $operario = [
                'clave' => $operarioClave !== '' ? $operarioClave : (string) ($operarioRegistro->clave ?? ''),
                'obra' => (string) ($request->session()->get('obra') ?: ($operarioRegistro->obra ?? '')),
                'vendedor' => (string) (($operarioRegistro->ciao_vend ?? '') ?: $request->session()->get('vendedor', '')),
                'terminal' => (string) ($request->session()->get('terminal') ?: ($operarioRegistro->terminal ?? '')),
                'sucursal' => (string) ($request->session()->get('sucursal') ?: ($operarioRegistro->sucursal ?? '')),
            ];

            if ($operario['vendedor'] !== '') {
                $request->session()->put('vendedor', $operario['vendedor']);
            }

            $despacho = $request->session()->get('despacho_actual', []);

            $pedidoId = $this->pedidoFlowService->crearEncabezadoTemporal($connection, $cliente, $operario, is_array($despacho) ? $despacho : []);
            $encabezadoBasico = $this->pedidoFlowService->obtenerEncabezadoBasico($connection, $pedidoId);
            $grupos = $this->pedidoFlowService->cargarGrupos($connection);
        } catch (Throwable $throwable) {
            report($throwable);

            $mensaje = 'No fue posible crear el pedido temporal en la base externa.';

            if (App::hasDebugModeEnabled()) {
                $mensaje .= ' Detalle: '.$throwable->getMessage();
            }

            return redirect()->route('cliente.index')->withErrors([
                'continuar' => $mensaje,
            ]);
        }

        $request->session()->put([
            'pedido_actual' => $pedidoId,
            'pedido_encabezado' => $encabezadoBasico,
            'pedido_catalogo.grupos' => $grupos,
            'pedido_catalogo.articulos' => [],
            'pedido_catalogo.busqueda' => [],
            'pedido_catalogo.busqueda_realizada' => false,
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
        $movimientos = [];
        /** @var array<string, mixed> $busqueda */
        $busqueda = $request->session()->get('pedido_catalogo.busqueda', []);
        $busquedaRealizada = (bool) $request->session()->get('pedido_catalogo.busqueda_realizada', false);
        /** @var array<string, mixed> $cliente */
        $cliente = $request->session()->get('cliente_seleccionado', []);

        try {
            $empresaId = $request->session()->get('empresa_id');
            $empresa = Empresa::query()->find($empresaId);

            if ($empresa && $empresa->ip_servidor) {
                $this->empresaConnection->connect($empresa);
                $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);
                $movimientos = $this->pedidoFlowService->cargarMovimientos($connection, (int) $pedidoId);
            }
        } catch (Throwable $throwable) {
            report($throwable);
        }

        $totalPedido = collect($movimientos)->sum(fn (object $movimiento): int => (int) data_get($movimiento, 'neto', 0));
        $cantidadTotal = collect($movimientos)->sum(fn (object $movimiento): int => (int) data_get($movimiento, 'cantidad', 0));
        $pesoTotal = collect($movimientos)->sum(fn (object $movimiento): int => (int) data_get($movimiento, 'peso', 0));

        return view('pedido.index', [
            'pedidoId' => $pedidoId,
            'cliente' => $cliente,
            'grupos' => $grupos,
            'articulos' => $articulos,
            'movimientos' => $movimientos,
            'busqueda' => $busqueda,
            'busquedaRealizada' => $busquedaRealizada,
            'totalPedido' => $totalPedido,
            'cantidadTotal' => $cantidadTotal,
            'pesoTotal' => $pesoTotal,
        ]);
    }

    public function buscarArticulos(Request $request): RedirectResponse
    {
        if (! $request->session()->has('pedido_actual')) {
            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'No hay un pedido activo. Seleccione un cliente y presione Continuar.',
            ]);
        }

        $validated = $request->validate([
            'palabra_1' => ['nullable', 'string', 'max:80'],
            'palabra_2' => ['nullable', 'string', 'max:80'],
            'palabra_3' => ['nullable', 'string', 'max:80'],
            'grupo' => ['nullable', 'string', 'max:80'],
        ]);

        $empresaId = $request->session()->get('empresa_id');
        $empresa = Empresa::query()->find($empresaId);

        if (! $empresa || ! $empresa->ip_servidor) {
            return redirect()->route('pedido.index')->withErrors([
                'buscar' => 'No se encontro configuracion de empresa para buscar articulos.',
            ]);
        }

        try {
            $this->empresaConnection->connect($empresa);
            $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);

            $palabras = [
                (string) ($validated['palabra_1'] ?? ''),
                (string) ($validated['palabra_2'] ?? ''),
                (string) ($validated['palabra_3'] ?? ''),
            ];

            $articulos = $this->pedidoFlowService->buscarArticulos(
                $connection,
                $palabras,
                $validated['grupo'] ?: null,
            );
        } catch (Throwable $throwable) {
            report($throwable);

            $mensaje = 'No fue posible buscar articulos en la base externa.';

            if (App::hasDebugModeEnabled()) {
                $mensaje .= ' Detalle: '.$throwable->getMessage();
            }

            return redirect()->route('pedido.index')->withErrors([
                'buscar' => $mensaje,
            ]);
        }

        $request->session()->put([
            'pedido_catalogo.articulos' => $articulos,
            'pedido_catalogo.busqueda' => [
                'palabra_1' => $validated['palabra_1'] ?? '',
                'palabra_2' => $validated['palabra_2'] ?? '',
                'palabra_3' => $validated['palabra_3'] ?? '',
                'grupo' => $validated['grupo'] ?? '',
            ],
            'pedido_catalogo.busqueda_realizada' => true,
        ]);

        return redirect()->route('pedido.index');
    }

    public function buscarArticuloCodigo(Request $request, string $codigo): JsonResponse
    {
        if (! $request->session()->has('pedido_actual')) {
            return response()->json([
                'message' => 'No hay un pedido activo. Seleccione un cliente y presione Continuar.',
            ], 422);
        }

        $codigo = trim($codigo);

        if ($codigo === '') {
            return response()->json([
                'message' => 'Codigo de articulo vacio.',
            ], 422);
        }

        $empresa = Empresa::query()->find($request->session()->get('empresa_id'));

        if (! $empresa || ! $empresa->ip_servidor) {
            return response()->json([
                'message' => 'No se encontro configuracion de empresa para buscar articulos.',
            ], 422);
        }

        try {
            $this->empresaConnection->connect($empresa);
            $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);
            $articulo = $this->pedidoFlowService->buscarArticuloPorCodigo($connection, $codigo);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'No fue posible buscar el articulo en la base externa.',
            ], 500);
        }

        if (! $articulo) {
            return response()->json([
                'message' => 'No existe un articulo con el codigo '.$codigo.'.',
            ], 404);
        }

        return response()->json([
            'articulo' => $articulo,
        ]);
    }

    public function agregarArticulo(Request $request): RedirectResponse
    {
        $pedidoId = (int) $request->session()->get('pedido_actual', 0);

        if ($pedidoId === 0) {
            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'No hay un pedido activo. Seleccione un cliente y presione Continuar.',
            ]);
        }

        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:80'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'valor_unidad' => ['required', 'integer', 'min:0'],
            'detalle' => ['nullable', 'string', 'max:255'],
        ]);

        $empresaId = $request->session()->get('empresa_id');
        $empresa = Empresa::query()->find($empresaId);

        if (! $empresa || ! $empresa->ip_servidor) {
            return redirect()->route('pedido.index')->withErrors([
                'buscar' => 'No se encontro configuracion de empresa para agregar el articulo.',
            ]);
        }

        try {
            $this->empresaConnection->connect($empresa);
            $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);
            $articulo = $this->pedidoFlowService->buscarArticuloPorCodigo($connection, trim($validated['codigo']));

            if (! $articulo) {
                return redirect()->route('pedido.index')->withErrors([
                    'buscar' => 'No existe un articulo con el codigo '.$validated['codigo'].'.',
                ]);
            }

            /** @var array<string, mixed> $cliente */
            $cliente = $request->session()->get('cliente_seleccionado', []);
            $operario = [
                'clave' => (string) $request->session()->get('operario_clave', ''),
                'obra' => (string) $request->session()->get('obra', ''),
            ];

            $neto = $this->pedidoFlowService->agregarMovimientoTemporal(
                $connection,
                $pedidoId,
                $articulo,
                $cliente,
                $operario,
                (int) $validated['cantidad'],
                (int) $validated['valor_unidad'],
                (string) ($validated['detalle'] ?? ''),
                $request->session()->get('pedido_encabezado'),
            );
            $this->pedidoFlowService->ajustarTotalPedido($connection, $pedidoId, $neto);
        } catch (Throwable $throwable) {
            report($throwable);

            $mensaje = 'No fue posible agregar el articulo al pedido temporal.';

            if (App::hasDebugModeEnabled()) {
                $mensaje .= ' Detalle: '.$throwable->getMessage();
            }

            return redirect()->route('pedido.index')->withErrors([
                'buscar' => $mensaje,
            ]);
        }

        return redirect()->route('pedido.index');
    }

    public function editarMovimiento(Request $request, int $movimiento): RedirectResponse
    {
        $pedidoId = (int) $request->session()->get('pedido_actual', 0);

        if ($pedidoId === 0) {
            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'No hay un pedido activo. Seleccione un cliente y presione Continuar.',
            ]);
        }

        $validated = $request->validate([
            'detalle' => ['required', 'string', 'max:255'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'valor_unidad' => ['required', 'integer', 'min:0'],
        ]);

        $empresa = Empresa::query()->find($request->session()->get('empresa_id'));

        if (! $empresa || ! $empresa->ip_servidor) {
            return redirect()->route('pedido.index')->withErrors([
                'buscar' => 'No se encontro configuracion de empresa para editar el movimiento.',
            ]);
        }

        try {
            $this->empresaConnection->connect($empresa);
            $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);
            $neto = (int) $validated['cantidad'] * (int) $validated['valor_unidad'];
            $movimientoActual = $this->pedidoFlowService->obtenerMovimiento($connection, $pedidoId, $movimiento);
            $netoAnterior = (int) data_get($movimientoActual, 'neto', 0);

            $this->pedidoFlowService->editarMovimientoTemporal(
                $connection,
                $pedidoId,
                $movimiento,
                (string) $validated['detalle'],
                (int) $validated['cantidad'],
                (int) $validated['valor_unidad'],
                $neto,
            );
            $this->pedidoFlowService->ajustarTotalPedido($connection, $pedidoId, $neto - $netoAnterior);
        } catch (Throwable $throwable) {
            report($throwable);

            $mensaje = 'No fue posible editar el movimiento.';

            if (App::hasDebugModeEnabled()) {
                $mensaje .= ' Detalle: '.$throwable->getMessage();
            }

            return redirect()->route('pedido.index')->withErrors([
                'buscar' => $mensaje,
            ]);
        }

        return redirect()->route('pedido.index');
    }

    public function borrarMovimiento(Request $request, int $movimiento): RedirectResponse
    {
        $pedidoId = (int) $request->session()->get('pedido_actual', 0);

        if ($pedidoId === 0) {
            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'No hay un pedido activo. Seleccione un cliente y presione Continuar.',
            ]);
        }

        $empresa = Empresa::query()->find($request->session()->get('empresa_id'));

        if (! $empresa || ! $empresa->ip_servidor) {
            return redirect()->route('pedido.index')->withErrors([
                'buscar' => 'No se encontro configuracion de empresa para borrar el movimiento.',
            ]);
        }

        try {
            $this->empresaConnection->connect($empresa);
            $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);
            $movimientoActual = $this->pedidoFlowService->obtenerMovimiento($connection, $pedidoId, $movimiento);
            $netoAnterior = (int) data_get($movimientoActual, 'neto', 0);
            $this->pedidoFlowService->borrarMovimientoTemporal($connection, $pedidoId, $movimiento);
            $this->pedidoFlowService->ajustarTotalPedido($connection, $pedidoId, -$netoAnterior);
        } catch (Throwable $throwable) {
            report($throwable);

            $mensaje = 'No fue posible borrar el movimiento.';

            if (App::hasDebugModeEnabled()) {
                $mensaje .= ' Detalle: '.$throwable->getMessage();
            }

            return redirect()->route('pedido.index')->withErrors([
                'buscar' => $mensaje,
            ]);
        }

        return redirect()->route('pedido.index');
    }

    public function actualizarCatalogo(Request $request): RedirectResponse
    {
        $empresaId = $request->session()->get('empresa_id');
        $empresa = Empresa::query()->find($empresaId);

        if (! $empresa || ! $empresa->ip_servidor) {
            return redirect()->route('pedido.index')->withErrors([
                'buscar' => 'No se encontro configuracion de empresa para actualizar el catalogo.',
            ]);
        }

        try {
            $this->empresaConnection->connect($empresa);
            $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);

            $request->session()->put([
                'pedido_catalogo.grupos' => $this->pedidoFlowService->cargarGrupos($connection),
                'pedido_catalogo.articulos' => [],
                'pedido_catalogo.busqueda' => [],
                'pedido_catalogo.busqueda_realizada' => false,
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()->route('pedido.index')->withErrors([
                'buscar' => 'No fue posible actualizar el catalogo.',
            ]);
        }

        return redirect()->route('pedido.index');
    }

    public function reiniciar(Request $request): RedirectResponse
    {
        $pedidoId = (int) $request->session()->get('pedido_actual', 0);

        if ($pedidoId === 0) {
            return redirect()->route('cliente.index');
        }

        $empresaId = $request->session()->get('empresa_id');
        $empresa = Empresa::query()->find($empresaId);

        try {
            if ($empresa && $empresa->ip_servidor) {
                $this->empresaConnection->connect($empresa);
                $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);
                $this->pedidoFlowService->borrarPedidoTemporal($connection, $pedidoId, (string) $request->session()->get('vendedor', ''));
            }
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()->route('pedido.index')->withErrors([
                'buscar' => 'No fue posible reiniciar el pedido en la base externa.',
            ]);
        }

        $request->session()->forget([
            'pedido_actual',
            'pedido_encabezado',
            'pedido_catalogo',
            'cliente_seleccionado',
            'despacho_actual',
        ]);

        return redirect()->route('cliente.index');
    }

    public function terminar(Request $request): RedirectResponse
    {
        $pedidoId = (int) $request->session()->get('pedido_actual', 0);

        if ($pedidoId === 0) {
            return redirect()->route('cliente.index');
        }

        $empresaId = $request->session()->get('empresa_id');
        $empresa = Empresa::query()->find($empresaId);

        try {
            if ($empresa && $empresa->ip_servidor) {
                $this->empresaConnection->connect($empresa);
                $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);
                $movimientos = $this->pedidoFlowService->cargarMovimientos($connection, $pedidoId);

                if (count($movimientos) === 0) {
                    return redirect()->route('pedido.index')->withErrors([
                        'buscar' => 'No hay ningun pedido o articulo para enviar.',
                    ]);
                }

                $totalPedido = collect($movimientos)->sum(fn (object $movimiento): int => (int) data_get($movimiento, 'neto', 0));

                $this->pedidoFlowService->actualizarTotalesPedido($connection, $pedidoId, $totalPedido);

                $correoEstado = null;

                if ($request->boolean('enviar_correo')) {
                    /** @var array<string, mixed> $cliente */
                    $cliente = $request->session()->get('cliente_seleccionado', []);
                    $correo = (string) ($cliente['correo'] ?? '');

                    if ($correo !== '') {
                        try {
                            Mail::html($this->crearBodyPedido($movimientos, $totalPedido, (string) ($cliente['nombre'] ?? 'Cliente'), $this->nombreEmpresaCorreo($empresa)), function ($message) use ($correo): void {
                                $message->to($correo)->subject('Informacion Pedido');
                            });

                            $correoEstado = ' Correo enviado a '.$correo.'.';
                        } catch (Throwable $throwable) {
                            report($throwable);

                            $correoEstado = ' El pedido se finalizo, pero no fue posible enviar el correo.';
                        }
                    } else {
                        $correoEstado = ' El pedido se finalizo, pero el cliente no tiene correo registrado.';
                    }
                }
            }
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()->route('pedido.index')->withErrors([
                'buscar' => 'No fue posible terminar el pedido en la base externa.',
            ]);
        }

        $request->session()->forget([
            'pedido_actual',
            'pedido_encabezado',
            'pedido_catalogo',
            'cliente_seleccionado',
            'despacho_actual',
        ]);

        return redirect()->route('cliente.index')->with('status', 'Pedido realizado con éxito.'.($correoEstado ?? ''));
    }

    public function pedidosProceso(Request $request): View|RedirectResponse
    {
        $empresa = Empresa::query()->find($request->session()->get('empresa_id'));

        if (! $empresa || ! $empresa->ip_servidor) {
            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'No se encontro configuracion de empresa para listar pedidos.',
            ]);
        }

        try {
            $this->empresaConnection->connect($empresa);
            $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);
            $vendedor = (string) $request->session()->get('vendedor', '');
            $operarioNombre = (string) $request->session()->get('operario', '');

            if ($operarioNombre !== '') {
                $operarioRegistro = $connection->table('xxxxciao')
                    ->select('ciao_vend')
                    ->where('nombre', $operarioNombre)
                    ->first();

                if (! empty($operarioRegistro->ciao_vend)) {
                    $vendedor = (string) $operarioRegistro->ciao_vend;
                    $request->session()->put('vendedor', $vendedor);
                }
            }

            $pedidos = $this->pedidoFlowService->listarPedidosVendedor($connection, $vendedor, null, 15);
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'No fue posible cargar los pedidos en proceso.',
            ]);
        }

        return view('pedido.proceso', ['pedidos' => $pedidos]);
    }

    public function historicoPedidos(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'nit' => ['nullable', 'string', 'max:50'],
            'nombre' => ['nullable', 'string', 'max:120'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
            'estado' => ['nullable', 'string', 'in:todos,proceso,aprobados'],
        ]);

        $fechaDesde = $validated['fecha_desde'] ?? now()->toDateString();
        $fechaHasta = $validated['fecha_hasta'] ?? now()->toDateString();
        $estado = $validated['estado'] ?? 'aprobados';
        $estados = match ($estado) {
            'proceso' => [0, 1],
            default => null,
        };
        $soloAprobados = $estado === 'aprobados';
        $pedidos = [];
        $busquedaRealizada = $request->hasAny(['nit', 'nombre', 'fecha_desde', 'fecha_hasta']);
        $empresa = Empresa::query()->find($request->session()->get('empresa_id'));

        if (! $empresa || ! $empresa->ip_servidor) {
            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'No se encontro configuracion de empresa para consultar historico.',
            ]);
        }

        if ($busquedaRealizada) {
            try {
                $this->empresaConnection->connect($empresa);
                $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);
                $pedidos = $this->pedidoFlowService->filtrarPedidos(
                    $connection,
                    (string) $request->session()->get('vendedor', ''),
                    (string) ($validated['nit'] ?? ''),
                    (string) ($validated['nombre'] ?? ''),
                    $fechaDesde,
                    $fechaHasta,
                    $estados,
                    $soloAprobados,
                    15,
                );
            } catch (Throwable $throwable) {
                report($throwable);

                return redirect()->route('pedido.historico')->withErrors([
                    'pedidos' => 'No fue posible filtrar pedidos.',
                ]);
            }
        }

        return view('pedido.historico', [
            'pedidos' => $pedidos,
            'busquedaRealizada' => $busquedaRealizada,
            'filtros' => [
                'nit' => $validated['nit'] ?? '',
                'nombre' => $validated['nombre'] ?? '',
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'estado' => $estado,
            ],
        ]);
    }

    public function pedidosAprobados(Request $request): View|RedirectResponse
    {
        $empresa = Empresa::query()->find($request->session()->get('empresa_id'));

        if (! $empresa || ! $empresa->ip_servidor) {
            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'No se encontro configuracion de empresa para consultar pedidos aprobados.',
            ]);
        }

        try {
            $this->empresaConnection->connect($empresa);
            $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);
            $pedidos = $this->pedidoFlowService->listarPedidosAprobados($connection, (string) $request->session()->get('vendedor', ''), 15);
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()->route('cliente.index')->withErrors([
                'continuar' => 'No fue posible cargar los pedidos aprobados.',
            ]);
        }

        return view('pedido.aprobados', ['pedidos' => $pedidos]);
    }

    public function detallePedido(Request $request, int $pedido): View|RedirectResponse
    {
        $empresa = Empresa::query()->find($request->session()->get('empresa_id'));

        if (! $empresa || ! $empresa->ip_servidor) {
            return redirect()->route('pedido.proceso');
        }

        try {
            $this->empresaConnection->connect($empresa);
            $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);
            $encabezado = $this->pedidoFlowService->obtenerPedido($connection, $pedido, (string) $request->session()->get('vendedor', ''));

            if (! $encabezado) {
                return redirect()->route('pedido.proceso')->withErrors([
                    'pedidos' => 'No se encontro el pedido seleccionado.',
                ]);
            }

            $numero = (string) data_get($encabezado, 'numero', '');
            $movimientos = ((int) data_get($encabezado, 'ped_estado', 0) <= 1 || $numero === '')
                ? $this->pedidoFlowService->cargarMovimientos($connection, $pedido)
                : $this->pedidoFlowService->cargarMovimientosPorNumero($connection, $numero);
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()->route('pedido.proceso')->withErrors([
                'pedidos' => 'No fue posible cargar el detalle del pedido.',
            ]);
        }

        return view('pedido.detalle', [
            'pedido' => $encabezado,
            'movimientos' => $movimientos,
        ]);
    }

    public function retomarPedido(Request $request, int $pedido): RedirectResponse
    {
        if ($request->session()->has('pedido_actual')) {
            return redirect()->route('pedido.detalle', $pedido)->withErrors([
                'pedidos' => 'Ya tiene un pedido en proceso, para empezar uno nuevo primero reinicie el pedido actual.',
            ]);
        }

        $empresa = Empresa::query()->find($request->session()->get('empresa_id'));

        if (! $empresa || ! $empresa->ip_servidor) {
            return redirect()->route('pedido.proceso');
        }

        try {
            $this->empresaConnection->connect($empresa);
            $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);
            $encabezado = $this->pedidoFlowService->obtenerPedido($connection, $pedido, (string) $request->session()->get('vendedor', ''));

            if (! $encabezado || ! in_array((int) data_get($encabezado, 'ped_estado', 0), [0, 1], true)) {
                return redirect()->route('pedido.detalle', $pedido)->withErrors([
                    'pedidos' => 'No se puede continuar con el pedido, solicite autorizacion.',
                ]);
            }

            $cliente = $connection->table('xxxx3ros')
                ->where('tronit', data_get($encabezado, 'nit'))
                ->first();

            $grupos = $this->pedidoFlowService->cargarGrupos($connection);
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()->route('pedido.detalle', $pedido)->withErrors([
                'pedidos' => 'No fue posible retomar el pedido.',
            ]);
        }

        $request->session()->put([
            'pedido_actual' => $pedido,
            'pedido_encabezado' => [
                'id_vtaped' => (int) data_get($encabezado, 'id_vtaped', $pedido),
                'nit' => (string) data_get($encabezado, 'nit', ''),
                'fecha' => (string) data_get($encabezado, 'fecha', now()->toDateString()),
                'hdigita' => (string) data_get($encabezado, 'hdigita', now()->format('H:i:s')),
            ],
            'pedido_catalogo.grupos' => $grupos,
            'pedido_catalogo.articulos' => [],
            'pedido_catalogo.busqueda' => [],
            'pedido_catalogo.busqueda_realizada' => false,
            'cliente_seleccionado' => [
                'nit' => data_get($encabezado, 'nit'),
                'nombre' => data_get($encabezado, 'titular'),
                'ciudad' => data_get($encabezado, 'tituciud'),
                'telefono' => data_get($encabezado, 'titutelf'),
                'correo' => data_get($cliente, 'troemail', ''),
                'direccion' => data_get($encabezado, 'titudire'),
                'precio' => (int) data_get($cliente, 'troprecio', 1),
            ],
            'despacho_actual' => [
                'recibe' => (string) data_get($encabezado, 'desp_nomb', ''),
                'direccion' => (string) data_get($encabezado, 'desp_direc', ''),
                'ciudad' => (string) data_get($encabezado, 'desp_city', ''),
                'telefono' => (string) data_get($encabezado, 'desp_telf', ''),
                'detalle' => (string) data_get($encabezado, 'datos1', ''),
            ],
        ]);

        return redirect()->route('pedido.index');
    }

    public function borrarPedido(Request $request, int $pedido): RedirectResponse
    {
        $empresa = Empresa::query()->find($request->session()->get('empresa_id'));

        if (! $empresa || ! $empresa->ip_servidor) {
            return redirect()->route('pedido.proceso');
        }

        try {
            $this->empresaConnection->connect($empresa);
            $connection = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME);
            $this->pedidoFlowService->borrarPedidoTemporal($connection, $pedido, (string) $request->session()->get('vendedor', ''));
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()->route('pedido.proceso')->withErrors([
                'pedidos' => 'No fue posible eliminar el pedido.',
            ]);
        }

        return redirect()->route('pedido.proceso');
    }

    /**
     * @param  array<int, object>  $movimientos
     */
    private function crearBodyPedido(array $movimientos, int $totalPedido, string $cliente, string $empresaNombre): string
    {
        $filas = '';

        foreach ($movimientos as $indice => $movimiento) {
            $filas .= '<tr><td style="border-bottom:1px solid #ecf0f1;">'.($indice + 1).'</td><td style="border-bottom:1px solid #ecf0f1;">'.e((string) data_get($movimiento, 'detalle', '')).'</td><td align="center" style="border-bottom:1px solid #ecf0f1;">'.(int) data_get($movimiento, 'cantidad', 0).'</td><td align="right" style="border-bottom:1px solid #ecf0f1;">$'.number_format((float) data_get($movimiento, 'neto', 0), 0, ',', '.').'</td></tr>';
        }

        return "<!DOCTYPE html>
<html lang=\"es\">
<head>
  <meta charset=\"UTF-8\">
  <title>Confirmación de pedido</title>
</head>
<body style=\"margin:0;padding:0;font-family:Segoe UI,sans-serif;background-color:#f8f9fa;\">
  <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"padding:20px;background-color:#f8f9fa;\">
    <tr>
      <td align=\"center\">
        <table width=\"700\" cellpadding=\"0\" cellspacing=\"0\" style=\"background-color:#ffffff;border-radius:8px;padding:30px;box-shadow:0 4px 8px rgba(0,0,0,0.1);\">
          <tr>
            <td align=\"center\" style=\"padding-bottom:20px;\">
              <h2 style=\"color:#2d3436;margin:0;\">🛒 ".e($empresaNombre !== '' ? $empresaNombre : 'nombre de la empresa')."</h2>
              <p style=\"color:#636e72;margin:5px 0;\">Confirmación de Pedido</p>
            </td>
          </tr>
          <tr>
            <td>
              <p style=\"color:#2d3436;font-size:15px;\">Estimado cliente <strong>".e($cliente)."</strong></p>
              <p style=\"color:#2d3436;font-size:15px;\">Hemos recibido correctamente su solicitud de pedido. A continuación se detalla la lista de productos solicitados:</p>
              <table width=\"100%\" cellpadding=\"8\" cellspacing=\"0\" style=\"margin:20px 0;border-collapse:collapse;\">
                <thead>
                  <tr style=\"background-color:#dfe6e9;\">
                    <th align=\"left\">#</th>
                    <th align=\"left\">Producto</th>
                    <th align=\"center\">Cantidad</th>
                    <th align=\"right\">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  ".$filas."
                  <tr style=\"border-top:2px solid #b2bec3;\">
                    <td colspan=\"3\" align=\"right\"><strong>Total del Pedido:</strong></td>
                    <td align=\"right\"><strong>$".number_format($totalPedido, 0, ',', '.')."</strong></td>
                  </tr>
                </tbody>
              </table>
              <p style=\"color:#2d3436;font-size:14px;\">Fecha de registro: <strong>".now()->format('d/m/Y')."</strong></p>
              <p style=\"color:#2d3436;font-size:14px;\">Un representante de logística se pondrá en contacto para coordinar el despacho y tiempos de entrega.</p>
              <hr style=\"border:none;border-top:1px solid #dcdde1;margin:20px 0;\">
              <p style=\"color:#636e72;font-size:13px;\">Gracias por preferirnos. Para cualquier duda o modificación del pedido, puede contactarnos directamente.</p>
              <p style=\"color:#b2bec3;font-size:12px;text-align:center;\">Este correo fue generado automáticamente. No responda a este mensaje.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>";
    }

    private function nombreEmpresaCorreo(Empresa $empresa): string
    {
        $nombre = trim((string) $empresa->nombre);
        $codigo = trim((string) $empresa->codigo);

        if ($codigo !== '') {
            $nombre = trim((string) preg_replace('/\s*\('.preg_quote($codigo, '/').'\)\s*/i', ' ', $nombre));
            $nombre = trim((string) preg_replace('/\b'.preg_quote($codigo, '/').'\b/i', '', $nombre));
            $nombre = trim($nombre, " \t\n\r\0\x0B-_/()");
        }

        return $nombre !== '' ? $nombre : (string) config('app.name', 'Pedidos Web');
    }
}
