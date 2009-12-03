<?php

class TestScore
{
    /*
    * 0 - gebaeude
    * 1 - forschung
    * 2 - roboter
    * 3 - flotte
    * 4 - verteidigung
    * 5 - flugerfahrung
    * 6 - kampferfahrung
    * 7 - spent carbon
    * 8 - spent alu
    * 9 - spent wolf
    * 10 - spent rad
    * 11 - spent trit
    */
    private $buildings = 0;
    private $research = 0;
    private $robotic = 0;
    private $fleet = 0;
    private $defense = 0;
    private $fleetexp = 0;
    private $fightexp = 0;
    private $spent_carb = 0;
    private $spent_alu = 0;
    private $spent_wolf = 0;
    private $spent_rad = 0;
    private $spent_trit = 0;
    
    public function __construct() 
    { }
    
    public function __destruct()
    { }
    
    public function getAllScoresAsArray()
    {
        $array = array();
        $array[] = $this->buildings;
        $array[] = $this->research;
        $array[] = $this->robotic;
        $array[] = $this->fleet;
        $array[] = $this->defense;
        $array[] = $this->fleetexp;
        $array[] = $this->fightexp;
        $array[] = $this->spent_carb;
        $array[] = $this->spent_alu;
        $array[] = $this->spent_wolf;
        $array[] = $this->spent_rad;
        $array[] = $this->spent_trit;
        
        return $array;        
    }
    
    public function addBuildingScore($score=false)    
    {
    	if($score===false)
    	{
    		throw new Exception("addBuildingScore() failed, invalid parameter");
    	}
    	
    	$this->buildings += $score;
    }
}