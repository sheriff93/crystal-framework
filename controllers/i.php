<?php

class i extends ctrl{
    public function init(){
        $this->viewName('indexLogged');
        //$this->setPerms();
        //$this->execModel('log', ['log' => 'vp']);
    }

    public function indexAction(){
        $this->put('title', 'Wyprzedaze.pl');
        $this->addHeadObject('css', _ROOT.'css/bootstrap.min.css');
        $this->addHeadObject('css', _ROOT.'css/jq-style.css');
        $this->addHeadObject('css', _ROOT.'style/style.css');
        $this->addHeadObject('js', _ROOT.'js/jquery-1.8.3.js');
        $this->addHeadObject('js', _ROOT.'js/bootstrap.min.js');
        $this->addHeadObject('js', _ROOT.'js/jquery-ui-1.9.2.custom.min.js');
        $this->addHeadObject('js', _ROOT.'js/tinymce/tinymce.min.js');
        $this->addHeadObject('js', _ROOT.'js/plupload.js');
        $this->addHeadObject('js', _ROOT.'js/plupload.html5.js');
        $this->addHeadObject('js', _ROOT.'js/tiny.init.js');
        $this->addHeadObject('js', _ROOT.'js/slider.js');
    }
    
    public function addProductAction(){
        $this->viewName('debug');
        $productArray = array(
            'kampania_id' => 2,
            'nazwa' => 'BUTY - adaś',
            'sztuki' => 100,
            'kategoria_id' => 2,
            'link' => 'buty-nike',
            'status_id' => 1,
            'cena_przed' => 120,
            'cena_po' => 99,
            'opis' => 'Najlepsza bluza na zimne zimowe wieczory, polecam Maciej Żurawski',
            'zdjecie_hash' => 'ehwf89g23bqyfuqgorweiogyuq3bh948'
        );
        $this->addModel('product', array('data' => $productArray, 'operation' => 'add'));
        $this->execModels();
        if (isset($this->models['product']->result['answer']['result'])){
            echo $this->models['product']->result['answer']['result'];
        }
    }
    
    public function deleteProductAction(){
        $this->viewName('debug');
        $this->addModel('product', array('operation' => 'delete', 'delete_id' => 800));
        $this->execModels();        
    }
    
    public function acceptProductAction(){
        $this->viewName('debug');
        $this->addModel('product', array('operation' => 'accept', 'accept_id' => 804));
        $this->execModels();
    }
    
    public function disableProductAction(){
        $this->viewName('debug');
        $this->addModel('product', array('operation' => 'disable', 'disable_id' => 804));
        $this->execModels();
    }
    
    public function getSaleAction(){
        $this->viewName('debug');
        $this->addModel('product', array('operation' => 'getSale', 'kampania_id' => 2));
        $this->execModels();
    }
    
    public function getProductAction(){
        $this->viewName('debug');
        $this->addModel('product', array('operation' => 'getProduct', 'id' => 804));
        $this->execModels();
    }
    
    
    
    

    public function testAction(){
        $data = array(
        'nazwa' => 'Super wyprzedaż',
        'opis' => 'TARAFARA, TRZESZCZY SZPARA',
        'hurtownik_id' => 6,
        'procent_znizki' => 50,
        'data_rozpoczecia' => '2014-12-12',
        'data_zakonczenia' => 'wegweg',
        'plik_zdjecia' => '235235235',
        'notatki' => 'madafakia _notatka',
        'data_dodania' => 'a',
        'status' => 'N'
    );
        $this->addModel('sale', array('operation' => 'getSalesMainPage'));
        $this->execModels();

    }

    public function filterAction(){
        $this->addHelper('filter');
        $this->execHelpers();
        $filter = $this->helpers['filter']->filtered;
        
    }

