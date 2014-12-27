<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of test
 *
 * @author sheriff
 */
class filter extends helper {

    public $isFiltered = 0;

    public $filtered = array();    

    public $context = '';

    public $matchFail = 0;

    public function init(){
        if (isset($this->request['context'])) $this->context = $this->request['context'];
        $this->parse();        
    }

    public function parse(){
        if (isset($_GET) && !empty($_GET)) $this->parseGET();
        if (isset($_POST) && !empty($_POST)) $this->parsePOST();
        if (isset($_SESSION) && !empty($_SESSION)) $this->parseSESSION();
    }

    public function parseGET(){        
        foreach($_GET as $name=>$value){
            $methodName = $name.$this->context.'Filter';
            if (method_exists($this, $methodName)) $this->$methodName($name, $value);
        }
    }

    public function parsePOST(){

    }

    public function parseSESSION(){
        
    }

    public function pm($input, $preg_pattern){
        if(!preg_match($preg_pattern, $input)) $this->matchFail = true;
        return $this->matchFail;
    }

    public function emailFilter($varname, $value){
        $this->pm($value, EMAIL_RE);
        if ($this->matchFail == false) $this->filtered[$varname] = $value;
    }

    public function emailRegisterFilter($varname, $value){
        echo 'Kontekst register działa dla email';
    }

    public function emailAuthFilter($varname, $value){
        echo 'Kontekst auth działa dla email';
    }

}
