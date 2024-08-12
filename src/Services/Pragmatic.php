<?php

namespace GamesPackage\Services;

use GamesPackage\Contracts\CassinoInterface;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class Pragmatic implements CassinoInterface
{
    public function play(Request $request)
    {
        $urlStaging = config('casinos.pragmatic.url');
        $url = "$urlStaging/IntegrationService/v3/http/CasinoGameAPI/game/url";
        $game = $request->game;
        $domain = $request->getHost();
        $secure = config('casinos.pragmatic.secure_login');
        $data = [
            "secureLogin" => $secure,
            "symbol" => $game,
            "language" => "pt",
            "currency" => "BRL",
            "platform" => "WEB",
            "technology" => "H5",
            "token" => $request->key . "#" . $request->userId . "#" . now()->timestamp,
            "userId" => $request->key . "#" . $request->userId,
            "stylename" => $secure,
            "cashierUrl" => $domain,
            "lobbyUrl" => $domain,
            "country" => "BR"
        ];

        ksort($data);
        $query = "";
        foreach ($data as $key => $value) {
            $query .= $key . '=' . $value . '&';
        }

        // Remove o último caractere '&' da string
        $query = rtrim($query, '&');

        $keyStaging = config('casinos.pragmatic.key');

        $concatenatedString = $query . $keyStaging;

        $hash = md5($concatenatedString);

        $data["hash"] = $hash;

        $client = new Client();

        $response = $client->request(
            "POST",
            $url,
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $data
            ]
        );

        $response = json_decode($response->getBody());

        return response()->json(["url" => $response->gameURL]);
    }
}
