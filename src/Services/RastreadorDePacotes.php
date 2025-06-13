<?php

namespace Sdkcorreios\Services;

use Sdkcorreios\Config\FormatResponse;
use Sdkcorreios\Config\Status;

class RastreadorDePacotes
{
    private $api_url = "https://api.rastreadordepacotes.com.br/rastreio/";
    private $service_provider = "www.rastreadordepacotes.com.br";

    private function setStatus($string, $error = false)
    {
        return Status::getStatus($error ? "" : $string);
    }

    private function getLocale($data, $indice)
    {
        if ($indice < 0 || $indice >= count($data)) return "";
        
     

        for ($i = $indice + 1; $i < count($data); $i++) {
            if ($data[$i]->DetalhesFormatado !== $data[$indice]->DetalhesFormatado) {
                return trim(explode('para', $data[$i]->DetalhesFormatado)[1] ?? "");
            }
        }

        return trim(explode('para', $data[$indice]->DetalhesFormatado)[1] ?? "");
    }

    private function getLocaleTo($data, $indice)
    {
        if ($indice < 0 || $indice >= count($data)) return "";
        
        if (preg_match('/em\s+(.*?)\s+(para\s+(.*?))?$/', $data[$indice]->DetalhesFormatado, $matches)) {
            return $matches[3] ?? $matches[1];
        }

        return "";
    }

    public function tracking($codes)
    {
        $codes = explode(",", $codes);
        if (empty($codes)) throw new \Exception("empty codes");

        $objs = ["success" => true, "result" => []];

        foreach ($codes as $code) {
            $execute = $this->httpGet(trim($code));
            if ($execute) {
                $objs["result"][] = $execute;
            }
        }

        return (object) $objs;
    }

    private function httpGet($code)
    {
        $curl = curl_init($this->api_url . $code);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'User-Agent: Mozilla/5.0',
            ],
        ]);

        $response = curl_exec($curl);
        if (!$response || !json_decode($response)) throw new \Exception('Invalid JSON response');

        $decode = json_decode($response);
        if (!isset($decode->tracking[0])) throw new \Exception('Object not found');

        $decode->Posicoes = array_reverse((array)($decode->tracking[0]->Posicoes ?? []));
        $response_obj["code"] = $code;

        $formatResponse = new FormatResponse();
        $response_obj = $formatResponse->formatTracking;

        foreach ($decode->Posicoes as $key => $mov) {
            $from = $this->getLocale((array)$decode->Posicoes, $key);
            $to = $this->getLocaleTo((array)$decode->Posicoes, $key);
            $response_obj["data"][] = [
                "date" => date("m-d-Y H:i:s", strtotime($mov->Data)),
                "to" => $to,
                "from" => $from,
                "location" => $to,
                "originalTitle" => $mov->Acao,
                "details" => str_replace(["\n\r"], ' - ', $mov->DetalhesFormatado),
            ];
        }

        $response_obj["status"] = empty($decode->Posicoes) ? $this->setStatus("", true) : $this->setStatus($decode->Posicoes[0]->Acao);
        $response_obj["service_provider"] = $this->service_provider;

        return $response_obj;
    }
}
