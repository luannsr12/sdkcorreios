<?php

namespace Sdkcorreios\Services;

use Sdkcorreios\Config\FormatResponse;
use Sdkcorreios\Config\Status;

class RastreamentoCorreio
{
    private $api_url = "https://rastreamentocorreio.com/pesquisa?codigo=";
    private $service_provider = "rastreamentocorreio.com";

    private function setStatus($string, $error = false)
    {
        return Status::getStatus($error ? "" : $string);
    }

    public function addTo($array)
    {
        if (!is_array($array)) {
            throw new \Exception("Input should be an array.");
        }

        foreach ($array as $i => $item) {
            $array[$i]['to'] = isset($array[$i + 1]) ? $array[$i + 1]['locale'] : '';
        }

        return json_decode(json_encode(array_reverse($array)));
    }

    public function tracking($codes)
    {
        $codes = $this->objectsCodes($codes);

        if (empty($codes)) {
            throw new \Exception("Invalid or empty codes provided.");
        }

        $results = ["success" => true, "result" => []];

        foreach ($codes as $code) {
            try {
                $results["result"][] = $this->httpGet($code);
            } catch (\Exception $e) {
                throw new \Exception("Error tracking code $code: " . $e->getMessage());
            }
        }

        return (object)$results;
    }

    private function getElementsByClass(&$parentNode, $tagName, $className)
    {
        return array_filter(iterator_to_array($parentNode->getElementsByTagName($tagName)), function($node) use ($className) {
            return stripos($node->getAttribute('class'), $className) !== false;
        });
    }

    public function objectsCodes($codes)
    {
        return explode(",", $codes);
    }

    public function httpGet($code)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->api_url . $code,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        if ($response === false) {
            throw new \Exception('Curl error: ' . curl_error($curl));
        }
        curl_close($curl);

        $html = explode('<div class="eventos my-3">', $response);
        if (!isset($html[1])) {
            throw new \Exception('No events found in the response.');
        }

        $dom = new \DOMDocument();
        @$dom->loadHTML('<!DOCTYPE html><meta charset="UTF-8">' . explode('<button type="button" class="btn btn-danger botao-acompanhar"', $html[1])[0]);
        $items = $this->getElementsByClass($dom, 'div', 'tl-item');

        if (empty($items)) {
            throw new \Exception('No tracking items found.');
        }

        $tl_items = [];
        foreach ($items as $item) {
            
            $content = explode("\n", str_replace("\r", "", $this->getElementsByClass($item, 'div', 'tl-content')[1]->nodeValue));
            $content = array_values(array_filter(array_map('trim', ($content))));

            if (count($content) < 3) {
                throw new \Exception('Insufficient content found for tracking item.');
            }

            $date = isset($content[3]) ? $content[3] : $content[2];
            
            $tl_items[] = [
                'title' => iconv(mb_detect_encoding($content[0]), "UTF-8", $content[0]),
                'locale' => trim($content[1]),
                'date' => trim($date),
            ];
        }

        return $this->formatResponse($tl_items, $code);
    }

    private function formatResponse($tl_items, $code)
    {
        $decode = $this->addTo($tl_items);
        $formatResponse = new FormatResponse();
        $response_obj = $formatResponse->formatTracking;
        $response_obj["code"] = $code;
        $response_obj["status"] = empty($decode[0]->title) ? $this->setStatus("", true) : $this->setStatus($decode[0]->title);
        $response_obj["service_provider"] = $this->service_provider;

    
        foreach ($decode as $mov) {
        
            $date_format = \DateTime::createFromFormat('d/m/Y H:i', trim($mov->date))->format('d-m-Y H:i:s');
            $response_obj["data"][] = [
                "date" => $date_format,
                "to" => $mov->to ?? "",
                "from" => $mov->locale,
                "location" => $mov->locale,
                "originalTitle" => $mov->title,
                "details" => $mov->title,
            ];
        }

        return $response_obj;
    }
}
