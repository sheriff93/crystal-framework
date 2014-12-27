<?php

class basket extends model{
    
    public $user_id = 0;
    
    public $isset_user_id = false;
    
    
    public function init(){
        if(PERM_LVL == 0){ //operacje na gościu
            if(isset($_SESSION['basket_id'])){ // jeżeli koszyk istnieje
                if($this->isValidMd5($_SESSION['basket_id'])){ //jeżeli jest poprawny
                    $this->user_id = $_SESSION['basket_id'];
                    $this->isset_user_id = true;
                }else{
                    exit;
                }
            }else{ //jezeli koszyk nie istnieje, to go tworzymy
                $sess_id = session_id();
                $_SESSION['basket_id'] = md5($sess_id);
                $this->user_id = $_SESSION['basket_id'];
                $this->isset_user_id = true;
            }
        }else{
            if(isset($_SESSION['user_id'])){
                $this->user_id = $_SESSION['user_id'];
                $this->isset_user_id = true;
            }else{
                exit;
            }
        }
                
        if(isset($this->request['operation'])){
            if($this->request['operation'] == 'addItem'){
                $this->addItem($this->request['product_id'], $this->request['itemsNumber']);
            }
            if($this->request['operation'] == 'addAttr'){
                $this->addAttr($this->request['attr']);
            }
            if($this->request['operation'] == 'deleteItem'){
                $this->deleteItem($this->request['product_id']);
            }
            if($this->request['operation'] == 'changeNumberOfItems'){
                $this->changeNumberOfItems($this->request['product_id'], $this->request['newItemsNumber']);
            }
            if($this->request['operation'] == 'issetItem'){
                $this->issetItem($this->request['product_id']);
            }
            if($this->request['operation'] == 'getUserBasket'){
                $this->getUserBasket();
            }
            if($this->request['operation'] == 'flushMyBasket'){
                $this->flushMyBasket();
            }
            if($this->request['operation'] == 'getSummaryPrice'){
                $this->getSummaryPrice();
            }
            if($this->request['operation'] == 'count'){
                $this->count();
            }
        }
        
    }
    
    public function isValidMd5($md5){        
        return !empty($md5) && preg_match('/^[a-f0-9]{32}$/', $md5);    
    }    
    
    public function addItem($product_id, $itemsNumber){
        if($this->isset_user_id){ //czy użytkownik ma koszyk?
            $this->execModel('product', ['operation' => 'issetActiveItem', 'product_id' => $product_id]); //szukaj produktu
            if(isset($this->models['product']->answer['data'][0][0])){ //czy produkt istnieje?
                $canAdd = 1;
            }else{
                $canAdd = 0;
            }
            if($canAdd){ //czy można dodać?
                $res = $this->insertSQL('koszyk', ['user_id' => $this->user_id, 'produkt_id' => (int)$product_id, 'sztuki' => (int)$itemsNumber]);
            }else{
                $res = 0; //nie istnieje
            }
            $this->splitItems($product_id);
            $this->put('result', $res);
            
        }
    }
    
    public function deleteItem($product_id){
        if($this->isset_user_id){
            $condition = array(
                0 => ['conVar' => 'produkt_id', 'operator' => '=', 'conValue' => $product_id, 'condition' => 'AND'],
                1 => ['conVar' => 'user_id', 'operator' => '=', 'conValue' => $this->user_id]
            );            
            $res = $this->deleteSQL('koszyk', $condition);
            $this->put('result', $res);
        }
    }
    
    public function deleteDuplicate($product_id){
        $condition = array(
            0 => ['conVar' => 'produkt_id', 'operator' => '=', 'conValue' => $product_id]
        );            
        $res = $this->deleteSQL('koszyk', $condition);
        return $res;
    }
    
    public function changeNumberOfItems($product_id, $newItemsNumber){
        if($this->isset_user_id){
            $condition = array(
                0 => ['conVar' => 'produkt_id', 'operator' => '=', 'conValue' => $product_id, 'condition' => 'AND'],
                1 => ['conVar' => 'user_id', 'operator' => '=', 'conValue' => $this->user_id]
            );
            $sztuki = (int)$newItemsNumber;
            $data = array(
                'sztuki' => $sztuki
            );
            $res = $this->updateSQL('koszyk', $data, $condition);
            $this->put('result', $res);
        }
    }
    
