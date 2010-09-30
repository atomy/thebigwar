<?php

require_once "TestConstants.php";
require_once "TestUser.php";
require_once "TestHighscore.php";

/**
 * @author atomy
 *
 */
class TestData
{

    /*
	 * holds all planets for testing
	 */
    private $planets = array();

    /*
	 * holds all users for testing
	 */
    private $users = array();

    /*
	 * holds all fleets for testing
	 */
    private $fleet = array();

    /*
	 * holds highscore
	 */
    private $testHighScore;

    /*
     * increasing index used to retrieve testuserlist elements
     */
    private $testUserListIndex;

    /*
     * holds a compiled list of all our testusers
     */
    private $testUserList;

    /**
     * constructor
     * @return 
     */
    public function __construct( )
    {
        $this->generateTestData();
        $this->testHighScore = new TestHighscore();
    }

    /**
     * generate all needed test data
     * @return 
     */
    public function generateTestData( )
    {
        $this->generateTestUsers();
        
        foreach ( $this->getTestUsers() as $user ) {
            $user->generateTestPlanets();
        }
    }

    /**
     * generate some testusers
     * @return 
     */
    private function generateTestUsers( )
    {
        // used for initially setting up a testing universe with users
        $user = new TestUser( "helmut" );
        $user->setShouldCreate( true );
        $user->setShouldCreateOnSetup( true );
        $this->users[] = $user;
        
        $user = new TestUser( "bernd" );
        $user->setShouldCreate( true );
        $user->setShouldCreateOnSetup( true );
        $this->users[] = $user;
        
        // used for creating test users after setup within tests
        $user = new TestUser( "herbert" );
        $user->setShouldCreate( true );
        $user->setShouldCreateOnSetup( false );
        $this->users[] = $user;
        
        $user = new TestUser( "jenny" );
        $user->setShouldCreate( true );
        $user->setShouldCreateOnSetup( false );
        $this->users[] = $user;
    
    }

    /**
     * generate some test user names
     * @param object $count
     * @return 
     * @TODO - needs moar randomizing
     */
    private function generateRandomUserNames( $count )
    {

    }

    public function getUnusedUser( )
    {
        foreach ( $this->users as $user ) {
            if ( $user->isCreated() ) {
                continue;
            }
            
            if ( ! $user->shouldCreate() ) {
                continue;
            }
            
            return $user;
        }
        
        return false;
    }

    /**
     * 
     * @return array() - containing all users for testing
     */
    public function getTestUsers( )
    {
        return $this->users;
    }

    public function getUserWithName( $name )
    {
        foreach ( $this->getTestUsers() as $user ) {
            if ( $user->getName() == $name ) {
                return $user;
            }
        }
    }

    public function getExistingTestUsers( )
    {
        $existingUsers = array();
        
        foreach ( $this->users as $tUser ) {
            if ( $tUser->isCreated() )
                $existingUsers[] = $tUser;
        }
        
        if ( count( $existingUsers ) <= 0 )
            throw new Exception( "getExistingTestUsers() didnt catch any existing users!" );
        
        return $existingUsers;
    }

    public function getTestHighscore( )
    {
        return $this->testHighScore;
    }

    /**
     * gathers all active test users in the testData enviroment and saves them to a list for later usage
     */
    private function generateTestUserList( )
    {
        $this->testUserListIndex = 0;
        $this->testUserList = &$this->getExistingTestUsers();
    }

    /**
     * returns testPlanet objects as long we have ones
     * as long we arent out of elements return it!
     */
    public function getNextTestUser( )
    {
        if ( count( $this->testUserList ) == 0 ) {
            $this->generateTestUserList();
        }
        
        $index = $this->testUserListIndex;
        
        if ( isset( $this->testUserList[$index] ) ) {
            $this->testUserListIndex ++;
            
            return $this->testUserList[$index];
        }
        else
        {
            return false;
        }
    }
}
?>
