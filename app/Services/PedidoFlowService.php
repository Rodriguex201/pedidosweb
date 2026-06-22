<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;

class PedidoFlowService
{
    /**
     * @param  array<string, mixed>  $cliente
     * @param  array<string, mixed>  $operario
     * @param  array<string, mixed>  $despacho
     */
    public function crearEncabezadoTemporal(ConnectionInterface $connection, array $cliente, array $operario, array $despacho = []): int
    {
        $fecha = now()->toDateString();
        $hdigita = now()->format('H:i:s');

        return (int) $connection->table('xxxxvpex')->insertGetId([
            'nit' => $cliente['nit'],
            'numero' => '',
            'fecha' => $fecha,
            'dias' => 1,
            'obra' => $operario['obra'] ?? '',
            'transporte' => 'trans',
            'fdigitar' => $fecha,
            'hdigita' => $hdigita,
            'datos1' => $despacho['detalle'] ?? '',
            'vendedor' => $operario['vendedor'] ?? '',
            'valor' => 0,
            'abono' => 0,
            'saldo' => 0,
            'terminal' => $operario['terminal'] ?? '',
            'vriva' => 0,
            'desctos' => 0,
            'neto' => 0,
            'costo' => 0,
            'titular' => $cliente['nombre'] ?? '',
            'titudire' => $cliente['direccion'] ?? '',
            'titutelf' => $cliente['telefono'] ?? '',
            'tituciud' => $cliente['ciudad'] ?? '',
            'ped_fraxx' => 0,
            'ped_envio' => 1,
            'ped_estado' => 0,
            'sucursal' => $operario['sucursal'] ?? '',
            'operario' => $operario['clave'] ?? '',
            'grupo' => 0,
            'desp_nomb' => $despacho['recibe'] ?? '',
            'desp_direc' => $despacho['direccion'] ?? ($cliente['direccion'] ?? ''),
            'desp_telf' => $despacho['telefono'] ?? ($cliente['telefono'] ?? ''),
            'desp_city' => $despacho['ciudad'] ?? ($cliente['ciudad'] ?? ''),
            'consumo' => 0,
        ]);
    }

    /**
     * @return array<int, object>
     */
    public function cargarGrupos(ConnectionInterface $connection): array
    {
        return $connection->table('xxxxgrup')
            ->get()
            ->map(function (object $grupo): object {
                $valores = array_values(get_object_vars($grupo));

                $grupo->grupcodigo = (string) ($valores[0] ?? '');
                $grupo->grupnomb = (string) ($valores[1] ?? '');

                return $grupo;
            })
            ->all();
    }

    /**
     * @return array<int, object>
     */
    public function cargarArticulos(ConnectionInterface $connection): array
    {
        return $this->buscarArticulos($connection);
    }

    /**
     * @param  array<int, string>  $palabras
     * @return array<int, object>
     */
    public function buscarArticulos(ConnectionInterface $connection, array $palabras = [], ?string $grupoCodigo = null): array
    {
        $palabras = collect($palabras)
            ->map(fn (string $palabra): string => trim($palabra))
            ->filter()
            ->take(3)
            ->values()
            ->all();

        return $connection->table('xxxxarti as a')
            ->leftJoin('xxxxartv as v', 'a.articodigo', '=', 'v.artvcodigo')
            ->select([
                'a.articodigo',
                'a.artigrupo',
                'a.artinomb',
                'a.artiunidad',
                'a.artimarca',
                'a.articodi2',
                'a.artipeso',
            ])
            ->selectRaw('IFNULL(v.artivlr1_c, 0) AS artivlr1_c')
            ->selectRaw('IFNULL(v.artivlr2_c, 0) AS artivlr2_c')
            ->selectRaw('IFNULL(v.artivlr3_c, 0) AS artivlr3_c')
            ->selectRaw('IFNULL(v.artivlr4_c, 0) AS artivlr4_c')
            ->selectRaw('IFNULL(v.artiiva, 0) AS artiiva')
            ->selectRaw('IFNULL(v.articant, 0) AS articant')
            ->when($grupoCodigo, fn ($query) => $query->where('a.artigrupo', $grupoCodigo))
            ->when($palabras !== [], function ($query) use ($palabras): void {
                $query->where(function ($query) use ($palabras): void {
                    foreach ($palabras as $palabra) {
                        $query->orWhere('a.artinomb', 'like', '%'.$palabra.'%')
                            ->orWhere('a.artimarca', 'like', '%'.$palabra.'%');
                    }
                });
            })
            ->orderBy('a.artinomb')
            ->limit(100)
            ->get()
            ->all();
    }

