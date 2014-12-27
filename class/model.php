<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of model
 *
 * @author sheriff
 */
class model {

    public $request = 'modelRequestArray';

    public $answer;

    public $pdo;
    
    public $models = array();

    public function  __construct($pdo, $modelRequest) {
        $this->pdo = $pdo;
        $this->request = $modelRequest;
        if (method_exists($this, 'init')) $this->init();
    }

    public function put($putName, $putContent){
        $this->answer[$putName] = $putContent;
        return 1;
    }

    public function insertSQL($tablename, $data){
        $sql = 'INSERT INTO `'.$tablename.'` (';
        foreach($data as $name=>$row){
            $sql .= $name.',';
        }
        $sql = substr($sql, 0, strlen($sql)-1);
        $sql .= ') VALUES(';
        foreach($data as $name=>$row){
            $sql .= ':'.$name.',';
        }
        $sql = substr($sql, 0, strlen($sql)-1);
        $sql .= ');';        
        $stmt = $this->pdo->prepare($sql);

        foreach($data as $name=>$row){
            if(is_int($row)){
                $stmt -> bindValue((':'.$name), $row, PDO::PARAM_INT);
            }else{
                $stmt -> bindValue((':'.$name), $row, PDO::PARAM_STR);
            }            
        }
        $res = $stmt->execute();
        $stmt->closeCursor();
        return $res;
    }

    public function updateSQL($tablename, $data, $condition = null){
        $sql = 'UPDATE `'.$tablename.'` SET ';
        foreach($data as $name=>$row){
            $sql .= $name.' = :'.$name.',';
        }
        $sql = substr($sql, 0, strlen($sql)-1);
        if (is_array($condition)) {
            $sql .=  ' WHERE ';
            $i = 0;
            foreach($condition as $row){
                if (isset($row['condition'])){
                    $sql .= $row['conVar'].' '.$row['operator'].' :var'.$i.' '.$row['condition'].' ';
                }else{
                    $sql .= $row['conVar'].' '.$row['operator'].' :var'.$i;
                }
                $i++;
            }
        }
        $sql .= ';';
        $stmt = $this->pdo->prepare($sql);
        foreach($data as $name=>$row){
            $stmt->bindValue((':'.$name), $row);
        }
        $i = 0;
        if (is_array($condition)){
            foreach($condition as $row){
                $stmt->bindValue((':var'.$i), $row['conValue']);
                $i++;
            }
        }
        
        $res = $stmt->execute();
        $stmt->closeCursor();        
        return $res;
    }


    public function deleteSQL($tablename, $condition){
        $sql = 'DELETE FROM `'.$tablename.'` WHERE ';

        $i = 0;
        foreach($condition as $row){
            if (isset($row['condition'])){
                $sql .= $row['conVar'].' '.$row['operator'].' :var'.$i.' '.$row['condition'].' ';
            }else{
                $sql .= $row['conVar'].' '.$row['operator'].' :var'.$i;
            }
            $i++;
        }
        $sql .= ';';      
        $stmt = $this->pdo->prepare($sql);
        $i = 0;
        foreach($condition as $row){
            $stmt->bindValue((':var'.$i), $row['conValue']);
            $i++;
        }
        $res = $stmt -> execute();
        $stmt -> closeCursor();        
        return $res;
    }

    public function selectSQL($tablename, $condition, $fields = '*'){
        $sql = 'SELECT '.$fields.' FROM `'.$tablename.'` WHERE ';
        $i = 0;
        foreach($condition as $row){
            if (isset($row['condition'])){
                $sql .= $row['conVar'].' '.$row['operator'].' :var'.$i.' '.$row['condition'].' ';
            }else{
                $sql .= $row['conVar'].' '.$row['operator'].' :var'.$i;
            }
            $i++;
        }
        $sql .= ';';        
        $stmt = $this->pdo->prepare($sql);
        $i = 0;
        foreach($condition as $row){
            $stmt->bindValue((':var'.$i), $row['conValue']);
            $i++;
        }
        $res = $stmt -> execute();
        $out = $stmt -> fetchAll();
        $stmt -> closeCursor();
        return $out;
    }

    public function selectLimitSQL($tablename, $condition, $limit, $fields = '*'){
        $sql = 'SELECT '.$fields.' FROM `'.$tablename.'` WHERE ';
        $i = 0;
        foreach($condition as $row){
            if (isset($row['condition'])){
                $sql .= $row['conVar'].' '.$row['operator'].' :var'.$i.' '.$row['condition'].' ';
            }else{
                $sql .= $row['conVar'].' '.$row['operator'].' :var'.$i;
            }
            $i++;
        }
        $limit = (int)$limit;
        $sql .= ' LIMIT '.$limit.';';        
        $stmt = $this->pdo->prepare($sql);
        $i = 0;
        foreach($condition as $row){
            $stmt->bindValue((':var'.$i), $row['conValue']);
            $i++;
        }
        $res = $stmt -> execute();
        $out = $stmt -> fetchAll();
        $stmt -> closeCursor();
        return $out;
    }
    
    public function issetRecord($tablename, $fieldsToGet, $fieldName, $value){
        $sql = 'SELECT '.$fieldsToGet.' FROM `'.$tablename.'` WHERE `'.$fieldName.'` = :value LIMIT 1;';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':value', $value);
        $res = $stmt -> execute();
        if($res){
            $out = $stmt -> fetchAll();
            $stmt -> closeCursor();
            return $out;
        }else{
            $stmt -> closeCursor();
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
    
    public function flushModels(){
        unset($this->models);
    }
    

}

