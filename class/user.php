<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of user
 *
 * @author sheriff
 */
class user {

    public $userID; //id użytkownika (dostępne po funkcji copyData())

    public $userName; //nazwa użytkownika (dostępne po funkcji copyData())

    public $userData = array(
            'id' => '',
            'imie' => '',
            'nazwisko'  => '',
            'plec' => '',
            'data_urodzenia' => '',
            'ulica' => '',
            'nr' => '',
            'miasto' => '',
            'kod_pocztowy' => '',
            'nr_telefonu' => '',
            'email'  => '',
            'haslo_hash' => '',
            'reg_ip' => '',
            'data_rejestracji'  => '',
            'reg_status' => '',
        );

    private $pdo;

    public $dataCompleted = false;


    public function  __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getUserData(){
        $sqlQuery = 'SELECT * FROM klienci WHERE ';
        $paramsCounter = 0;
        foreach($this->userData as $key=>$row){
            if (!empty($row)){
                $sqlQuery .= $key.' = :'.$key.' AND ';
                $paramsCounter++;
            }
        }

        if ($paramsCounter >= 1){
            $sqlQuery = substr($sqlQuery, 0, -5);
            $sqlQuery .= ';';
            $stmt = $this->pdo->prepare($sqlQuery);
            foreach($this->userData as $key=>$row){
                if (!empty($row)) $stmt -> bindValue(':'.$key, $row, PDO::PARAM_STR);
            }
            $stmt -> execute();
            $DBOut = $stmt -> fetchAll();
            $stmt->closeCursor();
            $outCounter = $allOutCounter = 0;
            if (isset($DBOut[0])){
            foreach($DBOut[0] as $key=>$row){
               if (!is_int($key)){
                   $this->userData[$key] = $DBOut[0][$key];
                   $outCounter++;
               }
               $allOutCounter++;
            }
            }
            if ($outCounter == ($allOutCounter/2) && $allOutCounter !== 0){
                $DBCount = count($DBOut);
                unset($DBOut);
                $this->dataCompleted = true;
                return $DBCount;
            }
        }
        $this->dataCompleted = false;
        return 0;

        
    }//end getUserData()


    public function registerUser(){
        $sqlQuery = 'INSERT INTO klienci (id, ';
        foreach($this->userData as $key=>$row){
            if (!empty($row)) $sqlQuery .= $key.', ';
        }
        $sqlQuery = substr($sqlQuery, 0, -2);
        $sqlQuery .= ') VALUES(NULL, ';
        foreach($this->userData as $key=>$row){
            if (!empty($row)) $sqlQuery .= ':'.$key.', ';
        }
        $sqlQuery = substr($sqlQuery, 0, -2);
        $sqlQuery .= ');';
        echo $sqlQuery;
        
        $stmt = $this->pdo->prepare($sqlQuery);
        $fieldCount = 0;
        foreach($this->userData as $key=>$row){
            
           if (!empty($row)){
               $fieldInsert = $row;
               //if ($key == 'haslo_hash') $fieldInsert = $this->hashPassword ($row);
               $stmt -> bindValue(':'.$key, $fieldInsert, PDO::PARAM_STR);
           }
           $fieldCount++;
        }
        $queryResult = $stmt -> execute();
        $stmt -> closeCursor();
        return $queryResult;
    }//end registerUser()


    public function updateUser($updateData, $conditions){
        $this->getUserData();
        $sqlQuery = 'UPDATE `klienci` SET ';
        foreach($updateData as $key => $row){
            $sqlQuery .= $key.' = :'.$key.', ';
        }
        $sqlQuery = substr($sqlQuery, 0, -2);
        $sqlQuery .= ' WHERE ';
        foreach($conditions as $key => $row){
            $sqlQuery .= $key.' = :'.$key.' AND ';
        }
        $sqlQuery = substr($sqlQuery, 0, -5);
        $sqlQuery .= ';';
        $stmt = $this->pdo->prepare($sqlQuery);
        $bindArray = array_merge($updateData, $conditions);
        foreach($bindArray as $key => $row){
            $stmt -> bindValue(':'.$key, $row, PDO::PARAM_STR);
        }
        $queryResult = $stmt -> execute();
        $stmt->closeCursor();
        return $queryResult;
    }


    public function hashPassword($pass){
        return hash('sha256', $pass.USER_SALT);
    }// end hashPassword()
    

    public function checkUser($email, $pass){
        foreach ($this->userData as $key=>$row){
            $this->userData[$key] = '';
        }
        $this->userData['email'] = $email;
        $getResult = $this->getUserData();
        if ($getResult == 1){
            if ($this->userData['haslo_hash'] == $this->hashPassword($pass)){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
        return 0;
    }//end checkUser()

    public function issetEmail($email){
        foreach ($this->userData as $key=>$row){
            $this->userData[$key] = '';
        }
        $this->userData['email'] = $email;
        $getResult = $this->getUserData();
        if ($getResult >= 1){
            return 1;
        }else{
            return 0;
        }
        
    }//end issetEmail()

    public function copyData(){
        if($this->dataCompleted){
            if(isset($this->userData['klient_id'])) $this->userID = $this->userData['klient_id'];
            if(isset($this->userData['email'])) $this->userID = $this->userData['email'];
        }
    }//end copyData()

    public function authUser($email, $pass){
        unset($_SESSION['ulevel']);
        unset($_SESSION['user_id']);
        unset($_SESSION['imie']);
        if($this->checkUser($email, $pass)){            
            $_SESSION['imie'] = $this->userData['imie'];
            $_SESSION['user_id'] = $this->userData['id'];
            $_SESSION['ulevel'] = $this->userData['reg_status'];
            return 1;
        }
        return 0;
    }//end authUser()



}
?>
