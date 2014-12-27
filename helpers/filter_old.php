<?php

/**
 * Description of test
 *
 * @author sheriff
 */
define('EMAIL_RE', '/^([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})$/i');
define('EMAIL_RE_REG', '/^([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})$/i');
define('EMAIL_RE_AUTH', '/^([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})$/i');

//Regex polskich imion Świętopełk to słyszałem ale kurwa Śnieżka
//Pierwsza litera wstępnie wyklucza kombinacje z nieistniejącymi polskimi imionami
//potem dowolna liczba znaków łącznie z polskimi
define('NAME_RE', '/^[A-ZŁŻŚ]{1}[a-ząęóżźćńłś]*$/i');
define('NAME_RE_REG', '/^[A-ZŁŻŚ]{1}[a-ząęóżźćńłś]*$/i');
define('NAME_RE_AUTH', '/^[A-ZŁŻŚ]{1}[a-ząęóżźćńłś]*$/i');

// Wzorce imienia, Pierwsza duża litera
//define('NAME_RE', '/^[A-ZŁŻŚ]{1}[a-ząęóżźćńłś]*$/');
//define('NAME_RE_REG', '/^[A-ZŁŻŚ]{1}[a-ząęóżźćńłś]*$/');
//define('NAME_RE_AUTH', '/^[A-ZŁŻŚ]{1}[a-ząęóżźćńłś]*$/');

define('LASTNAME_RE', '/^[A-ZŁŻ]{1}[a-ząęóżźćńłś]*(\s|-)[A-ZŁŻ]{1}[a-ząęóżźćńłś]*$/');
define('LASTNAME_RE_REG', '/^[A-ZŁŻ]{1}[a-ząęóżźćńłś]*(\s|-)[A-ZŁŻ]{1}[a-ząęóżźćńłś]*$/');
define('LASTNAME_RE_AUTH', '/^[A-ZŁŻ]{1}[a-ząęóżźćńłś]*(\s|-)[A-ZŁŻ]{1}[a-ząęóżźćńłś]*$/');

//Wzorce nazwiska, Pierwsza duza litera
//define('LASTNAME_RE', '/^[A-ZŁŻ]{1}[a-ząęóżźćńłś]*(\s|-)[A-ZŁŻ]{1}[a-ząęóżźćńłś]*$/');
//define('LASTNAME_RE_REG', '/^[A-ZŁŻ]{1}[a-ząęóżźćńłś]*(\s|-)[A-ZŁŻ]{1}[a-ząęóżźćńłś]*$/');
//define('LASTNAME_RE_AUTH', '/^[A-ZŁŻ]{1}[a-ząęóżźćńłś]*(\s|-)[A-ZŁŻ]{1}[a-ząęóżźćńłś]*$/');
//polski kod pocztowy w formacie [cyfra][cyfra][myślnik][cyfra][cyfra][cyfra]
define('ZIP_RE', '/^[0-9]{2}-[0-9]{3}$/');
define('ZIP_RE_REG', '/^[0-9]{2}-[0-9]{3}$/');
define('ZIP_RE_AUTH', '/^[0-9]{2}-[0-9]{3}$/');

// polski numer telefonu -9 cyfr w postaci 515154404 lub stacjonarka w postaci 412608338
define('TELEFON_RE', '/^[0-9]{9}$/');
define('TELEFON_RE_REG', '/^[0-9]{9}$/');
define('TELEFON_RE_AUTH', '/^[0-9]{9}$/');

class filter extends helper {

    public $isFiltered = 0;
    public $filtered = array();
    public $context = '';
    public $matchFail = 0;
    public $validateFail = 0;

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
        if (isset($_SESSION) && !empty($_SESSION)) {
            $this->parseSESSION();
        }
    }

