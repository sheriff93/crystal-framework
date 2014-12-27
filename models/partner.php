<?php

class partner extends model{
    
    public $user_id = 0;
    
    public $isset_id = false;
    
    public function init(){
        if(PERM_LVL>0){
            $this->user_id = $_SESSION['user_id'];
            $this->isset_id = true;
        }
        
        if(isset($this->request['operation'])){
            switch($this->request['operation']){
                case 'addMoney' :
                    $this->addMoney($this->request['sum'], $this->request['referer_id']);
                    break;
                case 'checkMyAccount' :
                    $this->checkMyAccount();
                    break;
                case 'getMyOperations' :
                    $this->getMyOperations();
                    break;
                case 'registerUser' :
                    $this->registerUser();
                    break;
                case 'issetUser' :
                    $this->issetUser($this->request['user_id']);
                    break;
                case 'addBank' :
                    $this->addBank($this->request['number']);
                    break;
                case 'getNumber' :
                    $this->getNumber();
                    break;
                case 'payOutCash' :
                    $this->payOutCash($this->request['cash']);
                    break;
                case 'getAllPayOutOperations' :
                    $this->getAllPayOutOperations();
                    break;
                case 'getAllEarnOperations' :
                    $this->getAllEarnOperations();
                    break;
                case 'getAllUsers' :
                    $this->getAllUsers();
                    break;
                case 'getAllPayOutSuccessOperations' :
                    $this->getAllPayOutSuccessOperations();
                    break;
                case 'earnOperation' :
                    $this->earnOperation($this->request['id']);
                    break;
                case 'changePercent' :
                    $this->changePercent($this->request['id'], $this->request['percent']);
                    break;
            }
        }
    }
    
    public function addMoney($sum, $referer_id){
        if($this->isset_id){
            
            $procent = $this->getPercent();
            $stan_kasy = $this->getCashStatus();
            
            $cash = $sum * $procent;
            
            $sumCash = $cash + $stan_kasy;
            
            $data = array(
                'user_id' => $referer_id,
                'rodzaj_operacji' => 'P',
                'kwota' => $cash        
            );
            $res = $this->insertSQL('ppartnerski_saldo', $data);
            if($res){
                $res = $this->updateCash($sumCash, $referer_id);
            }
            $this->put('result', $res);
        }
    }
    
    public function earnOperation($id){
        $data = array(
            'rodzaj_operacji' => 'M'
        );
        $condition = array(
            0 => ['conVar' => 'id', 'operator' => '=', 'conValue' => $id]
        );
        $res = $this->updateSQL('ppartnerski_saldo', $data, $condition);
        $this->put('result', $res);
    }
    
    
    public function getPercent(){
        if($this->isset_id){
            $out = $this->issetRecord('ppartnerski_userzy', 'procent', 'user_id', $this->request['referer_id']);
            if(isset($out[0]['procent'])){
                return $out[0]['procent'];
            }
        }
        return 0;
    }
    
    public function getCashStatus(){
        if($this->isset_id){
            $out = $this->issetRecord('ppartnerski_userzy', 'stan_kasy', 'user_id', $this->request['referer_id']);
            if(isset($out[0]['stan_kasy'])){
                return $out[0]['stan_kasy'];
            }
        }
        return 0;
    }
    
    public function getMyCashStatus(){
        if($this->isset_id){
            $out = $this->issetRecord('ppartnerski_userzy', 'stan_kasy', 'user_id', $this->user_id);
            if(isset($out[0]['stan_kasy'])){
                return $out[0]['stan_kasy'];
            }
        }
        return 0;
    }
    
    public function checkMyAccount(){
        if($this->isset_id){
            $out = $this->issetRecord('ppartnerski_userzy', 'user_id, stan_kasy, procent', 'user_id', $this->user_id);
            
            if(isset($out[0]['user_id'])){
                
                $this->put('result', $out);
            }
        }
        
        return 0;
    }
    
    
    public function updateCash($cash, $referer_id){
        $condition = array(
            0 => ['conVar' => 'user_id', 'operator' => '=', 'conValue' => $referer_id]
        );
        $data = array(
            'stan_kasy' => $cash
        );
        $res = $this->updateSQL('ppartnerski_userzy', $data, $condition);
        return $res;
    }
    
