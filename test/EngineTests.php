<?php

require_once 'PHPUnit/Framework.php';
require_once 'engine/classes/userTest.php';

class EngineTests
{

    public static function suite( )
    {
        $suite = new PHPUnit_Framework_TestSuite( 'Engine Tests' );
        
        $suite->addTestSuite( 'userTest' );
        
        return $suite;
    }
}
