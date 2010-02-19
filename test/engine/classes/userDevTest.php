<?php

// Call userTest::main() if this source file is executed directly.
if ( ! defined( 'PHPUNIT_MAIN_METHOD' ) )
{
    define( 'PHPUNIT_MAIN_METHOD', 'userDevTest::main' );
}

require_once 'PHPUnit/Framework.php';

if ( is_file( '../../../include/config_inc.php' ) )
{
    require_once '../../../include/config_inc.php';
}
else 
    if ( is_file( '../include/config_inc.php' ) )
    {
        require_once '../include/config_inc.php';
    } 
    else
    {
        require_once 'include/config_inc.php';
    }

require_once TBW_ROOT . 'engine/include.php';
require_once TBW_ROOT . 'engine/classes/galaxy.php';
require_once TBW_ROOT . 'test/TestData/TestConstants.php';
require_once TBW_ROOT . 'test/TestData/TestData.php';
require_once TBW_ROOT . 'test/TestData/Tester.php';
require_once TBW_ROOT . 'test/TestData/TestMessage.php';
require_once TBW_ROOT . 'test/TestData/TestScore.php';

/** 
 * Test class for user.
 * Generated by PHPUnit on 2009-10-31 at 06:39:22.
 */
class userDevTest extends PHPUnit_Framework_TestCase
{

    private $testData;

    /**
     * tests if all researches within testData exists on the user itself
     * @param User $user
     * @param TestPlanet $planetData
     * @return -
     */
    public function _testActiveResearch( &$user, &$planetData )
    {
        $this->assertTrue( $user->setActivePlanet( $planetData->getIndex() ) );
        
        $research = $planetData->getActiveResearch();
        
        if ( $research == false )
            return;
            //		else
        //			print "testing research...\n";
        

        $aForschung = $user->checkBuildingThing( "forschung" );
        
        $this->assertEquals( $research->getId(), $aForschung[0], "failed comparing research id on planet " . $user->getActivePlanet() . " on user " . $user->getName() . "\n" );
        $this->assertEquals( $research->getGlobal(), $aForschung[2] );
        
        if ( $research->isGlobal() )
        {
            $this->assertEquals( $research->getStartPlanet(), $aForschung[4], "_testActiveResearch() failed for user " . $user->getName() . " planet: " . $user->getActivePlanet() . "\n" );
        }
        else
        {
            $this->assertFalse( isset( $aForschung[4] ) );
        }
    }

    /**
     * tests if users messages equals their messages stored within the testdata
     * @param string $uname
     * @return -
     */
    public function _testMessages( $uname )
    {
        $user = Classes::User( $uname );
        $testUser = $this->testData->getUserWithName( $uname );
        $testMsgs = $testUser->getMessages();
        $i = 0;
        
        foreach ( $testMsgs as $testMsg )
        {
            $found = 0;
            
            foreach ( $user->getMessagesList( $testMsg->getType() ) as $msg )
            {
                $msgObj = Classes::Message( $msg );
                
                if ( $msgObj->rawText() == $testMsg->getText() && $testMsg->getText() != "" )
                {
                    if ( $msgObj->getSubject() == $testMsg->getSubject() && $testMsg->getSubject() != "" )
                    {
                        if ( $msgObj->from( $uname ) == $testMsg->getFrom() )
                        {
                            $found ++;
                        }
                        else
                            echo "from doesnt match\n";
                    }
                    else
                        echo "subject doesnt match\n";
                }
                else
                    echo "test doesnt match - expected: \n" . $testMsg->getText() . "\n == \n" . $msgObj->rawText() . "\n";
            }
            $this->assertEquals( 1, $found );
        }
        $this->greaterThan( 0, $i );
    }

