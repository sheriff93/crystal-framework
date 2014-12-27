<?php

class user extends model{
    
    
    public $user_id = 0;
    
    public $isset_id = false;

    public $data = array();

    public $reqFields = array(
        'imie',
        'nazwisko',
        'plec',
        'data_urodzenia',
        'ulica',
        'numer_budynku',
        'numer_mieszkania',
        'miasto',
        'pocztowy',
        'telefon',
        'email',
        'haslo_hash',
        'reg_ip',
        'data_rejestracji',
        'reg_status',
        'ostatnie_logowanie_data',
        'ostatnie_logowanie_ip',
        'referer_id'
    );

    public $fieldError = 0;
    
    public $sort_insert = 'id';
    
    public $limit_insert = 'LIMIT 20';


    public function init(){        
        if(defined('PERM_LVL')){
            if(PERM_LVL>0){
                $this->user_id = $_SESSION['user_id'];
                $this->isset_id = true;
            }
        }
        
        
        if(isset($this->request['operation'])){
            
            if($this->request['operation'] == 'auth') $this->checkUser();
            if($this->request['operation'] == 'register') $this->register();
            if($this->request['operation'] == 'activate') $this->activate();
            if($this->request['operation'] == 'getRefererId') $this->getRefererId();
            if($this->request['operation'] == 'getUserAddress') $this->getUserAddress ();
            if($this->request['operation'] == 'issetEmail') $this->issetEmail();
            if($this->request['operation'] == 'changePassword'){
                $this->changePassword($this->request['pass'], $this->request['new_pass'], $this->request['new_pass_r']);
            }
            if($this->request['operation'] == 'changeEmail'){
                $this->changeEmail($this->request['email']);
            }
            if($this->request['operation'] == 'getUser'){
                $this->getUser($this->request['id']);
            }
            if($this->request['operation'] == 'deleteUser'){
                $this->deleteUser($this->request['id']);
            }
            if($this->request['operation'] == 'makeContractor'){
                $this->makeContractor($this->request['id']);
            }
            if($this->request['operation'] == 'makeAdmin'){
                $this->makeAdmin($this->request['id']);
            }
        }
    }

    public function hashPassword($pass){
        return hash('sha256', $pass.USER_SALT);
    }

    public function checkUser(){
        $conds = array(
            0 => array('conVar' => 'email', 'operator' => '=', 'conValue' => $this->request['email'], 'condition' => 'AND'),
            1 => array('conVar' => 'reg_status', 'operator' => '>', 'conValue' => 0)
        );
        $res = $this->selectLimitSQL('klienci', $conds, 1, 'id, email, haslo_hash, reg_status, imie');
        if(isset($res[0]['haslo_hash'])){
            if($res[0]['haslo_hash'] == $this->hashPassword($this->request['pass'])){
                $this->authUser($res);
                $this->put('result', 1);
                $this->put('user', $res);
                return 1;
            }else{
                $this->put('result', 0);
                return 0;
            }
        }else{
            $this->put('result', 0);
            return 0;
        }
    }
    
    public function changePassword($pass, $new_pass, $new_pass_r){
        if($new_pass === $new_pass_r && $this->isset_id){
            $old_pass_hash = $this->hashPassword($pass);
            $new_pass_hash = $this->hashPassword($new_pass);
            $data = array(
                'haslo_hash' => $new_pass_hash
            );
            $condition = array(
                0 => array('conVar' => 'id', 'operator' => '=', 'conValue' => $this->user_id, 'condition' => 'AND'),
                1 => array('conVar' => 'haslo_hash', 'operator' => '=', 'conValue' => $old_pass_hash)
            );
            
            $this->updateSQL('klienci', $data, $condition);
            
            $condition = array(
                0 => array('conVar' => 'id', 'operator' => '=', 'conValue' => $this->user_id, 'condition' => 'AND'),
                1 => array('conVar' => 'haslo_hash', 'operator' => '=', 'conValue' => $new_pass_hash)
            );
            $check = $this->selectLimitSQL('klienci', $condition, 1, 'id');
            if(isset($check[0]['id'])){
                $this->put('result', 1);
            }else{
                $this->put('result', 0);
            }
           
        }
    }
    