    public function getMyOperations(){
        if($this->isset_id){
            $condition = array(
                0 => ['conVar' => 'user_id', 'operator' => '=', 'conValue' => $this->user_id]
            );
            $out = $this->selectSQL('ppartnerski_saldo', $condition);
            $this->put('result', $out);
        }
    }
    
    public function registerUser(){
        if($this->isset_id){
            $data = array(
                'user_id' => $this->user_id,
                'procent' => PERCENT_DEFAULT,
                'stan_kasy' => 0
            );
            $this->insertSQL('ppartnerski_userzy', $data);
            $this->put('result', 1);
        }else{
            $this->put('result', 0);
        }
    }
    
    public function issetUser($user_id){
        $user_id = (int)$user_id;
        $res = $this->issetRecord('ppartnerski_userzy', 'user_id', 'user_id', $user_id);
        if(isset($res[0]['user_id'])){
            $this->put('result', 1);
        }else{
            $this->put('result', 0);
        }
    }
    
    public function addBank($number){
        if($this->isset_id){
            $data = array(
                'user_id' => $this->user_id,
                'nr_konta' => $number
            );
            $condition = array(
                0 => ['conVar' => 'user_id', 'operator' => '=', 'conValue' => $this->user_id]
            );
            $this->deleteSQL('ppartnerski_konta', $condition);
            $this->insertSQL('ppartnerski_konta', $data);
            $this->put('result', 1);
        }else{
          exit;
        }
    }
    
    public function payOutCash($cash){
        if($this->isset_id){
            $account_cash = $this->getMyCashStatus();
            if($cash < $account_cash && $cash != 0){
                $new_cash = $account_cash - $cash;
                $this->updateCash($new_cash, $this->user_id);
                $data = array(
                    'user_id' => $this->user_id,
                    'rodzaj_operacji' => 'W',
                    'kwota' => $cash
                );
                $this->insertSQL('ppartnerski_saldo', $data);
                $this->put('result', 1);
            }else{
                $this->put('result', 0);
            }
            
        }else{
            exit;
        }
    }
    
    public function getAllPayOutOperations(){
        $sql = 'SELECT * FROM ppartnerski_saldo, ppartnerski_konta WHERE ppartnerski_konta.user_id = ppartnerski_saldo.user_id'
                . ' AND ppartnerski_saldo.rodzaj_operacji = "W";';
        $stmt = $this->pdo->query($sql);
        $out = $stmt->fetchAll();
        $stmt -> closeCursor();
        $this->put('result', $out);
    }
    
    public function getAllPayOutSuccessOperations(){
        $condition = array(
            0 => ['conVar' => 'rodzaj_operacji', 'operator' => '=', 'conValue' => 'M']
        );
        $res = $this->selectSQL('ppartnerski_saldo', $condition);
        $this->put('result', $res);
    }
    
    public function getAllEarnOperations(){
        $condition = array(
            0 => ['conVar' => 'rodzaj_operacji', 'operator' => '=', 'conValue' => 'P']
        );
        $res = $this->selectSQL('ppartnerski_saldo', $condition);
        $this->put('result', $res);
    }
    
    public function getAllUsers(){
        $sql = 'SELECT * FROM ppartnerski_userzy;';
        $stmt = $this->pdo->query($sql);
        $out = $stmt->fetchAll();
        $stmt -> closeCursor();
        $this->put('result', $out);
    }
    
    
    
    
    public function getNumber(){
        if($this->isset_id){
            $out = $this->issetRecord('ppartnerski_konta', 'nr_konta', 'user_id', $this->user_id);
            if(isset($out[0]['nr_konta'])){
                $this->put('result', $out[0]['nr_konta']);
            }else{
                //$this->put('result', 0);
            }
        }else{
            //$this->put('result', 0);
        }
    }
    
    public function changePercent($id, $newPercent){
        $id = (int)$id;
        
        
            $data = array(
                'procent' => $newPercent
            );
            $condition = array(
                0 => ['conVar' => 'id', 'operator' => '=', 'conValue' => $id]
            );
            $this->updateSQL('ppartnerski_userzy', $data, $condition);
            $this->put('result', 1);
       
                  
    }
    
}
