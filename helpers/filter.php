<?php

/**
 * Description of test
 *
 * @author sheriff
 */


define('EMAIL_RE_REG', '/^([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})$/i');
define('EMAIL_RE_AUTH', '/^([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})$/i');



define('NAME_RE', '/^[a-ząęóżźćńłś]*$/i');


define('LASTNAME_RE', '/^[a-ząęóżźćńłś\-\s]*$/i');


define('ZIP_RE', '/^[0-9]{2}-[0-9]{3}$/');

define('TELEFON_RE', '/^[0-9]{9}$/');

define('ROK_RE', '/^(19[3-9][0-9])|(200[0-9])|(201[0-9])|(202[0-9])$/');

define('DZIEN_RE', '/^([1-9]|[12]\d|3[01])$/');

define('MIESIAC_RE','/^([1-9]|1[012])$/');
define('PLEC_RE','/^(m|k)$/');
define('ULICA_RE', '/^[a-ząęóżźćńłś\-\s\.1234567890]*$/i');
define('BUDYNEK_RE','/^[1-9]?\d{1,3}[a-z]{0,1}$/i');
define('MIESZKANIE_RE','/^[1-9]?\d{1,2}$/i');

define('PROCENT_RE','/^(0)|(100)|([1-9]{1}) |([1-9]{1}\d{1})$/i');
define('NAZWA_KATEGORII_RE', '/^[a-ząęóżźćńłś\-\s\d]*$/i');

define('NAZWA_WYPRZEDAZY_RE', '/^[a-ząęóżźćńłś\-\s\d]*$/i');
define('MILIONY_RE', '/^\d*$/');

define('NAZWA_PODSTRONY_RE', '/^[a-z\-\d]*$/i');

define('URL_RE','/(((http{1}:(?:\/\/)?)(?:[\-;:&=\+\$,\w]+@)?[A-Za-z0-9\.\-]+|(?:www\.|[\-;:&=\+\$,\w]+@)[A-Za-z0-9\.\-]+)((?:\/[\+~%\/\.\w\-_]*)?\??(?:[\-\+=&;%@\.\w_]*)#?(?:[\.\!\/\\\w]*))?)/');

class filter extends helper {

    public $isFiltered = 0;
    public $filtered = array();
    public $context = '';
    public $matchFail = 0;

    public function init() {
        if (isset($this->request['context'])) {
            $this->context = $this->request['context'];
        }
        $this->parse();
    }

    public function parse() {
        if (isset($_GET) && !empty($_GET)) {
            $this->parseGET();
        }
        if (isset($_POST) && !empty($_POST)) {
            $this->parsePOST();
        }
        
    }


    public function sanitize_input_data($data) {
        $data = trim($data);
        $data = htmlspecialchars($data);
        return $data;
    }


    public function parseGET() {
        foreach ($_GET as $name => $value) {
            $methodName = $name . $this->context . 'Filter';
            if (method_exists($this, $methodName)) {
                $this->$methodName($name, $this->sanitize_input_data($value));
            } else {
                $methodName = 'unknownType' . $this->context . 'Filter';
                $this->$methodName($name, $this->sanitize_input_data($value));
            }
        }
    }

    public function parsePOST() {
        foreach ($_POST as $name => $value) {
            $methodName = $name . $this->context . 'Filter';
            if (method_exists($this, $methodName)) {
                $this->$methodName($name, $this->sanitize_input_data($value));
            } else {
                $methodName = 'unknownType' . $this->context . 'Filter';
                $this->$methodName($name, $this->sanitize_input_data($value));
            }
        }
    }

    public function parseSESSION() {
        foreach ($_SESSION as $name => $value) {
            $methodName = $name . $this->context . 'Filter';
            if (method_exists($this, $methodName)) {
                $this->$methodName($name, $this->sanitize_input_data($value));
            } else {
                $methodName = 'unknownType' . $this->context . 'Filter';
                $this->$methodName($name, $this->sanitize_input_data($value));
            }
        }
    }

    //sprawdza czy podana liczba miesci sie w podanym zakresie
    public function chk_num($input, $min_value, $max_value) {
      $input = (int) $input;
        //echo $input;
        if ($input < $min_value || $input > $max_value) {
            $this->matchFail = true;
        }
        return $this->matchFail;
    }

    //sprawdza czy podane wyrazenie miesci sie w podanej dlugosci od do
    // i sprawdza czy zgodne jest ze wzorcem
    public function pm($input, $preg_pattern, $min_len, $max_len) {
          $len = mb_strlen($input, 'UTF-8');
        if (($len < $min_len) || ($len > $max_len) || (empty($input))) {
            $this->matchFail = true;
            return $this->matchFail;
        }
        if (!preg_match($preg_pattern, $input)) {
            //echo $input.'<br>';
            $this->matchFail = true;
        }
        return $this->matchFail;
    }

