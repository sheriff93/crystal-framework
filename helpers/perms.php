<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of test
 *
 * @author sheriff
 */
class perms extends helper {

    public $ulevel = 0;

    public $authResult = 0;

    public function init(){
        if(isset($_SESSION['ulevel'])) $this->ulevel = (int)$_SESSION['ulevel'];
        $this->check($this->helperRequest['ulevel']);
    }

    private function check($ulevel){
        if($this->ulevel >= $ulevel) $this->authResult = 1;
        $this->put('userAllowed', $this->authResult);
        return $this->authResult;
    }

}
?>
