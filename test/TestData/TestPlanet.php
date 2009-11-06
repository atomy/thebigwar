<?php

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
	 * the planets index
	 */
	private $index;
	
	/*
	 * the planets name
	 */
	private $name;
	
	private $isCreated;
	
	/*
	 * constructor
	 */
	public function __construct()
	{
		$galaxy = 0;
		$system = 0;
		$index = 0;
		$name = false;
	}
	
    /**
     * Returns $galaxy.
     *
     * @see testPlanet::$galaxy
     */
    public function getGalaxy() 
	{
        return $this->galaxy;
    }
    
    /**
     * Sets $galaxy.
     *
     * @param object $galaxy
     * @see testPlanet::$galaxy
     */
    public function setGalaxy($galaxy)
	{
        $this->galaxy = $galaxy;
    }
    
    /**
     * Returns $index.
     *
     * @see testPlanet::$index
     */
    public function getIndex() 
	{
        return $this->index;
    }
    
    /**
     * Sets $index.
     *
     * @param object $index
     * @see testPlanet::$index
     */
    public function setIndex($index) 
	{
        $this->index = $index;
    }
    
    /**
     * Returns $system.
     *
     * @see testPlanet::$system
     */
    public function getSystem() 
	{
        return $this->system;
    }
    
    /**
     * Sets $system.
     *
     * @param object $system
     * @see testPlanet::$system
     */
    public function setSystem($system) 
	{
        $this->system = $system;
    }
	
	/**
	 * 
	 * @return 
	 */
	public function getPosString()
	{
		$pos = $this->getGalaxy().".".$this->getSystem().".".$this->getIndex();
		
		return $pos;
	}
    
    /**
     * Returns $isCreated.
     *
     * @see testPlanet::$isCreated
     */
    public function getIsCreated() 
	{
        return $this->isCreated;
    }
    
    /**
     * Sets $isCreated.
     *
     * @param object $isCreated
     * @see testPlanet::$isCreated
     */
    public function setIsCreated($isCreated) 
	{
        $this->isCreated = $isCreated;
    }
    
}

?>