<?php

    include_once (__DIR__.'/ResponseCodes.php');
    
    class ZenophSMSGH_Exception extends Exception {
        private $responsecode = ZenophSMSGH_RESPONSECODE::ERR_UNKNOWN;
        
        public function __construct($message, $code){
            $this->responsecode = $code;
            parent::__construct($message);
        }
        
        public function getResponseCode(){
            return $this->responsecode;
        }
    }
?>