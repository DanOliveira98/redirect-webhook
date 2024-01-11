<?php

namespace GamesPackage\Services;

use Illuminate\Http\Request;
class Darwin
{

    public $url = "";
    public $operatorID = "";

    public function __construct()
    {
        $this->url = "https://eg.paconassa.com/";
        $this->operatorID = "everestgames";
    }


    public function play(Request $request)
    {

        $userId = $request->key . "#" . $request->userId;
        $data = [
            "token" => $userId,
            "currency" => "BRL",
            "language" => "pt-BR",
            "gameID" => $request->game,
            "lobbyUrl" => "https://{$request->key}"
        ];

        $params = http_build_query($data);
        $url = "{$this->url}{$this->operatorID}/?{$params}";
        return [
            "url" => $url
        ];
    }
}