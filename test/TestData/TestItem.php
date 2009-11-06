<?php
 
class TestItem
{
	/*
	 * level of the item, 
	 * which usually means the count of it or level on buildings
	 */
	private $level;
	
	/*
	 * id of the item
	 * e.g. "S1" (transporter)
	 */
	private $id;
	
	public function __construct($id)
	{		
		$this->id = $id;
		$this->level = 0;		
	}
    
    /**
     * Returns $id.
     *
     * @see testItem::$id
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Sets $id.
     *
     * @param object $id
     * @see testItem::$id
     */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
     * Returns $level.
     *
     * @see testItem::$level
     */
    public function getLevel() {
        return $this->level;
    }
    
    /**
     * Sets $level.
     *
     * @param object $level
     * @see testItem::$level
     */
    public function setLevel($level) {
        $this->level = $level;
    }
    
}  
 
?>
