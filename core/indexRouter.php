<?php
/**
 * Index router
 * Crystal Framework
 *
 * @author Krystian Biela <1bitam1@gmail.com>
 * @version 0.3
 * @copyright Copyright (c) Krystian Biela 2014
 */
	
ini_set('display_errors', '0');
define('ERR_PREFIX', '(frontController.php) Front controller: ');


/**
 * Autoloader of libs
 * @param string $name Name of lib to load
 */
function __autoload($name){
               $filename = './class/'.$name.'.php';
               if(file_exists($filename)) {
                    require_once($filename);
                }
            }//end __autoload()




class indexRouter{		    
    /**
     * Controller name parsed from URL
     * @var string
     */
    public $ctrlName;
    /**
     * A reference to controller object
     * @var object
     */
    public $ctrlObject;
    /**
     * Action name parsed from URL
     * @var string 
     */
    public $ctrlAction;
    /**
     * Additional params parsed from URL
     * @var string
     */    
    public $params;    
    /**
     * Obiekt widoku
     * @var object
     */
    public $view;
    /**
     * Database object - PDO
     * @var object
     */
    public $PDObject;
    /**
     * Path of the configuration file
     * @var string
     */
    public $configPath;
    /**
     * Is config loaded? 1=yes, 0=no
     * @var bool
     */
    public $configLoaded = 0;
    /**
     * Session object
     * @var object
     */
    public $session;
    /**
     * Permissions level (e.g. 0 = guest, 10 = admin)
     * @var integer
     */
    public $permsLevel = 0;
    /**
     * URL address of HTTP request
     * @var string
     */
    public $requestURL;
    /**
     * Array of collected errors
     * @var array
     */
    public $errors = array();
    /**
     * List of tasks to do by router class
     * @var array
     */
    public $routerTasks = array(

        'configCompleted' => '0', //controller configuration

        'requestParsed' => '0', //parse URL request

        'dbInit' => '0', //init DB connection

        'authUser' => '0', //start session mechanism

        'ctrlSelected' => '0', //select controller and run it

        'viewExecuted' => '2', //select view and execute it
        
        'workCompleted' => '0', //the end
        
        );

        /**
         * $routerTasks codes
         * 0 = procedure not executed
         * 1 = procedure executed and completed
         * 2 = procedure executed, but failed
         * 
         */

    /**
     * Create indexRouter instantion - main class of framework
     *
     * @param string $cfgPath
     */
    public function __construct($cfgPath = null){

            $this->requestURL = $_SERVER['REQUEST_URI'];

            if (file_exists('./config/crystal.config.php')){
                    require_once('./config/crystal.config.php');
                    $this->configLoaded = 1;
            }else{
                            if(file_exists($cfgPath)){
                                    require_once($cfgPath);
                          $this->configLoaded = 1;
                                    }else{
                                            $this->configLoaded = 0;
                                            $this->errors[] = ERR_PREFIX.'Plik konfiguracyjny nie zaladowany przy inicjalizacji index routera.';
                                            }
                    }


             if ($this->configLoaded == 1){
                     if (IS_DEVELOP == 1) {
                     ini_set('display_errors', '1');
                     } else{
                         ini_set('display_errors', '0');
                     }
             }
              
             

    }//end __construct()

    /**
     * It reloads config on demand
     * @return bool
     */
    public function reloadConfig(){
        if($this->configLoaded == 0){
            if(file_exists($this->configPath)){
                     require_once($this->configPath);
                     $this-> configLoaded = 1;
                     return 1;
                     }else{
                         return 0;
                     }
        }
    }//end reload()

