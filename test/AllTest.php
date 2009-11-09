<?php

require_once 'PHPUnit/Framework.php';
require_once 'EngineTests.php';
 
class AllTest
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('TheBigWar');
 
        $suite->addTest(EngineTests::suite());
 
        return $suite;
    }
}
?>
