<?php

namespace GamesPackage\Controllers;

use GamesPackage\Services\Pg;
use GamesPackage\Services\Pragmatic;
use GuzzleHttp\Psr7\Request;

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
    }
}