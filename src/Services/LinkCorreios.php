<?php

/*
 *  https://www.linkcorreios.com.br
 *
 */

namespace Sdkcorreios\Services;

use Sdkcorreios\Config\FormatResponse;
use Sdkcorreios\Config\Status;

class LinkCorreios
{

    private $api_url = "https://www.linkcorreios.com.br/{code}";

    private $service_provider = "www.linkcorreios.com.br";

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

            $html = explode("class=\"singlepost\"", $response);
            $notfound = false;

            if(!isset($html[1])){
                $notfound = true;
              }

            $html = explode("<br>", $html[1]);
            $html = explode("<ul class=\"linha_status\"", $html[1]);
 
            $formatResponse = new FormatResponse();
            $response_obj = $formatResponse->formatTracking;

            $response_obj["code"]   = $code;
            $response_obj["service_provider"] = $this->service_provider;
            
            $y = 0;
            $lastTitle = "";
            unset($html[0]);
            $html = array_values($html);
   
            foreach ($html as $key => $value) {
                if(trim($value) != ""){

                    $html  = str_replace(' style="">','<ul>', $value);
                    $html  = str_replace('<b>', '', str_replace('</b>', '', $html));
                    $array = explode('<li>', $html);
                
                    $action   = str_replace('</ul>','', str_replace('</li>','', explode('-',trim(str_replace('Status:','',$array[1])))[0] ));
                    $date     = str_replace('</ul>','', str_replace('</li>','', trim(str_replace(':','',str_replace('Data','',explode('|',$array[2])[0]))) ));
                    $hour     = str_replace('</ul>','', str_replace('</li>','', trim(str_replace('Hora:','',explode('|',$array[2])[1])) ));

                    $date_format = \DateTime::createFromFormat('d/m/Y H:i:s', $date . $hour . ':00');
                    $date_format = $date_format ? $date_format->format('d-m-Y H:i:s') : $date;

                    $message = "";

                    if(!isset($array[1])){
                        $message = $action;
                      }else{
                        $message  = str_replace('</ul>','', str_replace('</li>','', @explode('-',trim(str_replace('Status:','',$array[1])))[1] ));
                      }

                      $location = str_replace('</ul>','', str_replace('</li>','', trim(str_replace('Origem:','', str_replace('Local:','',$array[3]))) ));
                      $location = isset(explode('-', $location)[1]) ? trim(explode('-', $location)[1]) : $location;

                      array_push($response_obj["data"], [
                        "date" => $date_format,
                        "to" => '',
                        "from" => $location,
                        "location" => $location,
                        "originalTitle" => $action,
                        "details" => $message
                    ]);

                    if($key == 0){
                        $lastTitle = $action;
                    }
                }

            }

            $response_obj["status"] = $notfound ? $this->setStatus("", true) : $this->setStatus($lastTitle);

            return $response_obj;

        } catch (\Exception $e) {
            throw new \Exception('' . $e->getMessage());
        }

    }

}
