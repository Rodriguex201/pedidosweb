<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Services\EmpresaExternaConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;
use Throwable;

class ClienteController extends Controller
{
    public function __construct(private readonly EmpresaExternaConnectionService $empresaConnection)
    {
    }

    public function index(): View
    {
        return view('cliente.index');
    }

    public function buscar(Request $request): JsonResponse
    {
        $termino = trim((string) ($request->input('termino') ?: $request->input('nit', '')));
        $nitNormalizado = preg_replace('/\D+/', '', $termino) ?: '';
        $busquedaPorNit = $nitNormalizado !== '' && preg_match('/^[\d\s.\-]+$/', $termino) === 1;

        if ($termino === '') {
            return response()->json([
                'message' => 'Digite un NIT o nombre para buscar el cliente.',
            ], 422);
        }

        if (mb_strlen($termino) > 100) {
            return response()->json([
                'message' => 'La búsqueda no puede superar los 100 caracteres.',
            ], 422);
        }
        $empresaId = $request->session()->get('empresa_id');
        $empresa = Empresa::query()->find($empresaId);

        if (! $empresa || ! $empresa->ip_servidor) {
            return response()->json([
                'message' => 'No se encontró configuración de empresa para consultar clientes.',
            ], 422);
        }

        try {
            $this->empresaConnection->connect($empresa);

            $clientesQuery = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME)
                ->table('xxxx3ros')
                ->select([
                    'tronit',
                    'tronombre',
                    'trozona',
                    'trociudad',
                    'trotelef',
                    'troemail',
                    'trodirec',
                    'trocccupo',
                    'trotipo',
                    'trocelular',
                    'tronomb_2',
                    'troapel_1',
                    'troapel_2',
                    'trocpsaldo',
                    'troprecio',
                    'troccvnc',
                    'troccsaldo',
                ]);

            $clientePorNit = (clone $clientesQuery)
                ->where('tronit', $termino)
                ->first();

            if (! $clientePorNit && $nitNormalizado !== '') {
                $clientePorNit = (clone $clientesQuery)
                    ->whereRaw("REPLACE(REPLACE(REPLACE(TRIM(tronit), '.', ''), '-', ''), ' ', '') = ?", [$nitNormalizado])
                    ->first();
            }

            if ($clientePorNit) {
                $clienteData = $this->mapearCliente($clientePorNit);
                $request->session()->put('cliente_seleccionado', $clienteData);

                return response()->json([
                    'data' => $clienteData,
                ]);
            }

            $palabras = collect(preg_split('/\s+/', $termino) ?: [])
                ->map(fn (string $palabra): string => trim($palabra))
                ->filter()
                ->take(4)
                ->values();

            $clientes = (clone $clientesQuery)
                ->where(function ($query) use ($termino, $nitNormalizado, $palabras): void {
                    $query->where('tronit', 'like', $termino.'%')
                        ->when($nitNormalizado !== '', function ($query) use ($nitNormalizado): void {
                            $query->orWhereRaw("REPLACE(REPLACE(REPLACE(TRIM(tronit), '.', ''), '-', ''), ' ', '') LIKE ?", [$nitNormalizado.'%']);
                        })
                        ->orWhere(function ($query) use ($palabras): void {
                            foreach ($palabras as $palabra) {
                                $query->where(function ($query) use ($palabra): void {
                                    $query->where('tronombre', 'like', '%'.$palabra.'%')
                                        ->orWhere('tronomb_2', 'like', '%'.$palabra.'%')
                                        ->orWhere('troapel_1', 'like', '%'.$palabra.'%')
                                        ->orWhere('troapel_2', 'like', '%'.$palabra.'%')
                                        ->orWhereRaw('SOUNDEX(tronombre) = SOUNDEX(?)', [$palabra])
                                        ->orWhereRaw('SOUNDEX(tronomb_2) = SOUNDEX(?)', [$palabra])
                                        ->orWhereRaw('SOUNDEX(troapel_1) = SOUNDEX(?)', [$palabra])
                                        ->orWhereRaw('SOUNDEX(troapel_2) = SOUNDEX(?)', [$palabra]);
                                });
                            }
                        });
                })
                ->orderBy('tronombre')
                ->limit(20)
                ->get();
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'No fue posible consultar el cliente en la base de datos externa.',
            ], 500);
        }

        if ($clientes->isEmpty()) {
            return response()->json([
                'message' => 'No se encontró cliente para el NIT o nombre ingresado.',
            ], 404);
        }

        $resultados = $clientes
            ->map(fn (stdClass $cliente): array => $this->mapearCliente($cliente))
            ->values();

        if ($busquedaPorNit || $resultados->count() === 1) {
            $clienteData = $resultados->first();
            $request->session()->put('cliente_seleccionado', $clienteData);

            return response()->json([
                'data' => $clienteData,
            ]);
        }

        return response()->json([
            'matches' => $resultados,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapearCliente(stdClass $cliente): array
    {
        return [
            'nit' => $cliente->tronit,
            'nombre' => $this->nombreCompleto($cliente),
            'zona' => $cliente->trozona,
            'ciudad' => $cliente->trociudad,
            'telefono' => $cliente->trotelef,
            'correo' => $cliente->troemail,
            'direccion' => $cliente->trodirec,
            'cupo' => (int) ($cliente->trocccupo ?? 0),
            'tipo_cliente' => $cliente->trotipo,
            'escala' => $this->obtenerPrecioCliente($cliente),
            'celular' => $cliente->trocelular,
            'nombre2' => $cliente->tronomb_2,
            'apellido1' => $cliente->troapel_1,
            'apellido2' => $cliente->troapel_2,
            'saldo_vencido' => (int) ($cliente->trocpsaldo ?? 0),
            'precio' => (int) ($cliente->troprecio ?? 0),
            'facturas_por_cobrar' => (int) ($cliente->troccvnc ?? 0),
            'saldo_cartera' => (int) ($cliente->troccsaldo ?? 0),
        ];
    }

    private function nombreCompleto(stdClass $cliente): string
    {
        return collect([
            $cliente->tronombre ?? '',
            $cliente->tronomb_2 ?? '',
            $cliente->troapel_1 ?? '',
            $cliente->troapel_2 ?? '',
        ])
            ->map(fn (mixed $valor): string => trim((string) $valor))
            ->filter()
            ->implode(' ');
    }

    private function obtenerPrecioCliente(stdClass $cliente): string
    {
        return match ((int) ($cliente->troprecio ?? 0)) {
            1 => 'contado',
            2 => 'mayorista',
            3 => 'distribuidor',
            4 => 'credito',
            default => 'no especificado',
        };
    }
}
