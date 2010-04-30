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
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}