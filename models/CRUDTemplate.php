<?php

class sale extends model{

    public function init(){
        if(isset($this->request['operation'])){
            if($this->request['operation'] == 'create'){
                $this->create();
            }
            if($this->request['operation'] == 'read'){
                $this->read();
            }
            if($this->request['operation'] == 'update'){
                $this->update();
            }
            if($this->request['operation'] == 'delete'){
                $this->delete();
            }
        }
    }

    public function create(){

    }

    public function read(){

    }

    public function update(){

    }

    public function delete(){

    }





}