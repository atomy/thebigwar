<?php
 
require_once 'TestPlanet.php';

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
	
	public function __construct( $name )
	{
		$this->name = $name;	
		$this->bCreated = false;
		$this->bShouldCreate = false;
		$this->bCreateOnSetup = false;
	}
    
	public function shouldCreateOnSetup()
	{
		return $this->bCreateOnSetup;
	}
	
	public function setShouldCreateOnSetup($should)
	{
		$this->bCreateOnSetup = $should;
	}
	
    /**
     * Returns $name.
     *
     * @see testUser::$name
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Sets $name.
     *
     * @param object $name
     * @see testUser::$name
     */
    public function setName($name) {
        $this->name = $name;
    }
    
    public function generateTestPlanets()
    {
    	$planet = new TestPlanet();
    	$planet->setName("TestPlanet".rand(0,100));
    	$this->planets[] = $planet;
    }
    
    public function getPlanets()
    {
    	return $this->planets;
    }
    
    public function setIsCreated($iscreated)
    {
    	$this->bCreated = $iscreated;
    }
    
    public function isCreated()
    {
    	return $this->bCreated;
    }
    
    public function shouldCreate()
    {
    	return $this->bShouldCreate;
    }
    
    public function setShouldCreate($should)
    {
    	$this->bShouldCreate = $should;
    }
    
}

?>
