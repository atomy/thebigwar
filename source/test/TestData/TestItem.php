<?php

class TestItem
{

    /* 
	 * level of the item, 
	 * which usually means the count of it or level on buildings
	 */
    private $level;

    /**
     * score of the item, used for highscores
     * @var int
     */
    private $score;

    private $spentRes = array();
    
    /*
	 * id of the item
	 * e.g. "S1" (transporter)
	 */
    private $id;
    
    private $class;

    public function __construct( $id )
    {
        $this->id = $id;
        $this->level = 0;
        $this->score = 0;
        
        for ( $i = 0; $i <= 4; $i++ )
            $this->spentRes[$i] = 0;
    }
	/**
     * @param $class the $class to set
     */
    public function setClass( $class )
    {
        $this->class = $class;
    }

	/**
     * @return the $class
     */
    public function getClass( )
    {
        return $this->class;
    }
    
    public function getNumClass()
    {
        switch ( $this->class )
        {
            case 'gebaeude':
                return 0;
                break;
            
            case 'forschung':
                return 1;
                break;  
                
            case 'roboter':
                return 2;
                break;
                
            case 'schiffe':
                return 3;
                break;
               
            case 'verteidigung':
                return 4;
                break;
                
            default:
                return 0;
                break;                            
        }
    }


    /**
     * Returns $id.
     *
     * @see testItem::$id
     */
    public function getId( )
    {
        return $this->id;
    }

    /**
     * @param $score the $score to set
     */
    public function setScore( $score )
    {
        $this->score = $score;
    }

    /**
     * @return the $score
     */
    public function getScore( )
    {
        return $this->score;
    }

    /**
     * Sets $id.
     *
     * @param object $id
     * @see testItem::$id
     */
    public function setId( $id )
    {
        $this->id = $id;
    }

    /**
     * Returns $level.
     *
     * @see testItem::$level
     */
    public function getLevel( )
    {
        return $this->level;
    }

    /**
     * Sets $level.
     *
     * @param object $level
     * @see testItem::$level
     */
    public function setLevel( $level )
    {
        $this->level = $level;
    }
    
    public function setSpentResViaArray( $res )
    {       
        $this->spentRes = $res;
    }
    
    public function getSpentResViaArray( )
    {
        return $this->spentRes;
    }    
    
    public function getSpentResForType( $type )
    {
        if ( !isset( $this->spentRes[$type] ) )
            throw new Exception("getSpentResForType() failed, given type ".$type." not set\n");
            
        return $this->spentRes[$type];
    }       
}

?>
