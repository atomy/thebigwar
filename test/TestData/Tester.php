<?php


class tester 
{
	private $testData;
	
	public function __construct( &$testData )
	{
		$this->testData = $testData;
	}
	
    public function setUp()
    {
     
    	foreach( $this->testData->getTestUsers() as $user )
    	{
    		if ( !$user->shouldCreate() || !$user->shouldCreateOnSetup() )
    			continue;
    			
    		if ( !$this->setUp_NewUser( $user ) )
    		{
    			throw new Exception( 'setUp() failed, borken status while setting up ".$user." returned' );
    		}
    		
    		$setupResearch = true;
    		
    		foreach( $user->getPlanets() as $planet )
    		{
    			if(!$planet->getShouldCreate())
    				continue;
    			
    			$index = $this->setUp_NewPlanet($user->getName(), $planet);
    			
    			if ($index === false)
    			{
    				throw new Exception('setUp() failed, setting up planet failed.');
    			}

    			$planet->setIndex($index);
    				
    			$this->setUp_RandomizePlanet($planet, $user->getName(), $setupResearch);
    			
    			// LAST IN THAT FOREACH
    			if ($setupResearch)
    			{
    				$setupResearch = false;
    			}
    		}    		
    		
    	}
    }

    protected function setUp_NewUser( $testUser )
    {
        $nuser = Classes::User( $testUser->getName() );
        $nuser->create();
		$testUser->setIsCreated(true);

        return $nuser->getStatus();
    }

    /*
     * helper func for setting up a random planet,
     * sets and gets back random item levels for each class
     */
    protected function setUp_RandomizePlanet( &$planetData, $uname, $research = false )
    {
		$planetData->addItemLevels($this->setUp_RandomItemClass( $planetData->getIndex(), $uname, 'gebaeude' ));
        $planetData->addItemLevels($this->setUp_RandomItemClass( $planetData->getIndex(), $uname, 'roboter' ) );
        $planetData->addItemLevels($this->setUp_RandomItemClass( $planetData->getIndex(), $uname, 'schiffe' ) );
        $planetData->addItemLevels($this->setUp_RandomItemClass( $planetData->getIndex(), $uname, 'verteidigung' ) );

        if ( $research )
        {
        	$rData = $this->setUp_RandomItemClass( $planetData->getIndex(), $uname, 'forschung' );
        	$planetData->addItemLevels($rData);      	
        }
            
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
        
        if ($user->setActivePlanet($planet) === false )
        {
        	throw new Exception('setUp_RandomItemClass() failed, couldnt setactiveplanet to '.$planet.'\n');
        }
        
        $itemList = $user->getItemsList( $class );

        if ( !$itemList )
            throw new Exception( 'setUp_RandomItemClass() couldnt get ItemsList of class: '.$class.' from user '.$user->getName() );

        foreach( $itemList as $item )
        {
            $randomLevel = rand( $minlvl, $maxlvl );
            $randomItemLevels[$item] = $randomLevel;
            //if($class == 'forschung')
            	//echo "changing lvl of ".$item." from ".$user->getItemLevel($item, 'forschung')." to ".$randomLevel."\n";
            $user->changeItemLevel( $item, $randomLevel, $class );
//            echo "added ".$randomLevel." items of ".$item."\n";
        }
        unset( $user );

        return $randomItemLevels;
    }

    /*
     * sets up a new planet
     * @args $name - name of the new planet
     */
    protected function setUp_NewPlanet( $uname, &$planet )
    {
    	$index = $this->setUp_addPlanet( $uname, $planet );
    	
        if ( $index === false )
        {
            throw new Exception( 'setUp_MainPlanet() failed, setUp_addPlanet() returned false' );
        }
        else
        {
        	return $index;
        }
       
    }

    /* 
     * adds another planet to the user
     */
    protected function setUp_addPlanet( $uname, &$planet )
    {
        $koords = getFreeKoords();

        if( !User::userExists( $uname ) )
            throw new Exception( 'setUp_MainPlanet() failed, $user is invalid' );

        if( $koords )
        {
//            print "setUp_addPlanet() trying to register a planet for ".$user->getName()." at ".$koords."\n";
            $user = Classes::User( $uname );
            $index = $user->registerPlanet( $koords );

            if ( $index === false )
                throw new Exception( 'setUp_MainPlanet() failed, couldnt setup planet on given coordinates - '.$koords.' for '.$user->getName() );
            else
            {
                $user->setActivePlanet( $index );

                if ( $planet->getName() )
                {
                    $user->planetName( $planet->getName() );
                }

                $planet->setPosString($koords);
                $planet->setIsCreated(true);
        
                return $index;
            }
        }
        else
            throw new Exception( 'setUp_MainPlanet() failed, no free coordinates for setting up planet' );        
    }

}

?>