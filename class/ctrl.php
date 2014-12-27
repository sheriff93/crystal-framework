<?php
/**
 * Main controller class
 * Crystal Framework
 *
 * @author Krystian Biela <1bitam1@gmail.com>
 * @version 0.3
 * @copyright Copyright (c) Krystian Biela 2013
 */



class ctrl {


    public $name;

    public $models = array();

    public $helpers = array();

    public $view;

    public $viewName;

    public $pdo;

    public $viewData = array('headincludes' => null);

    public $action;

    public $errors = array();

    public function  __construct($ir = null) {
        $this->pdo = $ir->PDObject;
        $this->name =  $ir->ctrlName;
        $this->action = $ir->ctrlAction;        
        if ($this->action == '') $this->action = 'index';
        if (method_exists($this, 'init')) $this->init();
        if (isset($this->models['perms']->modelAnswer['userAllowed'])
                && $this->models['perms']->modelAnswer['userAllowed'] == 0){
            $this->viewName = 'denied';
            $this->execView();
            exit;
        }
//        if (method_exists($this, 'indexAction') && $this->action == 'index'){
//            $this->indexAction();
//        }
        $requestAction = $this->action.'Action';
        if (method_exists($this, $requestAction)){            
            $this->$requestAction();
        }
        if (method_exists($this, 'finish')){
            $this->finish();
        }
    }
    
    public function addModel($modelName, $modelRequest = array()){
        $this->models[$modelName] = array($modelName, $modelRequest);
        return 1;
    }

    public function addHelper($helperName, $helperRequest = array()){
        $this->helpers[$helperName] = array($helperName, $helperRequest);
        return 1;
    }
    
    public function execHelper($helperName, $helperRequest = array()){
        if(file_exists('./helpers/'.$helperName.'.php')){
                require_once('./helpers/'.$helperName.'.php');
                $this->helpers[$helperName] = new $helperName($this->pdo, $helperRequest);
                return 1;
            }else{
                return 0;
            }
    }
    
    public function execModel($modelName, $modelRequest = array()){
        if(file_exists('./models/'.$modelName.'.php')){
                require_once('./models/'.$modelName.'.php');
                $this->models[$modelName] = new $modelName($this->pdo, $modelRequest);
                return 1;
            }else{
                return 0;
            }
    }

    public function execModels(){
        foreach($this->models as $i => $modelArray){
            $modelName = $modelArray[0];
            $modelRequest = $modelArray[1];
            if(file_exists('./models/'.$modelName.'.php')){
                require_once('./models/'.$modelName.'.php');
                $this->models[$modelName] = new $modelName($this->pdo, $modelRequest);
            }else{
                return 0;
            }
        }
        return 1;
    }

    public function execHelpers(){
        foreach($this->helpers as $i => $helpersArray){
            $helperName = $helpersArray[0];
            $helperRequest = $helpersArray[1];
            if(file_exists('./helpers/'.$helperName.'.php')){
                require_once('./helpers/'.$helperName.'.php');
                $this->helpers[$helperName] = new $helperName($this->pdo, $helperRequest);
            }else{
                return 0;
            }
        }
        return 1;
    }

    public function execView(){
        $this->view = new view();
        $this->view->viewName = $this->viewName;
        if($this->view->init($this->viewData)){
            if($this->view->select()) return 1;
        }        
        return 0;
    }

    public function addHeadObject($type, $data){
        $objects = array(
            'js' => '<script charset="utf-8" src="'.$data.'"></script>',
            'css' => '<link rel="stylesheet" href="'.$data.'" />',
            'fav' => '<link rel="shortcut icon" href="'.$data.'" type="image/x-icon" />',
            'title' => '<title>'.$data.'</title>'
            );
        $this->viewData['headincludes'] .= $objects[$type]."\r\n";
    }    

     public function put($putName, $putContent){
        $this->viewData[$putName] = $putContent;
        return 1;
    }

    public function setPerms(){
        if(isset($this->data['logout_bool'])){
            $this->put('statusGuest', '1');
        }
        if(isset($_SESSION['user_id']) || isset($_SESSION['ulevel'])){
            define('PERM_LVL', $_SESSION['ulevel']);
            if(PERM_LVL == 2){
                $this->put('statusLogged', '1');                                
            }
            if(PERM_LVL == 1){
                $this->put('statusLogged', '1');
            }
            if(PERM_LVL == 10){
                $this->put('statusAdmin');
            }
            return 1;
        }else{
            define('PERM_LVL', 0);
            $this->put('statusGuest', '1');
            return 0;
        }
    }

    public function selectHeader(){
        $name = $this->getHeader();
        $this->put($name, '1');
    }

    public function viewName($vName){
        $this->viewName = $vName;
        return 1;
    }
    
    public function getIDFromAction(){
        if(isset($this->action)){
            if(!empty($this->action)){
                $id = (int)$this->action;
                return $id;
            }
        }
        return 0;
    }




}//end ctrl