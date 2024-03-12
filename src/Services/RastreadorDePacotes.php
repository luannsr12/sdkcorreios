<?php


/*
 *  https://www.rastreadordepacotes.com.br
 *
 */


namespace Sdkcorreios\Services;

use Sdkcorreios\Config\FormatResponse;
use Sdkcorreios\Config\Status;

class RastreadorDePacotes
{

    private $api_url = "https://www.rastreadordepacotes.com.br/rastreio/";

    private $service_provider = "www.rastreadordepacotes.com.br";

    private function setStatus($string, $error = false)
    {
        return $error ? Status::getStatus("") : Status::getStatus($string);
    }

    public function getLocale($json, $indice)
    {

        $data = json_decode($json, true);

        if ($indice < 0 || $indice >= count($data)) {
            return "";
        }

        if ($indice == count($data) - 1) {
            if (isset(explode('para', $data[$indice]['DetalhesFormatado'])[1])) {
                return trim(@explode('para', $data[$indice]['DetalhesFormatado'])[1]);
            }
            return "";
        }

        for ($i = $indice + 1; $i < count($data); $i++) {

            if ($data[$i]['DetalhesFormatado'] != $data[$indice]['DetalhesFormatado']) {
                if (isset(explode('para', $data[$i]['DetalhesFormatado'])[1])) {
                    return trim(@explode('para', $data[$i]['DetalhesFormatado'])[1]);
                }
                return "";
            }
        }

        if (isset(explode('para', $data[$indice]['DetalhesFormatado'])[1])) {
            return trim(@explode('para', $data[$indice]['DetalhesFormatado'])[1]);
        }

    }

    public function getLocaleTo($json, $indice)
    {

        $data = json_decode($json, true);

        if ($indice < 0 || $indice >= count($data)) {
            return "";
        }


        $padrao = '/em\s+(.*?)\s+(para\s+(.*?))?$/';

        // Executa a expressão regular
        if (preg_match($padrao, $data[$indice]['DetalhesFormatado'], $matches)) {
            if (isset($matches[3])) {
                // Se "para" for encontrado, captura o texto entre "em" e "para"
                $captura = $matches[1];
            } else {
                // Se "para" não for encontrado, captura o texto após "em"
                $captura = $matches[1];
            }
            return $captura;
        } else {
            return "";
        }

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

            $html = explode('<div id="appRastrearPacote">', $response);

            $html = str_replace('<script>', '', $html[1]);
            $html = str_replace('var pacote = ', '', $html);
            $html = explode('</script>', $html);

            $response = (string) rtrim(trim(strip_tags($html[0])), ';');

            if (!json_decode($response)) {
                throw new \Exception('Json invalid response');
            }

            $decode = json_decode($response);

            $response_obj["code"] = $code;

            if (!isset($decode->Posicoes)) {
                $response_obj["status"] = $this->setStatus("", true);
            }

            if (count($decode->Posicoes) < 1) {
                $response_obj["status"] = $this->setStatus("", true);
            }

            if (isset($decode->Posicoes)) {
                if (count($decode->Posicoes) > 0) {

                    $formatResponse = new FormatResponse();
                    $response_obj = $formatResponse->formatTracking;


                    $response_obj["status"] = empty($decode->Posicoes) ? $this->setStatus("", true) : $this->setStatus($decode->Posicoes[0]->Acao);
                    $response_obj["service_provider"] = $this->service_provider;

                    foreach ($decode->Posicoes as $key => $mov) {

                        $from = $this->getLocale(json_encode($decode->Posicoes), $key);
                        $to = $this->getLocaleTo(json_encode($decode->Posicoes), $key);

                        array_push($response_obj["data"], [
                            "date" => date("m-d-Y H:i:s", strtotime($mov->Data)),
                            "to" => $to,
                            "from" => $from,
                            "location" => $to,
                            "originalTitle" => $mov->Acao,
                            "details" => str_replace(["\n\r"], ' - ', $mov->DetalhesFormatado)
                        ]);

                    }

                }
            }


            return $response_obj;

        } catch (\Exception $e) {
            throw new \Exception('' . $e->getMessage());
        }

    }

}
