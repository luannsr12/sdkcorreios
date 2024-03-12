<?php

/*
 *  https://rastreiocorreios.com.br
 *
 */

namespace Sdkcorreios\Services;

use Sdkcorreios\Config\FormatResponse;
use Sdkcorreios\Config\Services;
use Sdkcorreios\Config\Status;

class RastreioCorreios
{

    private $api_url = "https://rastreiocorreios.com.br/resultado/?rastreio={code}";

    private function setStatus($string, $error = false)
    {
        return $error ? Status::getStatus("") : Status::getStatus($string);
    }

    public function tracking($codes)
    {
        try {

            $codes = $this->objectsCodes($codes);
            if (is_array($codes)) {

                if (count($codes) > 0) {

                    $objs["success"] = true;
                    $objs["result"] = [];

                    foreach ($codes as $code) {
                        $execute = $this->httpGet($code);

                        if ($execute) {
                            try {

                                array_push($objs["result"], $execute);

                            } catch (\Throwable $th) {
                                throw new \Exception($th->getMessage());
                            }
                        }
                    }

                    return (object) $objs;

                } else {
                    throw new \Exception("empty codes");
                }

            } else {
                throw new \Exception("Types codes invalid");
            }

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    private function getCity($string){
        $city = "";
        preg_match('/(?: - |:\s)([A-Z\s]+(?:\/[A-Z]+)?)\b/', $string, $matches);
        if (isset($matches[1])) {
            $city = strtoupper($matches[1]);
        }
        return $city;
    }
    private function getElementsByClass(&$parentNode, $tagName, $className)
    {
        $nodes = array();

        $childNodeList = $parentNode->getElementsByTagName($tagName);
        for ($i = 0; $i < $childNodeList->length; $i++) {
            $temp = $childNodeList->item($i);
            if (stripos($temp->getAttribute('class'), $className) !== false) {
                $nodes[] = $temp;
            }
        }

        return $nodes;
    }

    public function objectsCodes($codes)
    {
        $codes = explode(",", $codes);
        return $codes;
    }

    public function httpGet($code) // tracking
    {

        try {

            $curl = curl_init();

            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL => str_replace("{code}", $code, $this->api_url),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_POSTFIELDS => '',
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                    ),
                )
            );

            $response = curl_exec($curl);
            curl_close($curl);

            $html = explode('<div class="historic__content">', $response);
            $html = explode('</ul>', $html[1]);

            $dom = new \DOMDocument();
            $dom->loadHTML('<!DOCTYPE html><meta charset="UTF-8">' . $html[0]);

            $items = $dom->getElementsByTagName('li');
 
            $lastTitle = "";

            foreach ($items as $item) {
                 $conts     = $item->getElementsByTagName('p');
                 $lastTitle = $conts->item(1)->nodeValue;
                 break;
            }
 
            $formatResponse = new FormatResponse();
            $response_obj = $formatResponse->formatTracking;

            $response_obj["code"]   = $code;
            $response_obj["status"] = empty($items) ? $this->setStatus("", true) : $this->setStatus($lastTitle);
            
            
            foreach ($items as $i => $item) {
              
                $conts = $item->getElementsByTagName('p');

                $date = $conts->item(0)->nodeValue;
                $date_format = \DateTime::createFromFormat('d/m/Y H:i:s', $date);
                $date_format = $date_format ? $date_format->format('d-m-Y H:i:s') : $date;

                $locale = $this->getCity($conts->item(2)->nodeValue);
                

                array_push($response_obj["data"], [
                    "date" => $date_format,
                    "to" => '',
                    "from" => $locale,
                    "location" => $locale,
                    "originalTitle" => isset($conts->item(1)->nodeValue) ? $conts->item(1)->nodeValue : "",
                    "details" => isset($conts->item(3)->nodeValue) ? $conts->item(2)->nodeValue : ""
                ]);

            }

            return $response_obj;

        } catch (\Exception $e) {
            throw new \Exception('' . $e->getMessage());
        }

    }

}
