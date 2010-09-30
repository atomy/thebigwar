<?php

class TestHighScore
{
    // key => username, value => score
    private $scoreList = array();
    
    // key => rank, value => username
    private $rankList = array();
    
    public function __construct( )
    {
        // get rid of the 0 index, rank 0 is nonexistant
        $this->rankList[] = "";
    }

    public function __destruct( )
    {}

    public function addUser( $uName = false, $score = false )
    {
        if ( $uName === false || $score === false )
        {
            throw new Exception("addUser() stop feeding me with empty parameters!");
        }       
        
        $this->scoreList[$uName] = $score;        
    }
    
    public function buildRankList()
    {
        if ( count( $this->scoreList ) == 0 || !arsort( $this->scoreList ) )
        {
            throw new Exception("buildRankList() failed to sort array");
        }
        
        $i = 1;
        
        foreach( $this->scoreList as $uname => $score )
        {
            $this->rankList[] = $uname;
            $i++;
        }  
              
        //print_r($this->scoreList);
    }
    
    public function getRankList()
    {
        return $this->rankList;
    }
}
