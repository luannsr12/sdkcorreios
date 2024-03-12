<?php

/*
 * https://api.melhorrastreio.com.br/
 *
 */

namespace Sdkcorreios\Services;

use Sdkcorreios\Config\FormatResponse;
use Sdkcorreios\Config\Status;

class MelhorRastreio
{

    private $api_url = "https://api.melhorrastreio.com.br/graphql";

    private $service_provider = "api.melhorrastreio.com.br";

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
                    CURLOPT_URL => $this->api_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => '{
                    "query": "mutation searchParcel ($tracker: TrackerSearchInput!) {\\n  result: searchParcel (tracker: $tracker) {\\n    id\\n    createdAt\\n    updatedAt\\n    lastStatus\\n    lastSyncTracker\\n    pudos {\\n      type\\n      trackingCode\\n    }\\n    trackers {\\n      type\\n      shippingService\\n      trackingCode\\n    }\\n    trackingEvents {\\n      trackerType\\n      trackingCode\\n      createdAt\\n      translatedEventId\\n      originalTitle\\n      to\\n      from\\n      location {\\n        zipcode\\n        address\\n        locality\\n        number\\n        complement\\n        city\\n        state\\n        country\\n      }\\n      additionalInfo\\n    }\\n    pudoEvents {\\n      pudoType\\n      trackingCode\\n      createdAt\\n      translatedEventId\\n      originalTitle\\n      from\\n      to\\n      location {\\n        zipcode\\n        address\\n        locality\\n        number\\n        complement\\n        city\\n        state\\n        country\\n      }\\n      additionalInfo\\n    }\\n  }\\n}",
                    "variables": {
                        "tracker": {
                            "trackingCode": "' . $code . '",
                            "type": "correios"
                        }
                    }
                }',
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

            if(!isset($decode->data->result->trackingEvents)){
                throw new \Exception('Not found');
            }

            $formatResponse = new FormatResponse();
            $response_obj = $formatResponse->formatTracking;

            $response_obj["code"] = $code;
            $response_obj["status"] = empty($decode->data->result->trackingEvents) ? $this->setStatus("", true) : $this->setStatus($decode->data->result->trackingEvents[0]->originalTitle);
            $response_obj["service_provider"] = $this->service_provider;

            foreach ($decode->data->result->trackingEvents as $key => $mov) {

                array_push($response_obj["data"], [
                    "date" => date("m-d-Y H:i:s", strtotime($mov->createdAt)),
                    "to" => $mov->to,
                    "from" => $mov->from,
                    "location" => $mov->location->complement . " - " . $mov->location->city . "/" . $mov->location->state,
                    "originalTitle" => $mov->originalTitle,
                    "details" => $mov->originalTitle
                ]);

            }

            return $response_obj;


        } catch (\Exception $e) {
            throw new \Exception('' . $e->getMessage());
        }

    }

}
