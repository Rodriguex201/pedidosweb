<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

class EmpresaExternaConnectionService
{
    public const CONNECTION_NAME = 'empresa_externa';

    /**
     * @return array<string, mixed>
     */
    public function buildConfig(Empresa $empresa): array
    {
        $config = (array) config('database.connections.'.self::CONNECTION_NAME, []);

        return array_merge($config, [
            'host' => $empresa->ip_servidor,
            'database' => $empresa->database ?: strtolower((string) $empresa->codigo),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function connect(Empresa $empresa): array
    {
        $config = $this->buildConfig($empresa);

        config()->set('database.connections.'.self::CONNECTION_NAME, $config);
        DB::purge(self::CONNECTION_NAME);
        DB::reconnect(self::CONNECTION_NAME);

        return $config;
    }
}
