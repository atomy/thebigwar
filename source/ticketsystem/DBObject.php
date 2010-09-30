<?php

/**
 * class for objects which data is loaded from database
 * @author atomy
 *
 */
class DBObject
{
    /**
     * indicates if all necessary object data has been loaded
     * @var unknown_type
     */
    private $loaded;
    
    private $id;

    /**
     * check if we are ready for use or just an empty object
     */
    public function isValid()
    {
        if ( $this->id >= 0 && $this->loaded )
            return true;
        else if ( !$this->loaded )
        {
            //echo "obj with id: ".$this->id." is invalid cause of not being loaded\n";            
            return false;
        }
        else
        {            
            //echo "obj with id: ".$this->id." is invalid cause of neg. id\n";
            return false;
        }
    }
    
	/**
     * @return the $loaded
     */
    public function getLoaded( )
    {
        return $this->loaded;
    }

	/**
     * @return the $id
     */
    public function getId( )
    {
        return $this->id;
    }

	/**
     * @param $loaded the $loaded to set
     */
    public function setLoaded( $loaded )
    {
        $this->loaded = $loaded;
    }

	/**
     * @param $id the $id to set
     */
    public function setId( $id )
    {
        $this->id = $id;
    }

    
    
}