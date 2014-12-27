<?php

class index extends ctrl{
    
    
    public function init(){
        $this->put('title', PROJECT_NAME.' v'.PROJECT_VER);
        $this->addHeadObject('css', _ROOT.'css/bootstrap.min.css');
        $this->collectInfo();
        
    }
    
    public function infoAction(){
        phpinfo();
    }
    
    public function checkEnv(){
        
    }
    
    public function collectInfo(){
        $php_version = phpversion();
        $pdo_drivers = pdo_drivers();
        print_r($pdo_drivers);
    }    
    
    public function finish(){
        $this->viewName('index_view');
        $this->execView();
    }

}