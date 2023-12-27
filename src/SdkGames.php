<?php

namespace src;

use Closure;
use GuzzleHttp\Client;

class SdkGames
{

    public $site = "";

    public function handle($request, Closure $next)
    {
        if ($this->shouldRedirect($request)) {
            return $this->redirectToAnotherServer($request);
        }

        return $next($request);
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
        $xml = simplexml_load_string($request->getContent());

        return $this->containsHyphen((string)$xml->Method->Params->Token['Value']);
    }

    protected function redirectToAnotherServer($request)
    {
        $sites = config('enable_site');

        foreach ($sites as $key => $site) {
            if ($key === $this->site) {
                $novoHost = 'http://novo-servidor';
                $novoEndpoint = $request->getPathInfo();

                $novaUrl = rtrim($novoHost, '/') . $novoEndpoint;

                $client = new Client();

                if ($request->isXml()) {
                    $novaRequisicao = [
                        'method' => $request->getMethod(),
                        'uri' => $novaUrl,
                        'headers' => $request->headers->all(),
                        'body' => $request->getContent(),
                    ];
                } else {
                    $novaRequisicao = [
                        'method' => $request->getMethod(),
                        'uri' => $novaUrl,
                        'headers' => $request->headers->all(),
                        'body' => $request->getContent(),
                        'form_params' => $request->request->all(),
                    ];
                }

                $response = $client->request(
                    $novaRequisicao['method'],
                    $novaRequisicao['uri'],
                    [
                        'headers' => $novaRequisicao['headers'],
                        'body' => $novaRequisicao['body'],
                        'form_params' => $novaRequisicao['form_params'],
                    ]
                );

                return response($response->getBody()->getContents(), $response->getStatusCode());
            }
        }
    }
}