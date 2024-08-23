<?php

namespace GamesPackage;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;


class SdkGames
{

    public $site = "";
    public $salsa = false;

    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $host = $request->getHost();
        if (
            $request->skip !== true
            && $this->shouldRedirect($request)
        ) {
            if ($host === $this->site) {
                return $response;
            }
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
                $this->salsa = true;
                return true;
            }
            $valorCampo = $request->input($campo);

            if ($this->containsHyphen($valorCampo)) {
                return true;
            }
        }

        return false;
    }


    protected function containsHyphen($value, $cod = '#')
    {
        if ($value === null) {
            return false;
        }

        $value = urldecode($value);
        if (strpos($value, $cod) !== false) {
            $this->site = explode($cod, $value)[0];
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

        if ($contentType !== 'text/xml') {
            return false;
        }
        $xmlstring = $request->getContent();

        $xml = simplexml_load_string($xmlstring, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json, true);
        $params = $array['Method']['Params'];

        return $this->containsHyphen($params['Token']['@attributes']['Value'], '-');
    }


    protected function redirectToAnotherServer($request)
    {
        try {
            $novoHost = $this->site;
            $novoEndpoint = $request->getPathInfo();
            if ($this->salsa) {
                $novoEndpoint = "/api/salsa";
            }
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
                    'headers' => [
                        "content-type" => $this->salsa ? "application/xml" : "application/json"
                    ],
                    'body' => $this->salsa ? $request->getContent() : json_encode($request->all())

                ]
            );

            if ($this->salsa) {
                return response((string)$response->getBody(), 200)->header('Content-Type', 'application/xml');
            }
            return response()->json(json_decode($response->getBody(), true));
        } catch (\Throwable $exception) {
            Log::debug($exception->getMessage());
            return response()->json(["message" => $exception->getMessage()], 400);
        }
    }
}