    public function getUserBasket(){
        if($this->isset_user_id){
            $sql = 'select koszyk.produkt_id, koszyk.sztuki, produkty.nazwa, produkty.cena_po, produkty.zdjecie_hash, produkty.status from koszyk, produkty
                    where produkty.produkt_id = koszyk.produkt_id
                    and koszyk.user_id = :user_id
                    and produkty.status = "A";';
            $stmt = $this->pdo->prepare($sql);
            $stmt -> bindValue(':user_id', $this->user_id);
            $res = $stmt-> execute();
            if($res){
                $out = $stmt -> fetchAll();
            }
            $stmt -> closeCursor();
            
            $this->put('data', $out);
            
        }
    }
    
    public function flushMyBasket(){
        if($this->isset_user_id){
            $condition = array(
                0 => ['conVar' => 'user_id', 'operator' => '=', 'conValue' => $this->user_id]
            );
            $res = $this->deleteSQL('koszyk', $condition);
            $this->put('result', $res);
        }
    }
    
    public function getSummaryPrice(){
        if($this->isset_user_id){
            $sql = 'SELECT produkty.produkt_id, produkty.cena_po, koszyk.user_id, koszyk.produkt_id, koszyk.sztuki
                    FROM produkty, koszyk
                    WHERE koszyk.user_id = :user_id
                    AND produkty.status = "A"
                    AND produkty.produkt_id = koszyk.produkt_id;';
            $stmt = $this->pdo->prepare($sql);
            $stmt -> bindValue(':user_id', $this->user_id);
            $res = $stmt -> execute();
            $sum = 0;
            if ($res){
                $out = $stmt ->fetchAll();
            }
            $stmt -> closeCursor();
            
            if(isset($out[0]['produkt_id'])){
                foreach($out as $item){
                    $sum = ($sum + ($item['cena_po'] * $item['sztuki']));
                }
            }
            $this->put('result', $sum);
            
        }
        
    }
    
    public function issetItem($product_id){
        if($this->isset_user_id){
            $condition = array(
                0 => ['conVar' => 'produkt_id', 'operator' => '=', 'conValue' => $product_id, 'condition' => 'AND'],
                1 => ['conVar' => 'user_id', 'operator' => '=', 'conValue' => $this->user_id]
            );
            $res = $this->selectLimitSQL('koszyk', $condition, 1, 'id');
            if (isset($res[0]['id'])){
                $res = 1;
            }else{
                $res = 0;
            }
            $this->put('result', $res);
        }
    }
    
    public function count(){
        if($this->isset_user_id){
            $condition = array(
                0 => ['conVar' => 'user_id', 'operator' => '=', 'conValue' => $this->user_id]
            );
            $count = 0;
            $res = $this->selectSQL('koszyk', $condition, 'sztuki');
            if(isset($res[0]['sztuki'])){
                foreach($res as $item){
                    $count = $count + ((int)$item['sztuki']);
                }
            }
            $this->put('result', $count);
        }
    }
    
    public function splitItems($product_id){
         if($this->isset_user_id){
            $condition = array(
                0 => ['conVar' => 'user_id', 'operator' => '=', 'conValue' => $this->user_id, 'condition' => 'AND'],
                1 => ['conVar' => 'produkt_id', 'operator' => '=', 'conValue' => $product_id]
            );
            $count = 0;
            $res = $this->selectSQL('koszyk', $condition, 'sztuki');
            if(isset($res[0]['sztuki'])){
                foreach($res as $item){
                    $count = $count + ((int)$item['sztuki']);
                }
            }
            $this->deleteDuplicate($product_id);
            $res = $this->insertSQL('koszyk', ['user_id' => $this->user_id, 'produkt_id' => (int)$product_id, 'sztuki' => (int)$count]);
        }
    }
    
    
}