    public function sessionAction(){
        $this->addHeadObject('css', _ROOT.'css/bootstrap.min.css');
        $this->addHeadObject('css', _ROOT.'css/jq-style.css');
        $this->addHeadObject('css', _ROOT.'style/style.css');
        $this->addHeadObject('js', _ROOT.'js/jquery-1.8.3.js');
        $this->addHeadObject('js', _ROOT.'js/bootstrap.min.js');
        print_r($_SESSION);
    }

    public function updateAction(){
        
    }

    public function userAction(){
        $this->addHelper('filter');
        $this->execHelpers();
        $data = array(
            'imie' => 'marian',
            'nazwisko' => 'zjebuszko'
        );
        
        $this->addModel('user', array('email' => 'hurt@test.pl', 'pass' => 'testowe', 'data' => $data));
        $this->execModels();
    }
    
    public function debugAction(){
        $this->setPerms();
        echo 'PERMS LEVEL: '.PERM_LVL;
        $this->put('title', 'DEBUG');
        echo '<pre>PRINT: $_SESSION<br>';
        print_r($_SESSION);
        echo '<br>PRINT: $_COOKIE<br>';
        print_r($_COOKIE);
        echo 'Sesje gdzie trzymane: ';
        echo session_save_path();
        echo '<br>PRINT: $_POST</br>';
        print_r($_POST);
        echo '</pre>';
    }

    public function finish(){        
        $this->execView();
        print_r($this->models);
    }
    
    public function infoAction(){
        phpinfo();
    }
    
    public function modelViewerAction(){
        $this->viewName('debug');
        
    }
    
    public function addItemToBasketAction(){
        $this->setPerms();
        $this->execModel('basket', ['operation' => 'addItem', 'product_id' => 810, 'itemsNumber' => 4]);
        echo $this->models['basket']->answer['result'];
    }
    
    public function deleteItemFromBasketAction(){
        $this->setPerms();
        $this->execModel('basket', ['operation' => 'deleteItem', 'product_id' => 810]);
        echo $this->models['basket']->answer['result'].'<br>';
    }
    
    public function issetBasketItemAction(){
        $this->setPerms();
        $this->execModel('basket', ['operation' => 'issetItem', 'product_id' => 808]);
        echo $this->models['basket']->answer['result'].'<br>';
    }
    
    public function zmienSztukiBasketAction(){
        $this->setPerms();
        $this->execModel('basket', ['operation' => 'changeNumberOfItems', 'product_id' => 810, 'newItemsNumber' => 3]);
        echo $this->models['basket']->answer['result'].'<br>';
    }
    
    public function pokazKoszykAction(){
        $this->setPerms();
        $this->execModel('basket', ['operation' => 'getUserBasket']);
        print_r($this->models['basket']->answer['data']).'<br>';
    }
    
    public function flushBasketAction(){
        $this->setPerms();
        $this->execModel('basket', ['operation' => 'flushMyBasket']);
        echo($this->models['basket']->answer['result']);
    }
    
    public function sumaAction(){
        $this->setPerms();
        $this->execModel('basket', ['operation' => 'getSummaryPrice']);
        print_r($this->models['basket']->answer['data']);
    }
    
    public function orderAction(){
        $this->setPerms();
        $this->execModel('order', ['operation' => 'purchase']);
    }
    
    public function orderItemsAction(){
        $this->setPerms();
        $this->execModel('order', ['operation' => 'itemsOfOrder', 'order_id' => 2]);
    }
    
    public function orderExecAction(){
        $this->setPerms();
        $this->execModel('order', ['operation' => 'executeOrder', 'order_id' => '1']);
    }
    
    public function getMyOrdersAction(){
        $this->setPerms();
        $this->execModel('order', ['operation' => 'getMyOrders']);
    }
    
    public function countBasketAction(){
        $this->setPerms();
        $this->execModel('basket', ['operation' => 'count']);
    }
    
    public function issetUserAction(){
        $this->setPerms();
        $this->execModel('partner', ['operation' => 'issetUser', 'user_id' => 13]);
    }
    public function get3Action(){
        $this->setPerms();
        $this->execModel('product', ['operation' => 'get15New']);
        
    }

}

