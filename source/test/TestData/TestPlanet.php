<?php

require_once 'TestItem.php';
require_once 'TestResearch.php';

class TestPlanet
{

    /*
	 * the plantes galaxy
	 */
    private $galaxy;

    /*
	 * the planets system
	 */
    private $system;

    /*
	 * the planets index within the system
	 */
    private $sysindex;

    /*
	 * the planets index within the user
	 */
    private $index;

    /*
	 * the planets name
	 */
    private $name;

    /*
	 * if it was created
	 */
    private $isCreated;

    private $res = array();

    /*
	 * holds all items on the planet, building levels etc.
	 */
    private $items = array();

    private $shouldCreate;

    /**
     * holds the researches going on on the planet
     * @var TestResearch
     */
    private $activeResearch;

    /*
	 * constructor
	 */
    public function __construct( )
    {
        $this->galaxy = 0;
        $this->system = 0;
        $this->sysindex = 0;
        $this->index = 0;
        $this->name = false;
        $this->shouldCreate = false;
        $this->isCreated = false;
    }

    public function addStartRes( )
    {
        for ( $i = 0; $i < 5; $i ++ ) {
            $this->res[$i] = 100000000000; // 100 mrd
        }
    }

    public function getRes( $refreshRes = false )
    {
        return $this->res;
    }

    /**
     * sets interal ressources
     * @param $res array
     */
    public function setRes( $res )
    {
        $this->res = $res;
    }

    public function setEnergy( $energy )
    {
        $this->res[5] = $energy;
    }
    
    public function setActiveResearch( $id, $global, $startPlanet = false )
    {
        $tRes = new TestResearch( $id, $global );
        
        //echo "setActiveResearch() id: ".$id." global: ".$global." startplanet: ".$startPlanet."\n";
        

        if ( $global && $startPlanet !== false ) {
            $tRes->setStartPlanet( $startPlanet );
        }
        else 
            if ( $global ) {
                throw new Exception( "addResearch() failed, research global but no start planet given" );
            }
        
        $this->activeResearch = $tRes;
    }

    public function getActiveResearch( )
    {
        return $this->activeResearch;
    }

    public function removeActiveResearch( )
    {
        //echo "removeActiveResearch(): ".$this->getActiveResearch()->getId()." global: ".$this->getActiveResearch()->isGlobal()." startplanet: ".$this->getActiveResearch()->getStartPlanet()."\n";
        

        if ( isset( $this->activeResearch ) && $this->activeResearch !== false )
            $this->activeResearch = false;
        else {
            throw new Exception( "removeActiveResearch() failed, no active research exists" );
        }
    }

    public function setShouldCreate( $should )
    {
        $this->shouldCreate = $should;
    }

    public function getShouldCreate( )
    {
        return $this->shouldCreate;
    }

    /**
     * Returns $galaxy.
     *
     * @see testPlanet::$galaxy
     */
    public function getGalaxy( )
    {
        return $this->galaxy;
    }

    /**
     * Sets $galaxy.
     *
     * @param object $galaxy
     * @see testPlanet::$galaxy
     */
    public function setGalaxy( $gala )
    {
        if ( $gala > 0 && $gala <= 999 ) {
            $this->galaxy = $gala;
        }
        else {
            throw new Exception( "setSystem() failed, impossible $galaxy submitted" );
        }
    }

    public function getSysIndex( )
    {
        return $this->sysindex;
    }

    public function setSysIndex( $ind )
    {
        if ( $ind > 0 && $ind <= 999 ) {
            $this->sysindex = $ind;
        }
        else {
            throw new Exception( "setSystem() failed, impossible " . $sysindex . " submitted" );
        }
    }

    /**
     * Returns $index.
     *
     * @see testPlanet::$index
     */
    public function getIndex( )
    {
        return $this->index;
    }

    /**
     * Sets $index.
     *
     * @param object $index
     * @see testPlanet::$index
     */
    public function setIndex( $index )
    {
        //echo "setting index of planet: ".$this->getName()." index: ".$index."\n";
        

        $this->index = $index;
    }

    /**
     * Returns $system.
     *
     * @see testPlanet::$system
     */
    public function getSystem( )
    {
        return $this->system;
    }

    /**
     * Sets $system.
     *
     * @param object $system
     * @see testPlanet::$system
     */
    public function setSystem( $system )
    {
        if ( $system > 0 && $system <= 999 ) {
            $this->system = $system;
        }
        else {
            throw new Exception( "setSystem() failed, impossible " . $system . " submitted" );
        }
    }

    /**
     * 
     * @return 
     */
    public function getPosString( )
    {
        $pos = $this->getGalaxy() . ":" . $this->getSystem() . ":" . $this->getSysIndex();
        
        return $pos;
    }

    public function setPosString( $pos )
    {
        $apos = explode( ":", $pos );
        
        if ( $apos[0] > 0 && $apos[1] > 0 && $apos[2] > 0 ) {
            $this->setGalaxy( $apos[0] );
            $this->setSystem( $apos[1] );
            $this->setSysIndex( $apos[2] );
        }
        else {
            //print_r( $pos );
            throw new Exception( "setPosString() failed, couldnt split argument into seperate koords" );
        }
    }

    /**
     * Returns $isCreated.
     *
     * @see testPlanet::$isCreated
     */
    public function isCreated( )
    {
        return $this->isCreated;
    }

    /**
     * Sets $isCreated.
     *
     * @param object $isCreated
     * @see testPlanet::$isCreated
     */
    public function setIsCreated( $isCreated )
    {
        $this->isCreated = $isCreated;
    }

    public function setName( $name )
    {
        $this->name = $name;
    }

    public function getName( )
    {
        return $this->name;
    }

    public function addItemLevels( $itemLevels, $class = false )
    {
        foreach ( $itemLevels as $id => $level ) {
            $item = new TestItem( $id );
            $item->setLevel( $level );
            
            if ( $class !== false )
                $item->setClass( $class );
            
            $this->items[$id] = $item;
        }
    }

    public function setItemScores( $itemScores )
    {
        foreach ( $itemScores as $id => $score ) {
            if ( ! isset( $this->items[$id] ) )
                throw new Exception( "setItemScores() failed, wanted to set score for item: " . $id . " which doesnt exists!" );
            
            $item = &$this->items[$id];
            $item->setScore( $score );
        }
    }

    public function getItemScores( $item )
    {
        if ( ! isset( $this->items[$item] ) )
            return 0;
        else
            return $this->items[$item]->score;
    }

    public function getItems( )
    {
        return $this->items;
    }

    public function setItemSpentRes( $sRes )
    {
        foreach ( $sRes as $id => $resArray ) {
            if ( ! isset( $this->items[$id] ) )
                throw new Exception( "setItemSpentRes() failed, wanted to set score for item: " . $id . " which doesnt exists!" );
            
            $item = &$this->items[$id];
            $item->setSpentResViaArray( $resArray );
        }
    }
    
    /**
     * subtracting given res from testPlanet
     * @param $res - array()
     */
    public function subRes( $res = -1 )
    {
        if ( $res == -1 )
        {
            throw new Exception( __FUNCTION__. "invalid parameter given");            
        }
        
        // subtract each single res
        for( $i = 0; $i < 5; $i++)
        {
            if ( $res[$i] < 0 )
            {
                throw new Exception( __FUNCTION__. "subtracting res is restricted to positive values only, use addRes() instead");
            }
            
            $this->res[$i] -= $res[$i];
        }
    }
    
}

?>