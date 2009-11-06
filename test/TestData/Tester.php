<?php


class tester 
{
	public function setUp( &$testData )
	{
		// define_globals( Uni ); to set globals like where db files are located etc
		define_globals( 'TestUni1' );
		
		if ( !$this->setUp_NewUser( $this->testUname1 ) )
			throw new Exception( 'setUp() failed, borken for status for $user obj returned' );

        if ( !$this->setUp_NewUser( $this->testUname2 ) )
            throw new Exception( 'setUp() failed, borken for status for $user obj returned' );

		// $this->test_Users[UserID][UserName]
		$this->setUp_NewPlanet( $this->test_Users[0][0], "MainPlanet" );
		$this->setUp_NewPlanet( $this->test_Users[0][0], "TestPlanet1" );

        $this->setUp_NewPlanet( $this->test_Users[1][0], "MainPlanet" );
        $this->setUp_NewPlanet( $this->test_Users[1][0], "TestPlanet1" );

		$this->setUp_RandomizePlanet( $this->test_Users[0][0], 0, true );
		$this->setUp_RandomizePlanet( $this->test_Users[0][0], 1, false );

        $this->setUp_RandomizePlanet( $this->test_Users[1][0], 0, true );
		$this->setUp_RandomizePlanet( $this->test_Users[1][0], 1, false );
    }

	protected function setUp_NewUser( $username )
	{
        $nuser = Classes::User( $username );
        $nuser->create();

        array_push( $this->test_Users, array( $username, true ) );
		$status = $nuser->getStatus();
		unset( $nuser );

		return $status;
	}

	/*
	 * helper func for setting up a random planet,
	 * sets and gets back random item levels for each class
	 */
	protected function setUp_RandomPlanet( $planet, $uname, $research = false )
	{
		$random_ItemLevels = array();

		$random_ItemLevels = array_merge( $random_ItemLevels, $this->setUp_RandomItemClass( $planet, $uname, 'gebaeude' ) );
		$random_ItemLevels = array_merge( $random_ItemLevels, $this->setUp_RandomItemClass( $planet, $uname, 'roboter' ) );
		$random_ItemLevels = array_merge( $random_ItemLevels, $this->setUp_RandomItemClass( $planet, $uname, 'schiffe' ) );
		$random_ItemLevels = array_merge( $random_ItemLevels, $this->setUp_RandomItemClass( $planet, $uname, 'verteidigung' ) );

		if ( $research )
			$random_ItemLevels = array_merge( $random_ItemLevels, $this->setUp_RandomItemClass( $planet, $uname, 'forschung' ) );

		return $random_ItemLevels;
	}

	/*
	 * setting up random items for a given class on the active planet
	 * @args $class - name the class for which all available items should be randomized
	 * @return - returns a list of the random levels with the id as key
	 */
	protected function setUp_RandomItemClass( $planet, $uname, $class )
	{
		$minlvl = 0;
		$maxlvl = 0;

		switch( $class )
		{
			case 'gebaeude' :
				$minlvl = 0;
				$maxlvl = 20;
			break;

			case 'roboter' :
				$minlvl = 0;
				$maxlvl = 200;
			break;

			case 'schiffe' :
				$minlvl = 0;
				$maxlvl = 9999;
			break;

			case 'verteidigung' :
				$minlvl = 0;
				$maxlvl = 9999;
			break;

			case 'forschung' :
				$minlvl = 0;
				$maxlvl = 20;
			break;

			default:
				throw new Exception( 'setUp_RandomItemClass() called with unsupported class: '.$class );
			break;
		}

        $randomItemLevels = array();
		$user = Classes::User($uname);
		$user->setActivePlanet($planet);

		if (! $user->getStatus() )
			throw new Exception( "OOOOOOOOOOOOOOOOOPS" );
        $itemList = $user->getItemsList( $class );

		if ( !$itemList )
			throw new Exception( 'setUp_RandomItemClass() couldnt get ItemsList of class: '.$class.' from user '.$user->getName() );

		foreach( $itemList as $item )
		{
			$randomLevel = rand( $minlvl, $maxlvl );
			$randomItemLevels[$item] = $randomLevel;
			$user->changeItemLevel( $item, $randomLevel, $class );
//			echo "added ".$randomLevel." items of ".$item."\n";
		}
		unset( $user );

		return $randomItemLevels;
	}

	/*
	 * sets up a new planet
	 * @args $name - name of the new planet
	 */
	protected function setUp_NewPlanet( $uname, $name = false )
	{
		if ( !$this->setUp_addPlanet( $uname, $name ) )
			throw new Exception( 'setUp_MainPlanet() failed, setUp_addPlanet() returned false' );
	}

	/*
	 * this func will randomize all levels for buildings, research, robots and ships on a given planet
	 * and return its values for asserting
	 *
	 * @args $planet - planet which will be the target
	 * @return array() - array containing the random levels
	 */	
	protected function setUp_RandomizePlanet( $uname, $planet, $research = false )
	{
		$this->random_ItemLevels[$uname][$planet] = $this->setUp_RandomPlanet( $planet, $uname, $research );
	}

	/* 
	 * adds another planet to the user
	 */
	protected function setUp_addPlanet( $uname, $name = false )
	{
        $koords = getFreeKoords();

		if( !User::userExists( $uname ) )
			throw new Exception( 'setUp_MainPlanet() failed, $user is invalid' );

        if( $koords )
        {
//			print "setUp_addPlanet() trying to register a planet for ".$user->getName()." at ".$koords."\n";
			$user = Classes::User( $uname );
            $index = $user->registerPlanet( $koords );

            if ( $index === false )
                throw new Exception( 'setUp_MainPlanet() failed, couldnt setup planet on given coordinates - '.$koords.' for '.$user->getName() );
            else
            {
                $user->setActivePlanet( $index );

				if ( $name )
				{
	                $user->planetName( $name );
					$this->test_PlanetNames[$user->getName()][$index] = $name;
				}

				$this->test_PlanetCoordinates[$user->getName()][$index] = $koords;
				$this->test_PlanetCreated[$user->getName()][$index] = true;
		
				return true;
            }
			unset($user);
        }
        else
            throw new Exception( 'setUp_MainPlanet() failed, no free coordinates for setting up planet' );		
	}

}
?>