<?php

class product extends model{

    public $data = array();

    public $reqFields = array(        
        'kampania_id',
        'nazwa',
        'sztuki',    
        'hurtownik_id',
        'cena_przed',
        'cena_po',
        'opis',
        'zdjecie_hash'
        
    );
    
    public $failFields = array();
    
    public $fieldError = 0;

    public function init(){
        if(isset($this->request['operation'])){
            if($this->request['operation'] == 'add'){
                $this->add();
            }
            
            if($this->request['operation'] == 'delete'){
                $this->delete($this->request['delete_id']);
            }
            if($this->request['operation'] == 'accept'){
                $this->accept();
            }            
            if($this->request['operation'] == 'disable'){
                $this->disable();
            }
            if($this->request['operation'] == 'getSale'){
                $this->getSaleProducts($this->request['kampania_id']);
            }
            if($this->request['operation'] == 'getProduct'){
                $this->getProduct();
            }
            if($this->request['operation'] == 'update'){
                $this->update();
            }
            if($this->request['operation'] == 'issetActiveItem'){
                $this->issetActiveItem($this->request['product_id']);
            }
            if($this->request['operation'] == 'getSaleAttrs'){
                $this->getSaleAttrs($this->request['kampania_id']);
            }
            if($this->request['operation'] == 'getProductAttrs'){
                $this->getProductAttrs($this->request['product_id']);
            }
            if($this->request['operation'] == 'get3New'){
                $this->get3New();
            }
            if($this->request['operation'] == 'get15New'){
                $this->get15New();
            }
            if($this->request['operation'] == 'getValueProducts'){
                $this->getValueProducts($this->request['value_id']);
            }
            if($this->request['operation'] == 'getDesc'){
                $this->getDesc($this->request['product_id']);
            }
            if($this->request['operation'] == 'getActive'){
                $this->getActive();
            }
            if($this->request['operation'] == 'getDisabled'){
                $this->getDisabled();
            }
            if($this->request['operation'] == 'getWaiting'){
                $this->getWaiting();
            }
            

           
        }
    }

    public function add(){
        $this->data['status'] = 'W';
        $this->copyData();
        $a = 0;
        if(!$this->fieldError) {
            $a = $this->insertSQL('produkty', $this->data);            
        }
        $this->answer['result'] = $a;
    }
    
    public function delete($delete_id){
        $delete_id = (int)$delete_id;        
        $conditions = array(
            0 => array('conVar' => 'produkt_id', 'operator' => '=', 'conValue' => $delete_id)            
        );                
        $this->answer['result'] = $this->deleteSQL('produkty', $conditions);
    }
    
    public function accept(){
        $accept_id = (int)$this->request['accept_id'];
        $conditions = array(
                0 => array('conVar' => 'produkt_id', 'operator' => '=', 'conValue' => $accept_id)
        );
        $this->answer['result'] = $this->updateSQL('produkty', array('status' => 'A'), $conditions);
    }
    
    public function disable(){
        $disable_id = (int)$this->request['disable_id'];
        $conditions = array(
                0 => array('conVar' => 'produkt_id', 'operator' => '=', 'conValue' => $disable_id)
        );
        $this->answer['result'] = $this->updateSQL('produkty', array('status' => 'D'), $conditions);
    }
    
    public function getDesc($product_id){
        $product_id = (int)$product_id;
        $res = $this->issetRecord('opis_produktow', 'opis', 'produkt_id', $product_id);
        if(isset($res[0]['opis'])){
            $this->put('result', $res[0]['opis']);
        }else{
            $this->put('result', 0);
        }
    }
    

    

    public function getSaleProducts($kampania_id){        
        $kampania_id = (int)$kampania_id;
        $conditions = array(
            0 => array('conVar' => 'kampania_id', 'operator' => '=', 'conValue' => $kampania_id, 'condition' => 'AND'),
            1 => array('conVar' => 'status', 'operator' => '=', 'conValue' => 'A')
        );
        $r = $this->selectSQL('produkty', $conditions);
        $this->answer['data'] = $r;
    }
    
    public function getWaiting(){
        $condition = array(
             0 => array('conVar' => 'status', 'operator' => '=', 'conValue' => 'W')
        );
        $res = $this->selectSQL('produkty', $condition);
        $this->put('result', $res);
    }
    
    
    public function getActive(){
        $condition = array(
             0 => array('conVar' => 'status', 'operator' => '=', 'conValue' => 'A')
        );
        $res = $this->selectSQL('produkty', $condition);
        $this->put('result', $res);
    }
    
    public function getDisabled(){
        $condition = array(
             0 => array('conVar' => 'status', 'operator' => '=', 'conValue' => 'D')
        );
        $res = $this->selectSQL('produkty', $condition);
        $this->put('result', $res);
    }
    
    public function getSaleAttrs($kampania_id){
        $kampania_id = (int)$kampania_id;
        $sql = 'SELECT produkty.produkt_id, atrybuty_produkt.atrybut_id, atrybuty_produkt.wartosc_id'
                . ' FROM produkty, atrybuty_produkt'
                . ' WHERE atrybuty_produkt.produkt_id  = produkty.produkt_id'
                . ' AND produkty.kampania_id = :kampania_id;';
        $stmt = $this->pdo->prepare($sql);
        $stmt ->bindValue(':kampania_id', $kampania_id);
        $res = $stmt -> execute();
        $out = 0;
        if($res){
            $out = $stmt ->fetchAll();
        }
        $stmt->closeCursor();
        $this->put('result', $out);
    }
    
