<?php

namespace GamesPackage;

use GamesPackage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;

class GamesPackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $kernel = $this->app->make(Kernel::class);
        $kernel->prependMiddleware(GamesPackage\SdkGames::class);
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }
}