    /**    
	 * send a fleet and test if it were created
	 * the actual testing it derivated into a sub method
	 */
    public function _testSendFleetTo( $uname, $pos, $res = 0 )
    {
        $fleet = Classes::Fleet();
        $user = Classes::User( $uname );
        $mypos = $user->getPosString();
        unset( $user );       

        /*
		 * flotte als transport mit 10 kleinen transportern zum ziel $pos versenden
		 */
        $type = 6; // stationieren
        $fleetContent = array( "S1" => 100 ); // 100 gro�e? transporter
        $fleet->create(); // no return 
        $this->test_Fleets[$uname][] = $fleet->getName();
        $this->assertTrue( $fleet->addTarget( $pos, $type, false ) );
        $this->assertEquals( $uname, $fleet->addUser( $uname, $mypos, 1 /* default */ ) );
        
        $doTransportWithRes = false;
        
        if ( $res != 0 ) 
        {
            for( $i = 0; $i <= 4; $i++ )
            {
                if ( isset( $res[$i] ) )
                {
                    $doTransportWithRes = true;
                }
                
            }            
        }      
        
        $this->assertTrue( $fleet->addFleet( key( $fleetContent ), current( $fleetContent ), $uname ) );

        // Fleet::addTransport() has do be done after calling Fleet::addFleet()
        if ( $doTransportWithRes )
        {
            for( $i = 0; $i <= 4; $i++ )
            {
                if ( !isset( $res[$i] ) )
                {
                    $res[$i] = 0;
                }                
            }
  
            $this->assertTrue( $fleet->addTransport( $uname, $res ) );                        
        }
        else
        {
            $this->assertTrue( $fleet->addTransport( $uname, array( 0, 0, 0, 0, 0 ) ) );
        }
                
        $this->assertTrue( $fleet->addHoldTime( 0 ) );
        $this->assertGreaterThan( 0, $fleet->calcNeededTritium( $uname ) );
        $fleet->start(); // no return
        $this->assertEquals( $pos, $fleet->getCurrentTarget() );
        
        $user = Classes::User( $uname );
        $fleetid = $fleet->getName();
        $this->assertTrue( $user->addFleet( $fleetid ) );
        unset( $user );
        unset( $fleet );
        
        $this->_testIsFleetExistingSpecific( $uname, $fleetid, $pos, $mypos, $fleetContent, $type, false );
    }
    

    /**
     * returns the MAX_PLANETS global setting and tests it for plausibility
     * @return unknown_type
     */
    public function _testAndGetMaxPlanets( )
    {
        $maxplanets = global_setting( "MAX_PLANETS" );
        
        $this->assertGreaterThan( 1, $maxplanets );
        
        return $maxplanets;
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
        
        foreach ( $files as $value )
        {
            $fname = $dir . "/" . $value;
            
            if ( ! is_dir( $fname ) && is_file( $fname ) && is_writable( $fname ) )
            {
                unlink( $fname );
            }
        }
    }

    /**
     * subfunc for testing the whole setup
     * @param TestUser $userData
     * @return -
     */
    public function _testSetup( &$userData )
    {
        if ( $userData->shouldCreate() && $userData->shouldCreateOnSetup() )
        {
            $this->assertTrue( User::userExists( $userData->getName() ) );
        }
        else
        {
            $this->assertFalse( User::userExists( $userData->getName() ), "user " . $userData->getName() . " exists but shouldnt" );
        }
        
        if ( ! $userData->isCreated() )
        {
            $this->assertFalse( User::userExists( $userData->getName() ) );
            return;
        }
        
        $this->_testScoresOfUser( $userData );
        $userObj = Classes::User( $userData->getName() );
        
        foreach ( $userData->getPlanets() as $planetData )
        {
            $i = $planetData->getIndex();
            
            if ( $planetData->isCreated() )
            {
                $this->assertTrue( $userObj->setActivePlanet( $i ) );
                $this->assertTrue( $userObj->planetExists( $i ) );
                $this->assertEquals( $planetData->getName(), $userObj->planetName(), "for index: " . $i . "\n" );
                $this->_testPlanetItems( $userObj, $planetData );
                $this->_testRes( $userObj, $planetData );
            }
            else
            {
                $this->assertFalse( $userObj->setActivePlanet( $i ) );
                $this->assertFalse( $userObj->planetExists( $i ) );
                //$this->assertFalse( $userObj->planetName() ); // doesnt work, cause we couldnt change to that planet with setActivePlanet()
            }
        }
        
        //$userObj->doRecalcHighscores( true, true, true, true, true );
    }