    public function changeEmail($new_email){
        $res = $this->issetRecord('klienci', 'email', 'email', $new_email);
        if(!isset($res[0]['email'])){
            $data = array(
            'email' => $new_email
            );
            $condition = array(
                0 => array('conVar' => 'id', 'operator' => '=', 'conValue' => $this->user_id)
            );

            $this->updateSQL('klienci', $data, $condition);
            $this->put('result', 1);
        }else{
            $this->put('result', 0);
        }
        
        
          
    }
    
    public function issetEmail(){
        $res = $this->issetRecord('klienci', 'id,email', 'email', $this->request['email']);
        $this->put('result', $res);
    }

    public function authUser($res){        
        unset($_SESSION['ulevel']);
        unset($_SESSION['user_id']);
        unset($_SESSION['imie']);
        $_SESSION['imie'] = $res[0]['imie'];
        $_SESSION['user_id'] = $res[0]['id'];
        $_SESSION['ulevel'] = $res[0]['reg_status'];
    }

    public function register(){
        $this->copyData();
        if(!$this->fieldError){
            $res = $this->insertSQL('klienci', $this->request['data']);
            $this->put('result', $res);
        }   
        
    }
    
    public function getRefererId(){
        $user_id = $this->request['user_id'];
        $out = $this->issetRecord('klienci', 'referer_id', 'id', $user_id);
        $ref_id = 0;
        if(isset($out[0]['referer_id'])){
            $ref_id = $out[0]['referer_id'];
        }
        $this->put('result', $out[0]['referer_id']);        
    }
    
    public function getUserAddress(){
        $user_id = $this->request['user_id'];
        $out = $this->issetRecord('klienci', 'imie, nazwisko, ulica, numer_budynku, numer_mieszkania, miasto, pocztowy, telefon', 'id', $user_id);
        if(isset($out[0]['imie'])){
            $this->put('result', $out);
        }else{
            $this->put('result', 0);
        }
        
    }
    
    
    
    
    public function activate(){
        $user_id = $this->request['user_id'];
        $token = $this->request['token'];
        $condition = array(
            0 => ['conVar' => 'id', 'operator' => '=', 'conValue' => $user_id]
        );
        $res = $this->selectLimitSQL('klienci', $condition, 1, 'id ,email, reg_ip');
        $activated = 0;
        if($res){                        
            if($this->activateLink($res[0]['email'], $res[0]['reg_ip']) == $token){                      
                $activated = $this->updateSQL('klienci', ['reg_status' => '1'], $condition);
            }
        }
        $this->put('activated', $activated);
    }
    
    public function activateLink($email, $reg_ip){
        return md5($reg_ip.$email.EMAIL_SALT);
    }
    
    public function makeContractor($id){
        $id = (int)$id;
        $data = array(
            'reg_status' => 2
        );
        $condition = array(
            0 => ['conVar' => 'id', 'operator' => '=', 'conValue' => $id]
        );
        $res = $this->updateSQL('klienci', $data, $condition);
        $this->put('result', $res);
    }
    
    public function makeAdmin($id){
        $id = (int)$id;        
        $data = array(
            'reg_status' => 10
        );
        $condition = array(
            0 => ['conVar' => 'id', 'operator' => '=', 'conValue' => $id]
        );
        $res = $this->updateSQL('klienci', $data, $condition);
        $this->put('result', $res);
    }
    
    public function getUser($id){
        $id = (int)$id;
        $res = $this->issetRecord('klienci', '*', 'id', $id);
        $this->put('result', $res);
    }
    
    public function deleteUser($id){
        $id = (int)$id;
        $condition = array(
            0 => ['conVar' => 'id', 'operator' => '=', 'conValue' => $id]
        );
        $this->deleteSQL('klienci', $condition);
    }


    public function copyData(){
        foreach($this->reqFields as $fieldName){
            if(isset($this->request['data'][$fieldName])){
                $this->data[$fieldName] = $this->request['data'][$fieldName];
            }else{
                //echo 'FieldError przy: '.$fieldName.'<br>';
                $this->fieldError = 1;
            }
        }

    }


}
