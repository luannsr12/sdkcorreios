<?php

namespace Sdkcorreios\Services;

use Sdkcorreios\Config\FormatResponse;
use Sdkcorreios\Config\Status;

class MelhorRastreio
{
    private $api_url = "https://api.melhorrastreio.com.br/graphql";
    private $service_provider = "api.melhorrastreio.com.br";

    private function setStatus($string, $error = false)
    {
        return Status::getStatus($error ? "" : $string);
    }

    public function tracking($codes)
    {
        $codes = $this->objectsCodes($codes);
        if (!is_array($codes) || empty($codes)) {
            throw new \Exception(empty($codes) ? "empty codes" : "Types codes invalid");
        }

        $objs = ["success" => true, "result" => []];

        foreach ($codes as $code) {
            try {
                $objs["result"][] = $this->httpGet($code);
            } catch (\Throwable $th) {
                $objs["result"][] = ["code" => $code, "error" => $th->getMessage()];
            }
        }

        return (object) $objs;
    }

    public function objectsCodes($codes)
    {
        return explode(",", $codes);
    }

    public function httpGet($code)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                "query" => 'mutation searchParcel ($tracker: TrackerSearchInput!) {
                    result: searchParcel (tracker: $tracker) {
                        id
                        createdAt
                        updatedAt
                        lastStatus
                        trackingEvents {
                            trackerType
                            trackingCode
                            createdAt
                            title
                            description
                            location {
                                complement
                                city
                                state
                            }
                        }
                    }
                }',
                "variables" => [
                    "tracker" => [
                        "trackingCode" => $code,
                        "type" => "correios"
                    ]
                ]
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $decode = json_decode($response);
        if (!$decode || isset($decode->errors)) {
            throw new \Exception('Invalid response');
        }

        $formatResponse = new FormatResponse();
        $response_obj = $formatResponse->formatTracking;
        $response_obj["code"] = $code;
        $response_obj["service_provider"] = $this->service_provider;

        foreach ($decode->data->result->trackingEvents as $mov) {
            $location = "{$mov->location->complement} - {$mov->location->city}/{$mov->location->state}";
            $response_obj["data"][] = [
                "date" => date("m-d-Y H:i:s", strtotime($mov->createdAt)),
                "to" => $mov->to ?? '',
                "from" => $mov->from ?? '',
                "location" => $location,
                "originalTitle" => $mov->title ?? $mov->description,
                "details" => $mov->description
            ];
        }

        usort($response_obj["data"], fn($a, $b) => \DateTime::createFromFormat('m-d-Y H:i:s', $b['date']) <=> \DateTime::createFromFormat('m-d-Y H:i:s', $a['date']));

        $response_obj["status"] = empty($response_obj["data"]) ? $this->setStatus("", true) : $this->setStatus($response_obj["data"][0]['originalTitle']);

        return $response_obj;
    }
}