    /**
     * 
     * @param $userData
     * @return unknown_type
     */
    private function _testScoresOfUser( &$userData )
    {
        $testScores = &$userData->getScores();
        $userObj = Classes::User( $userData->getName() );
        $this->assertType( 'object', $userObj );
        
        $userObj->doRecalcHighscores( true, true, true, true, true );
        $userObj->clearCache();
        
        $testPlanets = $userData->getPlanets();
        
        foreach ( $testPlanets as $testPlanet )
        {
            $ret = $userObj->setActivePlanet( $testPlanet->getIndex() );
            
            if ( $ret === false )
            {
                $this->assertFalse( $testPlanet->isCreated() );
                continue;
            }
            else
                $this->assertTrue( $testPlanet->isCreated() );
            
            $testItems = $testPlanet->getItems();
            
            // check all buildings
            $items = $userObj->getItemsList( 'gebaeude' );
            
            foreach ( $items as $item )
            {
                // test if item exists in our test data
                $this->assertTrue( isset( $testItems[$item] ), "item: " . $item . " on planet: " . $testPlanet->getIndex() . " of user: " . $userData->getName() . " doesnt exists in testData" );
                
                $this->assertEquals( $testPlanet->getIndex(), $userObj->getActivePlanet() );
                
                $testItem = $testItems[$item];
                $item_info = $userObj->getItemInfo( $item, 'gebaeude', true, true );
                
                // test if score of the item is the same as in our test data
                $this->assertEquals( $item_info['scores'], $testItem->getScore(), "failed comparing score for " . $item . " user: " . $userObj->getName() . " planet: " . $testPlanet->getIndex() . " level of testItem: " . $testItem->getLevel() . " level of realItem: " . $item_info['level'] );
            }
            
            // check all robots
            $items = $userObj->getItemsList( 'roboter' );
            
            foreach ( $items as $item )
            {
                // test if item exists in our test data
                $this->assertTrue( isset( $testItems[$item] ), "item: " . $item . " on planet: " . $testPlanet->getIndex() . " of user: " . $userData->getName() . " doesnt exists in testData" );
                
                $testItem = $testItems[$item];
                $item_info = $userObj->getItemInfo( $item, 'roboter', true, true );
                
                // test if score of the item is the same as in our test data
                $this->assertEquals( $item_info['scores'], $testItem->getScore(), "failed comparing score for " . $item . " user: " . $userObj->getName() . " planet: " . $testPlanet->getIndex() );
            }
            
            // check all ships
            $items = $userObj->getItemsList( 'schiffe' );
            
            foreach ( $items as $item )
            {
                // test if item exists in our test data
                $this->assertTrue( isset( $testItems[$item] ), "item: " . $item . " on planet: " . $testPlanet->getIndex() . " of user: " . $userData->getName() . " doesnt exists in testData" );
                
                $testItem = $testItems[$item];
                $item_info = $userObj->getItemInfo( $item, 'schiffe', true, true );
                
                // test if score of the item is the same as in our test data
                $this->assertEquals( $item_info['scores'], $testItem->getScore(), "failed comparing score for " . $item . " user: " . $userObj->getName() . " planet: " . $testPlanet->getIndex() );
            }
            
            // check all defense
            $items = $userObj->getItemsList( 'verteidigung' );
            
            foreach ( $items as $item )
            {
                // test if item exists in our test data
                $this->assertTrue( isset( $testItems[$item] ), "item: " . $item . " on planet: " . $testPlanet->getIndex() . " of user: " . $userData->getName() . " doesnt exists in testData" );
                
                $testItem = $testItems[$item];
                $item_info = $userObj->getItemInfo( $item, 'verteidigung', true, true );
                
                // test if score of the item is the same as in our test data
                $this->assertEquals( $item_info['scores'], $testItem->getScore(), "failed comparing score for " . $item . " user: " . $userObj->getName() . " planet: " . $testPlanet->getIndex() );
            }
        }
        
        $k = 0;
        
        // till 11 or more for ressource scores - but should be 11
        for ( $i = 0; $userObj->getScores( $i ) != 0 || $i <= 11; $i ++ )
        {
            if ( $i == 1 )
            {
                continue;
            }
            
            $sum = 0;
            
            //$userObj->doRecalcHighscores( true, true, true, true, true, true );
            if ( $i >= 0 && $i <= 4 )
                $sum = $userData->getSumScores( $i );
            else 
                if ( $i >= 4 && $i <= 11 )
                {
                    $testScores = $userData->getScores();
                    $sum = $testScores->getScoreID( $i );
                }
            
            $this->assertGreaterThanOrEqual( 0, $sum );
            $diff = $sum - $userObj->getScores( $i );
            $this->assertEquals( $sum, $userObj->getScores( $i ), "_testScoresOfUser() failed, for key: " . $i . " diff: " . $diff . "\n" );
            
            $k ++;
        }
        
        // we expect to test 11 score values
        $this->assertEquals( 11, $k );
        
        $sum = 0;
        $testScoresArray = $testScores->getAllScoresAsArray();
        
        for ( $i = 0; $i <= 6; $i ++ )
        {
            $sum += $testScoresArray[$i];
            //print "testScoresArray() adding " . $testScoresArray[$i] . " for id: " . $i . " user: " . $userData->getName() . "\n";
        }
        
        $userObj->doRecalcHighscores( true, true, true, true, true );
        $userObj->clearCache();
        
        $this->assertGreaterThan( 0, $sum, "testscores of user " . $userData->getName() . " are empty?!\n" );       

        // scores only exists up to 11, above shouldnt exists
        $this->assertEquals( 0, $userObj->getScores( 12 ) );
        
        // erase cache and retest
        $userObj->doRecalcHighscores( true, true, true, true, true );
    }

