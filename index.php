<?php 


   require 'vendor/autoload.php';

   use Sdkcorreios\Config\Services;
   use Sdkcorreios\Methods\Tracking;
    
   Services::setServiceTracking("RastreadorDePacotes");
   Services::setDebug(true);

   $tracking = new Tracking();
   $tracking->setCode("QQ781772845BR");

   echo json_encode($tracking->get());

   //MelhorRastreio::tracking("QQ588651634BR");