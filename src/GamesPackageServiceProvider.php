<?php

namespace GamesPackage;

use GamesPackage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;

class GamesPackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Adicionar o middleware ao início do pipeline
        $kernel = $this->app->make(Kernel::class);
        $kernel->prependMiddleware(GamesPackage\SdkGames::class);
    }
}