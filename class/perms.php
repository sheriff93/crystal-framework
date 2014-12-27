<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of perms
 *
 * @author sheriff
 */
class perms {

    public $userid;

    public $userLevel;

    public $ctrlInstance;

    public function __construct(){
        if (isset($_SESSION['user_id']) && isset($_SESSION['ulevel'])){
            $this->userid = (int)$_SESSION['user_id'];
            $this->userLevel = (int)$_SESSION['ulevel'];
        }else{
            $this->userid = 0;
            $this->userLevel = 0;
        }
    }
        

    public function check($userid, $ulevel = 10){
        if ($userid == $this->userid && $this->userLevel >= $ulevel){
            return 1;
        }else{
            $this->ctrlInstance->errors[] = T_ACC_DENIED;
        }
    }

    public function checkLevel($ulevel){
        if($this->userLevel >= $ulevel){
            return 1;
        }else{
            die(T_ACC_DENIED);
        }
    }

}
?>
