<?php

class TestScore
{

    /*
     * 0 up to 4 should be unused cause those can be calculated by the actual existing items and their levels!
     * the others have to exist since they cant be recalculated
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

    public function __construct( )
    {}

    public function __destruct( )
    {}

    public function getAllScoresAsArray( )
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

    public function addBuildingScore( $score = false )
    {
        if ( $score === false )
        {
            throw new Exception( "addBuildingScore() failed, invalid parameter" );
        }
        
        $this->buildings += $score;
    }

    public function addScoreID( $id = false, $score = false )
    {
        if ( $score === false || $id === false )
        {
            throw new Exception( "addScoreID() failed, invalid parameter" );
        }
        
        switch ( $id )
        {
            case 0:
                $this->buildings += $score;
                break;
            
            case 1:
                $this->research += $score;
                break;
            
            case 2:
                $this->robotic += $score;
                break;
            
            case 3:
                $this->fleet += $score;
                break;
            
            case 4:
                $this->defense += $score;
                break;
            
            case 5:
                $this->fleetexp += $score;
                break;
            
            case 6:
                $this->fightexp += $score;
                break;
            
            case 7:
                $this->spent_carb += $score;
                break;
            
            case 8:
                $this->spent_alu += $score;
                break;
            
            case 9:
                $this->spent_wolf += $score;
                break;
            
            case 10:
                $this->spent_rad += $score;
                break;
            
            case 11:
                $this->spent_trit += $score;
                break;
            
            default:
                throw new Exception( "addScoreID() wrong id: " . $id . " given\n" );
                break;
        }
    }

    public function addSpentRes( $arSpentRes )
    {
        $this->spent_carb += $arSpentRes[0];
        $this->spent_alu += $arSpentRes[1];
        $this->spent_wolf += $arSpentRes[2];
        $this->spent_rad += $arSpentRes[3];
        $this->spent_trit += $arSpentRes[4];
    }

    public function getScoreID( $id = false )
    {
        $sRes = 0;
        
        if ( $id === false )
        {
            throw new Exception( "getScoreID() called with invalid id" );
        }
        else if ( $id <= 4 && $id >= 0 )
        {
            throw new Exception( "getScoreID() shouldnt be called with ids being 0-4 since those need to be calculated live from testItem levels and their scores" );
        }
        else
        {
            switch ( $id )
            {
                case 5:
                    return $this->fleetexp;
                    break;
                
                case 6:
                    return $this->fightexp;
                    break;
                
                case 7:
                    return $this->spent_carb;
                    break;
                
                case 8:
                    return $this->spent_alu;
                    break;
                
                case 9:
                    return $this->spent_wolf;
                    break;
                
                case 10:
                    return $this->spent_rad;
                    break;
                
                case 11:
                    return $this->spent_trit;
                    break;
                    
                default:
                    throw new Exception( "getScoreID() wrong id: " . $id . " given\n" );
                    break;                    
            }
        }
        
        return $sRes;
    }
}
