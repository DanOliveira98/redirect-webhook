<?php

namespace GamesPackage\Controllers;

use GamesPackage\Services\Pg;
use GamesPackage\Services\Pragmatic;
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
    }
}