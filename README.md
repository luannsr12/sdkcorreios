# SDK Correios Tracking 1.0.4

[![](https://img.shields.io/github/contributors/luannsr12/sdkcorreios.svg?style=flat-square)](https://github.com/luannsr12/sdkcorreios/graphs/contributors)
[![](https://badges.pufler.dev/updated/luannsr12/sdkcorreios)](https://github.com/luannsr12/sdkcorreios)
[![](https://badges.pufler.dev/visits/luannsr12/sdkcorreios)](https://github.com/luannsr12/sdkcorreios)

<br/>

### Uma SDK em PHP para rastrear encomendas dos correios gratuitamente.

<br/>
<br/>

Sites Disponiveis para busca

| Site                       | Status  | Name |
| :-----:                    | :---:   | :---: |
| encomenda.io               |  ✅    | EncomendaIo |
| melhorrastreio.com.br      |  ✅    | MelhorRastreio |
| rastreadordepacotes.com.br |  ✅    | RastreadorDePacotes |
| rastreamentocorreio.com    |  ✅    | RastreamentoCorreio |



Install Composer

```bash
 composer require luannsr12/sdkcorreios
```


Using

```php
 <?php 

   require 'vendor/autoload.php';

   use Sdkcorreios\Config\Services;
   use Sdkcorreios\Methods\Tracking;

   // RastreadorDePacotes / EncomendaIo / MelhorRastreio / etc... 
   // Confira na tabela os nomes das classes
   Services::setServiceTracking("RastreadorDePacotes"); // Site que o sdk irá fazer a busca
   Services::setDebug(true);

   $tracking = new Tracking();
   $tracking->setCode("OBJETO1");
   $tracking->setCode("OBJETO2");

   var_dump($tracking->get());



```

Response

```json
 {
  "success": true,
  "result": [
    {
      "code": "QQ781772845BR",
      "status": "DELIVERED",
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