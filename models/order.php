<?php

class order extends model{
    
    public $user_id = 0;
    
    public $isset_id = false;
    
    public $referer_id;
    
    public $order_value;
    
    public $payment;
    
    public $message;
    
    
    public function init(){
        
        if(PERM_LVL>0){
            $this->user_id = $_SESSION['user_id'];
            $this->isset_id = true;
        }
        
        if(isset($this->request['operation'])){
            switch($this->request['operation']){
                case 'purchase' :
                    $this->purchase($this->request['payment'], $this->request['message']);
                    break;
                case 'itemsOfOrder' :
                    $this->itemsOfOrder($this->request['order_id']);
                    break;
                case 'executeOrder' :
                    $this->executeOrder($this->request['order_id']);
                    break;
                case 'getMyOrders' :
                    $this->getMyOrders();
                    break;
                case 'getExecuted' :
                    $this->getExecuted();
                    break;
                case 'getWaiting' :
                    $this->getWaiting();
                    break;
                case 'getDeclined' :
                    $this->getDeclined();
                    break;
                case 'declineOrder' :
                    $this->declineOrder($this->request['order_id']);
                    break;
            }
        }
    }
    
    
    public function purchase($payment, $message){
        $res = 0;
        if($this->isset_id){
            $this->payment = $payment;
            $this->message = $message;
            $this->execModel('basket', ['operation' => 'getUserBasket']);
            if(isset($this->models['basket']->answer['data'][0])){
                $basket = $this->models['basket']->answer['data'];
                $order_id = $this->addOrderToList(); //dodanie zamowienia
                if($order_id){
                   $res = $this->addOrderItems($order_id, $basket); //dodanie pozycji zamowienia
                   $this->execModel('basket', ['operation' => 'flushMyBasket']); //wyczyszczenie koszyka
                }                
            }
        }
        $this->put('result', $res);
        return $res;
    }
    
    public function getWaiting(){
        $condition = array(
             0 => array('conVar' => 'kod_status_zamowienia', 'operator' => '=', 'conValue' => 'W')
        );
        $res = $this->selectSQL('lista_zamowien', $condition);
        $this->put('result', $res);
    }
    
    
    public function getExecuted(){
        $condition = array(
             0 => array('conVar' => 'kod_status_zamowienia', 'operator' => '=', 'conValue' => 'D')
        );
        $res = $this->selectSQL('lista_zamowien', $condition);
        $this->put('result', $res);
    }
    
    public function getDeclined(){
        $condition = array(
             0 => array('conVar' => 'kod_status_zamowienia', 'operator' => '=', 'conValue' => 'R')
        );
        $res = $this->selectSQL('lista_zamowien', $condition);
        $this->put('result', $res);
    }
    
    
    
    public function addOrderToList(){
        if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)){ //pobranie ip użytkownika
            $ip = '0.0.0.0';
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $this->execModel('user', ['operation' => 'getRefererId', 'user_id' => $this->user_id]); //pobranie referera użytkownika
        
        if(isset($this->models['user']->answer['result'])){
            $referer_id = (int)$this->models['user']->answer['result'];
        }else{
            $referer_id = 0;
        }
        
        $this->flushModels();
        
        $this->execModel('basket', ['operation' => 'getSummaryPrice']); //pobranie wartosci zamowienia
        if(isset($this->models['basket']->answer['result'])){
            $wartosc = (int)$this->models['basket']->answer['result'];
        }else{
            $wartosc = 0;
        }
        
        $this->flushModels();
        
        $data = array(
            'ip' => $ip,
            'referer_id' => $referer_id,
            'klient_id' => $this->user_id,
            'platnosc' => $this->payment,
            'kod_status_zamowienia' => 'W',
            'wiadomosc_klienta' => $this->message,
            'wartosc' => $wartosc,
        );
        $last_id = 0;
        if($this->insertSQL('lista_zamowien', $data)){
            $last_id = $this->pdo->lastInsertId('zamowienie_id');
            $last_id = (int)$last_id;
        }
        
        return $last_id;        
    }
    
    public function addOrderItems($order_id, $basket){
        foreach($basket as $item){
            $res = 0;
            $data = array(
                'zamowienie_id' => $order_id,
                'produkt_id' => $item['produkt_id'],
                'ilosc_sztuk' => $item['sztuki']
            );
            $res = $this->insertSQL('zamowienia_pozycje', $data);
        }
        return $res;
    }
    
    public function itemsOfOrder($order_id){
        if($this->isset_id){
            $klient_id = $this->issetRecord('lista_zamowien', 'klient_id', 'zamowienie_id', $order_id);
            $klient_id = $klient_id[0]['klient_id'];
            if($klient_id == $this->user_id){ //czy user ma prawo do tej listy
                $this->getItemsOfOrder($order_id);
            }else{
                $this->put('result', 0);
            }
        }else{
            $this->put('result', 0);
        }
        return 0;
    }
    
    public function getItemsOfOrder($order_id){ //pobierz liste produktow danego zamowienia
        $sql = 'SELECT produkty.produkt_id, produkty.nazwa 
                FROM produkty, zamowienia_pozycje
                WHERE produkty.produkt_id = zamowienia_pozycje.produkt_id
                AND zamowienia_pozycje.zamowienie_id = :order_id;';
        $stmt = $this->pdo->prepare($sql);
        $stmt -> bindValue(':order_id', $order_id);
        $res = $stmt -> execute();
        $out = 0;
        if ($res){
            $out = $stmt ->fetchAll();
        }
        $stmt -> closeCursor();
        $this->put('data', $out);
        return 1;
    }
    
    public function executeOrder($order_id){
        $c1 = $c2 = 0;
        if(PERM_LVL>0 && $this->isset_id && $this->getRefererData($order_id)){ //zmienić na = 10 !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!            
            $c1 = $this->addRefererMoney($this->order_value); 
            $c2 = $this->changeOrderState($order_id);
        }
        if($c1 && $c2){
            $this->put('result', 1);
            return 1;
        }else{
            $this->put('result', 0);
            return 0;
        }
    }
    
    public function getRefererData($order_id){        
        $out = $this->issetRecord('lista_zamowien', 'wartosc, referer_id', 'zamowienie_id', $order_id);
        if(isset($out[0]['wartosc'])){
            $this->order_value = $out[0]['wartosc'];
            $this->referer_id = $out[0]['referer_id'];
            return 1;
        }else{
            return 0;
        }
    }
    
    public function addRefererMoney($orderValue){
        $res = $this->execModel('partner', ['operation' => 'addMoney', 'sum' => $orderValue, 'referer_id' => $this->referer_id]);
        return $res;
    }
    
    public function changeOrderState($order_id){
        $data = array(
            'kod_status_zamowienia' => 'D'
        );
        $condition = array(
            0 => ['conVar' => 'zamowienie_id', 'operator' => '=', 'conValue' => $order_id]
        );
        $res = $this->updateSQL('lista_zamowien', $data, $condition);
        return $res;
    }
    
    public function declineOrder($order_id){
        $data = array(
            'kod_status_zamowienia' => 'R'
        );
        $condition = array(
            0 => ['conVar' => 'zamowienie_id', 'operator' => '=', 'conValue' => $order_id]
        );
        $res = $this->updateSQL('lista_zamowien', $data, $condition);
        $this->put('result', $res);
        return $res;
    }
    
    public function getMyOrders(){
        if($this->isset_id){
            $condition = array(
                0 => ['conVar' => 'klient_id', 'operator' => '=', 'conValue' => $this->user_id]
            );
            $out = $this->selectSQL('lista_zamowien', $condition);
            $this->put('result', $out);
        }
    }
    
}