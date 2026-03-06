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
        return (int) $connection->table('xxxxvpex')->insertGetId([
            'nit' => $cliente['nit'],
            'fecha' => now(),
            'obra' => $operario['obra'] ?? '',
            'vendedor' => $operario['vendedor'] ?? '',
            'terminal' => $operario['terminal'] ?? '',
            'sucursal' => $operario['sucursal'] ?? '',
            'operario' => $operario['clave'] ?? '',
            'recibe' => $despacho['recibe'] ?? '',
            'despdire' => $despacho['direccion'] ?? ($cliente['direccion'] ?? ''),
            'desptelf' => $despacho['telefono'] ?? ($cliente['telefono'] ?? ''),
            'despciud' => $despacho['ciudad'] ?? ($cliente['ciudad'] ?? ''),
            'despobse' => $despacho['detalle'] ?? '',
            'titular' => $cliente['nombre'] ?? '',
            'titudire' => $cliente['direccion'] ?? '',
            'titutelf' => $cliente['telefono'] ?? '',
            'tituciud' => $cliente['ciudad'] ?? '',
            'ped_estado' => 0,
        ]);
    }

    /**
     * @return array<int, object>
     */
    public function cargarGrupos(ConnectionInterface $connection): array
    {
        return $connection->table('xxxxgrup')
            ->orderBy('grupnomb')
            ->get()
            ->all();
    }

    /**
     * @return array<int, object>
     */
    public function cargarArticulos(ConnectionInterface $connection): array
    {
        return $connection->table('xxxxarti')
            ->join('xxxxartv', 'xxxxarti.articodigo', '=', 'xxxxartv.artvcodigo')
            ->select([
                'xxxxarti.articodigo',
                'xxxxarti.artigrupo',
                'xxxxarti.artinomb',
                'xxxxarti.artiiva',
                'xxxxarti.articant',
                'xxxxartv.artivlr1_c',
            ])
            ->orderBy('xxxxarti.artinomb')
            ->get()
            ->all();
    }
}
