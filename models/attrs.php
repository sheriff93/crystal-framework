<?php

class attrs extends model{
    
    
    public function init(){
        if(isset($this->request['operation'])){
            if($this->request['operation'] == 'addValue'){
                $this->addValue($this->request['value']);
            }
            if($this->request['operation'] == 'addAttr'){
                $this->addAttr($this->request['attr']);
            }
            if($this->request['operation'] == 'getAll'){
                $this->getAll($this->request['attr_array']);
            }
            if($this->request['operation'] == 'getValueName'){
                $this->getValueName($this->request['id']);
            }
            if($this->request['operation'] == 'getAttrs'){
                $this->getAttrs();
            }
            if($this->request['operation'] == 'getValues'){
                $this->getValues($this->request['id']);
            }
        }
    }
    
    
    public function addValue($name){
        $res = $this->insertSQL('wartosci', ['nazwa' => $name]);
        $this->put('result', $res);
    }
    
    public function addAttr($name){
        $res = $this->insertSQL('atrybuty', ['nazwa' => $name]);
        $this->put('result', $res);
    }
    
    public function getAttrs(){
        $stmt = $this->pdo->query('SELECT * FROM atrybuty;');
        $res = $stmt->fetchAll();
        $stmt->closeCursor();
        $this->put('result', $res);
    }
    
    public function getValues($id){
        $id = (int)$id;
        $condition = array(
            0 => ['conVar' => 'atrybut_id', 'operator' => '=', 'conValue' => $id]
        );
        $res = $this->selectSQL('wartosci', $condition);
        $this->put('result', $res);
    }
    
    public function editAttr($id, $name){
        $res = $this->updateSQL('wartości', $data, $condition);
    }
    
    public function getValueName($id){
        $id = (int)$id;
        $res = $this->issetRecord('wartosci', 'nazwa', 'wartosc_id', $id);
        if(isset($res[0]['nazwa'])){
            $this->put('result', $res[0]['nazwa']);
        }else{
            $this->put('result', 0);
        }
    }
    
    public function getAll($attr_array){
        //SELECT atrybuty.nazwa AS attr_nazwa, wartosci.nazwa AS value_nazwa FROM atrybuty, wartosci WHERE (atrybuty.atrybut_id = wartosci.atrybut_id) AND (wartosci.atrybut_id = 1 OR wartosci.atrybut_id = 2);

        $sql = 'SELECT atrybuty.nazwa AS attr_nazwa, wartosci.nazwa AS value_nazwa, wartosci.wartosc_id'
                . ' FROM atrybuty, wartosci'
                . ' WHERE (atrybuty.atrybut_id = wartosci.atrybut_id) AND (';
        $i = 1;
        foreach($attr_array as $value=>$key){            
            $sql_insert_name = ':attr_id_'.$i;
            $sql_insert_var = ' wartosci.atrybut_id = '.$sql_insert_name.' OR';
            $sql .= $sql_insert_var;
            $i++;
        }
        $sql = substr($sql, 0, strlen($sql)-3);
        $sql .= ');';
        
        $stmt = $this->pdo->prepare($sql);
        
        $i = 1;
        
        foreach($attr_array as $key=>$value){
            $stmt->bindValue(':attr_id_'.$i, $key);
            $i++;
        }
        if($stmt -> execute()){
            $out = $stmt -> fetchAll();
        }
        $stmt->closeCursor();
        
        $this->put('result', $out);
        
        
    }
    
    
    
    
}