    /**
     * It parses request and saves results to indexRouter class fields
     * @return bool
     */
    public function parseRequest(){
        $rqst = $this->requestURL;
        $rqst = (parse_url($rqst, PHP_URL_PATH));
        $rqst[0] = '';
        $reqArray = array();
        $reqArray = explode('/', $rqst);
        $reqArray[0] = substr($reqArray[0], 1);        
        if (!empty($reqArray[MODEL_PATH_INDEX]) && preg_match('/^[a-zA-Z0-9-]{1,90}$/', $reqArray[MODEL_PATH_INDEX])){
            $this-> ctrlName = $reqArray[MODEL_PATH_INDEX];
            }else{
                $this->ctrlName = 'index';
            }
        if (isset($reqArray[MODEL_PATH_INDEX + 1]) && preg_match('/^[a-zA-Z0-9-,]{1,90}$/', $reqArray[MODEL_PATH_INDEX + 1])) $this-> ctrlAction = $reqArray[MODEL_PATH_INDEX + 1];
        if (isset($reqArray[MODEL_PATH_INDEX + 2]) && preg_match('/^[a-zA-Z0-9-,]{1,120}$/', $reqArray[MODEL_PATH_INDEX + 2])) $this-> params = $reqArray[MODEL_PATH_INDEX + 2];
        return 1;
    }//end parseRequest()

       
     /**
      * Starts the session and saves object to index router field
      * @return bool
      */
     public function sessionControll(){
         $this->session = new userSession($this->PDObject);         
         return $this->session->create();
     }//end sessionControll()

     /**
      * Procedure creates new instance of token object
      * Saves token to index router field
      * @return bool
      */
     public function pushToken(){
         $tkn = new secToken();
         echo $tkn -> push();

         return 1;
     }//end pushToken()

     /**
      * Select controller
      * @return bool
      */
     public function ctrlSelect(){
        $ctrlName = $this->ctrlName;
        if (isset($ctrlName)){
            $includeFile = './controllers/'.$ctrlName.'.php';
            if (file_exists($includeFile)){
                require_once($includeFile);                
                $ctrlObject = new $ctrlName($this);
                return 1;
            }
        }
        return 0;
     }//end ctrlSelect()

     /**
      * Init new view object instance
      * @return bool
      */
     public function viewExec(){
        $this->view = new view();
        if($this->view->init($this->modelOutput)){
            unset($this->modelOutput);
            if($this->view->select()) return 1;
        }
        return 0;

     }//end viewExec()

     /**
      * Init PDO database connection
      * @return bool
      */
     public function connectDB(){
       try
        {
          $this->PDObject = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        }
       catch(PDOException $e)
       {
          $this->errors[] = ERR_PREFIX.$e;
          return 0;
       }
       return 1;
     }//end connectDB()

     
     /**
      * Index router tasks controller
      * @return boll
      */
     public function controller(){

         if ($this->configLoaded == 1){
              $this->routerTasks['configCompleted'] = '1';
         }else{
             return 0;
         }
         if ($this->routerTasks['configCompleted'] == '0'){
             $this->routerTasks['configCompleted'] = '2';
             if ($this->reloadConfig()){
                  $this->routerTasks['configCompleted'] = '1';
             }else{
                 return 0;
             }
         }

         if ($this->routerTasks['requestParsed'] == '0'){
             if($this->parseRequest()){
                  $this->routerTasks['requestParsed'] = '1';
             }else{
                  $this->routerTasks['requestParsed'] = '2';
             }
         }

         if ($this->routerTasks['dbInit'] == '0'){
             if($this->connectDB()){
                  $this->routerTasks['dbInit'] = '1';
             }else{
                  $this->routerTasks['dbInit'] = '2';
             }
         }

          if ($this->routerTasks['authUser'] == '0'){
             if($this->sessionControll()){
                  $this->routerTasks['authUser'] = '1';
             }else{
                  $this->routerTasks['authUser'] = '2';
             }
         }
 

         if ($this->routerTasks['ctrlSelected'] == '0'){
             if($this->ctrlSelect()){
                 $this->routerTasks['ctrlSelected'] = '1';
             }else{
                 $this->routerTasks['ctrlSelected'] = '2';
             }
         }

         
         if ($this->routerTasks['viewExecuted'] == '0'){
             if($this->viewExec()){
                 $this->routerTasks['viewExecuted'] = '1';
             }else{
                 $this->routerTasks['viewExecuted'] = '2';
             }
         }

        

         $this->routerTasks['workCompleted'] = '1';
         return 1;



     }//end controller()

			
}//end frontController
	
?>