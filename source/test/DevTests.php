<?php

require_once 'PHPUnit/Framework.php';
require_once 'engine/classes/userTest.php';

class DevTests
{

    public static function suite( )
    {
        $suite = new PHPUnit_Framework_TestSuite( 'Engine Tests' );
        
        $suite->addTest( userTest::testGetPlanetsList() );
        $suite->addTest( userTest::testRemovePlanet() );
        
        return $suite;
    }
}
