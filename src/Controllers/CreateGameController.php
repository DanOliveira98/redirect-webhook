<?php

namespace GamesPackage\Controllers;

use GamesPackage\Services\Evoplay;
use GamesPackage\Services\Pg;
use GamesPackage\Services\Pragmatic;
use GamesPackage\Services\Salsa;
use Illuminate\Http\Request;

class CreateGameController
{
    public function createGame(Request $request)
    {
        if ($request->provider === "pg") {
            return app()->make(Pg::class)->play($request);
        }
        if ($request->provider === "pragmatic") {
            return app()->make(Pragmatic::class)->play($request);
        }
        if ($request->provider === "salsa") {
            return app()->make(Salsa::class)->play($request);
        }
        if ($request->provider === "evoplay") {
            return app()->make(Evoplay::class)->play($request);
        }
        if ($request->provider === "darwin") {
            return app()->make(Evoplay::class)->play($request);
        }
    }
}