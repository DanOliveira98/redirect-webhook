<?php

namespace GamesPackage\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Evoplay
{

    public $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function play(Request $request)
    {
        $userId = $request->key . "#" . $request->userId;
        $params = array();
        $key = config('casinos.evoplay.key');
        $params['callback_version'] = 2;
        $params['return_url_info'] = 1;
        $params['currency'] = 'BRL';
        $params['denomination'] = 1;
        $params["settings"] = [
            "https" => 1,
            "user_id" => $request->userId,
            "language" => 'en',
            'exit_url' => "https://{$request->key}"
        ];
        $projectId = config('casinos.evoplay.project_id');
        $version = 1;
        $params['game'] = $request->game;
        $params['token'] = $userId;
        $signature = $this->getSignature($projectId, $version, array_reverse($params), $key);
        $params["signature"] = $signature;
        $params["version"] = 1;
        $params["project"] = $projectId;
        $urlEvo = "https://api.evoplay.games/Game/getURL?";
        $response = $this->client->request(
            "GET",
            $urlEvo . http_build_query(array_reverse($params))
        );
        $data = json_decode($response->getBody());

        if ($data->status === 'ok') {
            return ["url" => $data->data->link];
        }

        Log::debug("Erro a abrir jogo: {$request->game}", [
            "game" => $request->game,
            "response" => $data
        ]);

        return [
            "error" => "Não foi possivel abrir o jogo.",
            "url" => null
        ];
    }

    public function getSignature($system_id, $version, array $args, $system_key)
    {
        $md5 = array();
        $md5[] = $system_id;
        $md5[] = $version;
        foreach ($args as $required_arg) {
            $arg = $required_arg;
            if (is_array($arg)) {
                if (count($arg)) {
                    $recursive_arg = '';
                    array_walk_recursive($arg, function ($item) use (& $recursive_arg) {
                        if (!is_array($item)) {
                            $recursive_arg .= ($item . ':');
                        }
                    });
                    $md5[] = substr($recursive_arg, 0, strlen($recursive_arg) - 1);
                } else {
                    $md5[] = '';
                }
            } else {
                $md5[] = $arg;
            }
        };
        $md5[] = $system_key;
        $md5_str = implode('*', $md5);
        return md5($md5_str);
    }
}