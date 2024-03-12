# SDK Correios Tracking 1.0.3

[![](https://img.shields.io/github/contributors/luannsr12/sdkcorreios.svg?style=flat-square)](https://github.com/luannsr12/sdkcorreios/graphs/contributors)
[![](https://badges.pufler.dev/updated/luannsr12/sdkcorreios)](https://github.com/luannsr12/sdkcorreios)
[![](https://badges.pufler.dev/visits/luannsr12/sdkcorreios)](https://github.com/luannsr12/sdkcorreios)


```bash
 composer require luannsr12/sdkcorreios
```

```php
 <?php 

   require 'vendor/autoload.php';

   use Sdkcorreios\Config\Services;
   use Sdkcorreios\Methods\Tracking;

   // RastreadorDePacotes / EncomendaIo / MelhorRastreio
   Services::setServiceTracking("RastreadorDePacotes"); // Site que o sdk irá fazer a busca
   Services::setDebug(true);

   $tracking = new Tracking();
   $tracking->setCode("OBJETO1");
   $tracking->setCode("OBJETO2");

   var_dump($tracking->get());



```

Sites Disponiveis para busca

| Site                       | Status  | Name |
| :-----:                    | :---:   | :---: |
| encomenda.io               |  ✅    | EncomendaIo |
| melhorrastreio.com.br      |  ✅    | MelhorRastreio |
| rastreadordepacotes.com.br |  ✅    | RastreadorDePacotes |
| rastreamentocorreio.com    |  ✅    | RastreamentoCorreio |

