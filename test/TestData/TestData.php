<?php

require_once "TestConstants.php";
require_once "TestUser.php";
	
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
	
	/**
	 * constructor
	 * @return 
	 */
	public function __construct()
	{
		$this->generateTestData();
	}
	
	/**
	 * generate all needed test data
	 * @return 
	 */
	public function generateTestData()
	{
		$this->generateTestUsers();
		
		foreach( $this->getTestUsers() as $user )
		{
			$user->generateTestPlanets();
		}
	}
	
	/**
	 * generate some testusers
	 * @return 
	 */
	private function generateTestUsers()
	{
		// used for initially setting up a testing universe with users
		$user = new TestUser( "helmut" );
		$user->setShouldCreate(true);
		$user->setShouldCreateOnSetup(true);
		$this->users[] = $user;
		
		$user = new TestUser( "bernd" );
		$user->setShouldCreate(true);
		$user->setShouldCreateOnSetup(true);
		$this->users[] = $user;
		
		// used for creating test users after setup within tests
		$user = new TestUser( "herbert" );
		$user->setShouldCreate(true);
		$user->setShouldCreateOnSetup(false);
		$this->users[] = $user;
				
		$user = new TestUser( "jenny" );
		$user->setShouldCreate(true);
		$user->setShouldCreateOnSetup(false);
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
	
	public function getUnusedUser()
	{
		foreach($this->users as $user)
		{
			if($user->isCreated())
			{
				continue;
			}
			
			if(!$user->shouldCreate())
			{
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
	public function getTestUsers()
	{
		return $this->users;
	}
}
?>
