<?php 

  namespace Sdkcorreios\Config;
  
  class Services
  {
    public static string $service;

    public static array $list = [];

    public static bool $success = true;

    public static string $message_error;

    public static bool $debug = false;


    public function __construct( ){
      
    }

    public static function getServices(){
      $services =  [
        '0001' => 'MelhorRastreio',
        '0002' => 'EncomendaIo',
        '0003' => 'RastreadorDePacotes',
        '0004' => 'RastreamentoCorreio',
        '0005' => 'Muambator',
        '0006' => 'RastreioCorreios',
        '0007' => 'LinkCorreios'
      ];

      self::$list = $services;

      return $services;
    }

    public static function getMessageError(){
        return self::$message_error;
    }

    public static function setServiceTracking(string $service){

      $services = self::getServices();
      
      if(!isset($services[$service])){
        self::$success = false;
        self::$message_error = "Service not found";
        return false;
      }

      self::$service = $services[$service];
      return true;

    }

    public static function setDebug($d=true){
      self::$debug = $d;
    }

    public static function showError(){
      var_dump(['success' => self::$success, 'message'=> self::$message_error]);
      die;
    }

    
  }
  