    /*
	 * test if a given fleet is existant, it is expected to do, otherwise this test will fail
	 */
    public function _testIsFleetExistingSpecific( $from_user, $fleetid, $to_pos, $from_pos, $ships, $type, $flyingback )
    {
        $user = Classes::User( $from_user );
        $fleets = $user->getFleetsList();
        
        $this->assertGreaterThan( 0, count( $fleets ), "no fleets found" );

        // search our fleet by $fleetid
        $fleet = false;

        foreach ( $fleets as $ffleet )
        {
            // found!, save.
            // if fleetid is 0 take the very first fleet
            if ( $ffleet == $fleetid || $fleetid == 0 )
            {
                $fleet = $ffleet;
            }
        }
        
        // not found
        if ( $fleet == false )
        {
            throw new Exception( "_testIsFleetExistingSpecific() failed, no fleet found" );
        }
        
        $fleet_obj = Classes::Fleet( $fleet );
        $that = Classes::Fleet( $fleet );
        $blub = $user->getFleetsWithPlanet();
        
        unset( $user );
        
        $targets = $that->getTargetsList();
        
        $this->assertEquals( array( $to_pos ), $targets );
        $this->assertEquals( $ships, $fleet_obj->getFleetList( $from_user ) );
        
        if ( ! $flyingback )
        {
            $this->assertFalse( $fleet_obj->isFlyingBack() );
        }
        else
        {
            $this->assertTrue( $fleet_obj->isFlyingBack() );
        }
    }

    /**
     * compares all planet items stored in testdata to the actual existing ones on the users planet
     * @param User $user
     * @param TestPlanet $planetData
     * @return -
     */
    public function _testPlanetItems( &$user, &$planetData )
    {
        $this->assertGreaterThanOrEqual( 0, $planetData->getIndex() );
        
        $this->assertTrue( $user->setActivePlanet( $planetData->getIndex() ) );
        
        foreach ( $planetData->getItems() as $itemData )
        {
            $id = $itemData->getId();
            $level = $itemData->getLevel();
            
            $lvl = $user->getItemLevel( $id, false, false );
            if ( $level != $lvl /*&& $planetData->getIndex() == 4*/)
				echo "MISMATCH -- expected: " . $level . " got: " . $lvl . " for item: " . $id . " on planet " . $planetData->getName() . " (" . $user->planetName() . ")\n";
            $this->assertEquals( $level, $lvl, '_testPlanetItems() expected level didnt match for given item ' . $id . ' wanted: ' . $level . ' got: ' . $lvl );
            //echo "testing item: ".$id." for level: ".$level." which is: ".$lvl."\n";
        }
        
        $this->_testActiveResearch( $user, $planetData );
    }

    /**
     * compares main ressources from testData to the existing ones on the planet
     * @param User $user
     * @param TestPlanet $planetData
     * @return -
     */
    public function _testRes( &$user, &$planetData )
    {
        $this->assertTrue( $user->setActivePlanet( $planetData->getIndex() ) );
        
        $testRes = $planetData->getRes();
        $planetRes = $user->getRess();
        
        foreach ( $testRes as $key => $value )
        {
            // TODO, check and calc tritium
            if ( $key == 4 )
            {
                continue;
            }
            
            // TODO, needs more accurate check
            $this->assertGreaterThanOrEqual( $testRes[$key], $planetRes[$key], $key . " didnt match of planet " . $planetData->getIndex() . " - " . $planetData->getName() . " of user " . $user->getName() . "\n" );
        }
    
    }

    protected function _buildTestHighScore()
    {
        $userObj = NULL;
        
        foreach ( $this->testData->getTestUsers() as $testUser )
        {
            if ( ! $testUser->isCreated() )
            {
                $userObj = Classes::User( $testUser->getName() );
                continue;
            }
            else
            {
                $userObj = Classes::User( $testUser->getName() );
            }

            $userObj->doRecalcHighscores( true, true, true, true, true );
            $testHighScore = &$this->testData->getTestHighscore();
            $testHighScore->addUser( $userObj->getName(), $userObj->getScores() );
        }        
        
        $testHighScore->buildRankList();
    }      
    
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
        
        $this->testData = new TestData( );
        $this->tester = new Tester( $this->testData );
        $this->tester->setUp();
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
     

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////// TESTS END HERE //////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
}

// Call userDevTest::main() if this source file is executed directly.
if ( PHPUNIT_MAIN_METHOD == 'userDevTest::main' )
{
    userDevTest::main();
}
?>
