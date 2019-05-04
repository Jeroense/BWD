<?php

namespace App;

class boltokendata{

    private $token;

    private $valid_till;

    public function getBolTokenString(){

        if(isset($this->token)){
            return $this->token;
        }
    }

    public function getBolTokenValidUntil(){
        if(isset($this->valid_till)){
            return $this->valid_till;
        }
    }

    public function setBolTokenString($val){

        $this->token = $val;
    }

    public function setBolTokenValidUntil($val){

        $this->valid_till = $val;
    }
}
