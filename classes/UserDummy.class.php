<?php
class UserDummy {
    protected $name;
 
    public function getName() {
        return $this->name;
    }
 
    public function setName($name) {
        $this->name = $name;
    }
 
    public function talk() {
        return "Hello world!";
    }
}