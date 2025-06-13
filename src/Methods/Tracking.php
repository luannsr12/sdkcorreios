<?php 

  namespace Sdkcorreios\Methods;

  use Sdkcorreios\Config\Services;

  class Tracking {

    public $code = null;

    public function setCode($code) {
        if($this->code == null) {
            $this->code = $code;
        }else{
            $this->code .= "," . $code;
        }
    }

    public function get(){
        
        try {

            if(!Services::$success) return false;
         
            if($this->code == "") return false;
    
            $service_name = Services::$service;
    
            $classPath = 'Sdkcorreios\Services\\' . $service_name;
    
            if(!class_exists($classPath)) return false;
    
            $class = new $classPath();
    
            if(!method_exists($class, "tracking")) return false;
    
            return $class->tracking($this->code);

        } catch (\Exception $th) {

            Services::$success = false;
            Services::$message_error = $th->getMessage();

            if(Services::$debug){
                Services::showError();
            }

            return false;
        }

    }
     
  }
  