    public function getProductAttrs($product_id){
        $sql = 'SELECT atrybuty.nazwa as atrybut, wartosci.nazwa as wartosc'
                . ' FROM atrybuty, wartosci, atrybuty_produkt'
                . ' WHERE atrybuty_produkt.produkt_id = :produkt_id'
                . ' AND atrybuty.atrybut_id = atrybuty_produkt.atrybut_id'
                . ' AND wartosci.wartosc_id = atrybuty_produkt.wartosc_id;';
        $stmt = $this->pdo->prepare($sql);
        $stmt ->bindValue(':produkt_id', $product_id);
        $res = $stmt -> execute();
        $out = 0;
        if($res){
            $out = $stmt ->fetchAll();
        }
        $stmt->closeCursor();
        
        $this->put('result', $out);
    }
    
    public function getValueProducts($value_id){
        $sql = 'SELECT * FROM produkty, atrybuty_produkt'
                . ' WHERE produkty.produkt_id = atrybuty_produkt.produkt_id'
                . ' AND produkty.status = "A"'
                . ' AND atrybuty_produkt.wartosc_id = :value_id;';
        $stmt = $this->pdo->prepare($sql);
        $stmt ->bindValue(':value_id', $value_id);
        $res = $stmt -> execute();
        $out = 0;
        if($res){
            $out = $stmt ->fetchAll();
        }
        $stmt->closeCursor();
        
        $this->put('result', $out);
    }
    
    public function getNewestAttr(){
        $sql = 'SELECT produkty.produkt_id, atrybuty_produkt.atrybut_id, atrybuty_produkt.wartosc_id'
                . ' FROM produkty, atrybuty_produkt'
                . ' WHERE atrybuty_produkt.produkt_id  = produkty.produkt_id'
                . ' AND produkty.kampania_id = :kampania_id;';
        $stmt = $this->pdo->prepare($sql);
        $stmt ->bindValue(':kampania_id', $kampania_id);
        $res = $stmt -> execute();
        $out = 0;
        if($res){
            $out = $stmt ->fetchAll();
        }
        $stmt->closeCursor();
        $this->put('result', $out);
    }
    
    public function get3New(){
        $sql = 'SELECT * FROM produkty WHERE status = "A" ORDER BY produkt_id DESC LIMIT 3';
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute();
        
        $out = 0;
        if($res){
            $out = $stmt ->fetchAll();
        }
        $stmt->closeCursor();
        
        $this->put('result', $out);
    }
    
    public function get15New(){
        $sql = 'SELECT * FROM produkty WHERE status = "A" ORDER BY produkt_id DESC LIMIT 15';
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute();
        
        $out = 0;
        if($res){
            $out = $stmt ->fetchAll();
        }
        $stmt->closeCursor();
        
        $this->put('result', $out);
    }
    

    public function getProduct(){
        $id = (int)$this->request['id'];        
        $conditions = array(
            0 => array('conVar' => 'produkt_id', 'operator' => '=', 'conValue' => $id)
        );        
        $r = $this->selectLimitSQL('produkty', $conditions, 1);
        $this->answer['data'] = $r;
    }
    
    public function read(){
        $conditions = array(
            0 => array('conVar' => 'kampania_id', 'operator' => '=', 'conValue' => 1, 'condition' => 'OR'),
            1 => array('conVar' => 'kampania_id', 'operator' => '=', 'conValue' => 2, 'condition' => 'OR'),
            2 => array('conVar' => 'kampania_id', 'operator' => '=', 'conValue' => 3)
        );
        $a = $this->selectSQL('kampanie', $conditions);

    }
    
        
    
    public function update(){
        $update_id = (int)$this->request['update'];
        $conditions = array(
            0 => array('conVar' => 'kampania_id', 'operator' => '=', 'conValue' => 4, 'condition' => 'OR'),
            1 => array('conVar' => 'kampania_id', 'operator' => '=', 'conValue' => 5)
        );        
        $a = $this->updateSQL('kampanie', array('hurtownik_id' => 11, 'procent_znizki' => 45));
        if ($a) echo 'wykonano pomyslnie';
    }
    
    public function issetActiveItem($product_id){
        $condition = array(
            0 => ['conVar' => 'produkt_id', 'operator' => '=', 'conValue' => $product_id, 'condition' => 'AND'],
            1 => ['conVar' => 'status', 'operator' => '=', 'conValue' => 'A']
        );
        $res = $this->selectLimitSQL('produkty', $condition, 1, 'produkt_id, sztuki');
        $this->put('data', $res);
    }

    

    public function copyData(){
        foreach($this->reqFields as $fieldName){
            if(isset($this->request['data'][$fieldName])){
                $this->data[$fieldName] = $this->request['data'][$fieldName];
            }else{
                $this->failFields[] = $fieldName;
                $this->fieldError = 1;
            }
        }

    }



}