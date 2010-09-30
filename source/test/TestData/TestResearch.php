<?php

class TestResearch
{
	// id string of research
	private $id;
	
	// is it global?
	private $global;
	
	// used when research is global, specifies the planet where it was started from
	private $startPlanet;
		
	public function __construct($id, $global)
	{
		$this->id = $id;
		$this->global = $global;		
    }

	public function setStartPlanet($set)
	{
		$this->startPlanet = $set;
	}
	
	public function getStartPlanet()
	{
		return $this->startPlanet;
	}
	
	public function setGlobal($set)
	{
		$this->global = $set;
	}
	
	public function getGlobal()
	{
		return $this->global;
	}
	
	public function setId($id)
	{
		$this->id = $id;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function isGlobal()
	{
		if ( $this->global )
			return true;
		else
			return false;
	}
}

?>
