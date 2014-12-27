<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of secToken
 *
 * @author sheriff
 */
class secToken {

    public $token;

    public $tokenName = 'token';


    public function __construct(){
        $this->token = (time().sha1(rand(1,5000).TOKEN_SALT.rand(1, 999999999)));
    }//end __consctruct()

    public function check($token){
        if (isset($_SESSION[$this->tokenName])){
            if ($_SESSION[$this->tokenName] == $token) return 1;
        }
        return 0;
    }//end check()

    public function getIdFromRequest($params){
        $pArray = explode('i', $params);
        if (isset($pArray[1])){
            $id = (int)$pArray[1];
        }else{
            return 0;
        }
        if (is_int($id) && $id != 0) return $id;
        return 0;
    }

    public function getTokenFromRequest($params){
        $pArray = explode('i', $params);
        if (isset($pArray[0])){
            $id = $pArray[0];
            return $id; 
        }else{
            return 0;
        }
               
    }

    public function push(){
        $_SESSION[$this->tokenName] = $this->token;
        return $this->token;
    }//end push()




}
?>
