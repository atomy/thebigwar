<?php

class CAdminPermission
{
    private $id;
    
    private $name;

    private $description;

    public function __construct( $id, $name, $description )
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        
        // define some globals which return the id
        define($name, $id);
    }
    
	/**
     * @return the $id
     */
    public function getId( )
    {
        return $this->id;
    }

	/**
     * @return the $name
     */
    public function getName( )
    {
        return $this->name;
    }

	/**
     * @return the $description
     */
    public function getDescription( )
    {
        return $this->description;
    }

	/**
     * @param $id the $id to set
     */
    public function setId( $id )
    {
        $this->id = $id;
    }

	/**
     * @param $name the $name to set
     */
    public function setName( $name )
    {
        $this->name = $name;
    }

	/**
     * @param $description the $description to set
     */
    public function setDescription( $description )
    {
        $this->description = $description;
    }

    
    
}