    public function validateNIP($value) {
        $value = preg_replace("/[^0-9]+/", "", $value);
        $value = (int) $value;
        if ((strlen($value) != 10) OR ( $value == '0000000000' ) OR ( !is_int($value) )) {
            $this->matchFail = true;
            return $this->matchFail;
        }

        $wagi = array(6, 5, 7, 2, 3, 4, 5, 6, 7);
        $suma = 0;
        for ($i = 0; $i < 9; $i++) {
            $suma += $wagi[$i] * $value[$i];
        }
        $int = $suma % 11;

        $lkontrolna = ($int == 10) ? 0 : $int;
        if ($lkontrolna == $value[9]) {
            $this->matchFail = false;
        } else {
            $this->matchFail = true;
        }
        return $this->matchFail;
    }


    public function unknownTypeFilter($varname, $value) {
        $this->matchFail = true;
        $this->filtered[$varname] = 'Bzdury(Niedopuszczone pole) o długości:' . (string) (strlen($value));

    }

////Filtrowanie pola e-mail
    public function emailFilter($varname, $value) {
        $this->pm($value, EMAIL_RE, 6, 55);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

//Filtrowanie pola imienia
    public function imieFilter($varname, $value) {
        $this->pm($value, NAME_RE, 3, 50);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

//Filtrowanie nazwiska
    public function nazwiskoFilter($varname, $value) {
        $this->pm($value, LASTNAME_RE, 2, 50);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

//Filtrowanie kodu pocztowego
    public function kodFilter($varname, $value) {
        $this->pm($value, ZIP_RE, 6, 6);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

//Filtrowanie numeru telefonu
    public function telefonFilter($varname, $value) {
        $this->pm($value, TELEFON_RE, 9, 9);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

//walidacja polskiego NIP
    public function nipFilter($varname, $value) {
        $this->validateNIP($value);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function rokFilter($varname, $value) {
        $this->pm($value, ROK_RE, 4, 4);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function dzienFilter($varname, $value) {
        $this->pm($value, DZIEN_RE, 1, 2);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function miesiacFilter($varname, $value) {
        $this->pm($value, MIESIAC_RE, 1, 2);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function rok_rozpFilter($varname, $value) {
        $this->pm($value, ROK_RE, 4, 4);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function dzien_rozpFilter($varname, $value) {
        $this->pm($value, DZIEN_RE, 1, 2);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function miesiac_rozpFilter($varname, $value) {
        $this->pm($value, MIESIAC_RE, 1, 2);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function plecFilter($varname, $value) {
        $this->pm($value, PLEC_RE, 1, 1);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function ulicaFilter($varname, $value) {
        $this->pm($value, ULICA_RE, 3, 50);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function numer_budynkuFilter($varname, $value) {
        $this->pm($value, BUDYNEK_RE, 1, 5);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function numer_mieszkaniaFilter($varname, $value) {        
        if ($this->matchFail == false && (is_int($varname) || empty($varname))) {
            $this->filtered[$varname] = $value;
        }
    }
    public function procentFilter($varname, $value) {
        $this->pm($value, PROCENT_RE, 1, 3);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function nazwa_kategoriiFilter($varname, $value) {
        $this->pm($value, NAZWA_KATEGORII_RE, 3, 25);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function kategoria_idFilter($varname, $value) {
        $this->pm($value, MILIONY_RE, 1, 7);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function liczba_sztukFilter($varname, $value) {
        $this->pm($value, MILIONY_RE, 1, 7);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function cena_przedFilter($varname, $value) {
        $this->pm($value, MILIONY_RE, 1, 7);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function cena_poFilter($varname, $value) {
        $this->pm($value, MILIONY_RE, 1, 7);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function nazwa_podstronyFilter($varname, $value) {
        $this->pm($value, NAZWA_PODSTRONY_RE, 1, 30);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function nazwa_submenuFilter($varname, $value) {
        $this->pm($value, NAZWA_PODSTRONY_RE, 1, 30);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function urlFilter($varname, $value) {
        $this->pm($value, URL_RE, 1, 255);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    public function nazwa_wyprzedazyFilter($varname, $value) {
        $this->pm($value, NAZWA_WYPRZEDAZY_RE, 1, 255);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }
    
    public function nazwaFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }
    
    public function opisFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }
    
    public function sztukiFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }
    
    public function opis_longFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }
    
    
    public function tokenFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }


    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!!!!!!!!!!!!!!!!!!

    public function passFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }

    public function rpassFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }
    
    public function new_passFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }
    
    public function new_pass_rFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }

    public function miastoFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }

    public function numerFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }
    
    public function nr_kontaFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }

    public function pocztowyFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }
    
    public function recaptcha_challenge_fieldFilter($varname,$value){
        $this->filtered[$varname] = $value;
    }
    
    public function recaptcha_response_fieldFilter($varname, $value){
        $this->filtered[$varname] = $value;
    }






}