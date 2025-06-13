<?php

namespace Sdkcorreios\Services;

use Sdkcorreios\Config\FormatResponse;
use Sdkcorreios\Config\Status;

class Muambator
{
    private $api_url = "https://www.muambator.com.br/pacotes/{code}/detalhes/";
    private $service_provider = "www.muambator.com.br";

    private function setStatus($string, $error = false)
    {
        return $error ? Status::getStatus("Error") : Status::getStatus($string);
    }

    public function tracking($codes)
    {
        $codes = $this->objectsCodes($codes);
        if (!is_array($codes) || count($codes) === 0) {
            throw new \Exception("No valid tracking codes provided.");
        }

        $objs = ["success" => true, "result" => []];

        foreach ($codes as $code) {
            $execute = $this->httpGet($code);
            if ($execute) {
                $objs["result"][] = $execute;
            }
        }

        return (object) $objs;
    }

    private function getCity($string)
    {
        return preg_match('/(?:\s-\s|\spara\s)([A-Z\s]+\/[A-Z]+)\b/', $string, $matches) ? strtoupper($matches[1]) : '';
    }

    public function objectsCodes($codes)
    {
        if (!is_string($codes) || empty(trim($codes))) {
            throw new \Exception("Invalid codes format.");
        }
        return explode(",", $codes);
    }

    public function httpGet($code)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => str_replace("{code}", $code, $this->api_url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        if ($response === false) {
            throw new \Exception('cURL Error: ' . curl_error($curl));
        }
        curl_close($curl);

        return $this->extractTrackingData($this->parseHtml($response), $code);
    }

    private function parseHtml($response)
    {
        $htmlParts = explode('<div role="tabpanel" class="tab-pane fade in active" id="historico">', $response);
        if (!isset($htmlParts[1])) {
            throw new \Exception('Object not found in response');
        }
        $htmlParts = explode('</ul>', $htmlParts[1]);

        $dom = new \DOMDocument();
        @$dom->loadHTML('<!DOCTYPE html><meta charset="UTF-8">' . $htmlParts[0]);

        return $dom->getElementsByTagName('li');
    }

    private function extractTrackingData($items, $code)
    {
        $lastTitle = [];
        $formatResponse = new FormatResponse();
        $response_obj = $formatResponse->formatTracking;

        $response_obj["code"] = $code;
        $response_obj["service_provider"] = $this->service_provider;

        $setTitled = false;

        foreach ($items as $item) {
            $content = array_values(array_filter(explode("\n", $item->nodeValue)));
            if (!isset($content[1]))
                continue;
           
            
            $lastTitle_string = trim(explode("-", $content[1])[0]);
            array_push($lastTitle, $lastTitle_string);

            $to = $this->getCity($content[1]);
            $locale = $this->getCity($content[2]);
            $date = \DateTime::createFromFormat('d/m/Y H:i', $content[0] ?? '')?->format('d-m-Y H:i') ?: '';

            $response_obj["data"][] = [
                "date" => $date,
                "to" => $to,
                "from" => $locale,
                "location" => $locale,
                "originalTitle" => $lastTitle_string,
                "details" => $content[2],
            ];
        }
        
        $response_obj["status"] = empty($items) ? $this->setStatus("", true) : $this->setStatus($lastTitle[0]);
        return $response_obj;
    }
}
