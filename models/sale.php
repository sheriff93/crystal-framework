<?php

class sale extends model{

    public $data = array();   

    public $reqFields = array(
        'nazwa',
        'opis',
        'hurtownik_id',
        'procent_znizki',
        'data_rozpoczecia',
        'data_zakonczenia',
        'plik_zdjecia',
        'data_dodania',
        'notatki',
        'status'
    );

    public $fieldError = 0;

    public function init(){                
        if(isset($this->request['operation'])){
            if($this->request['operation'] == 'create'){
                $this->create();
            }
            if($this->request['operation'] == 'read'){
                $this->read();
            }
            if($this->request['operation'] == 'update'){
                $this->update();
            }
            if($this->request['operation'] == 'delete'){
                $this->delete();
            }
            if($this->request['operation'] == 'getSalesMainPage'){
                $this->getSalesMainPage();
            }
            if($this->request['operation'] == 'getSaleName'){
                $this->getSaleName($this->request['kampania_id']);
            }
            if($this->request['operation'] == 'getWaiting'){
                $this->getWaiting();
            }
            if($this->request['operation'] == 'getActive'){
                $this->getActive();
            }
        }
    }

    public function create(){
        $this->copyData();
        $a = 0;
        if(!$this->fieldError) $a = $this->insertSQL('kampanie', $this->data);
        $this->answer['result'] = $a;
    }

    public function read(){
        $conditions = array(
            0 => array('conVar' => 'kampania_id', 'operator' => '=', 'conValue' => 1, 'condition' => 'OR'),
            1 => array('conVar' => 'kampania_id', 'operator' => '=', 'conValue' => 2, 'condition' => 'OR'),
            2 => array('conVar' => 'kampania_id', 'operator' => '=', 'conValue' => 3)
        );
        $a = $this->selectSQL('kampanie', $conditions);
    }
    
    public function getActive(){
        $condition = array(
             0 => array('conVar' => 'status', 'operator' => '=', 'conValue' => 'A')
        );
        $res = $this->selectSQL('kampanie', $condition);
        $this->put('result', $res);
    }
    
    
    public function getWaiting(){
        $condition = array(
             0 => array('conVar' => 'status', 'operator' => '=', 'conValue' => 'W')
        );
        $res = $this->selectSQL('kampanie', $condition);
        $this->put('result', $res);
    }

    public function getSalesMainPage(){
        $conditions = array(
            0 => array('conVar' => 'status', 'operator' => '=', 'conValue' => 'A')
        );
        $r = $this->selectLimitSQL('kampanie', $conditions, '12');
        $this->answer['data'] = $r;
    }
    
    public function getSaleName($kampania_id){
        $kampania_id = (int)$kampania_id;
        $out = $this->issetRecord('kampanie', 'nazwa', 'kampania_id', $kampania_id);
        if(isset($out[0]['nazwa'])){
            $this->put('result', $out[0]['nazwa']);
        }else{
            $this->put('result', 'Wyprzedaż');
        }
    }
    

    public function update(){
        $conditions = array(
            0 => array('conVar' => 'kampania_id', 'operator' => '=', 'conValue' => 4, 'condition' => 'OR'),
            1 => array('conVar' => 'kampania_id', 'operator' => '=', 'conValue' => 5)
        );
        $a = $this->updateSQL('kampanie', array('hurtownik_id' => 11, 'procent_znizki' => 45));
        if ($a) echo 'wykonano pomyslnie';
    }

    public function delete(){
        $conditions = array(
            0 => array('conVar' => 'kampania_id', 'operator' => '=', 'conValue' => 4, 'condition' => 'OR'),
            1 => array('conVar' => 'kampania_id', 'operator' => '=', 'conValue' => 5)
        );
        $a = $this->deleteSQL('kampanie', $conditions);
        if ($a) echo 'wykonano pomyslnie';
    }

    public function copyData(){
        foreach($this->reqFields as $fieldName){
            if(isset($this->request['data'][$fieldName])){
                $this->data[$fieldName] = $this->request['data'][$fieldName];
            }else{
                echo 'FieldError przy: '.$fieldName;
                $this->fieldError = 1;
            }
        }
        
    }



}