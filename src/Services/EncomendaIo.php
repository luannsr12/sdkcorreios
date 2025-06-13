<?php

namespace Sdkcorreios\Services;

use Sdkcorreios\Config\FormatResponse;
use Sdkcorreios\Config\Status;

class EncomendaIo
{
    private $api_url = "https://encomenda.io/api/tracking/";
    private $service_provider = "encomenda.io";

    private function setStatus(string $string, bool $error = false): string
    {
        return Status::getStatus($error ? "" : $string);
    }

    public function getLocale(string $json, int $indice): string
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE)
        {
            throw new \Exception('Erro ao decodificar JSON: ' . json_last_error_msg());
        }

        if (!isset($data['tracking']) || !is_array($data['tracking']))
        {
            throw new \Exception('Estrutura de dados de rastreamento inválida');
        }

        if ($indice < 0 || $indice >= count($data['tracking']))
        {
            throw new \Exception("Índice inválido");
        }

        for ($i = $indice + 1; $i < count($data['tracking']); $i++)
        {
            if ($data['tracking'][$i]['locale'] !== $data['tracking'][$indice]['locale'])
            {
                return $data['tracking'][$i]['locale'];
            }
        }

        return $data['tracking'][$indice]['locale'];
    }

    public function tracking(string $codes): object
    {
        $codes = $this->objectsCodes($codes);
        if (empty($codes))
        {
            throw new \Exception("Tipos de códigos inválidos ou vazio");
        }

        $objs = ["success" => true, "result" => []];

        foreach ($codes as $code)
        {
            try
            {
                $execute = $this->httpGet($code);
                $objs["result"][] = $execute;
            }
            catch (\Exception $th)
            {
                $objs["result"][] = ["code" => $code, "error" => $th->getMessage()];
            }
        }

        return (object) $objs;
    }

    public function objectsCodes(string $codes): array
    {
        return array_map('trim', explode(",", $codes));
    }

    public function httpGet(string $code): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->api_url . $code,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        curl_close($curl);

        echo $response;
        die;

        if ($curl_error)
        {
            throw new \Exception('Erro na requisição CURL: ' . $curl_error);
        }

        $decode = json_decode($response);
        if (json_last_error() !== JSON_ERROR_NONE)
        {
            throw new \Exception('Erro ao decodificar JSON: ' . json_last_error_msg());
        }

        if (isset($decode->errors))
        {
            throw new \Exception($decode->errors[0]->details[0]);
        }

        $formatResponse = new FormatResponse();
        $response_obj = $formatResponse->formatTracking;

        $response_obj["code"] = $code;
        $response_obj["status"] = empty($decode->data->tracking) ? $this->setStatus("", true) : $this->setStatus($decode->data->tracking[0]->status);
        $response_obj["service_provider"] = $this->service_provider;

        foreach ($decode->data->tracking as $key => $mov)
        {
            $from = $this->getLocale(json_encode($decode->data), $key);
            $response_obj["data"][] = [
                "date" => date("m-d-Y H:i:s", strtotime($mov->date)),
                "to" => $mov->locale,
                "from" => $from,
                "location" => $mov->locale,
                "originalTitle" => $mov->status,
                "details" => $mov->status,
            ];
        }

        return $response_obj;
    }
}
