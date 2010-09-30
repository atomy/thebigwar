<?php

class TestFleet
{

    private $_name;

    public function __construct( )
    {

    }

    public function setName( $newname )
    {
        $this->_name = $newname;
    }

    public function getName( )
    {
        return $this->_name;
    }
}