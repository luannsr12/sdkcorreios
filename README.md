# SDK Correios Tracking 1.0.6

[![Latest Stable Version](http://poser.pugx.org/luannsr12/sdkcorreios/v/stable)](https://packagist.org/packages/luannsr12/sdkcorreios)
[![](https://badges.pufler.dev/visits/luannsr12/sdkcorreios)](https://github.com/luannsr12/sdkcorreios)
[![PHP Version Require](http://poser.pugx.org/luannsr12/sdkcorreios/require/php)](https://packagist.org/packages/luannsr12/sdkcorreios)
[![Downloads](http://poser.pugx.org/luannsr12/sdkcorreios/downloads)](https://packagist.org/packages/luannsr12/sdkcorreios)
 [![License](https://poser.pugx.org/luannsr12/sdkcorreios/license)](https://packagist.org/packages/luannsr12/sdkcorreios)

Uma SDK em PHP para rastrear encomendas dos correios gratuitamente.

## 💡 Requirements
> The SDK Supports PHP version 8.2 or higher.

<br/>

## 🖥️ Sites Disponiveis para busca
> Caso algum dos sites listados abaixo deseja a remoção do mesmo da biblioteca, favor entrar em contato: luanalvesnsr@gmail.com

| Status  | Site                                                                   | ID                   |
| :---:   | ---------------------------------------------------------------------- | ---------------------|
|   ✅   | [encomenda.io](https://encomenda.io/OBJETO)                             | EncomendaIo         |
|   ✅   | [melhorrastreio.com.br](https://melhorrastreio.com.br/)                 | MelhorRastreio      |
|   ✅   | [rastreadordepacotes.com.br](https://www.rastreadordepacotes.com.br/)   | RastreadorDePacotes |
|   ✅   | [rastreamentocorreio.com](https://rastreamentocorreio.com/)             | RastreamentoCorreio |
|   ✅   | [muambator.com.br](https://www.muambator.com.br/)                       | Muambator           |
|   ✅   | [rastreiocorreios.com.br](https://rastreiocorreios.com.br/)             | RastreioCorreios    |
|   ✅   | [linkcorreios.com.br](https://www.linkcorreios.com.br/)                 | LinkCorreios        |

## Install Composer
> Fala download do composer aqui: [Download composer](https://getcomposer.org/download/)

linha de comando
```bash
 composer require luannsr12/sdkcorreios
```


## Usando

```php
 <?php 

   require 'vendor/autoload.php';

   use Sdkcorreios\Config\Services;
   use Sdkcorreios\Methods\Tracking;

   // RastreadorDePacotes / EncomendaIo / MelhorRastreio / etc... 
   // Confira na tabela os IDs das classes
   Services::setServiceTracking("MelhorRastreio"); // ID do site de busca
   Services::setDebug(true);

   $tracking = new Tracking();
   $tracking->setCode("OBJETO1");
   $tracking->setCode("OBJETO2");

   // OR
   // $tracking->setCode("OBJETO1,OBJETO2");

   echo json_encode($tracking->get());



```

Response

```json
 {
  "success": true,
  "result": [
    {
      "code": "QQ781772845BR",
      "status": "DELIVERED",
      "service_provider": "rastreiocorreios.com.br",
      "data": [
        {
          "date": "05-03-2024 12:54:09",
          "to": "",
          "from": "PORTO ALEGRE - RS",
          "location": "PORTO ALEGRE - RS",
          "originalTitle": "Objeto entregue ao destinatário",
          "details": "Objeto entregue ao destinatário"
        },
        {
          "date": "05-03-2024 10:41:25",
          "to": "PORTO ALEGRE - RS",
          "from": "PORTO ALEGRE - RS",
          "location": "PORTO ALEGRE - RS",
          "originalTitle": "Objeto saiu para entrega ao destinatário",
          "details": "Objeto saiu para entrega ao destinatário"
        },
        {
          "date": "29-02-2024 15:48:50",
          "to": "JOINVILLE - SC",
          "from": "JOINVILLE - SC",
          "location": "JOINVILLE - SC",
          "originalTitle": "Objeto postado",
          "details": "Objeto postado"
        }
      ]
    }
  ]
}

```

Status

| Status             | Description |
| -----              | ---------------------------------------------------------  |
| DELIVERED          | Objeto entregue ao destinatário                            |
| NOBODYHOME         | Carteiro não atendido                                      |
| MOVEMENT           | Objeto em transferência - por favor aguarde                |
| OUT_DELIVERY       | Objeto saiu para entrega ao remetente                      |
| DELIVERY_FAILURE   | A entrega não pode ser efetuada                            |
| REFUSED_RECEIVE    | Cliente recusou-se a receber                               |
| UNKNOWN_CUSTOMER   | Cliente desconhecido no local                              |
| CUSTOMER_MOVED     | Cliente mudou-se                                           |
| NO_IDENTIFICATION  | Destinatário não apresentou documento exigido              |
| NEW_TRY            | Será feita uma nova tentativa de entrega                   |
| RETURN_SENDER      | Objeto será devolvido ao remetente                         |
| WAITING_WITHDRAWAL | Objeto aguardando retirada no endereço indicado            |
| LATE               | Objeto com atraso na entrega                               |
| RETURN             | Objeto devolvido ao remetente                              |
| MAILBOX            | Objeto disponível para retirada em Caixa Postal            |
| NOTFOUND           | Objeto não localizado                                      |
| LOST               | Objeto Extraviado                                          |
| POSTED             | Objeto postado                                             |
| DISTRUBTION        | Objeto recebido na unidade de distribuição                 |
| RECEIVED_BRAZIL    | Objeto recebido no Brasil                                  |
| STOLEN             | Objeto roubado                                             |

