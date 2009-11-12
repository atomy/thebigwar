<?php

// Call userTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) 
{
    define('PHPUnit_MAIN_METHOD', 'userTest::main');
}

require_once 'PHPUnit/Framework.php';

if( is_file('../../../include/config_inc.php') )
{
	require_once '../../../include/config_inc.php';
}
else if( is_file( '../include/config_inc.php') )
{
	require_once '../include/config_inc.php';
}
else
{
	require_once 'include/config_inc.php';
}

require_once TBW_ROOT.'engine/include.php' ;
require_once TBW_ROOT.'engine/classes/galaxy.php';
require_once TBW_ROOT.'test/TestData/TestConstants.php';
require_once TBW_ROOT.'test/TestData/TestData.php';
require_once TBW_ROOT.'test/TestData/Tester.php';
require_once TBW_ROOT.'test/TestData/TestMessage.php';

/**
 * Test class for user.
 * Generated by PHPUnit on 2009-10-31 at 06:39:22.
 */
class userTest extends PHPUnit_Framework_TestCase
{
	private $testData;
	
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
    	// must be the FIRST line   	
    	// define_globals( Uni ); to set globals like where db files are located etc
        define_globals( 'TestUni1' );
        
        $this->cleanUp();
    	
    	$this->testData = new TestData();
    	$this->tester = new Tester( $this->testData );
    	$this->tester->setUp();
	}

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {

	}
	
	protected function cleanUp()
	{

		Classes::resetInstances();
		/*
		foreach( $this->testData->getTestUsers() as $user )
		{
			user_control::removeUser( $user->getName() );
		}

		Classes::resetInstances();
*/
		
		$this->_tearDown_DeleteDir(global_setting("DB_PLAYERS"));
		$this->_tearDown_DeleteDir(global_setting("DB_FLEETS"));
		$this->_tearDown_DeleteDir(global_setting("DB_MESSAGES"));
	}
	
	public function testEmpty()
	{
		$this->assertFalse( false );
	}

	protected function _tearDown_DeleteDir( $dir )
	{
		$exclude = array('.', '..');
		$files = array_diff(scandir($dir), $exclude);
		   
		foreach($files as $value)
		{
			$fname = $dir."/".$value;

			if(!is_dir($fname) && is_file($fname) && is_writable($fname) )
			{
				unlink( $fname );
			}
		}
	}

	/**
	 * test our test setup
	 */
	public function testSetup()
	{
		foreach( $this->testData->getTestUsers() as $userData )
		{
			$this->_testSetup( $userData );
		}
	}

	public function _testSetup( &$userData )
	{	
		if ( $userData->shouldCreate() && $userData->shouldCreateOnSetup() )
		{
			$this->assertTrue( User::userExists( $userData->getName() ) );
		}
		else
		{
			$this->assertFalse( User::userExists( $userData->getName() ), "user ".$userData->getName()." exists but shouldnt" );
		}
		
		if(!$userData->isCreated())
		{
			$this->assertFalse( User::userExists( $userData->getName() ));
			return;
		}

		foreach($userData->getPlanets() as $planetData)
		{
			$i = $planetData->getIndex();
			
			if ($planetData->isCreated())
			{
				$user = Classes::User($userData->getName());
                $this->assertTrue( $user->setActivePlanet( $i ) );
                $this->assertTrue( $user->planetExists( $i ) );
                $this->assertEquals( $planetData->getName(), $user->planetName() );
                $this->_testPlanetItems( $user, $planetData );				
			}
		    else
            {
                $this->assertFalse( $user->setActivePlanet( $i ) );
                $this->assertFalse( $user->planetExists( $i ) );
                //$this->assertFalse( $user->planetName() ); // doesnt work, cause we couldnt change to that planet with setActivePlanet()
            }
		}
	}

	public function _testPlanetItems( &$user, &$planetData )
	{
		$this->assertGreaterThanOrEqual( 0, $planetData->getIndex() );
		
		$user->setActivePlanet( $planetData->getIndex() );
	
		foreach( $planetData->getItems() as $itemData )
		{
			$id = $itemData->getId();
			$level = $itemData->getLevel();
			
			$lvl = $user->getItemLevel( $id, false, false );
			$this->assertEquals( $level, $lvl, '_testPlanetItems() expected level didnt match for given item '.$id.' wanted: '.$level.' got: '.$lvl );
			//echo "testing item: ".$id." for level: ".$level." which is: ".$lvl."\n";
		}
	}

	/**
	 * check new user creation
	 */
	public function	testCreate()
	{
		$usa = $this->testData->getUnusedUser();
		
		if($usa === false)
		{
			throw new Exception("testCreate() failed, no remaining users for testing");
		}
			
		// new user
		$newuser = Classes::User( $usa->getName() );

		// already exists
		$dupuser = Classes::User( $usa->getName() );

		$this->assertTrue( $newuser->create(), "couldnt create user" );

		$usa->setIsCreated(true);

		$this->assertFalse( $dupuser->create(), "could create user which already exist" );
	}

	public function testUserExists()
	{
		foreach($this->testData->getTestUsers() as $user)
		{
			if($user->isCreated())
			{
				$this->assertTrue( User::userExists( $user->getName() ), "user which was created doesnt exist" );
			}
			else if(!$user->isCreated())
			{
				$this->assertFalse( User::userExists( $user->getName() ), "user which wasnt created does exist" );
			}				
		}		
		
		$this->assertFalse( User::userExists( NULL ), "func returned true but no name was given as parameter" );
	}

	public function testPlanetExists()
	{
		$users = $this->testData->getTestUsers();
	
		foreach($users as $user)
		{
			if(!$user->isCreated())
			{
				continue;
			}
			else
			{
				$userObj = Classes::User($user->getName());
				$planets = $user->getPlanets();
				
				foreach($planets as $planet)
				{
					if($planet->isCreated())
					{
						$this->assertTrue( $userObj->planetExists( $planet->getIndex() ), "planet setup but doesnt exist" );
					}
					else
					{
						$this->assertFalse( $userObj->planetExists( $planet->getIndex() ), "planet shouldnt exist" );
					}
				}
			}
		}

		// call it with an initialised user but which doesnt exists
		$fuser = Classes::User( "fakeuser1337" );

		for( $i = 0; $i <= $this->_testAndGetMaxPlanets(); $i++ )
		{
			$this->assertFalse( $fuser->planetExists( $i ), "planet shouldnt exists on non-existing user" );
		}
	}

	public function testSetActivePlanet()
	{	
		$users = $this->testData->getTestUsers();
	
		foreach($users as $user)
		{
			if(!$user->isCreated())
			{
				continue;
			}
			else
			{
				$userObj = Classes::User($user->getName());
				$planets = $user->getPlanets();
				
				foreach($planets as $planet)
				{
					if($planet->isCreated())
					{
						$this->assertTrue( $userObj->setActivePlanet( $planet->getIndex() ), "planet setup but doesnt exist" );
					}
					else
					{
						$this->assertFalse( $userObj->setActivePlanet( $planet->getIndex() ), "planet shouldnt exist" );
					}
				}
			}
		}

		// call it with an initialised user but which doesnt exists
		$fuser = Classes::User( "fakeuser1337" );

		for( $i = 0; $i <= $this->_testAndGetMaxPlanets(); $i++ )
		{
			$this->assertFalse( $fuser->setActivePlanet( $i ), "planet shouldnt exists on non-existing user" );
		}
	}

	public function testGetPlanetByPos()
	{
		$users = $this->testData->getTestUsers();
		
        // get our very fist user for testing
		$uname = $users[0]->getName();
        $user = Classes::User($uname);

		$fuser = Classes::User( "fakeuser1339" );

		$this->assertFalse( $fuser->getPlanetByPos( false ) );

		for( $i = 0; $i <= TEST_MAX_GALAXIES; $i++ )
		{
			for( $k = 0; $k <= TEST_MAX_SYSTEMSINGALAXY; $k++ )
			{
				for( $m = 0; $m <= TEST_MAX_PLANETSINSYSTEM; $m++ )
				{
					if ( rand(0, 10) != 5 )
					{
						continue;
					}

					$pos = $i.":".$k.":".$m;
					$bMyPlanet = false;

					foreach( $users[0]->getPlanets() as $planet )
					{
						if ( $planet->isCreated() && $pos == $planet->getPosString() )
						{
							$bMyPlanet = true;
							$this->assertEquals( $planet->getIndex(), $user->getPlanetByPos( $pos ) );
							//echo "found mah planet @ ".$pos."\n"; 
						}
					}

					if ( !$bMyPlanet )
						$this->assertFalse( $user->getPlanetByPos( $pos ) );
				}
			}
		}
	}

	/*
	 * checking if the returned planetlist is the same as our one of the created planets
	 */
	public function testGetPlanetsList()
	{
		$testUsers = $this->testData->getTestUsers();
		$testusr = $testUsers[0];
		
		// get our very fist user for testing
        $user = Classes::User($testusr->getName());		

		$fuser = Classes::User( "fakeuser1340" );

		$this->assertFalse( $fuser->getPlanetsList() );

		$planets = $user->getPlanetsList();
	
		for( $i = 0; isset( $planets[$i] ) || $testusr->hasCreatedPlanetAtIndex($i); $i++ )
		{
			$this->assertTrue( isset( $planets[$i] ) );
			$this->assertTrue( $testusr->hasCreatedPlanetAtIndex($i) );
		}
	}

    /*
     * checking if the returned planetlist is the same as our one of the created planets
     */
    public function testRemovePlanet()
    {
    	global $types_message_types;
    	
        // get our very fist user for the fleet start pos, using active planet
        $testusers = $this->testData->getTestUsers();
		$testUser = $testusers[0];
		$uname = $testUser->getName();
        $user = Classes::User( $uname );

		$fuser = Classes::User( "fakeuser1341" );

		$this->assertFalse( $fuser->removePlanet() );

		/*
		 * fleet zurueckrufen welche zu plani unterwegs ist - testen
		 */
		 /*
		$koords = $user->getPosString();
		$i = 0;

		while( $koords == $user->getPosString() && $i < 100 )
		{
			$i++;
			$usr = array_rand( $this->test_PlanetCoordinates );
			$planet = array_rand( $this->test_PlanetCoordinates[$usr] );
			$koords = $this->test_PlanetCoordinates[$usr][$planet];

			$this->assertLessThan( 100, $i );
		}
		*/

		// set active planet to 0 and send fleet to planet 1
		$user->setActivePlanet(0);
		$planets = $testUser->getPlanets();
		$mypos =  $user->getPosString();
		$pos = $planets[1]->getPosString();
		$toName = $planets[1]->getName();
		$fromName = $planets[0]->getName();
		$type = 6; // stationieren
		$delPlanetIndex = 1;
		$delPlanetPos = $mypos;
		
		$this->_testSendFleetTo( $uname, $pos );
		
        // core func, remove the planet
        $user = Classes::User( $uname );
		$this->assertTrue($user->setActivePlanet($delPlanetIndex));
        $this->assertTrue($user->removePlanet());
        unset($user);
        
		$fleet_obj = Classes::Fleet( $this->test_Fleets[$uname][0] );
        // check if the fleet was sent back
        $this->_testIsFleetExistingSpecific( $uname, $mypos, $pos, array( "S1", 10 ), $type, true );

        // check if planet still exists
        $galaxy = Classes::Galaxy(1);
        $koords = explode( ":", $pos );
        $this->assertEquals( "", $galaxy->getPlanetOwner( $koords[1], $koords[2] ) );

        $user = Classes::User($uname);
        $msgs = $user->getMessagesList(3);
        $msg = Classes::Message($msgs[0]);
        
        //echo $msg->rawText()."\n";
        
        $testMsg = new TestMessage();
        $testMsg->setSubject('Flotte zurückgerufen');
        $testMsg->setText('Ihre Flotte befand sich auf dem Weg zum Planeten „'.$toName.'“ ('.$pos.', Eigentümer: '.$user->getName().'). Soeben wurde jener Planet verlassen, weshalb Ihre Flotte sich auf den Rückweg zu Ihrem Planeten „'.$fromName.'“ ('.$mypos.') macht.');
        $testMsg->setFrom($uname);
        $testMsg->setType($types_message_types[$type]);
        $testUser->addMessage($testMsg);
        
     	$this->_testMessages($uname);
     	
     	// check if we still have a link to the old planet
     	foreach($user->getPlanetsList() as $planet)
     	{
     		$this->assertTrue($user->setActivePlanet($planet));
     		$this->assertNotSame($delPlanetPos, $user->getPos());
     	}

     	//maybe TODO, highscores test, something happens with research
	}

	public function _testMessages($uname)
	{
		$user = Classes::User($uname);
		$testUser = $this->testData->getUserWithName($uname);
		$testMsgs = $testUser->getMessages();		
		$i = 0;
		
		foreach($testMsgs as $testMsg)
		{
			$found = 0;
			
			foreach($user->getMessagesList($testMsg->getType()) as $msg)
			{
				$msgObj = Classes::Message($msg);
				
				if($msgObj->rawText() == $testMsg->getText() && $testMsg->getText() != "")
				{
					if ($msgObj->getSubject() == $testMsg->getSubject() && $testMsg->getSubject() != "")
					{
						if($msgObj->from( $uname ) == $testMsg->getFrom())
						{
							$found++;
						}
						else
							echo "from doesnt match\n";
					}
					else
						echo "subject doesnt match\n";
				}
				else
					echo "test doesnt match - expected: \n".$testMsg->getText()."\n == \n".$msgObj->rawText()."\n";

			}
			
			$this->assertEquals(1, $found);
		}
		
		$this->greaterThan(0, $i);
	}
	
	/*
	 * send a fleet and test if it were created
	 * the actual testing it derivated into a sub method
	 */
	public function _testSendFleetTo( $uname, $pos )
	{
		$fleet = Classes::Fleet();
		$user = Classes::User($uname);
		$mypos =  $user->getPosString();
		unset($user);

//		echo "\nflying from: ".$mypos. " to: ".$pos."\n";
	
		/*
		 * flotte als transport mit 10 kleinen transportern zum ziel $pos versenden
		 */
		$type = 6; // stationieren
		$fleet->create(); // no return 
		$this->test_Fleets[$uname][] = $fleet->getName();
		$this->assertTrue( $fleet->addTarget( $pos, $type, false ) );
		$this->assertEquals( $uname, $fleet->addUser( $uname, $mypos, 1 /* default */ ) );
		$this->assertTrue( $fleet->addTransport( $uname, array( 0, 0, 0, 0, 0 ), array() ) );
		$this->assertTrue( $fleet->addFleet( "S1", 100, $uname) );
		$this->assertTrue( $fleet->addHoldTime( 0 ) );
		$this->assertGreaterThan( 0, $fleet->calcNeededTritium( $uname) );
		$fleet->start(); // no return
		$this->assertEquals( $pos, $fleet->getCurrentTarget() );

		$user = Classes::User($uname);
		$this->assertTrue( $user->addFleet( $fleet->getName() ) );
		unset($user);
		unset($fleet);

		$this->_testIsFleetExistingSpecific( $uname, $pos, $mypos, array( "S1", 10 ), $type, false );
	}

	/*
	 * test if a given fleet is existant, it is expected to do, otherwise this test will fail
	 */
	public function _testIsFleetExistingSpecific( $from_user, $to_pos, $from_pos, $ships, $type, $flyingback )
	{
		$user = Classes::User($from_user);
		$fleets = $user->getFleetsList();

		$this->assertGreaterThan( 0, count( $fleets ), "no fleets found" );

		$fleet = false;

		foreach( $fleets as $ffleet )
		{
		 	$fleet = $ffleet;
		}
	
		if ( $fleet == false )
			throw new Exception( "_testIsFleetExistingSpecific() failed, no fleet found" );
		
		$fleet_obj = Classes::Fleet( $fleet );
		$that = Classes::Fleet( $fleet );
		$blub =	$user->getFleetsWithPlanet();
		
		unset( $user );

		$targets = $that->getTargetsList();

		$this->assertEquals( array( $to_pos ), $targets );
		$this->assertEquals( array( "S1" => 100 ), $fleet_obj->getFleetList( $from_user) );
		
		if(!$flyingback)
		{
			$this->assertFalse( $fleet_obj->isFlyingBack() );
		}
		else
		{
			$this->assertTrue( $fleet_obj->isFlyingBack() );
		}
			
	}

	public function _testAndGetMaxPlanets()
	{
		$maxplanets = global_setting( "MAX_PLANETS" );

		$this->assertGreaterThan( 1, $maxplanets );

		return $maxplanets;
	}

	public function testRegisterPlanet()
	{
		$fuser = Classes::User( "fakeuser1341" );
		$freeKoords = getFreeKoords();
		$maxplanets = $this->_testAndGetMaxPlanets();
		$testUsers = $this->testData->getTestUsers();
		$testUser = $testUsers[0];
		$user = Classes::User($testUser->getName());	
		$loops = 0;	
		
		while($testUser->getCreatedPlanetCount() <= $maxplanets + 1)
		{
			$loops++;
			$this->assertLessThan(100, $loops);
			
			if($freeKoords == false)
			{
				throw new Exception("testRegisterPlanet() failed, no free coords available");
			}
			
			$testCount = $testUser->getCreatedPlanetCount();
			
			if( $testCount >= $maxplanets)
			{
				$index = $user->registerPlanet($freeKoords);
				$this->assertFalse($index);
				break;
			}
			else
			{
				$index = $user->registerPlanet($freeKoords);
				$this->assertGreaterThanOrEqual(0, $index, 'registerPlanet() failed, current planetcount is :'.$testUser->getPlanetCount());
				$testUser->addNewPlanetCreated($index, $freeKoords);
			}
			
			$freeKoords = getFreeKoords();
		}
		
		// non-existant galaxy - 9
		$this->assertFalse($user->registerPlanet("9:".$freeKoords[1].":".$freeKoords[2]));
		
		// malformed koordinates
		$this->assertFalse($fuser->registerPlanet("1:3:3:7"));		

		$testPlanets = $testUser->getPlanets();
		$testPlanet = NULL;

		// try to register an already existing planet
		foreach($testPlanets as $planet)
		{
			if($planet->isCreated())
			{
				$testPlanet = &$planet;
			}
		}
		
		$this->assertNotNull($testPlanet);
		$this->assertFalse($user->registerPlanet($testPlanet->getGalaxy().":".$testPlanet->getSystem().":".$testPlanet->getSysIndex()));
		
		// 1st registered planet always has 375 fields
		$freeKoords = getFreeKoords();
		$freeKoordsArray = explode(":", $freeKoords);
		$newTestUser = $this->testData->getUnusedUser();
		$usaName = $newTestUser->getName();
		$newUser = Classes::User($usaName);
		$newUser->create();
		$newTestUser->setIsCreated(true);
		$index = $newUser->registerPlanet($freeKoords);
		$testPlanet = $newTestUser->addNewPlanetCreated($index, $freeKoords);
		$this->assertGreaterThanOrEqual(0, $index);
		$newUser->setActivePlanet($index);
		
		$galaxy = Classes::Galaxy( $freeKoords[0] );
		$this->assertGreaterThan(0, $galaxy->getStatus());
		$this->assertEquals(375, $newUser->getBasicFields());
		// we cant compare this, since galaxys size doesnt get overwritten when creating a 1st planet	
		//$this->assertEquals(375, $galaxy->getPlanetSize($freeKoordsArray[1], $freeKoordsArray[2]));
		
		// default planet name is "Kolonie"
		$this->assertEquals("Kolonie", $galaxy->getPlanetName($freeKoordsArray[1], $freeKoordsArray[2]));
		
		// check if research for making planets bigger is applied as it should
		$expFields = $newUser->getBasicFields();
		$newUser->changeItemLevel("F9", 10, "forschung");
		$expFields *= $newUser->getItemLevel( 'F9', 'forschung' ) + 1;
		$this->assertEquals($expFields, $newUser->getFields());
		$this->assertNotEquals($expFields, $newUser->getBasicFields());
		
		// 2nd planet, check if its size is same as galaxy says
		$freeKoords = getFreeKoords();
		$freeKoordsArray = explode(":", $freeKoords);		
		$index = $newUser->registerPlanet($freeKoords);
		$testPlanet = $newTestUser->addNewPlanetCreated($index, $freeKoords);
		$this->assertEquals(1, $index);
		$newUser->setActivePlanet($index);
		$galaxy = Classes::Galaxy( $freeKoords[0] );
		$this->assertGreaterThan(0, $galaxy->getStatus());
		$this->assertGreaterThan(0, $newUser->getBasicFields());	
		$this->assertGreaterThan(0, $galaxy->getPlanetSize($freeKoordsArray[1], $freeKoordsArray[2]));		
	}
	
	/**
	 * @testing 
	 * - very last planet cant be moved down
	 * - all other planets should be able to
	 * - check for reassigned researches
	 * - check for all items on the planets
	 * - check the planetList if they matches the new one
	 * - only available for testUsers with more than 2 created planets
	 * @return unknown_type
	 */
	public function testMovePlanetDown()
	{
	return;
		$fuser = Classes::User( "fakeuser1341" );
		$this->assertFalse($user->movePlanetDown(0));
		$testUsers = $this->testData->getTestUsers();
		
		foreach($testUsers as $testUser)
		{
			
		}
		
	}
}

// Call userTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'userTest::main') 
{
    userTest::main();
}
?>
