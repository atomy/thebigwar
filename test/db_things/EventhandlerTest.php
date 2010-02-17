<?php

ini_set( 'include_path', ini_get( 'include_path' ).':../../include:../../engine:../../engine/classes:../../loghandler:../../db_things:../../:' );

// Call userTest::main() if this source file is executed directly.
if ( ! defined( 'PHPUNIT_MAIN_METHOD' ) )
{
    define( 'PHPUNIT_MAIN_METHOD', 'EventhandlerTest::main' );
}

require_once 'PHPUnit/Framework.php';

require_once 'config_inc.php';
require_once 'engine/include.php';
require_once 'eventhandler.php';

/** 
 * Test class for user.
 * Generated by PHPUnit on 2009-10-31 at 06:39:22.
 */
class EventhandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp( )
    {
        // must be the FIRST line   	
        // define_globals( Uni ); to set globals like where db files are located etc
        define_globals( 'TestUni1' );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown( )
    {

    }

    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main( )
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';
        
    //$suite  = new PHPUnit_Framework_TestSuite('userDevTest');
    //$result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////// TESTS START HERE //////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    

    /**
     * subtests:
	 * - checking for deletion of a very old (>=14 days w/o urlaubsmodus, >=35 days w urlaubsmodus enabled) user _o_
     */
    public function testExpireUser( )
    {   
		$uname = "haxx0r";

        $this->assertGreaterThan( 0, $tested );

        $userObj = Classes::User( $uname );
        $this->assertTrue( $userObj->create(), "couldnt create user" );

		// set reg time to 40 days ago
		$userObj->setRegistrationTime( time() - ( 3600 * 24 * 40 ) );

		checkExpiredUsers( );
	
		$this->assertFalse( User::userExists( $uname ) );
    }
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////// TESTS END HERE //////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
}

// Call userDevTest::main() if this source file is executed directly.
if ( PHPUNIT_MAIN_METHOD == 'EventhandlerTest::main' )
{
    eventhandlerTest::main();
}
?>
