<?php

namespace GamesPackage\Services;


use GamesPackage\Contracts\CassinoInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Pg implements CassinoInterface
{
    public $op = "";
    public $sk = "";
    public $url = "";

    public function __construct()
    {
        $this->op = config('casinos.pg.op');
        $this->sk = config('casinos.pg.secret_key');
        $this->url = config('casinos.pg.url');
    }

    public function play(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $trace = (string)Str::uuid();
        try {
            $extraArgs = http_build_query([
                "ops" => $request->key . "#" . $request->userId,
                "btt" => 1,
            ]);

            $url = rtrim($this->url, "/");

            $response = $client->post(
                "{$url}/external-game-launcher/api/v1/GetLaunchURLHTML?trace_id={$trace}",
                [
                    "form_params" => [
                        "operator_token" => $this->op,
                        "path" => "/{$request->game}/index.html",
                        "url_type" => "game-entry",
                        "client_ip" => request()->ip(),
                        "extra_args" => $extraArgs
                    ]
                ]
            );
            return response()->json(["body" => $response->getBody()->getContents()]);
        } catch (\GuzzleHttp\Exception\ClientException $exception) {
            Log::debug($exception->getResponse()->getBody());
        }
    }
}