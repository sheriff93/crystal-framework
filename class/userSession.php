<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Klasa userSession umożliwa obsługę sesji w sposób obiektowy.
 *
 * @author sheriff
 */

class userSession {

    public $userLevel = 0; //poziom uprawnień, 0 = gość

    public $sessionName = COOKIE_NAME;

    public $sessionID;

    public $userID;

    private $PDObject;


    public function __construct($pdo){
        $this->PDObject = $pdo;

        
        
        $handler = new CF_sessionHandler($this->PDObject);
        
        
        session_set_save_handler($handler, true);
        
        
        
        //session_set_save_handler(
        //array($handler, 'open'),
        //array($handler, 'close'),
        //array($handler, 'read'),
        //array($handler, 'write'),
        //array($handler, 'destroy'),
        //array($handler, 'gc')
        //);
        
        //register_shutdown_function('session_write_close');
        
    }//end __construct()


    public function create(){
        session_name($this->sessionName);
        session_start();
        $this->sessionID = session_id();
        
        if (isset($_SESSION['user_id'])){
            $this->userID = $_SESSION['user_id'];
            if (isset($_SESSION['ulevel'])) $this -> userLevel = $_SESSION['ulevel'];
            if (isset($_SESSION['security_counter'])){
                $session_sec_int = (int)$_SESSION['security_counter'];
                if ($session_sec_int < 1){
                    $this->regenerateID();
                }else{
                $session_sec_int--;
                $_SESSION['security_counter'] = $session_sec_int;
                }
            }else{
                $_SESSION['security_counter'] = ID_LIMIT;
            }
        }
        return 1; 
    }//end create();


    public function isAnonymous(){
        if ($this -> userLevel == 0) return 1;
        return 0;
    }//end isAnonymous()


    public function destroy(){
        session_destroy();
        $this->userLevel = 0;
    }//end destroy()

    public function regenerateID(){
        $oldSess = session_id();
        session_regenerate_id();

        $stmt = $this->PDObject->prepare('DELETE FROM sessions WHERE session_id = :sess_id');
        $stmt -> bindValue(':sess_id', $oldSess, PDO::PARAM_STR);
        $stmt -> execute();
        $stmt -> closeCursor();        
        $_SESSION['security_counter'] = ID_LIMIT;
    }//end regenerateID()

    


}//end class userSession()
?>