    public function buscarArticuloPorCodigo(ConnectionInterface $connection, string $codigo): ?object
    {
        $query = fn () => $connection->table('xxxxarti as a')
            ->leftJoin('xxxxartv as v', 'a.articodigo', '=', 'v.artvcodigo')
            ->select([
                'a.articodigo',
                'a.artigrupo',
                'a.artinomb',
                'a.artiunidad',
                'a.artimarca',
                'a.articodi2',
                'a.artipeso',
            ])
            ->selectRaw('IFNULL(v.artivlr1_c, 0) AS artivlr1_c')
            ->selectRaw('IFNULL(v.artivlr2_c, 0) AS artivlr2_c')
            ->selectRaw('IFNULL(v.artivlr3_c, 0) AS artivlr3_c')
            ->selectRaw('IFNULL(v.artivlr4_c, 0) AS artivlr4_c')
            ->selectRaw('IFNULL(v.artiiva, 0) AS artiiva')
            ->selectRaw('IFNULL(v.articant, 0) AS articant');

        $articulo = $query()
            ->where('a.articodigo', $codigo)
            ->first();

        if ($articulo) {
            return $articulo;
        }

        return $query()
            ->where('a.articodi2', $codigo)
            ->first();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function obtenerEncabezadoBasico(ConnectionInterface $connection, int $pedidoId): ?array
    {
        $encabezado = $connection->table('xxxxvpex')
            ->select('id_vtaped', 'nit', 'fecha', 'hdigita')
            ->where('id_vtaped', $pedidoId)
            ->first();

        if (! $encabezado) {
            return null;
        }

        return [
            'id_vtaped' => (int) data_get($encabezado, 'id_vtaped', 0),
            'nit' => (string) data_get($encabezado, 'nit', ''),
            'fecha' => (string) data_get($encabezado, 'fecha', now()->toDateString()),
            'hdigita' => (string) data_get($encabezado, 'hdigita', now()->format('H:i:s')),
        ];
    }

    public function agregarMovimientoTemporal(
        ConnectionInterface $connection,
        int $pedidoId,
        object $articulo,
        array $cliente,
        array $operario,
        int $cantidad,
        int $valorUnidad,
        string $detalleMovimiento = '',
        ?array $encabezadoPedido = null
    ): int {
        $encabezado = $encabezadoPedido ?: null;

        if (! $encabezado) {
            $encabezado = (array) $connection->table('xxxxvpex')
                ->select('id_vtaped', 'nit', 'fecha', 'hdigita')
                ->where('id_vtaped', $pedidoId)
                ->first();
        }

        $fecha = (string) ($encabezado['fecha'] ?? now()->toDateString());
        $hdigita = (string) ($encabezado['hdigita'] ?? now()->format('H:i:s'));
        $neto = $cantidad * $valorUnidad;
        $detalle = trim((string) data_get($articulo, 'artinomb', '').' '.$detalleMovimiento);

        $connection->table('xxxxvpax')->insert([
            'numero' => '',
            'fecha' => $fecha,
            'tpcmbte' => 'V',
            'codigo' => data_get($articulo, 'articodigo', ''),
            'nit' => $encabezado['nit'] ?? ($cliente['nit'] ?? ''),
            'punto' => '000',
            'puntox' => '000',
            'obra' => $operario['obra'] ?? '',
            'detalle' => $detalle,
            'medida' => data_get($articulo, 'artiunidad', ''),
            'operario' => $operario['clave'] ?? '',
            'hdigita' => $hdigita,
            'fdigitar' => $fecha,
            'entregan' => 0,
            'cantinic' => 0,
            'cantidad' => $cantidad,
            'valor' => $valorUnidad,
            'costo' => 0,
            'neto' => $neto,
            'dsct4' => 0,
            'dsct2' => 0,
            'desctos' => 0,
            'iva' => (int) data_get($articulo, 'artiiva', 0),
            'vriva' => 0,
            'vrventa' => 0,
            'consumo' => 0,
            'compuesto' => 0,
            'peso' => (int) data_get($articulo, 'artipeso', 0),
            'anulado' => 0,
            'id_vtaped' => $pedidoId,
        ]);

        return $neto;
    }

    /**
     * @return array<int, object>
     */
    public function cargarMovimientos(ConnectionInterface $connection, int $pedidoId): array
    {
        return $connection->table('xxxxvpax')
            ->select([
                'Id_vpar',
                'codigo',
                'detalle',
                'medida',
                'cantidad',
                'valor',
                'neto',
                'peso',
                'id_vtaped',
            ])
            ->where('id_vtaped', $pedidoId)
            ->get()
            ->all();
    }

    public function listarPedidosVendedor(ConnectionInterface $connection, string $vendedor, ?array $estados = null, int $porPagina = 15)
    {
        $vendedor = trim($vendedor);

        return $connection->table('xxxxvpex')
            ->select($this->columnasListadoPedidos())
            ->where('vendedor', $vendedor)
            ->when($estados !== null, fn ($query) => $query->whereIn('ped_estado', $estados))
            ->orderByDesc('id_vtaped')
            ->simplePaginate($porPagina);
    }

    public function filtrarPedidos(ConnectionInterface $connection, string $vendedor, string $nit, string $nombre, string $fechaDesde, string $fechaHasta, ?array $estados = null, bool $soloAprobados = false, int $porPagina = 15)
    {
        $vendedor = trim($vendedor);

        return $connection->table('xxxxvpex')
            ->select($this->columnasListadoPedidos())
            ->where('vendedor', $vendedor)
            ->when($nit !== '', fn ($query) => $query->where('nit', 'like', $nit.'%'))
            ->when($nombre !== '', fn ($query) => $query->where('titular', 'like', $nombre.'%'))
            ->when($estados !== null, fn ($query) => $query->whereIn('ped_estado', $estados))
            ->when($soloAprobados, fn ($query) => $query->where('ped_estado', '!=', 0))
            ->whereDate('fdigitar', '>=', $fechaDesde)
            ->whereDate('fdigitar', '<=', $fechaHasta)
            ->orderByDesc('id_vtaped')
            ->simplePaginate($porPagina);
    }

    public function listarPedidosAprobados(ConnectionInterface $connection, string $vendedor, int $porPagina = 15)
    {
        $vendedor = trim($vendedor);

        return $connection->table('xxxxvpex')
            ->select($this->columnasListadoPedidos())
            ->where('vendedor', $vendedor)
            ->where('ped_estado', '!=', 0)
            ->orderByDesc('id_vtaped')
            ->simplePaginate($porPagina);
    }

    public function obtenerPedido(ConnectionInterface $connection, int $pedidoId, string $vendedor): ?object
    {
        $vendedor = trim($vendedor);

        return $connection->table('xxxxvpex')
            ->where('id_vtaped', $pedidoId)
            ->where('vendedor', $vendedor)
            ->first();
    }

    /**
     * @return array<int, object>
     */
    public function cargarMovimientosPorNumero(ConnectionInterface $connection, string $numero): array
    {
        return $connection->table('xxxxvpax')
            ->where('numero', $numero)
            ->get()
            ->all();
    }

    public function editarMovimientoTemporal(ConnectionInterface $connection, int $pedidoId, int $movimientoId, string $detalle, int $cantidad, int $valorUnidad, int $neto): void
    {
        $connection->table('xxxxvpax')
            ->where('id_vtaped', $pedidoId)
            ->where('Id_vpar', $movimientoId)
            ->update([
                'detalle' => $detalle,
                'cantidad' => $cantidad,
                'valor' => $valorUnidad,
                'neto' => $neto,
            ]);
    }

    public function obtenerMovimiento(ConnectionInterface $connection, int $pedidoId, int $movimientoId): ?object
    {
        return $connection->table('xxxxvpax')
            ->select('Id_vpar', 'neto')
            ->where('id_vtaped', $pedidoId)
            ->where('Id_vpar', $movimientoId)
            ->first();
    }

    public function borrarMovimientoTemporal(ConnectionInterface $connection, int $pedidoId, int $movimientoId): void
    {
        $connection->table('xxxxvpax')
            ->where('id_vtaped', $pedidoId)
            ->where('Id_vpar', $movimientoId)
            ->delete();
    }

    public function borrarPedidoTemporal(ConnectionInterface $connection, int $pedidoId, string $vendedor): void
    {
        if (! $this->obtenerPedido($connection, $pedidoId, $vendedor)) {
            return;
        }

        $connection->table('xxxxvpax')->where('id_vtaped', $pedidoId)->delete();
        $connection->table('xxxxvpex')
            ->where('id_vtaped', $pedidoId)
            ->where('vendedor', trim($vendedor))
            ->delete();
    }

    public function actualizarTotalesPedido(ConnectionInterface $connection, int $pedidoId, int $valorTotal): void
    {
        $connection->table('xxxxvpex')
            ->where('id_vtaped', $pedidoId)
            ->update(['valor' => $valorTotal]);
    }

    public function ajustarTotalPedido(ConnectionInterface $connection, int $pedidoId, int $delta): void
    {
        if ($delta > 0) {
            $connection->table('xxxxvpex')
                ->where('id_vtaped', $pedidoId)
                ->increment('valor', $delta);

            return;
        }

        if ($delta < 0) {
            $connection->table('xxxxvpex')
                ->where('id_vtaped', $pedidoId)
                ->decrement('valor', abs($delta));
        }
    }

    /**
     * @return array<int, string>
     */
    private function columnasListadoPedidos(): array
    {
        return [
            'id_vtaped',
            'fecha',
            'fdigitar',
            'hdigita',
            'nit',
            'titular',
            'valor',
            'ped_estado',
            'numero',
            'vendedor',
        ];
    }
}
