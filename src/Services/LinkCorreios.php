<?php

namespace Sdkcorreios\Services;

use Sdkcorreios\Config\FormatResponse;
use Sdkcorreios\Config\Status;

class LinkCorreios
{
    private $api_url = "https://www.linkcorreios.com.br/{code}";
    private $service_provider = "www.linkcorreios.com.br";

    private function setStatus(string $string, bool $error = false): string
    {
        return Status::getStatus($error ? "" : $string);
    }

    public function tracking(string $codes): object
    {
        $codes = $this->objectsCodes($codes);
        if (!is_array($codes) || empty($codes)) {
            throw new \Exception("Códigos inválidos ou vazios");
        }

        $result = ["success" => true, "result" => []];

        foreach ($codes as $code) {
            try {
                $result["result"][] = $this->httpGet($code);
            } catch (\Exception $ex) {
                $result["result"][] = ["code" => $code, "error" => $ex->getMessage()];
            }
        }

        return (object) $result;
    }

    public function objectsCodes(string $codes): array
    {
        return array_map('trim', explode(",", $codes));
    }

    public function httpGet(string $code): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => str_replace("{code}", $code, $this->api_url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        if ($error = curl_error($curl)) {
            throw new \Exception('Erro na requisição: ' . $error);
        }
        curl_close($curl);

        if (!$response) {
            throw new \Exception('Resposta vazia da API');
        }

        $html = explode("class=\"singlepost\"", $response);
        if (!isset($html[1])) {
            throw new \Exception('Objeto não encontrado');
        }

        return $this->parseResponse($html[1], $code);
    }

    private function parseResponse(string $htmlContent, string $code): array
    {
        $html = explode("<br>", $htmlContent);
        if (!isset($html[1])) {
            throw new \Exception('Objeto não encontrado');
        }

        $html = explode("<ul class=\"linha_status\"", $html[1]);
        $formatResponse = new FormatResponse();
        $response_obj = $formatResponse->formatTracking;
        $response_obj["code"] = $code;
        $response_obj["service_provider"] = $this->service_provider;

        unset($html[0]);
        $html = array_values($html);
        $lastTitle = '';

        foreach ($html as $value) {
            if (trim($value) != "") {
                $dataInfo = $this->extractData(trim($value));
                if ($dataInfo) {
                    $response_obj["data"][] = $dataInfo;
                    if (!$lastTitle) {
                        $lastTitle = $dataInfo['originalTitle'];
                    }
                }
            }
        }

        $response_obj["status"] = $this->setStatus($lastTitle);
        return $response_obj;
    }

    private function extractData(string $value): ?array
    {
        $value = str_replace([' style="">', '<b>', '</b>'], ['', '', ''], $value);
        $array = explode('<li>', $value);

        $action = trim(str_replace('Status:', '', $array[1] ?? ''));
        $dateInfo = explode('|', $array[2] ?? '');
        $date = trim(str_replace('Data', '', $dateInfo[0] ?? ''));
        $hour = trim(str_replace('Hora:', '', $dateInfo[1] ?? ''));

        $dateFormat = \DateTime::createFromFormat('d/m/Y H:i:s', "$date $hour:00");
        $formattedDate = $dateFormat ? $dateFormat->format('d-m-Y H:i:s') : $date;

        $location = trim(str_replace(['Origem:', 'Local:'], '', $array[3] ?? ''));
        $location = isset(explode('-', $location)[1]) ? trim(explode('-', $location)[1]) : $location;

        return [
            "date" => $formattedDate,
            "to" => '',
            "from" => $location,
            "location" => $location,
            "originalTitle" => $action,
            "details" => trim(str_replace(['</ul>', '</li>'], '', $action)),
        ];
    }
}
