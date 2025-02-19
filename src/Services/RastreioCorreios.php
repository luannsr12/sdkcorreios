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

            $content = explode("\n", str_replace("\r", "", $item->nodeValue));
            $content = array_values(array_filter(array_map('trim', ($content))));
            
            if (count($content) < 3) {
                throw new \Exception('Insufficient content found for tracking item.');
            }

            preg_match('/\d{2}\/\d{2}\/\d{4}/', $content[0], $matches);
            preg_match('/\d{2}:\d{2}/', $content[0], $matches_hour);
            
            if (isset($matches[0]) && isset($matches_hour[0])) {
            
                $date = $matches[0] . ' ' . $matches_hour[0];
                
            } else {
                $date = date('d/m/Y H:i');
            }

         
            $date_format = \DateTime::createFromFormat('d/m/Y H:i', $date)->format('d-m-Y H:i');
            $locale = $this->getCity($content[2]);

            $response_obj["data"][] = [
                "date" => $date_format,
                "to" => '',
                "from" => $locale,
                "location" => $locale,
                "originalTitle" => $content[1] ?? "",
                "details" => $content[3] ?? "",
            ];
        }

        return $response_obj;
    }
}