//w przypadku urli trzeba bedzie dokladnie przetestowac
    public function sanitize_input_data($data) {
        $data = $data;
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

//Zerknij na metode unknown
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

    public function pm($input, $preg_pattern) {
        if (!preg_match($preg_pattern, $input)) {
            $this->matchFail = true;
        }
        return $this->matchFail;
    }

    public function validateNIP($nip) {
        if (!empty($nip)) {
            $wagi = array(6, 5, 7, 2, 3, 4, 5, 6, 7);
            //Dla osób fizycznych grupowano cyfry 123-456-78-19, a dla firm grupowano 123-45-67-819.
            //Teraz nadawany jest bez znaków łącznika, więc
            //wywalamy wszelkie spacje i łączniki
            $nip = preg_replace('/[\s-]/', '', $nip);
            //musi mieć 10 cyfr
            if (strlen($nip) == 10 && is_numeric($nip)) {
                $sum = 0;
                //w celu obliczenia sumy kontrolnej mnoży się każdą z pierwszych 9-ciu cyfr przez wagi
                for ($i = 0; $i < 9; $i++) {
                    //sumuje sie wyniki mnozenia
                    $sum += $nip[$i] * $wagi[$i];
                }
                //oblicza sie reszte z dzielenia przez 11, nie moze wyjść 10
                	$modulo = $sum % 11;
					$control_number = ($modulo == 10) ? 0 : $modulo;
					if ( $in_control_number == $control_number ){
						$this->validateFail = false;
					}
        }
        return $this->validateFail;
    }

//Pole nieznanego typu nie dopuszczone przez nas do uzytku
    public function unknownTypeFilter($varname, $value) {
        $this->filtered[$varname] = 'Bzdury(Niedopuszczone pole) o długości:' . (string) (strlen($value));
        //na debug bedzie mozna to wyswietlic po przepuszczeniu przez kilka funcji upierdalajacych html, mysql...
    }

//Filtrowanie pola e-mail
    public function emailFilter($varname, $value) {
        $this->pm($value, EMAIL_RE);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

    public function emailRegisterFilter($varname, $value) {
        $this->pm($value, EMAIL_RE_REG);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

    public function emailAuthFilter($varname, $value) {
        $this->pm($value, EMAIL_RE_AUTH);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

//Filtrowanie pola imienia
    public function imieFilter($varname, $value) {
        $this->pm($value, NAME_RE);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

    public function imieRegisterFilter($varname, $value) {
        $this->pm($value, NAME_RE_REG);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

    public function imieAuthFilter($varname, $value) {
        $this->pm($value, NAME_RE_AUTH);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

//Filtrowanie nazwiska
    public function nazwiskoFilter($varname, $value) {
        $this->pm($value, LASTNAME_RE);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

    public function nazwiskoRegisterFilter($varname, $value) {
        $this->pm($value, LASTNAME_RE_REG);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

    public function nazwiskoAuthFilter($varname, $value) {
        $this->pm($value, LASTNAME_RE_AUTH);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

//Filtrowanie kodu pocztowego
    public function kodFilter($varname, $value) {
        $this->pm($value, ZIP_RE);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

    public function kodRegisterFilter($varname, $value) {
        $this->pm($value, ZIP_RE_REG);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

    public function kodAuthFilter($varname, $value) {
        $this->pm($value, ZIP_RE_AUTH);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

//Filtrowanie numeru telefonu
    public function telefonFilter($varname, $value) {
        $this->pm($value, TELEFON_RE);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

    public function telefonRegisterFilter($varname, $value) {
        $this->pm($value, TELEFON_RE_REG);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

    public function telefonAuthFilter($varname, $value) {
        $this->pm($value, TELEFON_RE_AUTH);
        if ($this->matchFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

//walidacja polskiego NIP
    public function nipFilter($varname, $value) {
        $this->validateNIP($value);
        if ($this->validateFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

    public function nipRegisterFilter($varname, $value) {
        $this->validateNIP($value);
        if ($this->validateFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

    public function nipAuthFilter($varname, $value) {
        $this->validateNIP($value);
        if ($this->validateFail == false) {
            $this->filtered[$varname] = $value;
        }
    }

//Filtrowanie adresu (ogolnie pytanie bedzie czy nie musi byc jakies bazy polskich adresow)
//
}

