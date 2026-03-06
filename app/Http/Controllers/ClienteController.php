<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Services\EmpresaExternaConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
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
        $validated = $request->validate([
            'nit' => ['required', 'string', 'max:50'],
        ]);

        $empresaId = $request->session()->get('empresa_id');
        $empresa = Empresa::query()->find($empresaId);

        if (! $empresa || ! $empresa->ip_servidor) {
            return response()->json([
                'message' => 'No se encontró configuración de empresa para consultar clientes.',
            ], 422);
        }

        try {
            $this->empresaConnection->connect($empresa);

            $cliente = DB::connection(EmpresaExternaConnectionService::CONNECTION_NAME)
                ->table('xxxx3ros')
                ->select([
                    'tronit',
                    'tronombre',
                    'trozona',
                    'trociudad',
                    'trotelef',
                    'troemail',
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
                ])
                ->where('tronit', trim($validated['nit']))
                ->first();
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'No fue posible consultar el cliente en la base de datos externa.',
            ], 500);
        }

        if (! $cliente) {
            return response()->json([
                'message' => 'No se encontró cliente para el NIT ingresado.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'nit' => $cliente->tronit,
                'nombre' => $cliente->tronombre,
                'zona' => $cliente->trozona,
                'ciudad' => $cliente->trociudad,
                'telefono' => $cliente->trotelef,
                'correo' => $cliente->troemail,
                'cupo' => (int) ($cliente->trocccupo ?? 0),
                'escala' => $cliente->trotipo,
                'celular' => $cliente->trocelular,
                'nombre2' => $cliente->tronomb_2,
                'apellido1' => $cliente->troapel_1,
                'apellido2' => $cliente->troapel_2,
                'saldo_vencido' => (int) ($cliente->trocpsaldo ?? 0),
                'precio' => (int) ($cliente->troprecio ?? 0),
                'facturas_por_cobrar' => (int) ($cliente->troccvnc ?? 0),
                'saldo_cartera' => (int) ($cliente->troccsaldo ?? 0),
            ],
        ]);
    }
}
