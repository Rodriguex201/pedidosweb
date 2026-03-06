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
        return [
            'driver' => env('DB_CONNECTION_EXTERNA', 'mysql'),
            'host' => $empresa->ip_servidor,
            'port' => env('DB_PORT_EXTERNA', '3306'),
            'database' => strtolower((string) $empresa->codigo),
            'username' => env('DB_USERNAME_EXTERNA'),
            'password' => env('DB_PASSWORD_EXTERNA'),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? \Pdo\Mysql::ATTR_SSL_CA : \PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ];
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
