<?php 

  namespace Sdkcorreios\Config;

  class Services
  {
    public static string $service;

    public static array $list = ["MelhorRastreio", "EncomendaIo", "RastreadorDePacotes"];

    public static bool $success = true;

    public static string $message_error;

    public static bool $debug = false;

    public const STATUS_DELIVERED = "DELIVERED";

    public const STATUS_MOVEMENT = "MOVEMENT";

    public const STATUS_NOTFOUND = "NOTFOUND";


    public function __construct( ){
      
    }

    public static function setServiceTracking($service){
      
      if(!in_array($service, self::$list)){
        self::$success = false;
        self::$message_error = "Service not found";
        return false;
      }

      self::$service = $service;
      return true;

    }

    public static function setDebug($d=true){
      self::$debug = $d;
    }

    public static function showError(){
      var_dump(['success' => self::$success, 'message'=> self::$message_error]);
      die;
    }

    public static function getServices(){
      return self::$list;
    }
    
  }
  