<?php

require_once "testConstants.php";
	
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
		$this->generateTestUsers();
	}
	
	/**
	 * generate all needed test data
	 * @return 
	 */
	public function generateTestData()
	{
	}
	
	/**
	 * generate some testusers
	 * @return 
	 */
	private function generateTestUsers()
	{
		$this->users[] = new TestUser( "helmut" );
		$this->users[] = new TestUser( "bernd" );
		$this->users[] = new TestUser( "jenny" );				
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
}
?>
