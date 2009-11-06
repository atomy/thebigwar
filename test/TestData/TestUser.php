<?php
 
 /**
  * this class holds all information needed during tests related to user
  */   
class TestUser
{
	private $name;
	
	public function __construct( $name )
	{
		$this->name = $name;	
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
    
}

?>
