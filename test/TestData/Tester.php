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
    		
    		foreach( $user->getPlanets() as $planetData )
    		{
    			if( !$planetData->getShouldCreate())
    				continue;
    			
    			$index = $this->setUp_NewPlanet($user->getName(), $planetData);
    			
    			if ($index === false)
    			{
    				throw new Exception('setUp() failed, setting up planet failed.');
    			}

    			$planetData->setIndex( $index );
    			  
    			$this->setUp_PlanetRes( $user, $planetData );
    			$this->setUp_RandomizePlanet( $planetData, $user->getName(), $setupResearch);
    			
    			// LAST IN THAT FOREACH
    			if ($setupResearch)
    			{
    				$setupResearch = false;
    			}
    		}
    		
    		/*
    		 * this needs to be done after creating ALL planets for the given user since global researches affects all planets
    		 */
    		$tries = 0;
    		
    		for ( $uPlanets = &$user->getPlanets(); $tries < 100; $planetData = $uPlanets[array_rand( $uPlanets )] ) 
    		{
    			$tries++;
    			
    			if ($tries == 100)
    			{
    				$planetData = &$uPlanets[0];
    			}
    			
    			if ( $planetData->isCreated() )
    			{
    				$this->setUp_RandomBuildingResearch( $user, $planetData );
    				break;
    			}
    			else
    				continue;    		
    		}
    	}
    }
    
    protected function setUp_PlanetRes( &$testUser, &$planetData )
    {
    	$user = Classes::User( $testUser->getName() );
    	$user->setActivePlanet( $planetData->getIndex() );
    	$res = $planetData->getRes();
   		
    	if (!$user->addRess($res) )
    	{
    		throw new Exception("setUp_PlanetRes() failed, couldnt add ressources");
    	}
    }
    
    protected function setUp_RandomBuildingResearch( &$user, &$planetData )
    {
    	$items_instance = Classes::Items();
        $itemsList = $items_instance->getItemsList('forschung');
    	$i = array_rand($itemsList);
    	$id = $itemsList[$i];
    	$global = rand(0,1);
    	
    	// look for any researches active
    	foreach( $user->getPlanets() as $planet )
    	{
    		if ( $planet->getActiveResearch() != false )
    		{
    			// we already have a research going, return
    			return;
    		}
    	}
    	
    	if ( $id && $id != '' )
    	{
    		$userObj = Classes::User($user->getName());
    		$item_info = $userObj->getItemInfo( $id, 'forschung');

    		if ( !$userObj->setActivePlanet( $planetData->getIndex() ) )
    		{
    			throw new Exception("setUp_RandomBuildingResearch() failed, couldnt setActivePlanet to ".$planetData->getIndex()."\n");
    		}
    		
            //if ($global)
              //  print "user ".$user->getName()." ".$userObj->getName()." is going to buy global research on planet ".$userObj->getActivePlanet()." id ".$id." for ".$item_info['ress'][0]." ".$item_info['ress'][1]." ".$item_info['ress'][2]." ".$item_info['ress'][3]." ".$item_info['ress'][4]."\n";
            //else
              //  print "user ".$user->getName()." ".$userObj->getName()." is going to buy local research on planet ".$userObj->getActivePlanet()." id ".$id." for ".$item_info['ress'][0]." ".$item_info['ress'][1]." ".$item_info['ress'][2]." ".$item_info['ress'][3]." ".$item_info['ress'][4]."\n"; 

 	  		if ($global)
    		{
    			if ( !$userObj->getStatus() )
    			{
    				throw new Exception("setUp_RandomBuildingResearch() failed, invalid user obj");
    			}
    			
    		    if (!$userObj->buildForschung( $id, $global ))
    			{
					print "user ".$user->getName()." ".$userObj->getName()." is going to buy global research ".$id." for ".$item_info['ress'][0]." ".$item_info['ress'][1]." ".$item_info['ress'][2]." ".$item_info['ress'][3]." ".$item_info['ress'][4]."\n";
    				throw new Exception("setUp_RandomBuildingResearch() failed, failed setting up global research");
    			}    			
    			
    			foreach( $user->getPlanets() as $planet )
    			{
    				if ( !$planet->isCreated() )
    					continue;
    				
    				if ( !$userObj->setActivePlanet($planet->getIndex()) )
    					throw new Exception("setUp_RandomBuildingResearch() failed, couldnt set activePlanet to ".$planet->getIndex()."\n");
    				
    				//echo "setActiveResearch() on planet ".$userObj->getActivePlanet()." testData planet = ".$planetData->getIndex()."\n";
    				$planet->setActiveResearch( $id, $global, $planetData->getIndex() );
    			}
    		}
    		else
    		{
    			if (!$userObj->buildForschung( $id, $global ))
    			{
					print "user ".$user->getName()." ".$userObj->getName()." is going to buy local research ".$id." for ".$item_info['ress'][0]." ".$item_info['ress'][1]." ".$item_info['ress'][2]." ".$item_info['ress'][3]." ".$item_info['ress'][4]."\n";
    		        throw new Exception("setUp_RandomBuildingResearch() failed, failed to setup local research");
                }
                
               // echo "setActiveResearch() on planet ".$userObj->getActivePlanet()." testData planet = ".$planetData->getIndex()."\n";
                $planetData->setActiveResearch($id, $global);
            }
            
            // subtract ressources needed to build that from testData
          	$planetData->subRes( $item_info['ress'] ); 
          	
			/*
          	if ($global)
          		print "user ".$user->getName()." ".$userObj->getName()." has bought global research ".$id." for ".$item_info['ress'][0]." ".$item_info['ress'][1]." ".$item_info['ress'][2]." ".$item_info['ress'][3]." ".$item_info['ress'][4]."\n\n";
          	else
          		print "user ".$user->getName()." ".$userObj->getName()." has bought local research ".$id." for ".$item_info['ress'][0]." ".$item_info['ress'][1]." ".$item_info['ress'][2]." ".$item_info['ress'][3]." ".$item_info['ress'][4]."\n\n";           		      
			*/
        }
        else
        {
            throw new Exception("setup_RandomResearch() failed, no random research item");
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
                $minlvl = 20;
                $maxlvl = 40;
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
                $minlvl = 15;
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
