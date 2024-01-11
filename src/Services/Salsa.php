<?php

namespace GamesPackage\Services;

use Illuminate\Http\Request;

class Salsa
{
    public $url = "";
    public $pn = "";

    public function __construct()
    {
        $this->url = config('casinos.salsa.url');
        $this->pn = config('casinos.salsa.pn');
    }

    public function play(Request $request)
    {
        $userId = $request->key . "#" . $request->userId;

        return [
            "url" => "$this->url/game?token={$userId}&pn={$this->pn}&lang=pt&game={$request->game}"
        ];
    }
}