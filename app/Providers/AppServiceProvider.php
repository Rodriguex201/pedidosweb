<?php

namespace App\Providers;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $userName = null;
            $operarioName = session('operario');
            $empresaInfo = null;

            $userId = session('user_id');

            if ($userId) {
                $userName = User::query()->whereKey($userId)->value('name');
            }

            $empresaId = session('empresa_id');

            if ($empresaId) {
                /** @var Empresa|null $empresa */
                $empresa = Empresa::query()->select('nombre', 'codigo')->find($empresaId);

                if ($empresa) {
                    $empresaInfo = trim("{$empresa->nombre} ({$empresa->codigo})");
                }
            }

            $view->with('sessionHeaderData', [
                'userName' => $userName,
                'operarioName' => $operarioName,
                'empresaInfo' => $empresaInfo,
            ]);
        });
    }
}
