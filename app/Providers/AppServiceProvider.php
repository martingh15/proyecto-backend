<?php

namespace App\Providers;

use App\Services\PedidoService;
use App\Services\ProductoService;
use App\Services\UsuarioService;
use Illuminate\Support\ServiceProvider;
use JWTAuth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UsuarioService::class, function () {
            return new UsuarioService();
        });
        
		$this->app->bind(ProductoService::class, function () {
            return new ProductoService();
        });

        $this->app->bind(PedidoService::class, function () {
            return new PedidoService();
        });

        $this->app->bind( JWTAuth::class, function ($manager, $user, $auth, $request) {
            return new JWTAuth($manager, $user, $auth, $request);
        });

    }
}
