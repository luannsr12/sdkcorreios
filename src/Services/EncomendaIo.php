<?php

/*
 * https://encomenda.io
 *
 */

namespace Sdkcorreios\Services;

use Sdkcorreios\Config\FormatResponse;
use Sdkcorreios\Config\Services;
use Sdkcorreios\Config\Status;

class EncomendaIo
{

    private $api_url = "https://encomenda.io/api/tracking/";

    private function setStatus($string, $error = false)
    {
        return $error ? Status::getStatus("") : Status::getStatus($string);
    }

    public function getLocale($json, $indice)
    {

        $data = json_decode($json, true);

        if ($indice < 0 || $indice >= count($data['tracking'])) {
            return "Índice inválido";
        }

        if ($indice == count($data['tracking']) - 1) {
            return $data['tracking'][$indice]['locale'];
        }

        for ($i = $indice + 1; $i < count($data['tracking']); $i++) {

            if ($data['tracking'][$i]['locale'] != $data['tracking'][$indice]['locale']) {
                return $data['tracking'][$i]['locale'];
            }
        }

        return $data['tracking'][$indice]['locale'];

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

            if (!json_decode($response)) {
                throw new \Exception('Json invalid response');
            }

            $decode = json_decode($response);

            if (isset($decode->errors)) {
                throw new \Exception($decode->errors[0]->details[0]);
            }


            $formatResponse = new FormatResponse();
            $response_obj = $formatResponse->formatTracking;

            $response_obj["code"] = $code;
            $response_obj["status"] = empty($decode->data->tracking) ? $this->setStatus("", true) : $this->setStatus($decode->data->tracking[0]->status);

            foreach ($decode->data->tracking as $key => $mov) {

                $from = $this->getLocale(json_encode($decode->data), $key);

                array_push($response_obj["data"], [
                    "date" => date("m-d-Y H:i:s", strtotime($mov->date)),
                    "to" => $mov->locale,
                    "from" => $from,
                    "location" => $mov->locale,
                    "originalTitle" => $mov->status,
                    "details" => $mov->status
                ]);

            }

            return $response_obj;


        } catch (\Exception $e) {
            throw new \Exception('' . $e->getMessage());
        }

    }

}
