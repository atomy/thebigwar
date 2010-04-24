<?php

/**
 * this class is responsible for setting up a complete test enviroment,
 * including test users,
 * test planets,
 * test items on the planets,
 * test researches,
 * test ships,
 * etc.
 * @author atomy
 *
 */
class tester
{

    private $testData;

    public function __construct( &$testData )
    {
        $this->testData = $testData;
    }

    public function setUp( )
    {
        foreach ( $this->testData->getTestUsers() as $user )
        {
            if ( ! $user->shouldCreate() || ! $user->shouldCreateOnSetup() )
                continue;
            
            if ( ! $this->setUp_NewUser( $user ) )
            {
                throw new Exception( 'setUp() failed, borken status while setting up ".$user." returned' );
            }
            
            $setupResearch = true;
            //echo "processing user: ".$user->getName()."\n";
            
            foreach ( $user->getPlanets() as $planetData )
            {
                if ( ! $planetData->getShouldCreate() )
                    continue;
                
                $index = $this->setUp_NewPlanet( $user->getName(), $planetData );
                
                if ( $index === false ) 
                {
                    throw new Exception( 'setUp() failed, setting up planet failed.' );
                }
                
                $planetData->setIndex( $index );
                
                $this->setUp_PlanetRes( $user, $planetData );
                $this->setUp_RandomizePlanet( $planetData, $user, $setupResearch );
                
                // LAST IN THAT FOREACH
                if ( $setupResearch )
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
                $tries ++;
                
                if ( $tries == 100 )
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
            //$userObj = Classes::User($user->getName());
        //$userObj->doRecalcHighscores(true,true,true,true,true);
        }
    }

    protected function setUp_PlanetRes( &$testUser, &$planetData )
    {
        $user = Classes::User( $testUser->getName() );
        $user->setActivePlanet( $planetData->getIndex() );
        $res = $planetData->getRes();
        
        if ( ! $user->addRess( $res ) )
        {
            throw new Exception( "setUp_PlanetRes() failed, couldnt add ressources" );
        }
    }

    protected function setUp_RandomBuildingResearch( &$user, &$planetData )
    {
        $items_instance = Classes::Items();
        $itemsList = $items_instance->getItemsList( 'forschung' );
        $i = array_rand( $itemsList );
        $id = $itemsList[$i];
        $global = rand( 0, 1 );
        
        // look for any researches active
        foreach ( $user->getPlanets() as $planet )
        {
            if ( $planet->getActiveResearch() != false )
            {
                // we already have a research going, return
                return;
            }
        }
        
        if ( $id && $id != '' )
        {
            $userObj = Classes::User( $user->getName() );
            $item_info = $userObj->getItemInfo( $id, 'forschung' );
            
            if ( ! $userObj->setActivePlanet( $planetData->getIndex() ) )
            {
                throw new Exception( "setUp_RandomBuildingResearch() failed, couldnt setActivePlanet to " . $planetData->getIndex() . "\n" );
            }
            
            //if ($global)
            //  print "user ".$user->getName()." ".$userObj->getName()." is going to buy global research on planet ".$userObj->getActivePlanet()." id ".$id." for ".$item_info['ress'][0]." ".$item_info['ress'][1]." ".$item_info['ress'][2]." ".$item_info['ress'][3]." ".$item_info['ress'][4]."\n";
            //else
            //  print "user ".$user->getName()." ".$userObj->getName()." is going to buy local research on planet ".$userObj->getActivePlanet()." id ".$id." for ".$item_info['ress'][0]." ".$item_info['ress'][1]." ".$item_info['ress'][2]." ".$item_info['ress'][3]." ".$item_info['ress'][4]."\n"; 
            
            $spentRes = array( 0, 0, 0, 0, 0 );
            
            if ( $global )
            {
                if ( ! $userObj->getStatus() )
                {
                    throw new Exception( "setUp_RandomBuildingResearch() failed, invalid user obj" );
                }
                
                $spentRes = $item_info['ress'];
                $ret = $userObj->buildForschung( $id, $global );
                
                if ( $ret === false )
                {
                    print "user " . $user->getName() . " " . $userObj->getName() . " tried to build global research " . $id . " for " . $item_info['ress'][0] . " " . $item_info['ress'][1] . " " . $item_info['ress'][2] . " " . $item_info['ress'][3] . " " . $item_info['ress'][4] . "\n";
                    throw new Exception( "setUp_RandomBuildingResearch() failed, failed setting up global research" );
                }
                else
                {
                    $testScores = &$user->getScores();
                    $testScores->addSpentRes( $spentRes );
                }                
                
                foreach ( $user->getPlanets() as $planet )
                {
                    if ( ! $planet->isCreated() )
                        continue;
                    
                    if ( ! $userObj->setActivePlanet( $planet->getIndex() ) )
                        throw new Exception( "setUp_RandomBuildingResearch() failed, couldnt set activePlanet to " . $planet->getIndex() . "\n" );
                        
                    //echo "setActiveResearch() on planet ".$userObj->getActivePlanet()." testData planet = ".$planetData->getIndex()."\n";
                    $planet->setActiveResearch( $id, $global, $planetData->getIndex() );                    
                }
            }
            else
            {
                $spentRes = $item_info['ress'];                
                $ret = $userObj->buildForschung( $id, $global );                
                
                if ( $ret === false )
                {
                    print "user " . $user->getName() . " " . $userObj->getName() . " is going to buy local research " . $id . " for " . $item_info['ress'][0] . " " . $item_info['ress'][1] . " " . $item_info['ress'][2] . " " . $item_info['ress'][3] . " " . $item_info['ress'][4] . "\n";
                    throw new Exception( "setUp_RandomBuildingResearch() failed, failed to setup local research" );
                }
                else
                {
                    $testScores = &$user->getScores();
                    $testScores->addSpentRes( $spentRes );
                } 
                
                // echo "setActiveResearch() on planet ".$userObj->getActivePlanet()." testData planet = ".$planetData->getIndex()."\n";
                $planetData->setActiveResearch( $id, $global );
            }
            
            // subtract ressources needed to build that from testData
            $planetData->subRes( $spentRes );
            
        /*
          	if ($global)
          		print "user ".$user->getName()." ".$userObj->getName()." has bought global research ".$id." for ".$item_info['ress'][0]." ".$item_info['ress'][1]." ".$item_info['ress'][2]." ".$item_info['ress'][3]." ".$item_info['ress'][4]."\n\n";
          	else
          		print "user ".$user->getName()." ".$userObj->getName()." has bought local research ".$id." for ".$item_info['ress'][0]." ".$item_info['ress'][1]." ".$item_info['ress'][2]." ".$item_info['ress'][3]." ".$item_info['ress'][4]."\n\n";           		      
			*/
        }
        else
        {
            throw new Exception( "setup_RandomResearch() failed, no random research item" );
        }
    }

    protected function setUp_NewUser( $testUser )
    {
        $nuser = Classes::User( $testUser->getName() );
        $nuser->create();
        $testUser->setIsCreated( true );
        
        return $nuser->getStatus();
    }

    /*
     * helper func for setting up a random planet,
     * sets and gets back random item levels for each class
     */
    protected function setUp_RandomizePlanet( &$planetData, &$testUser, $research = false )
    {
        /*
         * request an array filled with all available items in an given category and random levels
         * those are also set on the given user
         */
        $gebItems = $this->setUp_RandomItemClass( $planetData->getIndex(), $testUser, 'gebaeude' );
        $robItems = $this->setUp_RandomItemClass( $planetData->getIndex(), $testUser, 'roboter' );
        $schItems = $this->setUp_RandomItemClass( $planetData->getIndex(), $testUser, 'schiffe' );
        $vertItems = $this->setUp_RandomItemClass( $planetData->getIndex(), $testUser, 'verteidigung' );
        
        /*
         * add the items to our test database
         */
        $planetData->addItemLevels( $gebItems, 'gebaeude' );
        $planetData->addItemLevels( $robItems, 'roboter' );
        $planetData->addItemLevels( $schItems, 'schiffe' );
        $planetData->addItemLevels( $vertItems, 'verteidigung' );        
        
        /*
         * loop through the items to get their scores and also save them in our test db
         */
        $itemScores = array();

        // get all items for buildings
        foreach( $gebItems as $id => $level )
        {
            $user = Classes::User( $testUser->getName() );
            $iInfo = $user->getItemInfo( $id, 'gebaeude', true, true );
            $itemScores[$id] = $iInfo['scores'];
            
            // echo "saving score: ".$iInfo['scores']." for item: ".$id." on planet: ".$planetData->getIndex()." on user: ".$user->getName()."\n";                         
        }
        
        // get all items for robots
        foreach( $robItems as $id => $level )
        {
            $user = Classes::User( $testUser->getName() );
            $iInfo = $user->getItemInfo( $id, 'roboter', true, true );
            $itemScores[$id] = $iInfo['scores'];
            
            //echo "saving score: ".$iInfo['scores']." for item: ".$id." on planet: ".$planetData->getIndex()." on user: ".$user->getName()."\n";                         
        }     

        // get all items for ships
        foreach( $schItems as $id => $level )
        {
            $user = Classes::User( $testUser->getName() );
            $iInfo = $user->getItemInfo( $id, 'schiffe', true, true );
            $itemScores[$id] = $iInfo['scores'];
            
            //echo "saving score: ".$iInfo['scores']." for item: ".$id." on planet: ".$planetData->getIndex()." on user: ".$user->getName()."\n";                         
        }  

        // get all items for defense
        foreach( $vertItems as $id => $level )
        {
            $user = Classes::User( $testUser->getName() );
            $iInfo = $user->getItemInfo( $id, 'verteidigung', true, true );
            $itemScores[$id] = $iInfo['scores'];
            
            //echo "saving score: ".$iInfo['scores']." for item: ".$id." on planet: ".$planetData->getIndex()." on user: ".$user->getName()."\n";                         
        }          

        // save
        $planetData->setItemScores( $itemScores );
                
        if ( $research )
        {
            $rData = $this->setUp_RandomItemClass( $planetData->getIndex(), $testUser, 'forschung' );
            $planetData->addItemLevels( $rData );
        }    
    }

    /*
     * setting up random items for a given class on the active planet
     * @args $class - name the class for which all available items should be randomized
     * @return - returns a list of the random levels with the id as key
     */
    protected function setUp_RandomItemClass( $planet, &$testUser, $class )
    {
        $minlvl = 0;
        $maxlvl = 0;
        
        switch ( $class )
        {
            case 'gebaeude':
                $minlvl = 20;
                $maxlvl = 40;
                break;
            
            case 'roboter':
                $minlvl = 0;
                $maxlvl = 200;
                break;
            
            case 'schiffe':
                $minlvl = 0;
                $maxlvl = 9999;
                break;
            
            case 'verteidigung':
                $minlvl = 0;
                $maxlvl = 9999;
                break; 
            
            case 'forschung':
                $minlvl = 15; // we need them such high cause of dependencies
                $maxlvl = 20; // find an overwrite for some researches down in code
                break;
            
            default:
                throw new Exception( 'setUp_RandomItemClass() called with unsupported class: ' . $class );
                break;
        }
        
        $randomItemLevels = array();
        $user = Classes::User( $testUser->getName() );
        
        if ( $user->setActivePlanet( $planet ) === false )
        {
            throw new Exception( 'setUp_RandomItemClass() failed, couldnt setactiveplanet to ' . $planet . '\n' );
        }
        
        $itemList = $user->getItemsList( $class );
        
        if ( ! $itemList )
            throw new Exception( 'setUp_RandomItemClass() couldnt get ItemsList of class: ' . $class . ' from user ' . $user->getName() );
        
        foreach ( $itemList as $item )
        {            
            if ( $item == "F8" || $item == "F9" || $item == "F11" || $item == "F10" || $item == "F7" )
            {
                $randomLevel = rand( 5, 5 ); // overwrite for f8 and f9, those are really expensive and could lead to test failure if they go really high
            }
            else
            {
                $randomLevel = rand( $minlvl, $maxlvl );
            }
            $randomItemLevels[$item] = $randomLevel;
            //if($class == 'forschung')
            //echo "changing lvl of ".$item." from ".$user->getItemLevel($item, 'forschung')." to ".$randomLevel."\n";
            $user->changeItemLevel( $item, $randomLevel, $class );
            //echo "added ".$randomLevel." items of ".$item." to ".$user->getName()."\n";
            

            // add scores
            $item_info = $user->getItemInfo( $item, $class, true, true );
            $testScores = &$testUser->getScores();
             
            if ( $class == "gebaeude" )
            {
                $testScores->addScoreID( 0, $item_info['scores'] );
                //print "setUp_RandomItemClass() adding score " . $item_info['scores'] . " id: 0 to " . $user->getName() . "\n";
            }
            else 
                if ( $class == "roboter" )
                {
                    $testScores->addScoreID( 1, $item_info['scores'] );
                    //print "setUp_RandomItemClass() adding score " . $item_info['scores'] . " id: 1 to " . $user->getName() . "\n";
                }
                else 
                    if ( $class == "schiffe" )
                    {
                        $testScores->addScoreID( 2, $item_info['scores'] );
                        //print "setUp_RandomItemClass() adding score " . $item_info['scores'] . " id: 2 to " . $user->getName() . "\n";
                    }
                    else 
                        if ( $class == "verteidigung" )
                        {
                            $testScores->addScoreID( 3, $item_info['scores'] );
                            //print "setUp_RandomItemClass() adding score " . $item_info['scores'] . " id: 3 to " . $user->getName() . "\n";
                        }
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
        // 1:33:7 is blacklisted for testing purposes, dont spawn a planet there!
        $i = 0;
        $koords = "1:33:7";
        while( $koords == "1:33:7" )
        {
            if($i >= 100)
                throw new Exception( 'setUp_MainPlanet() failed, unable to find non blacklisted coords' );
            $koords = getFreeKoords();
            $i++;         
        }
        
        if ( ! User::userExists( $uname ) )
            throw new Exception( 'setUp_MainPlanet() failed, $user is invalid' );
        
        if ( $koords )
        {
            //            print "setUp_addPlanet() trying to register a planet for ".$user->getName()." at ".$koords."\n";
            $user = Classes::User( $uname );
            $index = $user->registerPlanet( $koords );
            
            if ( $index === false )
                throw new Exception( 'setUp_MainPlanet() failed, couldnt setup planet on given coordinates - ' . $koords . ' for ' . $user->getName() );
            else
            {
                $user->setActivePlanet( $index );
                
                if ( $planet->getName() )
                {
                    $user->planetName( $planet->getName() );
                }
                
                $planet->setPosString( $koords );
                $planet->setIsCreated( true );
                
                return $index;
            }
        }
        else
            throw new Exception( 'setUp_MainPlanet() failed, no free coordinates for setting up planet' );
    }

}

?>
