<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of helper
 *
 * @author sheriff
 */
class helper {

    public $request = 'helperRequestArray';

    public $answer;

    public $pdo;

    public function  __construct($pdo, $helperRequest) {
        $this->pdo = $pdo;
        $this->request = $helperRequest;
        if (method_exists($this, 'init')) $this->init();
    }

    public function put($putName, $putContent){
        $this->answer[$putName] = $putContent;
        return 1;
    }

}
?>