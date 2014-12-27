<?php

class payment extends model{
    
    public function init(){
       
            switch($this->request['operation']){
                case 'getList' : 
                    $this->getList();
                    break;
                case 'getValue' :
                    $this->getValue($this->request['id']);
                    break;
            }
        
    }
    
    
    public function getList(){
        $condition = array(
            0 => ['conVar' => 'id', 'operator' => '!=', 'conValue' => 0]
        );
        $res = $this->selectSQL('platnosci', $condition);
        $this->put('result', $res);
    }
    
    public function getValue($id){
        $condition = array(
            0 => ['conVar' => 'id', 'operator' => '=', 'conValue' => $id]
        );
        $res = $this->selectSQL('platnosci', $condition);
        $this->put('result', $res);
    }
    
}