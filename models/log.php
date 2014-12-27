<?php

class log extends model{
    
    public function init(){
        if(isset($this->request['log'])){
            if(isset($_SESSION['user_id'])){
                $user_id = (int)$_SESSION['user_id'];
            }else{
                $user_id = 0;
            }
            switch($this->request['log']){
                case 'vp' : //visit product site
                    $this->insertSQL('logi', ['code' => 'vp', 'item_id' => $this->request['item_id']]);
                    break;
                case 'vh' : //visit homepage
                    $this->insertSQL('logi', ['code' => 'vh', 'item_id' => $user_id]);
                    break;
                case 'vc' : //visit campaign
                    $this->insertSQL('logi', ['code' => 'vc', 'item_id' => $this->request['item_id']]);
                    break;
                case 'au' : //auth user
                    $this->insertSQL('logi', ['code' => 'au', 'item_id' => $user_id]);
                    break;
                case 'bp' : //add product to basket
                    $this->insertSQL('logi', ['code' => 'bp', 'item_id' => $this->request['item_id']]);
                    break;
            }
        }
    }    
    
}
