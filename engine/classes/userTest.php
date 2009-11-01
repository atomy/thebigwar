<?php
// Call userTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'userTest::main');
}

require_once 'PHPUnit/Framework.php';
require_once '../../include/config_inc.php';
require_once( TBW_ROOT.'engine/include.php' );
require_once( TBW_ROOT.'engine/classes/galaxy.php' );

/**
 * Test class for user.
 * Generated by PHPUnit on 2009-10-31 at 06:39:22.
 */
class userTest extends PHPUnit_Framework_TestCase
{
	private $testUname = 'helmut';
	private $testCreateUname = 'hans';
	private $testNoUname = 'randomusernotcreatedbefore';
	private $userObj = NULL;

    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('userTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
		// define_globals( Uni ); to set globals like where db files are located etc
		define_globals( 'TestUni1' );

		$this->userObj = new User( $this->testUname );
		$this->userObj->create();

		if ( !$this->userObj->getStatus() )
			throw new Exception( 'setUp() failed, borken status returned' );

		$this->setUp_MainPlanet();
    }

	protected function setUp_MainPlanet()
	{
		$user = &$this->userObj;

		$koords = getFreeKoords();
		
		if( $koords )
		{
			$index = $user->registerPlanet( $koords );

			if ( $index === false )
				throw new Exception( 'setUp_MainPlanet() failed, couldnt setup planet on given coordinates - '.$koords );
			else
			{
				$user->setActivePlanet( $index );
				$user->planetName( "Mainplanet" );
			}
		}	
		else
			throw new Exception( 'setUp_MainPlanet() failed, no free coordinates for setting up planet' );
	}

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
		$user = &$this->userObj;
		$ufname = $user->getFileName();
		$user->__destruct();
		unset( $user );

		// remove the testusers
		user_control::removeUser( $this->testUname );
		user_control::removeUser( $this->testCreateUname );

		if ( User::userExists( $this->testUname ) )
		{
			if ( is_file( $ufname ) )
				unlink( $ufname );
		}

		if ( User::userExists( $this->testCreateUname ) )
		{
			$newuser = new User( $this->testCreateUname );

			if ( is_file( $newuser->getFileName() ) )
				unlink( $newuser->getFileName() );
		}
	}

	/**
	 * check new user creation
	 */
	public function	testCreate()
	{
		$newuser = new User( $this->testCreateUname );
		$dupuser = new User( $this->testUname );

		$this->assertTrue( $newuser->create(), "couldnt create user" );
		$this->assertFalse( $dupuser->create(), "could create user which already exist" );
	}

	public function testUserExists()
	{
		$this->assertTrue( User::userExists( $this->testUname ), "user which was created doesnt exist" );
		$this->assertFalse( User::userExists( $this->testNoUname ), "user which wasnt created does exist" );
		$this->assertFalse( User::userExists( NULL ), "func returned true but no name was given as parameter" );
	}

	public function testPlanetExists()
	{
		$user = &$this->userObj;

		$this->assertTrue( $user->planetExists( 0 ), "planet setup but doesnt exist" );
		$this->assertGreaterThan( 0, global_setting( "MAX_PLANETS" ), "MAX_PLANETS not above 0" );

		for( $i = 1; $i <= global_setting( "MAX_PLANETS" ); $i++ )
		{
			$this->assertFalse( $user->planetExists( $i ), "planet shouldnt exist" );
		}

		// call it with an initialised user but which doesnt exists
		$fuser = new User( "fakeuser1337" );

		for( $i = 0; $i <= global_setting( "MAX_PLAYERS" ); $i++ )
		{
			$this->assertFalse( $fuser->planetExists( $i ), "planet shouldnt exists in non-existing user" );
		}
		

	}

}

// Call userTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'userTest::main') 
{
    userTest::main();
}
?>
