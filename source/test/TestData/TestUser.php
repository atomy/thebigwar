<?php

require_once 'TestPlanet.php';
require_once 'TestScore.php';

/**
 * this class holds all information needed during tests related to user
 */
class TestUser
{

    private $name;

    private $planets = array();

    private $bCreated;

    private $bShouldCreate;

    private $bCreateOnSetup;

    private $messages = array();

    /*
     * increasing index used to retrieve testplanetlist elements
     */
    private $testPlanetListIndex;

    /*
     * holds a compiled list of all our testplanets
     */
    private $testPlanetList;

    /**
     * holding TestScore obj
     */
    private $scores;

    public function __construct( $name )
    {
        $this->name = $name;
        $this->bCreated = false;
        $this->bShouldCreate = false;
        $this->bCreateOnSetup = false;
        $this->scores = new TestScore();
        $this->testPlanetListIndex = 0;
        $this->testPlanetList = array();
    }

    public function getScores( )
    {
        return $this->scores;
    }

    public function getMessages( )
    {
        return $this->messages;
    }

    public function addMessage( $msgObj )
    {
        $this->messages[] = $msgObj;
    }

    public function shouldCreateOnSetup( )
    {
        return $this->bCreateOnSetup;
    }

    public function setShouldCreateOnSetup( $should )
    {
        $this->bCreateOnSetup = $should;
    }

    /**
     * Returns $name.
     *
     * @see testUser::$name
     */
    public function getName( )
    {
        return $this->name;
    }

    /**
     * Sets $name.
     *
     * @param object $name
     * @see testUser::$name
     */
    public function setName( $name )
    {
        $this->name = $name;
    }

    public function generateTestPlanets( )
    {
        $maxplanets = global_setting( "MAX_PLANETS" );
        
        if ( $maxplanets <= 0 ) {
            throw new Exception( "generateTestPlanets() failed, maxplanet constant is 0 or below" );
        }
        
        for ( $i = 0; $i <= $maxplanets; $i ++ ) {
            $planet = &new Testplanet();
            $planet->setName( "TestPlanet" . $i );
            $planet->setIndex( $i );
            
            if ( $i <= 4 ) {
                $planet->setShouldCreate( true );
            }
            else {
                $planet->setShouldCreate( false );
            }
            
            $planet->addStartRes();
            $this->planets[] = $planet;
        }
    }

    public function getPlanets( )
    {
        return $this->planets;
    }

    public function setIsCreated( $iscreated )
    {
        $this->bCreated = $iscreated;
    }

    public function isCreated( )
    {
        return $this->bCreated;
    }

    public function shouldCreate( )
    {
        return $this->bShouldCreate;
    }

    public function setShouldCreate( $should )
    {
        $this->bShouldCreate = $should;
    }

    public function hasCreatedPlanetAtIndex( $index )
    {
        foreach ( $this->getPlanets() as $planet ) {
            if ( $planet->getIndex() == $index && $planet->isCreated() ) {
                return true;
            }
        }
        
        return false;
    }

    public function getPlanetCount( )
    {
        return count( $this->planets );
    }

    public function getCreatedPlanetCount( )
    {
        $count = 0;
        
        foreach ( $this->planets as $planet ) {
            if ( $planet->isCreated() ) {
                $count ++;
            }
        }
        
        return $count;
    }

    public function addNewPlanetCreated( $index, $koordString )
    {
        $koords = explode( ":", $koordString );
        $testPlanet = &new TestPlanet();
        $testPlanet->setIndex( $index );
        $testPlanet->setGalaxy( $koords[0] );
        $testPlanet->setSystem( $koords[1] );
        $testPlanet->setSysIndex( $koords[2] );
        $testPlanet->setIsCreated( true );
        
        $this->planets[] = $testPlanet;
    }

    public function cyclePlanets( $a, $b )
    {
        $aPlanet = $this->planets[$a];
        $bPlanet = $this->planets[$b];
        
        $resA = $aPlanet->getActiveResearch();
        $resB = $bPlanet->getActiveResearch();
        
        if ( $resA ) {
            $res = $resA;
            
            if ( $res->isGlobal() ) {//echo "calling1 on planet: ".$a."\n";
//$aPlanet->setActiveResearch( $res->getId(), $res->getGlobal(), $b );
            }
        }
        
        if ( $resB ) {
            $res = $resB;
            
            if ( $res->isGlobal() ) {//echo "calling2 on planet: ".$b."\n";
//$bPlanet->setActiveResearch( $res->getId(), $res->getGlobal(), $a );
            }
        }
        
        $this->planets[$a] = $bPlanet;
        $this->planets[$b] = $aPlanet;
        
        $this->planets[$a]->setIndex( $a );
        $this->planets[$b]->setIndex( $b );
        
    //echo "a ( ".$a." ) dump: \n";
    //print_r($this->planets[$a]->getActiveResearch());
    

    //echo "b ( ".$b." ) dump: \n";
    //print_r($this->planets[$b]->getActiveResearch());
    }

    public function getSumScores( $key = false )
    {
        $sum = 0;
        
        foreach ( $this->planets as $planet ) {
            foreach ( $planet->getItems() as $item ) {
                // skipping elements that doesnt match our key filter
                if ( $key !== false && $key != $item->getNumClass() )
                    continue;
                    //echo "getSumScores() adding " . $item->getScore() . " for item: " . $item->getId() . " of class: " . $item->getNumClass() . " given key: " . $key . "\n";
                $sum += $item->getScore();
            }
        }
        
        //echo "getSumScores() returning: " . $sum . " for key: " . $key . "\n";
        

        return $sum;
    }

    public function destroyPlanet( $planetIndex = -1 )
    {
        return;
        if ( $planetIndex < 0 ) {
            throw new Exception( "given planetIndex is invalid" );
        }
        
        $remIndex = - 1;
        // search for a testplanetobject with the given planetindex, save its index      
        foreach ( $this->planets as $i => &$planetObj ) {
            if ( $planetObj->getIndex() == $planetIndex ) {
                $planetObj->setIsCreated( false );
                $planetObj->setIndex( 0 );
                $remIndex = $planetIndex;
                break;
            }
        }
        
        // reorder all planets, those whos index is over the removed one
        foreach ( $this->planets as &$planetObj ) {
            if ( $remIndex != - 1 ) {
                $oldIndex = $planetObj->getIndex();
                
                if ( $oldIndex > $remIndex ) {
                    $planetObj->setIndex( $oldIndex - 1 );
                }
            }
        }
    }

    /**
     * gathers all active planets in the testData enviroment and saves them to a list for later usage
     */
    private function generateTestPlanetList( )
    {
        $this->testPlanetListIndex = 0;
        $this->testPlanetList = array();
        
        foreach ( $this->getPlanets() as $testPlanet ) {
            if ( ! $testPlanet->isCreated() ) {
                continue;
            }
            
            $this->testPlanetList[] = $testPlanet;
        }       
    }

    /**
     * returns testPlanet objects of created test planets as long we have ones
     */
    public function getNextTestPlanet( )
    {
        if ( count( $this->testPlanetList ) == 0 ) {
            $this->generateTestPlanetList();
        }
        
        $index = $this->testPlanetListIndex;
        
        // as long we arent out of elements return it!
        if ( isset( $this->testPlanetList[$index] ) ) {
            $this->testPlanetListIndex ++;
            
            return $this->testPlanetList[$index];
        }
        else
        {
            return false;
        }
    }
}

?>
