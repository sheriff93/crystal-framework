<?php

class newsletter extends model{
    
    public $user_id = 0;
    
    public $isset_id = 0;
    
    public function init(){
        if(isset($this->request['operation'])){
            if(isset($_SESSION['user_id'])){
                $this->user_id = (int)$_SESSION['user_id'];
                $this->isset_id = 1;
            }
            switch($this->request['operation']){
                case 'add' :
                    $this->add($this->request['email']);
                    break;
            }
        }
    }    
    
    
    public function add($email){
        $res = $this->insertSQL('newsletter', ['email' => $email]);
        $this->put('result', $res);
    }
    
}
