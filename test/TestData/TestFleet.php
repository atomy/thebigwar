<?php

class TestFleet
{
    private $name;
    
    public function __construct()
    {
        
    }
    
    public function setName( $newname )
    {
        $this->name = $newname;
    }
    
    public function getName()
    {
        return $this->name;
    }
}

?>