<?php

/*
 *  https://rastreamentocorreio.com
 *
 */

namespace Sdkcorreios\Services;

use Sdkcorreios\Config\FormatResponse;
use Sdkcorreios\Config\Status;

class RastreamentoCorreio
{

    private $api_url = "https://rastreamentocorreio.com/pesquisa?codigo=";

    private $service_provider = "rastreamentocorreio.com";
    
    private function setStatus($string, $error = false)
    {
        return $error ? Status::getStatus("") : Status::getStatus($string);
    }


    public function addTo($array)
    {

        $new_array = array();
        for ($i = 0; $i < count($array); $i++) {
            if ($i < count($array) - 1) {
                $array[$i]['to'] = $array[$i + 1]['locale'];
            } else {
                $array[$i]['to'] = '';
            }
            
            $new_array[] = $array[$i];
        }
        
        return  json_decode(json_encode(array_reverse($new_array)));
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
                    CURLOPT_URL => $this->api_url . $code,
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

            $html = explode('<div class="eventos my-3">', $response);
            $html = explode('<button type="button" class="btn btn-danger botao-acompanhar" data-bs-toggle="modal" data-bs-target="#modalContato">', $html[1]);

            $dom = new \DOMDocument();
            $dom->loadHTML('<!DOCTYPE html><meta charset="UTF-8">' . $html[0]);

            $items =  $this->getElementsByClass($dom, 'div', 'tl-item');

            $tl_items = [];
            foreach ($items as $item) {

                $content = str_replace("\r", "", $this->getElementsByClass($item, 'div', 'tl-content')[0]->nodeValue);
                $content = explode("\n", $content);

                foreach ($content as $key => $c) {
                    if ($c == "" || $c == " ") {
                        unset($content[$key]);
                    }
                }

                $content = array_values($content);

                $tl_items[] = [
                    'title' => iconv(mb_detect_encoding($content[0]), "UTF-8", $content[0]),
                    'locale' => trim($content[1]),
                    'date' => trim($content[2])
                ];
            }

            $decode = $this->addTo($tl_items);
 
            if (count($decode) > 0) {

                $formatResponse = new FormatResponse();
                $response_obj = $formatResponse->formatTracking;

                $response_obj["code"]   = $code;
                $response_obj["status"] = empty($decode[0]->title) ? $this->setStatus("", true) : $this->setStatus($decode[0]->title);
                $response_obj["service_provider"] = $this->service_provider;
                
                foreach ($decode as $key => $mov) {

                    $mov = (object)$mov;

                    $date = $mov->date;
                    $date_format = \DateTime::createFromFormat('d/m/Y H:i:s', $date);
                    $date_format = $date_format ? $date_format->format('d-m-Y H:i:s') : $mov->date;
                  
                    array_push($response_obj["data"], [
                        "date" => $date_format,
                        "to" => isset($mov->to) ? $mov->to : "",
                        "from" => $mov->locale,
                        "location" => $mov->locale,
                        "originalTitle" => $mov->title,
                        "details" => $mov->title
                    ]);

                }

            }

            return $response_obj;

        } catch (\Exception $e) {
            throw new \Exception('' . $e->getMessage());
        }

    }

}
