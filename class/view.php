<?php
/**
 * Main view class
 * Crystal Framework
 *
 * @author Krystian Biela <1bitam1@gmail.com>
 * @version 0.3
 * @copyright Copyright (c) Krystian Biela 2013
 */
class view {

    /**
     * Name of the view
     * @var string
     */
    public $viewName;

    /**
     * Site title (<title></title>)
     * @var string
     */
    public $title;

    /**
     * Data recevied from controller
     * @var array
     */

    public $data = array(); 

    /**
     * Is a mobile device/webbrowser? true=yes, false=no
     * @var bool
     */

    public $isMobile = false; 

    /**
     * Function inits the view.
     * @param array $viewData The array filled by data to view
     * @return bool
     */

    public function init($viewData){
       $this->data = $viewData;
       return 1;
    }

    /**
     * Magic method used to showing the variables content
     * @param string $name Name of field to show in view
     * @param string $arguments Not used
     */

    public function  __call($name, $arguments) {
        if($name[0] == 'v'){            
            $methodName = substr($name, 1, 100);
            if(isset($this->data[$methodName])){
                echo $this->data[$methodName];
            }
        }
    }

    /**
     * Adds fields to viewData array
     * @param string $key A key of the record
     * @param string $value A value of the record
     */

    public function add($key, $value){
        $this->data[$key] = $value;
    }

    /**
     * Include to view template other HTML template
     * @param string $tplName The name of template to insert
     * @return bool
     */

    public function insert($tplName){
        $filename = './templates/'.$tplName.'.tpl.php';
        if (file_exists($filename)){
            include_once($filename);            
        }
        return 0;
    }

    /**
     *
     * @param <type> $tplName
     * @return <type> 
     */

    public function insertSubpage($tplName){
        $filename = './subpages/'.$tplName.'.tpl.php';
        if (file_exists($filename)){
            include_once($filename);
            return 1;
        }
        return 0;
    }

    public function insertIf($tplName, $ifCondition){
        $filename = './templates/'.$tplName.'.tpl.php';
        if (file_exists($filename) && isset($this->data[$ifCondition])){
            include_once($filename);
            return 1;
        }
        return 0;
    }

    public function insertIfNot($tplName, $ifCondition){
        $filename = './templates/'.$tplName.'.tpl.php';
        if (file_exists($filename) && !isset($this->data[$ifCondition])){
            include_once($filename);
            return 1;
        }
        return 0;
    }

    private function detectMobileDevice(){
        $iphone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
        $android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
        $palmpre = strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
        $berry = strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
        $ipod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
        if ($iphone || $android || $palmpre || $ipod || $berry == true){
            $this->isMobile = true;
        }
    }//end detectMobileDevice()

    public function title(){
        echo $this->title;
    }//end title()

    public function select(){
        $this->detectMobileDevice();
        $filename = './views/'.$this->viewName.'.php';        
        if(file_exists($filename)){
            require($filename);
            return 1;
        }
        return 0;
    }//end select()

    public function getHeader(){        
        if(isset($this->data['logout_bool'])) return 'header_guest';
        if(isset($_SESSION['user_id']) || isset($_SESSION['ulevel'])){
            if($_SESSION['ulevel'] == 2) return 'header_partner';
            if($_SESSION['ulevel'] == 1) return 'header';
            if($_SESSION['ulevel'] == 10) return 'header_admin';
        }else{          
            return 'header_guest';
        }
    }//end getHeader()

    public function getTabContent(){
        if (isset($this->data['tabname'])) return $this->data['tabname'];
        return 0;
    }


}