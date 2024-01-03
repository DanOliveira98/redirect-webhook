<?php

namespace GamesPackage;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;


class SdkGames
{

    public $site = "";

    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if (
            $request->skip !== true
            && $this->shouldRedirect($request)
        ) {
            return $this->redirectToAnotherServer($request);
        }

        return $response;
    }


    protected function shouldRedirect($request)
    {
        $camposParaVerificar = $this->fields();
        foreach ($camposParaVerificar as $campo) {
            if (
                $campo === "salsa"
                && $this->checkSalsa($request)
            ) {
                return true;
            }
            $valorCampo = $request->input($campo);

            if ($this->containsHyphen($valorCampo)) {
                return true;
            }
        }

        return false;
    }

    protected function containsHyphen($value)
    {
        if (strpos($value, '#') !== false) {
            $this->site = explode("#", $value)[0];

            return true;
        }

        return false;
    }

    protected function fields()
    {
        return [
            "token",
            "operator_player_session",
            "userId",
            "salsa"
        ];
    }

    public function checkSalsa($request)
    {
        $contentType = $request->header('Content-Type');

        if (!str_contains($contentType, 'xml')) {
            return false;
        }

        $xml = simplexml_load_string($request->getContent());

        return $this->containsHyphen((string)$xml->Method->Params->Token['Value']);
    }

    protected function redirectToAnotherServer($request)
    {
        try {
            $novoHost = $this->site;
            $novoEndpoint = $request->getPathInfo();

            $novaUrl = "https://" . rtrim($novoHost, '/') . $novoEndpoint;

            $client = new Client();

            $novaRequisicao = [
                'method' => $request->getMethod(),
                'uri' => $novaUrl,
                'headers' => $request->headers->all(),
                'body' => $request->getContent(),
                'form_params' => $request->request->all(),
            ];

            $response = $client->request(
                $novaRequisicao['method'],
                $novaUrl,
                [
                    'headers' => $novaRequisicao['headers'],
                    'body' => $novaRequisicao['body']
                ]
            );
            return response()->json($response->getBody());
        } catch (\Throwable $exception) {
            Log::debug($exception->getMessage());
            return response()->json(["message" => $exception->getMessage()], 400);
        }
    }
}
