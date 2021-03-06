<?php

// Call userTest::main() if this source file is executed directly.
if ( ! defined( 'PHPUNIT_MAIN_METHOD' ) ) {
    define( 'PHPUNIT_MAIN_METHOD', 'EventhandlerTest::main' );
}

require_once 'PHPUnit/Framework.php';

if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/engine/include.php';

/** 
 * Test class for Eventhandler
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
        
        $this->cleanUp();
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

    protected function cleanUp( )
    {
        
        Classes::resetInstances();
        /*
		foreach( $this->testData->getTestUsers() as $user )
		{
			user_control::removeUser( $user->getName() );
		}

		Classes::resetInstances();
		*/
        
        $this->_tearDown_DeleteDir( global_setting( "DB_PLAYERS" ) );
        $this->_tearDown_DeleteDir( global_setting( "DB_FLEETS" ) );
        $this->_tearDown_DeleteDir( global_setting( "DB_MESSAGES" ) );
    }

    /**
     * gets rid of old data stored in database for a fresh test setup
     * @param string $dir
     * @return -
     */
    protected function _tearDown_DeleteDir( $dir )
    {
        $exclude = array( '.', '..' );
        $files = array_diff( scandir( $dir ), $exclude );
        
        foreach ( $files as $value ) {
            $fname = $dir . "/" . $value;
            
            if ( ! is_dir( $fname ) && is_file( $fname ) && is_writable( $fname ) ) {
                unlink( $fname );
            }
        }
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

    public function _runEventhandler( )
    {
        $meh = TBW_ROOT;
        exec( "cd $meh; cd db_things; ./eventhandler.php --testrun" );
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////// TESTS START HERE //////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * subtests:
     * - check for deletion of 35 days and above inactivity accounts \o/
     */
    public function testExpireUser( )
    {
        $uname = "test123";
        
        /*
		 * 34 days, do not delete
		 */
        $userObj = Classes::User( $uname );
        $this->assertTrue( $userObj->create(), "couldnt create user" );
        
        // set reg time to 30 days ago
        $userObj->setRegistrationTime( time() - ( 3600 * 24 * 30 ) );
        
        // get rid of the userobj
        $userObj = false;
        Classes::resetInstances();
        
        // run eventhandler
        $this->_runEventhandler();
        
        $this->assertTrue( User::userExists( $uname ) );
        
        /*
		 * 35 days, delete
		 */
        // set reg time to 35 days ago
        $userObj = Classes::User( $uname );
        $userObj->setRegistrationTime( time() - ( 3600 * 24 * 35 ) );
        
        // get rid of the userobj
        $userObj = false;
        Classes::resetInstances();
        
        // run eventhandler
        $this->_runEventhandler();
        
        $this->assertFalse( User::userExists( $uname ) );
        
        /*
		 * 36 days, delete
		 */
        $userObj = Classes::User( $uname );
        $this->assertTrue( $userObj->create(), "couldnt create user" );
        
        // set reg time to 36 days ago
        $userObj->setRegistrationTime( time() - ( 3600 * 24 * 36 ) );
        
        // get rid of the userobj
        $userObj = false;
        Classes::resetInstances();
        
        // run eventhandler
        $this->_runEventhandler();
        
        $this->assertFalse( User::userExists( $uname ) );
    }
    
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////// TESTS END HERE //////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


}

// Call userDevTest::main() if this source file is executed directly.
if ( PHPUNIT_MAIN_METHOD == 'EventhandlerTest::main' ) {
    eventhandlerTest::main();
}
?>
