<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * SessionHandler zapewnia komunikacje między mechanizmem sesji PHP, a
 * mechanizmiem zaimplementowanym przez framework.
 *
 * @author sheriff
 */

class CF_sessionHandler implements SessionHandlerInterface {  

    private $pdo;
    

    public function __construct($pdo){
        $this->pdo = $pdo;
        
    }//end __construct();


    public function open($savePath, $sessionName){
        //-
        return true;
    }//end open();

    public function close(){
        return true;
    }// end close();

    public function read($id){
        $stmt = $this->pdo->prepare('SELECT data FROM `sessions` WHERE session_id = :sess_id;');
        $stmt -> bindValue(':sess_id', $id, PDO::PARAM_STR);
        $stmt -> execute();
        $DBout = $stmt -> fetchAll();
        $stmt -> closeCursor();
        if (isset($DBout[0]['data'])) return $DBout[0]['data'];
        return false;
    }//end read();

    public function write($id, $data){
        $stmt = $this->pdo->prepare('SELECT session_id FROM `sessions` WHERE session_id = :sess_id;');
        $stmt -> bindValue(':sess_id', $id, PDO::PARAM_STR);
        $stmt -> execute();
        $DBout = $stmt -> fetchAll();
        $stmt -> closeCursor();

        if (!empty($DBout[0][0])){ //czy sesja istnieje w bazie?
            $stmt = $this->pdo->prepare('UPDATE `sessions` SET data = :data WHERE session_id = :sess_id;');
            $stmt -> bindValue(':data', $data, PDO::PARAM_STR);
            $stmt -> bindValue(':sess_id', $id, PDO::PARAM_STR);
            $queryResult = $stmt -> execute();
            $stmt -> closeCursor();
            return $queryResult;
        }else{
            $stmt = $this->pdo->prepare('INSERT INTO `sessions` VALUES(:sess_id, :data, NULL);');
            $stmt -> bindValue(':sess_id', $id, PDO::PARAM_STR);
            $stmt -> bindValue(':data', $data, PDO::PARAM_STR);
            $queryResult = $stmt -> execute();
            $stmt -> closeCursor();
            return $queryResult;
        }
    }//end write();

    public function destroy($id){
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE session_id = :sess_id;');
        $stmt -> bindValue(':sess_id', $id, PDO::PARAM_STR);
        $execResult = $stmt -> execute();
        $stmt -> closeCursor();
        return $execResult;
    }//end destroy();

    public function gc($maxlifetime){
        //echo 'Garbage Collection';

        return true;
    }//end gc();



}//end class sessionHandler
?>
