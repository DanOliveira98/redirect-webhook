<?php

use Illuminate\Support\ServiceProvider;

class GamesPackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Adicionar o middleware ao início do pipeline
        $kernel = $this->app->make(Kernel::class);
        $kernel->prependMiddleware(GamesPackage\SdkGames::class);
    }
}