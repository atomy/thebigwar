<?php

require_once 'PHPUnit/Framework.php';
require_once 'EngineTests.php';
require_once 'db_things/EventhandlerTest.php';

class AllTest
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('TheBigWar');
 
        $suite->addTest(EngineTests::suite());
		$suite->addTestSuite('EventhandlerTest');
 
        return $suite;
    }
}
?>
