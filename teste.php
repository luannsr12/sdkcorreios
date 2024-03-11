<?php  

        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://www.rastreadordepacotes.com.br/rastreio/QQ781772845BR',
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
        
        $html =  explode('<div id="appRastrearPacote">', $response  );

        $html = str_replace('<script>','', $html[1]);
        $html = str_replace('var pacote = ','', $html);
        $html = explode('</script>', $html);

         var_dump(json_decode((string)rtrim(trim($html[0]), ';')));
         die;

        $dom = new DOMDocument();
        // Carregue o HTML
        $dom->loadHTML($html);

        // Localize o elemento com o ID 'appRastrearPacote'
        $element = $dom->getElementById('appRastrearPacote');

        if ($element) {
            // Obtenha o conteúdo do script dentro do elemento encontrado
            $scripts = $element->getElementsByTagName('script');

            var_dump($scripts);
            die;

            foreach ($scripts as $script) {
                // Verifique se há uma variável chamada 'pacote'
                if (strpos($script->nodeValue, 'var pacote') !== false) {
                    // Use expressões regulares para extrair o valor da variável 'pacote'
                    preg_match('/var pacote = "(.*?)";/', $script->nodeValue, $matches);
                    if (isset($matches[1])) {
                        $conteudo_pacote = $matches[1];
                        // Exibe o conteúdo da variável 'pacote'
                        echo $conteudo_pacote;
                    }
                }
            }
        } else {
            echo "Elemento não encontrado";
        }