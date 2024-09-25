<?php

namespace Sdkcorreios\Services;

use Sdkcorreios\Config\FormatResponse;
use Sdkcorreios\Config\Status;

class RastreioCorreios
{
    private $api_url = "https://rastreiocorreios.com.br/resultado/?rastreio={code}";
    private $service_provider = "rastreiocorreios.com.br";

    private function setStatus($string, $error = false)
    {
        return $error ? Status::getStatus("") : Status::getStatus($string);
    }

    public function tracking($codes)
    {
        $codes = $this->objectsCodes($codes);
        if (!is_array($codes) || empty($codes)) {
            throw new \InvalidArgumentException("Invalid or empty codes");
        }

        $results = ["success" => true, "result" => []];

        foreach ($codes as $code) {
            try {
                $results["result"][] = $this->httpGet($code);
            } catch (\Exception $e) {
                throw new \Exception("Error processing code {$code}: " . $e->getMessage());
            }
        }

        return (object)$results;
    }

    private function getCity($string)
    {
        preg_match('/(?: - |:\s)([A-Z\s]+(?:\/[A-Z]+)?)\b/', $string, $matches);
        return isset($matches[1]) ? strtoupper(trim($matches[1])) : '';
    }

    public function objectsCodes($codes)
    {
        return explode(",", $codes);
    }

    public function httpGet($code)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => str_replace("{code}", $code, $this->api_url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        if ($response === false) {
            throw new \Exception('cURL error: ' . curl_error($curl));
        }
        curl_close($curl);

        $html = explode('<div class="historic__content">', $response);
        if (!isset($html[1])) {
            throw new \Exception('Object not found');
        }

        $dom = new \DOMDocument();
        @$dom->loadHTML('<!DOCTYPE html><meta charset="UTF-8">' . explode('</ul>', $html[1])[0]);
        $items = $dom->getElementsByTagName('li');

        if ($items->length === 0) {
            throw new \Exception('No tracking items found');
        }

        $lastTitle = $items->item(0)->getElementsByTagName('p')->item(1)->nodeValue ?? '';

        $response_obj = (new FormatResponse())->formatTracking;
        $response_obj["code"] = $code;
        $response_obj["status"] = empty($lastTitle) ? $this->setStatus("", true) : $this->setStatus($lastTitle);
        $response_obj["service_provider"] = $this->service_provider;

        foreach ($items as $item) {
            $conts = $item->getElementsByTagName('p');
            if ($conts->length < 4) {
                throw new \Exception('Insufficient data in tracking item');
            }

            $date_format = \DateTime::createFromFormat('d/m/Y H:i:s', $conts->item(0)->nodeValue)->format('d-m-Y H:i:s');
            $locale = $this->getCity($conts->item(2)->nodeValue);

            $response_obj["data"][] = [
                "date" => $date_format,
                "to" => '',
                "from" => $locale,
                "location" => $locale,
                "originalTitle" => $conts->item(1)->nodeValue ?? "",
                "details" => $conts->item(3)->nodeValue ?? "",
            ];
        }

        return $response_obj;
    }
}
