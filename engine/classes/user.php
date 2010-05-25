<?php

require_once( TBW_ROOT.'loghandler/dbLogger.php' );

class User extends Dataset
{

    protected $active_planet = false;

    protected $datatype = 'user';

    protected $recalc_highscores = array( false, false, false, false, false );

    protected $last_eventhandler_run = array();

    function __construct( $name = false, $write = true )
    {
        $this->save_dir = global_setting( "DB_PLAYERS" );
        parent::__construct( $name, $write );
    }

    /**
     * create this user obj on the database
     * tests added
     */
    function create( )
    {
        if ( file_exists( $this->filename ) || isBlacklistedName($this->filename) )
            return false;
        
        $this->raw = array( 'username' => $this->name, 'planets' => array(), 'forschung' => array(), 'password' => 'x', 'punkte' => array( 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 ), 'registration' => time(), 'messages' => array(), 'description' => '', 'description_parsed' => '', 'flotten' => array(), 'alliance' => false );
        
        $highscores = Classes::Highscores();
        $highscores->updateUser( $this->name, '', 0 );
        
        if ( ! $this->write( true, false ) )
            return false;
        
        $this->__construct( $this->name );
        return true;
    }

    /**
     * checks if the user data file exists in the file system and is readeable,
     * returns false if it does not
     * tests added     
     */        
    public static function userExists( $user )
    {
        if ( ! $user )
            return false;
        
        $filename = global_setting( "DB_PLAYERS" ) . '/' . strtolower( urlencode( $user ) );
        
        return ( is_file( $filename ) && is_readable( $filename ) );
    }

    /**
     * checks if given planet exists     
     * tests added
     */     
    function planetExists( $planet )
    {
        if ( ! $this->status )
            return false;
        
        return isset( $this->raw['planets'][$planet] );
    }

    /**
     * retrieve status
     * tests not added, returning private member var only
     */     
    function getStatus( )
    {
        return $this->status;
    }

    /**
     * retrieve filename
     * tests not added, returning private member var only
     */       
    function getFilename( )
    {
        return $this->filename;
    }

    /**
     * set status
     * tests added
     */       
    function setStatus( $status )
    {
        $this->status = $status;
    }

    /**
     * DEBUG function
     * prints out all planets the user has
     * tests not added (debug func)
     */  
    public function printPlanets( )
    {
        if ( ! $this->status )
        {
            return false;
        }        
        
        echo "Listing Planets of user ".$this->getName().": \n";
        
        foreach ( $this->raw['planets'] as $index => $planet )
        {
            echo $index." ";
        }
        
        echo "\n";
    }
    
    /**
     * sets the active planet and copies the new values over
     * tests added
     */     
    function setActivePlanet( $planet )
    {
        if ( ! $this->status )
        {
            return false;
        }
        
        if ( ! isset( $this->raw['planets'][$planet] ) )
        {
            return false;
        }
        
        if ( isset( $this->planet_info ) ) {
            if ( isset( $this->items['gebaeude'] ) )
                $this->planet_info['gebaeude'] = $this->items['gebaeude'];
            
            if ( isset( $this->items['roboter'] ) )
                $this->planet_info['roboter'] = $this->items['roboter'];
            
            if ( isset( $this->items['schiffe'] ) )
                $this->planet_info['schiffe'] = $this->items['schiffe'];
            
            if ( isset( $this->items['verteidigung'] ) )
                $this->planet_info['verteidigung'] = $this->items['verteidigung'];
            
            if ( isset( $this->ress ) )
                $this->planet_info['ress'] = $this->ress;
        }
        
        $this->active_planet = $planet;
        $this->planet_info = &$this->raw['planets'][$planet];
        
        if ( isset( $this->cache['getPos'] ) )
        {
            unset( $this->cache['getPos'] );
        }
        
        $this->items['gebaeude'] = $this->planet_info['gebaeude'];
        $this->items['roboter'] = $this->planet_info['roboter'];
        $this->items['schiffe'] = $this->planet_info['schiffe'];
        $this->items['verteidigung'] = $this->planet_info['verteidigung'];
        
        $this->items['ids'] = array();
        
        foreach ( $this->items['gebaeude'] as $id => $level )
        {
            $this->items['ids'][$id] = & $this->items['gebaeude'][$id];
        }
        
        foreach ( $this->items['forschung'] as $id => $level )
        {
            $this->items['ids'][$id] = & $this->items['forschung'][$id];
        }
        
        foreach ( $this->items['roboter'] as $id => $level )
        {
            $this->items['ids'][$id] = & $this->items['roboter'][$id];
        }
        
        foreach ( $this->items['schiffe'] as $id => $level )
        {
            $this->items['ids'][$id] = & $this->items['schiffe'][$id];
        }
        
        foreach ( $this->items['verteidigung'] as $id => $level )
        {
            $this->items['ids'][$id] = & $this->items['verteidigung'][$id];
        }
        
        $this->ress = $this->planet_info['ress'];
        
        return true;
    }

    /**
     * returns the planets index at the given $pos
     * tests added
     */        
    function getPlanetByPos( $pos )
    {
        if ( ! $this->status )
        {
            return false;
        }
        
        $return = false;
        $planets = $this->getPlanetsList();
        $active_planet = $this->getActivePlanet();
        
        foreach ( $planets as $i => $planet ) {
            $this->setActivePlanet( $i );
            
            if ( $this->getPosString() == $pos ) {
                //echo "\n".$pos." matches: ".$i."\n";
                

                $return = $i;
                
                break;
            }
            //else
        //echo $this->getPosString()." doesnt match ".$pos."\n";
        }
        
        $this->setActivePlanet( $active_planet );
        
        return $return;
    }

    /**
     * returns the index of the active planet
     * tests added
     */     
    function getActivePlanet( )
    {
        if ( ! $this->status )
            return false;
        
        return $this->active_planet;
    }

    /**
     * get cached planets list or create one of our current raw data
     * tests added
     */     
    function getPlanetsList( )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->cache['getPlanetsList'] ) )
            $this->cache['getPlanetsList'] = array_keys( $this->raw['planets'] );
        
        return $this->cache['getPlanetsList'];
    }

    /**
     * get the total fields of the current planet
     * tests not added (method just returning private member values)
     */     
    function getTotalFields( )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        return $this->planet_info['size'][1];
    }

    /**
     * get the used fields of the current planet
     * tests not added (method just returning private member values)
     */     
    function getUsedFields( )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        return $this->planet_info['size'][0];
    }

    /**
     * change the used fields value according to $value
     * tests added
     */
    function changeUsedFields( $value )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        $this->planet_info['size'][0] += $value;
        $this->changed = true;
        
        return true;
    }

    /**
     * returns remaining fields on the active planet
     * tests added
     */    
    function getRemainingFields( )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        return ( $this->planet_info['size'][1] - $this->planet_info['size'][0] );
    }

    /**
     * returns basic fields on the active planet
     * tests added
     */        
    function getBasicFields( )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        return ( $this->planet_info['size'][1] / ( $this->getItemLevel( 'F9', 'forschung' ) + 1 ) );
    }

    /**
     * sets fields according to $size
     * tests added
     */      
    function setFields( $size )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        $this->planet_info['size'][1] = $size;
        $this->changed = true;
        
        return true;
    }

    /**
     * returns fields count
     * tests added
     */     
    function getFields( )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        return $this->planet_info['size'][1];
    }

    /**
     * returns pos in form of an array { 1, 13, 37 } for 1:13:37
     * tests added
     */
    function getPos( )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        $pos = explode( ':', $this->planet_info['pos'], 3 );
        
        if ( count( $pos ) < 3 )
            return false;
        
        return $pos;
    }

    /**
     * returns pos string in form of 1:13:37
     * tests not added, returning private member value only
     */     
    function getPosString( )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        return $this->planet_info['pos'];
    }

    /**
     * returns the class of a planet
     * tests added
     */     
    function getPlanetClass( )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        $pos = $this->getPos();
        __autoload( 'Galaxy' );
        
        return getPlanetClass( $pos[0], $pos[1], $pos[2] );
    }

   /**
    * kill a planet!
    * tests added
    */
    function removePlanet( )
    {
        global $types_message_types;
        
        if ( ! $this->status || ! isset( $this->planet_info ) )
        {
            return false;
        }
            
        # Alle feindlichen Flotten, die auf diesen Planeten, zurueckrufen
        $fleets = $this->getFleetsWithPlanet();
        
        foreach ( $fleets as $fleet ) {
            $fl = Classes::Fleet( $fleet );
            $users = $fl->getUsersList();
            
            foreach ( $users as $user ) {
                $pos_string = $fl->from( $user );
                $pos = explode( ':', $pos_string );
                $type = $fl->getCurrentType();
                $fl->callBack( $user );
                
                $this_galaxy = Classes::Galaxy( $pos[0] );
                
                $message = Classes::Message();
                
                if ( $message->create() ) {
                    $message->addUser( $user, $types_message_types[$type] );
                    $message->subject( "Flotte zur&uuml;ckgerufen" );
                    $message->from( $this->getName() );
                    $message->text( 'Ihre Flotte befand sich auf dem Weg zum Planeten &bdquo;' . $this->planetName() . '&ldquo; (' . $this->getPosString() . ', Eigent&uuml;mer: ' . utf8_htmlentities( $this->getName() ) . '). Soeben wurde jener Planet verlassen, weshalb Ihre Flotte sich auf den R&uuml;ckweg zu Ihrem Planeten &bdquo;' . $this_galaxy->getPlanetName( $pos[1], $pos[2] ) . '&ldquo; (' . $pos_string . ') macht.' );
                }
            }
        }
        
        # Planeten aus der Karte loeschen
        $this_pos = $this->getPos();
        
        if ( ! $this_pos )
        {
            return false;
        }
        
        $galaxy = Classes::galaxy( $this_pos[0] );
        
        if ( ! $galaxy->resetPlanet( $this_pos[1], $this_pos[2] ) )
        {
            return false;
        }
        
        $planets = $this->getPlanetsList();
        $active_key = array_search( $this->getActivePlanet(), $planets );
        
        unset( $this->planet_info );
        unset( $this->raw['planets'][$active_key] );
                
        $keys = array_keys( $this->raw['planets'] );
        $this->raw['planets'] = array_values( $this->raw['planets'] );     
        
        if ( isset( $planets[$active_key + 1] ) )
        {
            $new_active_planet = array_search( $planets[$active_key + 1], $keys );
        }
        else 
        { 
            if ( isset( $planets[$active_key - 1] ) )
            {
                $new_active_planet = array_search( $planets[$active_key - 1], $keys );
            }
            else
            {
                $new_active_planet = false;
            }
        }
        
        $new_planets = $this->getPlanetsList();
        
        foreach ( $new_planets as $planet ) {
            $this->setActivePlanet( $planet );
            $active_forschung = $this->checkBuildingThing( 'forschung' );
            
            if ( ! $active_forschung )
            {
                continue;
            }
            
            if ( $active_forschung[2] )
            {
                $this->planet_info['building']['forschung'][4] = array_search( $active_forschung[4], $keys );
            }
        }
        
        if ( $new_active_planet !== false )
        {
            $this->setActivePlanet( $new_active_planet );
        }
            
        # Highscores neu berechnen
        $this->recalcHighscores( true, true, true, true, true );
        
        if ( isset( $this->cache['getPlanetsList'] ) )
        {
            unset( $this->cache['getPlanetsList'] );
        }
                        
        return true;
    }

    /**
     * @test - added for almost everything
     * @param $pos_string
     * @return unknown_type
     */
    function registerPlanet( $pos_string )
    {
        if ( ! $this->status ) {
            return false;
        }
        
        $pos = explode( ':', $pos_string );
        
        if ( count( $pos ) != 3 ) {
            return false;
        }
        
        if ( ! $this->checkPlanetCount() ) {
            return false;
        }
        
        $galaxy = Classes::Galaxy( $pos[0] );
        
        if ( $galaxy->getStatus() != 1 ) {
            return false;
        }
        
        $owner = $galaxy->getPlanetOwner( $pos[1], $pos[2] );
        
        if ( $owner === false || $owner ) {
            return false;
        }
        
        $planet_name = 'Kolonie';
        
        if ( ! $galaxy->setPlanetOwner( $pos[1], $pos[2], $this->getName() ) ) {
            return false;
        }
        
        $galaxy->setPlanetName( $pos[1], $pos[2], $planet_name );
        
        if ( $this->allianceTag() )
            $galaxy->setPlanetOwnerAlliance( $pos[1], $pos[2], $this->allianceTag() );
        
        if ( count( $this->raw['planets'] ) <= 0 )
            $size = 375;
        else
            $size = $galaxy->getPlanetSize( $pos[1], $pos[2] );
        
        $size *= $this->getItemLevel( 'F9', 'forschung' ) + 1;
        
        $planets = $this->getPlanetsList();
        
        if ( count( $planets ) == 0 )
            $planet_index = 0;
        else
            $planet_index = max( $planets ) + 1;
        
        while ( isset( $this->raw['planets'][$planet_index] ) )
            $planet_index ++;
        
        $this->raw['planets'][$planet_index] = array( 'pos' => $pos_string, 'ress' => array( 0, 0, 0, 0, 0 ), 'gebaeude' => array(), 'roboter' => array(), 'schiffe' => array(), 'verteidigung' => array(), 'size' => array( 0, $size ), 'last_refresh' => time(), 'time' => $planet_name, 'prod' => array(), 'name' => $planet_name );
        
        if ( isset( $this->cache['getPlanetsList'] ) )
            unset( $this->cache['getPlanetsList'] );
        
        $this->changed = true;
        
        return $planet_index;
    }

    /**
     * move planet up in the users planets list, this sets the planets index
     * @test implemented
     * @param $planet
     * @return unknown_type
     */
    function movePlanetUp( $planet = false )
    {
        if ( ! $this->status )
            return false;
        
        if ( $planet === false ) {
            if ( ! isset( $this->planet_info ) )
                return false;
            
            $planet = $this->getActivePlanet();
        }
        
        $planets = $this->getPlanetsList();
        $planet_key = array_search( $planet, $planets );
        
        if ( $planet_key === false || ! isset( $planets[$planet_key - 1] ) )
            return false;
        
        return $this->movePlanetDown( $planets[$planet_key - 1] );
    }

    /**
     * move planet down 1 step in the users planet lists, affects the planets index
     * @test implemented
     * @param unknown_type $planet
     * @return unknown_type
     */
    function movePlanetDown( $planet = false )
    {
        if ( ! $this->status )
            return false;
        
        if ( $planet === false ) {
            if ( ! isset( $this->planet_info ) )
                return false;
            
            $planet = $this->getActivePlanet();
        }
        
        $planets = $this->getPlanetsList();
        $planet_key = array_search( $planet, $planets );
        
        if ( $planet_key === false || ! isset( $planets[$planet_key + 1] ) )
            return false;
        
        $planet2 = $planets[$planet_key + 1];
        
        $new_active_planet = $this->getActivePlanet();
        
        if ( $new_active_planet == $planet )
            $new_active_planet = $planet2;
        else 
            if ( $new_active_planet == $planet2 )
                $new_active_planet = $planet;
        
        unset( $this->planet_info );
        
        # Planeten vertauschen
        //echo $this->getName()." -- moving down planet: ".$planet."\n";
        list( $this->raw['planets'][$planet], $this->raw['planets'][$planet2] ) = array( $this->raw['planets'][$planet2], $this->raw['planets'][$planet] );
        
        # Aktive Forschungen aendern
        $this->setActivePlanet( $planet );
        $active_forschung = $this->checkBuildingThing( 'forschung' );
        
        //TODO, why would we need that?
        //if( $active_forschung && $active_forschung[2] )
        //    $this->planet_info['building']['forschung'][4] = $planet2;
        

        $this->refreshMessengerBuildingNotifications();
        
        $this->setActivePlanet( $planet2 );
        $active_forschung = $this->checkBuildingThing( 'forschung' );
        
        //TODO, why would we need that?
        //if( $active_forschung && $active_forschung[2] )
        //   $this->planet_info['building']['forschung'][4] = $planet;
        

        $this->refreshMessengerBuildingNotifications();
        
        if ( $new_active_planet != $planet2 )
            $this->setActivePlanet( $new_active_planet );
        
        if ( isset( $this->cache['getPlanetsList'] ) )
            unset( $this->cache['getPlanetsList'] );
        
        if ( isset( $this->cache['getItemInfo'] ) )
            unset( $this->cache['getItemInfo'] );
        
        return true;
    }

    /**
     * This is method getScores
     * @test implented
     * @param int $i index
     * @return int - scores
     *
     */
    function getScores( $i = false )
    {
        if ( ! $this->status ) {
            return false;
        }
        
        // if $i not set return all scores
        if ( $i === false ) {
            if ( ! isset( $this->cache['getScores'] ) ) {
                // summarize their index up to 6 - WTF? WHY? what happens with the other points?
                for ( $k = 0; $k <= 6; $k ++ ) {
                    if ( isset( $this->cache['getScores'] ) ) {
                        $temp = $this->cache['getScores'];
                    }
                    else {
                        $this->cache['getScores'] = 0;
                        $temp = - 1;
                    }
                    
                    // $this->cache['getScores'] =+ $this->raw['punkte'][$k]; // huh? whats that operator? seems like same as '=' - guess this was a typo then *shrug*
                    $this->cache['getScores'] += $this->raw['punkte'][$k];
                    //print "(".$this->getName().") added ".$this->raw['punkte'][$k]." to ".$temp." for: ".$k." now: ".$this->cache['getScores']."\n";
                }
            }
            
            return $this->cache['getScores'];
        }
        else {
            if ( ! isset( $this->raw['punkte'][$i] ) ) {
                return 0;
            }
            else {
                return $this->raw['punkte'][$i];
            }
        }
    }

    /**
     * adds Score to the given index, see comment within class TestScore for details about the index
     * @test implemented
     * @param $i - score index
     * @param $scores - score value
     * @return bool - true on success, false otherwise
     */
    function addScores( $i, $scores )
    {
        if ( ! $this->status || $i > 11 ) {
            return false;
        }
        
        if ( ! isset( $this->raw['punkte'][$i] ) )
            $this->raw['punkte'][$i] = $scores;
        else
            $this->raw['punkte'][$i] += $scores;
        
        if ( isset( $this->cache['getScores'] ) )
            $this->cache['getScores'] += $scores;
        
        $this->changed = true;
        return true;
    }

    /**
     * gets spent res for the given res type, 0-4 (5 types)
     * @test implemented
     * @param $i - res type (carbon=> 0, ...), if not given all res types are summarized
     * @return int - spent res
     */
    function getSpentRess( $i = false )
    {
        if ( ! $this->status ) {
            return false;
        }
        
        $spentRes = 0;
        
        if ( $i === false ) {
            if ( ! isset( $this->cache['getSpentRess'] ) ) {
                $this->cache['getSpentRess'] = $this->getScores( 7 ) + $this->getScores( 8 ) + $this->getScores( 9 ) + $this->getScores( 10 ) + $this->getScores( 11 );
            }
            
            $spentRes = $this->cache['getSpentRess'];
        }
        else {
            $spentRes = $this->getScores( $i + 7 );
        }
        
        return $spentRes;
    }

    /**
     * gets users rank in the highscore
     * @test implemented
     * @return int - rank
     */
    function getRank( )
    {
        if ( ! $this->status ) {
            return false;
        }
        
        $highscores = Classes::Highscores();
        $rank = $highscores->getPosition( 'users', $this->getName() );
        
        return $rank;
    }

    /**
     * set or get a planets name
     * @test implemented
     * @param $name - new planet name
     * @return bool - true on success, false otherwise
     */
    function planetName( $name = false )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) ) {
            return false;
        }
        
        $keyarray = array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Q', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', ' ' );
        
        #Planiname Zeichen pruefen
        $stringplanet = $name;
        
        for ( $i = 0; $i < strlen( $stringplanet ); $i ++ ) {
            $explode[$i] = substr( $stringplanet, $i, 1 );
            
            // char didnt match our postive list, return
            if ( ! in_array( $explode[$i], $keyarray ) ) {
                return false;
            }
        }
        
        if ( $name !== false && trim( $name ) != '' ) {
            $name = substr( $name, 0, 24 );
            
            if ( isset( $this->planet_info['name'] ) ) {
                $old_name = $this->planet_info['name'];
            }
            else {
                $old_name = '';
            }
            
            $this->planet_info['name'] = $name;
            
            $pos = $this->getPos();
            $galaxy = Classes::Galaxy( $pos[0] );
            
            if ( ! $galaxy->setPlanetName( $pos[1], $pos[2], $name ) ) {
                $this->planet_info['name'] = $old_name;
                return false;
            }
            else {
                $this->changed = true;
                return true;
            }
        }
        
        return $this->planet_info['name'];
    }

    /**
     * summarize all res that are on flying ships
     * @test implemented
     * @return 
     */
    function getRessOnAllFleets( )
    {
        if ( ! $this->status ) {
            return false;
        }
        
        $fleetres = array( 0, 0, 0, 0, 0 );
        
        /*
         * loop through all fleets, get their ress and summarize them
         */
        foreach ( $this->getFleetsList() as $flotte ) {
            $fl = Classes::Fleet( $flotte );
            $flres = array( 0, 0, 0, 0, 0 );
            
            // get ress of fleet
            $flres = $fl->getTransport( $this->getName() );
            
            $fleetres[0] += $flres[0][0];
            $fleetres[1] += $flres[0][1];
            $fleetres[2] += $flres[0][2];
            $fleetres[3] += $flres[0][3];
            $fleetres[4] += $flres[0][4];
        }
        
        return $fleetres;
    }

    /**
     * get ressource on active planet
     * @test implemented
     * @param bool $refresh - true if res should be recalculated
     * @return array() ressources on the actual planet, 
     *   NOTE: returns energy when $refresh was true! otherwise just the common res
     */
    function getRess( $refresh = true )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
        {
            return false;
        }
        
        if ( $refresh )
        {
            $this->refreshRess();
        }
        
        $ress = $this->ress;
        
        if ( $refresh ) {
            $prod = $this->getProduction();
            $ress[5] = $prod[5];
        }
        
        return $ress;
    }

    /**
     * adds ressources to the active planet
     * @test implemented
     * @param $ress array() ressources to add
     * @return bool true on success
     */
    function addRess( $ress )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
        {
            return false;
        }
        
        if ( ! is_array( $ress ) )
        {
            return false;
        }
        
        // add res
        for ( $i = 0; $i < 5; $i++ )
        {
            if ( isset( $ress[$i] ))
            {
                $this->ress[$i] += $ress[$i];
            }
            
        }
        
        if ( isset( $this->cache['getItemInfo'] ) && isset( $this->cache['getItemInfo'][$this->getActivePlanet()] ) )
        {
            unset( $this->cache['getItemInfo'][$this->getActivePlanet()] );
        }
        
        $this->changed = true;
        
        return true;
    }

    /**
     * subtracts res
     * @param $ress array() - res to subtract
     * @param $make_scores boolean - add spend res to scores
     * tests added
     */
    function subtractRess( $ress, $make_scores = true )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) ) {
            return false;
        }
        
        if ( ! is_array( $ress ) ) {
            return false;
        }
        
        if ( isset( $ress[0] ) ) {
            $this->ress[0] -= $ress[0];
            
            if ( $make_scores ) {
                $this->raw['punkte'][7] += $ress[0];
            }
        }
        
        if ( isset( $ress[1] ) ) {
            $this->ress[1] -= $ress[1];
            if ( $make_scores )
                $this->raw['punkte'][8] += $ress[1];
        }
        
        if ( isset( $ress[2] ) ) {
            $this->ress[2] -= $ress[2];
            if ( $make_scores )
                $this->raw['punkte'][9] += $ress[2];
        }
        
        if ( isset( $ress[3] ) ) {
            $this->ress[3] -= $ress[3];
            if ( $make_scores )
                $this->raw['punkte'][10] += $ress[3];
        }
        
        if ( isset( $ress[4] ) ) {
            $this->ress[4] -= $ress[4];
            if ( $make_scores )
                $this->raw['punkte'][11] += $ress[4];
        }
        
        if ( $make_scores && isset( $this->cache['getSpentRess'] ) ) {
            unset( $this->cache['getSpentRess'] );
        }
        
        $this->changed = true;
        
        //			print "substracting res from user: ".$this->getName()."\n";
        //			throw new Exception("bt");
        //			print_r($ress);
        //			print "------------------\n";
        //			print_r($this->ress);
        //			print "===========================\n";
        

        return true;
    }

    /**
     * checks if the user can pay the given res, returns false if he cant
     * tests added 
     */     
    function checkRess( $ress )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) ) {
            return false;
        }
        
        if ( ! is_array( $ress ) ) {
            return false;
        }
        
        if ( isset( $ress[0] ) && $ress[0] > $this->ress[0] ) {
            return false;
        }
        
        if ( isset( $ress[1] ) && $ress[1] > $this->ress[1] ) {
            return false;
        }
        if ( isset( $ress[2] ) && $ress[2] > $this->ress[2] ) {
            return false;
        }
        
        if ( isset( $ress[3] ) && $ress[3] > $this->ress[3] ) {
            return false;
        }
        
        if ( isset( $ress[4] ) && $ress[4] > $this->ress[4] ) {
            return false;
        }
        
        return true;
    }

    /**
     * check if the user owns a planet at the given coords
     * tests added
     */     
    function isOwnPlanet( $pos )
    {
        if ( ! $this->status )
            return false;
        
        $planets = $this->getPlanetsList();
        $active_planet = $this->getActivePlanet();
        $return = false;
        
        // loop through all of my planets and see if there's a planet with that position
        foreach ( $planets as $planet ) {
            $this->setActivePlanet( $planet );
            if ( ( is_array( $pos ) && $pos == $this->getPos() ) || ( ! is_array( $pos ) && $pos == $this->getPosString() ) ) {
                $return = true;
                break;
            }
        }
        
        // switch back to the old planet
        $this->setActivePlanet( $active_planet );
        
        return $return;
    }

    /**
     * returns the users fleets,
     * deletes any non-existant fleets  
     * tests added   
     */      
    function getFleetsList( )
    {
        if ( ! $this->status )
            return false;
        
        if ( isset( $this->raw['flotten'] ) ) {
            foreach ( $this->raw['flotten'] as $i => $flotte ) {
                __autoload( 'Fleet' );
                if ( ! Fleet::fleetExists( $flotte ) ) {
                    //echo "OH HAI, deleted fleet: ".$flotte."\n";
                    unset( $this->raw['flotten'][$i] );
                    $this->changed = true;
                }
            }
            return $this->raw['flotten'];
        }
        else
            return array();
    }

    /**    
     * links given fleet to the user
     * tests added 
     */      
    function addFleet( $fleet )
    {
        if ( $this->status != 1 )
            return false;
        
        if ( ! isset( $this->raw['flotten'] ) )
            $this->raw['flotten'] = array();
        elseif ( in_array( $fleet, $this->raw['flotten'] ) )
            return 2;
        $this->raw['flotten'][] = $fleet;
        natcasesort( $this->raw['flotten'] ); // why do u sort them?!
        $this->changed = true;
        return true;
    }

    /**
     * unlinks the given fleetid from the user     
	 * tests added
     */      
    function unsetFleet( $fleet )
    {
        if ( $this->status != 1 )
            return false;
        
        if ( ! isset( $this->raw['flotten'] ) ) {
            $this->raw['flotten'] = array();
            return true;
        }
        $key = array_search( $fleet, $this->raw['flotten'] );
        if ( $key === false )
            return true;
        unset( $this->raw['flotten'][$key] );
        $this->changed = true;
        return $key;
    }

    /**
     * unset the given fleet by unsetting its password, really ugly way to do that
     * tests added    
     */       
    function unsetVerbFleet( $fleet )
    {
        if ( $this->status != 1 )
            return false;
        if ( ! isset( $this->raw['flotten_passwds'] ) )
            return true;
        
        $passwd = $this->getFleetPasswd( $fleet );
        if ( $passwd === false )
            return true;
        unset( $this->raw['flotten_passwds'][$passwd] );
        $this->changed = true;
        return true;
    }

    /**
     * checks if fleets are flying to/from my active planet
     * tests added
     */       
    function checkOwnFleetWithPlanet( )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
        {
            return false;
        }

        foreach ( $this->getFleetsList() as $flotte ) {
            $fl = Classes::Fleet( $flotte );
            
            if ( in_array( $this->getName(), $fl->getUsersList() ) && ( $fl->from( $this->getName() ) == $this->getPosString() || $fl->isATarget( $this->getPosString() ) ) )
                return true;
        }
        return false;
    }

    /**
     * returns all fleets flying to my active planet
     * tests added    
     */       
    function getFleetsWithPlanet( )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        $fleets = array();
        foreach ( $this->getFleetsList() as $flotte ) {
            $fl = Classes::Fleet( $flotte );
            
            if ( in_array( $this->getName(), $fl->getUsersList() ) )
            {
                if ( $fl->from( $this->getName() ) == $this->getPosString() )
                {
                    //echo "match1 for ".$flotte." active planet: ".$this->getActivePlanet()."\n";
                    $fleets[] = $flotte;
                }
                else if ( $fl->isATarget( $this->getPosString() ) )
                {
                    //echo "match2 for ".$flotte." active planet: ".$this->getActivePlanet()."\n";
                    $fleets[] = $flotte;
                }
               // else
                //{
               //     echo "no match for ".$flotte." active planet: ".$this->getActivePlanet()."\n";
                //}
            }
                
        }
        return $fleets;
    }

    /**
     * returns an array filled with fleet ids which prevent me from going into u-mode
     * tests added
     */       
    function getFleetsForUmode( )
    {
        if ( ! $this->status )
        {
            return false;
        }
        
        // hold all umode-fleets in that array
        $fleets = array();
        
        // loop through all my fleets
        foreach ( $this->getFleetsList() as $flotte ) {
            $fl = Classes::Fleet( $flotte );
            $name = $fl->getUsersList();
            $name1 = array();
            $name1[] = $this->getName();
            $back = $fl->isFlyingBack();
            
            // this is my fleet and its not flying back, add it
            if ( $name == $name1 && $back == false ) {
                $fleets[] = $flotte;
            }
        }
        
        // we got more than 0 fleet, return our array
        if ( count( $fleets ) > 0 )
            return $fleets;
        else
            return false;
    }

    /**
     * returns
     * TODO, add tests
     *
     */       
    function getMaxParallelFleets( )
    {
        if ( ! $this->status )
            return false;
        
        $werft = 0;
        $planets = $this->getPlanetsList();
        $active_planet = $this->getActivePlanet();
        foreach ( $planets as $planet ) {
            $this->setActivePlanet( $planet );
            if ( $this->getItemLevel( 'B10', 'gebaeude' ) > 0 )
                $werft ++;
        }
        $this->setActivePlanet( $active_planet );
        
        return floor( pow( $werft * $this->getItemLevel( 'F0', 'forschung' ), .7 ) );
    }

    /**
     * TODO, add tests
     *
     */       
    function getCurrentParallelFleets( )
    {
        if ( ! $this->status )
            return false;
        
        $fleets = 0;
        foreach ( $this->getFleetsList() as $flotte ) {
            $fl = Classes::Fleet( $flotte );
            $key = array_search( $this->getName(), $fl->getUsersList() );
            if ( $key !== false ) {
                if ( $key )
                    $fleets ++;
                else
                    $fleets += $fl->getNeededSlots();
            }
        }
        return $fleets;
    }

    /**
     * TODO, add tests
     *
     */       
    function getRemainingParallelFleets( )
    {
        if ( ! $this->status )
            return false;
        
        return $this->getMaxParallelFleets() - $this->getCurrentParallelFleets();
    }

    /**
     * TODO, add tests
     *
     */       
    function checkMessage( $message_id, $type )
    {
        if ( ! $this->status )
            return false;
        
        return ( isset( $this->raw['messages'] ) && isset( $this->raw['messages'][$type] ) && isset( $this->raw['messages'][$type][$message_id] ) );
    }

    /**
     * TODO, add tests
     *
     */       
    function checkMessageStatus( $message_id, $type )
    {
        if ( ! $this->status ) {
            return false;
        }
        
        if ( isset( $this->raw['messages'] ) && isset( $this->raw['messages'][$type] ) && isset( $this->raw['messages'][$type][$message_id] ) )
            return (int) $this->raw['messages'][$type][$message_id];
        else
            return false;
    }

    /**
     * TODO, add tests
     *
     */       
    function findMessageType( $message_id )
    {
        if ( ! $this->status ) {
            return false;
        }
        
        foreach ( $this->raw['messages'] as $type => $messages ) {
            if ( isset( $messages[$message_id] ) ) {
                return $type;
            }
        }
        return false;
    }

    /**
     * TODO, add tests
     *
     */       
    function setMessageStatus( $message_id, $type, $status )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['messages'] ) || ! isset( $this->raw['messages'][$type] ) || ! isset( $this->raw['messages'][$type][$message_id] ) )
            return false;
        
        $this->raw['messages'][$type][$message_id] = $status;
        $this->changed = true;
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function getMessagesList( $type )
    {
        if ( ! $this->status ) {
            return false;
        }
        
        if ( ! isset( $this->cache['getMessagesList'] ) ) {
            $this->cache['getMessagesList'] = array();
        }
        
        if ( ! isset( $this->cache['getMessagesList'][$type] ) ) {
            if ( ! isset( $this->raw['messages'] ) || ! isset( $this->raw['messages'][$type] ) ) {
                $this->cache['getMessagesList'] = array();
            }
            else {
                $this->cache['getMessagesList'][$type] = array_reverse( array_keys( $this->raw['messages'][$type] ) );
            }
        }
        
        if ( isset( $this->cache['getMessagesList'][$type] ) ) {
            return $this->cache['getMessagesList'][$type];
        }
        else {
            return false;
        }
    }

    /**
     * TODO, add tests
     *
     */       
    function getMessageCategoriesList( )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->cache['getMessageCategoriesList'] ) ) {
            if ( ! isset( $this->raw['messages'] ) )
                $this->cache['getMessageCategoriesList'] = array();
            elseif ( ! isset( $this->raw['messages'] ) )
                $this->cache['getMessageCategoriesList'] = array();
            else
                $this->cache['getMessageCategoriesList'] = array_keys( $this->raw['messages'] );
            sort( $this->cache['getMessageCategoriesList'], SORT_NUMERIC );
        }
        return $this->cache['getMessageCategoriesList'];
    }

    /**
     * TODO, add tests
     *
     */       
    function addMessage( $message_id, $type )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['messages'] ) )
            $this->raw['messages'] = array();
        if ( ! isset( $this->raw['messages'][$type] ) )
            $this->raw['messages'][$type] = array();
        $this->raw['messages'][$type][$message_id] = 1;
        $this->changed = true;
        
        if ( isset( $this->cache['getMessagesList'] ) && isset( $this->cache['getMessagesList'][$type] ) )
            unset( $this->cache['getMessagesList'][$type] );
    }

    /**
     * TODO, add tests
     *
     */       
    function removeMessage( $message_id, $type, $edit_message = true )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['messages'] ) || ! isset( $this->raw['messages'][$type] ) || ! isset( $this->raw['messages'][$type][$message_id] ) )
            return 2;
        unset( $this->raw['messages'][$type][$message_id] );
        $this->changed = true;
        
        if ( isset( $this->cache['getMessagesList'] ) )
            unset( $this->cache['getMessagesList'] );
        
        if ( $edit_message ) {
            $message = Classes::Message( $message_id );
            return $message->removeUser( $this->name, false );
        }
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function checkPassword( $password )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['password'] ) )
            return false;
        if ( md5( $password ) == $this->raw['password'] ) {
            # Passwort stimmt, Passwort-vergessen-Funktion deaktivieren
            if ( isset( $this->raw['email_passwd'] ) && $this->raw['email_passwd'] ) {
                $this->raw['email_passwd'] = false;
                $this->changed = true;
            }
            
            return true;
        }
        else
            return false;
    }

    /**
     * TODO, add tests
     *
     */       
    function setPassword( $password )
    {
        if ( ! $this->status )
            return false;
        
        $this->raw['password'] = md5( $password );
        
        if ( isset( $this->raw['email_passwd'] ) && $this->raw['email_passwd'] )
            $this->raw['email_passwd'] = false;
        
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function getPasswordSum( )
    {
        if ( ! $this->status )
            return false;
        return $this->raw['planet'];
    }

    /**
     * TODO, add tests
     *
     */       
    function checkSetting( $setting )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->settings[$setting] ) )
            return - 1;
        else
            return $this->settings[$setting];
    }

    /**
     * TODO, add tests
     *
     */       
    function setSetting( $setting, $value )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->settings[$setting] ) )
            return false;
        else {
            $this->settings[$setting] = $value;
            $this->changed = true;
        }
    }

    /**
     * TODO, add tests
     *
     */       
    function getUserDescription( $parsed = true )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['description'] ) )
            $this->raw['description'] = '';
        
        if ( $parsed ) {
            if ( ! isset( $this->raw['description_parsed'] ) ) {
                $this->raw['description_parsed'] = parse_html( $this->raw['description'] );
                $this->changed = true;
            }
            return $this->raw['description_parsed'];
        }
        else
            return $this->raw['description'];
    }

    /**
     * TODO, add tests
     *
     */       
    function setUserDescription( $description )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['description'] ) )
            $this->raw['description'] = '';
        
        if ( $description != $this->raw['description'] ) {
            $this->raw['description'] = $description;
            $this->raw['description_parsed'] = parse_html( $this->raw['description'] );
            $this->changed = true;
            
            return true;
        }
        else
            return 2;
    }

    /**
     * TODO, add tests
     *
     */       
    function lastRequest( $last_request = false, $last_planet = false )
    {
        if ( ! $this->status )
            return false;
        
        if ( $last_request === false && $last_planet === false ) {
            $return = array();
            if ( ! isset( $this->raw['last_request'] ) && ! isset( $this->raw['last_planet'] ) )
                return false;
            
            if ( ! isset( $this->raw['last_request'] ) )
                $return[0] = false;
            else
                $return[0] = $this->raw['last_request'];
            
            if ( ! isset( $this->raw['last_planet'] ) || ! $this->planetExists( $this->raw['last_planet'] ) ) {
                $planets = $this->getPlanetsList();
                $return[1] = array_shift( $planets );
            }
            else
                $return[1] = $this->raw['last_planet'];
            
            return $return;
        }
        
        if ( $last_request !== false )
            $this->raw['last_request'] = $last_request;
        if ( $last_planet !== false )
            $this->raw['last_planet'] = $last_planet;
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function registerAction( )
    {
        if ( ! $this->status )
            return false;
        
        $this->raw['last_request'] = $_SERVER['REQUEST_URI'];
        $this->raw['last_planet'] = $this->getActivePlanet();
        $this->raw['last_active'] = time();
    }

    /**
     * TODO, add tests
     *
     */       
    function getLastActivity( )
    {
        if ( ! $this->status ) {
            return false;
        }
        
        if ( ! isset( $this->raw['last_active'] ) ) {
            return false;
        }
        
        return $this->raw['last_active'];
    }

    /**
     * TODO, add tests
     *
     */       
    function getRegistrationTime( )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['registration'] ) )
            return false;
        return $this->raw['registration'];
    }

    /**
     * TODO, add tests
     *
     */       
    /**
     * @param $time - unix time stamp for registration time
     */
    function setRegistrationTime( $time = 0 )
    {
        if ( ! $this->status || $time == 0 )
            return false;
        
        $this->raw['registration'] = $time;
        $this->write( true, false );
    }

    /**
     * TODO, add tests
     *
     */       
    function getItemsList( $type = false )
    {
        if ( ! $this->status )
            return false;
        
        $items_instance = Classes::Items();
        return $items_instance->getItemsList( $type );
    }
    
    /**
     * TODO, add tests
     *
     */       
    public function getCache( $cache = -1, $subcache = -1 )
    {        
        if( $cache == -1 )
        {
            throw new Exception( __FUNCTION__." called with invalid parameters " );
        }
        
        if ( $subcache == -1 && isset($this->cache[$cache]))
        {
            return $this->cache[$cache];
        }
        else if ( isset($this->cache[$cache]) && isset($this->cache[$cache][$subcache]) )
        {
            return $this->cache[$cache][$subcache];
        }
        else
        {
            return false;
        }
    }

    /**
     * TODO, add tests
     *
     */       
    function getItemInfo( $id, $type = false, $run_eventhandler = true, $calc_scores = false )
    {
        if ( ! $this->status ) {
            return false;
        }
        
        $this_planet = $this->getActivePlanet();
        
        if ( ! isset( $this->cache['getItemInfo'] ) )
            $this->cache['getItemInfo'] = array();
        
        if ( ! isset( $this->cache['getItemInfo'][$this_planet] ) )
            $this->cache['getItemInfo'][$this_planet] = array();
        
        if ( ! isset( $this->cache['getItemInfo'][$this_planet][$id] ) || ( $calc_scores && ! isset( $this->cache['getItemInfo'][$this_planet][$id]['scores'] ) ) ) {
            $item = Classes::Item( $id );
            
            if ( $type === false ) {
                $type = $item->getType();
            }
            
            $info = $item->getInfo();
            
            if ( ! $info ) {
                return false;
            }
            
            $info['type'] = $type;
            $info['buildable'] = $info['deps-okay'] = $item->checkDependencies( $this, $run_eventhandler );
            $info['level'] = $this->getItemLevel( $id, $type, $run_eventhandler );
            
            # Bauzeit als Anteil der Punkte des ersten Platzes
            /*if(isset($info['time']))
                {
                    $highscores = Classes::Highscores();
                    if($highscores->getStatus() && ($first = $highscores->getList('users', 1, 1)))
                    {
                        list($best_rank) = $first;
                        if($best_rank['scores'] == 0) $f = 1;
                        else $f = $this->getScores()/$best_rank['scores'];
                        if($f < .5) $f = .5;
                        $info['time'] *= $f;
                    }
                }*/
            
            $global_factors = get_global_factors();
            
            if ( isset( $info['time'] ) )
                $info['time'] *= $global_factors['time'];
            
            if ( isset( $info['prod'] ) ) {
                $info['prod'][0] *= $global_factors['prod'];
                $info['prod'][1] *= $global_factors['prod'];
                $info['prod'][2] *= $global_factors['prod'];
                $info['prod'][3] *= $global_factors['prod'];
                $info['prod'][4] *= $global_factors['prod'];
                $info['prod'][5] *= $global_factors['prod'];
            }
            if ( isset( $info['ress'] ) ) {
                $info['ress'][0] *= $global_factors['cost'];
                $info['ress'][1] *= $global_factors['cost'];
                $info['ress'][2] *= $global_factors['cost'];
                $info['ress'][3] *= $global_factors['cost'];
            }
            
            switch ( $type ) {
                case 'gebaeude':
                    $max_rob_limit = floor( $this->getBasicFields() / 2 * ( ( 0.01 + $this->getItemLevel( 'B9', 'gebaeude' ) ) / 10 ) );
                    #$max_rob_limit = 1000;
                    
                    $info['has_prod'] = false;
                    
                    if ( isset( $info['prod'] ) ) {
                        /*
                         * look if this item is relevant in our resource usage overview,
                         * whenever it influences one of our res it has production
                         */
                        for ( $i = 0; $i <= 5; $i ++ ) {
                            if ( $info['prod'][$i] != 0 ) {
                                $info['has_prod'] = true;
                                break;
                            }
                        }
                    }
                    else {
                        $info['has_prod'] = false;
                    }
                    
                    $level_f = pow( $info['level'], 2 );
                    
                    $percent_f = $this->checkProductionFactor( $id );
                    
                    if ( isset( $info['prod'] ) ) {
                        for ( $i = 0; $i <= 5; $i ++ ) {
                            $info['prod'][$i] *= $level_f * $percent_f;
                        }
                    }
                    
                    $lvlRobottec = $this->getItemLevel( 'F2', 'forschung', $run_eventhandler );
                    
                    $minen_rob = 1 + 0.0003125 * $lvlRobottec;
                    
                    if ( $minen_rob > 1 ) {
                        $use_max_limit = ! file_exists( global_setting( 'DB_NO_STRICT_ROB_LIMITS' ) );
                        
                        $rob = $this->getItemLevel( 'R02', 'roboter', $run_eventhandler );
                        if ( $rob > $this->getItemLevel( 'B0', 'gebaeude', $run_eventhandler ) )
                            $rob = $this->getItemLevel( 'B0', 'gebaeude', $run_eventhandler );
                        if ( $use_max_limit && $rob > $max_rob_limit )
                            $rob = $max_rob_limit;
                        $info['prod'][0] *= pow( $minen_rob, $rob );
                        
                        $rob = $this->getItemLevel( 'R03', 'roboter', $run_eventhandler );
                        if ( $rob > $this->getItemLevel( 'B1', 'gebaeude', $run_eventhandler ) )
                            $rob = $this->getItemLevel( 'B1', 'gebaeude', $run_eventhandler );
                        if ( $use_max_limit && $rob > $max_rob_limit )
                            $rob = $max_rob_limit;
                        $info['prod'][1] *= pow( $minen_rob, $rob );
                        
                        $rob = $this->getItemLevel( 'R04', 'roboter', $run_eventhandler );
                        if ( $rob > $this->getItemLevel( 'B2', 'gebaeude', $run_eventhandler ) )
                            $rob = $this->getItemLevel( 'B2', 'gebaeude', $run_eventhandler );
                        if ( $use_max_limit && $rob > $max_rob_limit )
                            $rob = $max_rob_limit;
                        $info['prod'][2] *= pow( $minen_rob, $rob );
                        
                        $rob = $this->getItemLevel( 'R05', 'roboter', $run_eventhandler );
                        if ( $rob > $this->getItemLevel( 'B3', 'gebaeude', $run_eventhandler ) )
                            $rob = $this->getItemLevel( 'B3', 'gebaeude', $run_eventhandler );
                        if ( $use_max_limit && $rob > $max_rob_limit )
                            $rob = $max_rob_limit;
                        $info['prod'][3] *= pow( $minen_rob, $rob );
                        
                        $rob = $this->getItemLevel( 'R06', 'roboter', $run_eventhandler );
                        if ( $rob > $this->getItemLevel( 'B4', 'gebaeude', $run_eventhandler ) )
                            $rob = $this->getItemLevel( 'B4', 'gebaeude', $run_eventhandler );
                        if ( $use_max_limit && $rob > $max_rob_limit )
                            $rob = $max_rob_limit;
                        $info['prod'][4] *= pow( $minen_rob, $rob );
                    }
                    
                    if ( isset( $info['prod'] ) && $info['prod'][5] > 0 )
                        $info['prod'][5] *= pow( 1.05, $this->getItemLevel( 'F3', 'forschung', $run_eventhandler ) );
                    
                    $info['time'] *= pow( 1.3, $info['level'] + 1 );
                    $baurob = 1 - 0.00025 * $this->getItemLevel( 'F2', 'forschung', $run_eventhandler );
                    $rob = $this->getItemLevel( 'R01', 'roboter', $run_eventhandler );
                    if ( $rob > $max_rob_limit )
                        $rob = $max_rob_limit;
                    $info['time'] *= pow( $baurob, $rob );
                    
                    if ( $calc_scores ) {
                        $ress = array_sum( $info['ress'] );
                        $scores = 0;
                        for ( $i = 1; $i <= $info['level']; $i ++ )
                            $scores += $ress * pow( 1.4, $i );
                        $info['scores'] = $scores / 1000;
                    }
                    
                    $ress_f = pow( 1.4, $info['level'] + 1 );
                    $info['ress'][0] *= $ress_f;
                    $info['ress'][1] *= $ress_f;
                    $info['ress'][2] *= $ress_f;
                    $info['ress'][3] *= $ress_f;
                    
                    if ( $info['buildable'] && $info['fields'] > $this->getRemainingFields() )
                        $info['buildable'] = false;
                    $info['debuildable'] = ( $info['level'] >= 1 && - $info['fields'] <= $this->getRemainingFields() );
                    
                    # Runden
                    if ( isset( $info['prod'] ) ) {
                        for ( $i = 0; $i <= 5; $i ++ ) {
                            stdround( $info['prod'][$i] );
                        }
                    }
                    
                    stdround( $info['time'] );
                    
                    for ( $i = 0; $i <= 4; $i ++ ) {
                        stdround( $info['ress'][$i] );
                    }
                    
                    break;
                case 'forschung':
                    $info['time'] *= pow( 1.5, $info['level'] + 1 );
                    
                    $local_labs = 0;
                    $global_labs = 0;
                    $planets = $this->getPlanetsList();
                    $active_planet = $this->getActivePlanet();
                    foreach ( $planets as $planet ) {
                        $this->setActivePlanet( $planet );
                        if ( $planet == $active_planet )
                            $local_labs += $this->getItemLevel( 'B8', 'gebaeude', $run_eventhandler );
                        else
                            $global_labs += $this->getItemLevel( 'B8', 'gebaeude', $run_eventhandler );
                    }
                    $this->setActivePlanet( $active_planet );
                    
                    $info['time_local'] = $info['time'] * pow( 0.99, $local_labs );
                    unset( $info['time'] );
                    $info['time_global'] = $info['time_local'] * pow( 0.99, $global_labs );
                    
                    if ( $calc_scores ) {
                        $ress = array_sum( $info['ress'] );
                        $scores = 0;
                        for ( $i = 1; $i <= $info['level']; $i ++ )
                            $scores += $ress * pow( 1.5, $i );
                        $info['scores'] = $scores / 1000;
                    }
                    
                    $ress_f = pow( 1.5, $info['level'] + 1 );
                    $info['ress'][0] *= $ress_f;
                    $info['ress'][1] *= $ress_f;
                    $info['ress'][2] *= $ress_f;
                    $info['ress'][3] *= $ress_f;
                    
                    # Runden
                    stdround( $info['time_local'] );
                    stdround( $info['time_global'] );
                    stdround( $info['ress'][0] );
                    stdround( $info['ress'][1] );
                    stdround( $info['ress'][2] );
                    stdround( $info['ress'][3] );
                    stdround( $info['ress'][4] );
                    break;
                case 'roboter':
                    $info['time'] *= pow( 0.95, $this->getItemLevel( 'B9', 'gebaeude', $run_eventhandler ) );
                    
                    if ( $calc_scores ) {
                        $info['simple_scores'] = array_sum( $info['ress'] ) / 1000;
                        $info['scores'] = $info['simple_scores'] * $info['level'];
                    }
                    
                    stdround( $info['time'] );
                    break;
                case 'schiffe':
                    $info['att'] *= pow( 1.05, $this->getItemLevel( 'F4', 'forschung', $run_eventhandler ) );
                    $info['def'] *= pow( 1.05, $this->getItemLevel( 'F5', 'forschung', $run_eventhandler ) );
                    $lad_f = pow( 1.2, $this->getItemLevel( 'F11', 'forschung', $run_eventhandler ) );
                    $info['trans'][0] *= $lad_f;
                    $info['trans'][1] *= $lad_f;
                    $info['time'] *= pow( 0.95, $this->getItemLevel( 'B10', 'gebaeude', $run_eventhandler ) );
                    $info['speed'] *= pow( 1.025, $this->getItemLevel( 'F6', 'forschung', $run_eventhandler ) );
                    $info['speed'] *= pow( 1.05, $this->getItemLevel( 'F7', 'forschung', $run_eventhandler ) );
                    $info['speed'] *= pow( 1.15, $this->getItemLevel( 'F8', 'forschung', $run_eventhandler ) );
                    
                    if ( $calc_scores ) {
                        $info['simple_scores'] = array_sum( $info['ress'] ) / 1000;
                        $info['scores'] = $info['simple_scores'] * $info['level'];
                    }
                    
                    # Runden
                    round( $info['att'], 3 );
                    round( $info['def'], 3 );
                    stdround( $info['trans'][0] );
                    stdround( $info['trans'][1] );
                    stdround( $info['time'] );
                    stdround( $info['speed'] );
                    break;
                case 'verteidigung':
                    $info['att'] *= pow( 1.05, $this->getItemLevel( 'F4', 'forschung', $run_eventhandler ) );
                    $info['def'] *= pow( 1.05, $this->getItemLevel( 'F5', 'forschung', $run_eventhandler ) );
                    $info['time'] *= pow( 0.95, $this->getItemLevel( 'B10', 'gebaeude', $run_eventhandler ) );
                    
                    if ( $calc_scores ) {
                        $info['simple_scores'] = array_sum( $info['ress'] ) / 1000;
                        $info['scores'] = $info['simple_scores'] * $info['level'];
                    }
                    
                    round( $info['att'], 3 );
                    round( $info['def'], 3 );
                    stdround( $info['time'] );
                    break;
            }
            
            # Mindestbauzeit zwoelf Sekunden aufgrund von Serverbelastung
            #                if($type == 'forschung')
            #                {
            #                    if($info['time_local'] < 12) $info['time_local'] = 12;
            #                    if($info['time_global'] < 12) $info['time_global'] = 12;
            #                }
            #                elseif($info['time'] < 12) $info['time'] = 12;
            

            $this->cache['getItemInfo'][$this_planet][$id] = $info;
        }
        
        return $this->cache['getItemInfo'][$this_planet][$id];
    }

    /**
     * TODO, add tests
     *
     */       
    function getItemLevel( $id, $type = false, $run_eventhandler = true )
    {
        if ( ! $this->status )
            return false;
        
        if ( $run_eventhandler )
            $this->eventhandler( $id, 0, 0, 0, 0, 0 );
        
        if ( $type === false )
            $type = 'ids';
        if ( ! isset( $this->items[$type] ) || ! isset( $this->items[$type][$id] ) )
            return 0;           
        
        return $this->items[$type][$id];
    }

    /**
     * TODO, add tests
     *
     */       
    function changeItemLevel( $id, $value = 1, $type = false, $time = false, &$actions = false )
    {
        if ( ! $this->status )
            return false;
        
        if ( $value == 0 )
            return true;
        
        if ( $time === false )
            $time = time();
        
        if ( $actions === false )
            $actions = array();
        
        $recalc = array( 'gebaeude' => 0, 'forschung' => 1, 'roboter' => 2, 'schiffe' => 3, 'verteidigung' => 4 );
        
        if ( $type !== false && $type != 'ids' ) {
            if ( ! isset( $this->items[$type] ) )
                $this->items[$type] = array();
            if ( isset( $this->items[$type][$id] ) )
                $this->items[$type][$id] += $value;
            else {
                $this->items[$type][$id] = $value;
                $this->items['ids'][$id] = &$this->items[$type][$id];
            }
        }
        else {
            $item = Classes::Item( $id );
            $type = $item->getType();
            if ( isset( $this->items['ids'][$id] ) )
                $this->items['ids'][$id] += $value;
            else {
                if ( ! isset( $this->items[$type] ) )
                    $this->items[$type] = array();
                $this->items[$type][$id] = $value;
                $this->items['ids'][$id] = &$this->items[$type][$id];
            }
        }
        
        $this->recalc_highscores[$recalc[$type]] = true;
        
        # Felder belegen
        if ( $type == 'gebaeude' ) {
            $item_info = $this->getItemInfo( $id, 'gebaeude' );
            if ( $item_info['fields'] > 0 )
                $this->changeUsedFields( $item_info['fields'] * $value );
        }
        
        switch ( $id ) {
            # Ingeneurswissenschaft: Planeten vergroessern
            case 'F9':
                $planets = $this->getPlanetsList();
                $active_planet = $this->getActivePlanet();
                foreach ( $planets as $planet ) {
                    $this->setActivePlanet( $planet );
                    $size = $this->getTotalFields() / ( $this->getItemLevel( 'F9', false, false ) - $value + 1 );
                    $this->setFields( $size * ( $this->getItemLevel( 'F9', false, false ) + 1 ) );
                }
                $this->setActivePlanet( $active_planet );
                break;
            
            # Bauroboter: Laufende Bauzeit verkuerzen
            case 'R01':
                $building = $this->checkBuildingThing( 'gebaeude' );
                if ( $building && $building[1] > $time ) {
                    $remaining = ( $building[1] - $time ) * pow( 1 - 0.00025 * $this->getItemLevel( 'F2', 'forschung', false ), $value );
                    $this->raw['building']['gebaeude'][1] = $time + $remaining;
                }
                
                # Auch in $actions schauen
                $one = false;
                foreach ( $actions as $i => $action2 ) {
                    if ( $action2[4] != $this->getActivePlanet() )
                        continue;
                    $this_item = Classes::Item( $action2[1] );
                    if ( $this_item->getType() == 'gebaeude' ) {
                        $remaining = ( $action2[0] - $time ) * pow( 1 - 0.00025 * $this->getItemLevel( 'F2', 'forschung', false ), $value );
                        $actions[$i][0] = $time + $remaining;
                        $one = true;
                    }
                }
                if ( $one )
                    usort( $actions, 'sortEventhandlerActions' );
                
                break;
            
            # Roboterbautechnik: Auswirkungen der Bauroboter aendern
            case 'F2':
                $planets = $this->getPlanetsList();
                $active_planet = $this->getActivePlanet();
                foreach ( $planets as $planet ) {
                    $this->setActivePlanet( $planet );
                    
                    $building = $this->checkBuildingThing( 'gebaeude' );
                    $robs = $this->getItemLevel( 'R01', 'roboter', false );
                    if ( $robs > 0 && $building && $building[1] > $time ) {
                        $f_1 = pow( 1 - 0.00025 * ( $this->getItemLevel( 'F2', false, false ) - $value ), $robs );
                        $f_2 = pow( 1 - 0.00025 * $this->getItemLevel( 'F2', false, false ), $robs );
                        $remaining = ( $building[1] - $time ) * $f_2 / $f_1;
                        $this->raw['building']['gebaeude'][1] = $time + $remaining;
                    }
                    
                    # Auch in $actions schauen
                    if ( $actions !== false && $planet == $active_planet ) {
                        $one = false;
                        foreach ( $actions as $i => $action2 ) {
                            if ( $action2[4] != $this->getActivePlanet() )
                                continue;
                            $this_item = Classes::Item( $action2[1] );
                            if ( $this_item->getType() == 'gebaeude' ) {
                                $f_1 = pow( 1 - 0.00025 * ( $this->getItemLevel( 'F2', false, false ) - $value ), $robs );
                                $f_2 = pow( 1 - 0.00025 * $this->getItemLevel( 'F2', false, false ), $robs );
                                $remaining = ( $action2[0] - $time ) * $f_2 / $f_1;
                                $actions[$i][0] = $action2[0] + $remaining;
                                $one = true;
                            }
                        }
                        if ( $one )
                            usort( $actions, 'sortEventhandlerActions' );
                    }
                }
                $this->setActivePlanet( $active_planet );
                
                break;
        }
        
        $this->changed = true;
        
        // log what we've changed
        $logger = new DBLogger();        
        $logger->logUserAction( $this->getName(), __METHOD__ . " -- on item: ".$id." (type: ".$type.") by value: ".$value );
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    public function refreshRess( $time = false )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) ) {
            return false;
        }
        
        if ( $time === false ) {
            $this->eventhandler( 0, 1, 1, 1, 0, 0 );
            $time = time();
        }
        
        if ( $this->planet_info['last_refresh'] >= $time ) {
            return false;
        }
        
        $prod = $this->getProduction( $time !== false );
        
        $f = ( $time - $this->planet_info['last_refresh'] ) / 3600;
        
        $this->ress[0] += $prod[0] * $f;
        $this->ress[1] += $prod[1] * $f;
        $this->ress[2] += $prod[2] * $f;
        $this->ress[3] += $prod[3] * $f;
        $this->ress[4] += $prod[4] * $f;
        
        $this->planet_info['last_refresh'] = $time;
        
        $this->changed = true;
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function checkProductionFactor( $gebaeude )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        if ( isset( $this->planet_info['prod'][$gebaeude] ) )
            return $this->planet_info['prod'][$gebaeude];
        else
            return 1;
    }

    /**
     * TODO, add tests
     *
     */       
    function setProductionFactor( $gebaeude, $factor )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        if ( ! $this->getItemInfo( $gebaeude, 'gebaeude' ) )
            return false;
        
        $factor = (float) $factor;
        
        if ( $factor < 0 )
            $factor = 0;
        if ( $factor > 1 )
            $factor = 1;
        
        $this->planet_info['prod'][$gebaeude] = $factor;
        $this->changed = true;
        
        if ( isset( $this->cache['getProduction'] ) && isset( $this->cache['getProduction'][$this->getActivePlanet()] ) )
            unset( $this->cache['getProduction'][$this->getActivePlanet()] );
        if ( isset( $this->cache['getItemInfo'] ) && isset( $this->cache['getItemInfo'][$this->getActivePlanet()] ) && isset( $this->cache['getItemInfo'][$this->getActivePlanet()][$gebaeude] ) )
            unset( $this->cache['getItemInfo'][$this->getActivePlanet()][$gebaeude] );
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function getProduction( $run_eventhandler = true )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        if ( ! isset( $this->cache['getProduction'] ) )
            $this->cache['getProduction'] = array();
        $planet = $this->getActivePlanet();
        if ( ! isset( $this->cache['getProduction'][$planet] ) ) {
            $prod = array( 0, 0, 0, 0, 0, 0, 0 );
            if ( $this->permissionToAct() ) {
                $gebaeude = $this->getItemsList( 'gebaeude' );
                
                $energie_prod = 0;
                $energie_need = 0;
                
                foreach ( $gebaeude as $id ) {
                    $item = $this->getItemInfo( $id, 'gebaeude', false );
                    
                    if ( $item['prod'][5] < 0 )
                        $energie_need -= $item['prod'][5];
                    elseif ( $item['prod'][5] > 0 )
                        $energie_prod += $item['prod'][5];
                    
                    $prod[0] += $item['prod'][0];
                    $prod[1] += $item['prod'][1];
                    $prod[2] += $item['prod'][2];
                    $prod[3] += $item['prod'][3];
                    $prod[4] += $item['prod'][4];
                }
                
                $f = 1;
                if ( $energie_need > $energie_prod ) # Nicht genug Energie
{
                    $f = $energie_prod / $energie_need;
                    $prod[0] *= $f;
                    $prod[1] *= $f;
                    $prod[2] *= $f;
                    $prod[3] *= $f;
                    $prod[4] *= $f;
                }
                
                $prod[5] = $energie_prod - $energie_need;
                
                stdround( $prod[0] );
                stdround( $prod[1] );
                stdround( $prod[2] );
                stdround( $prod[3] );
                stdround( $prod[4] );
                stdround( $prod[5] );
                
                $prod[6] = $f;
            }
            $this->cache['getProduction'][$planet] = $prod;
        }
        return $this->cache['getProduction'][$planet];
    }

    /**
     * TODO, add tests
     *
     */       
    function gameLocked( )
    {
        return database_locked();
    }

    /**
     * TODO, add tests
     *
     */       
    function userLocked( $check_unlocked = true )
    {
        if ( ! $this->status )
            return false;
        
        if ( $check_unlocked && isset( $this->raw['lock_time'] ) && $this->raw['lock_time'] && time() > $this->raw['lock_time'] )
            $this->lockUser( false, false );
        return ( isset( $this->raw['locked'] ) && $this->raw['locked'] );
    }

    /**
     * TODO, add tests
     *
     */       
    function lockedUntil( )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! $this->userLocked() )
            return false;
        if ( ! isset( $this->raw['lock_time'] ) )
            return false;
        return $this->raw['lock_time'];
    }

    /**
     * TODO, add tests
     *
     */       
    function lockUser( $lock_time = false, $check_unlocked = true )
    {
        if ( ! $this->status )
            return false;
        
        $this->eventhandler( 0, 1, 1, 1, 1, 1 );
        $this->raw['locked'] = ! $this->userLocked( $check_unlocked );
        $this->raw['lock_time'] = ( $this->raw['locked'] ? $lock_time : false );
        $this->changed = true;
        
        # Planeteneigentuemer umbenennen
        $flag = '';
        if ( $this->userLocked( false ) )
            $flag = 'g';
        $active_planet = $this->getActivePlanet();
        $planets = $this->getPlanetsList();
        foreach ( $planets as $planet ) {
            $this->setActivePlanet( $planet );
            $pos = $this->getPos();
            $galaxy = Classes::Galaxy( $pos[0] );
            $galaxy->setPlanetOwnerFlag( $pos[1], $pos[2], $flag );
        }
        if ( $active_planet !== false )
            $this->setActivePlanet( $active_planet );
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function umode( $set = -1 )
    {
        if ( ! $this->status )
            return false;
            #Beim Betreten des Umods Bedingungen und Zeiten setzen
        if ( $set !== - 1 && $this->raw['umode'] == false ) {
            #Kein Umod bei wegfliegenden Eigenflotten
            $flotte = $this->getFleetsForUmode();
            if ( $flotte == true ) {
                foreach ( $flotte as $fl ) {
                    $cb = Classes::Fleet( $fl );
                    $cb->callBack( $this->getName() );
                }
            }
            
            $planets1 = $this->getPlanetsList();
            foreach ( $planets1 as $planet1 ) {
                $this->setActivePlanet( $planet1 );
                
                #Gebauede verbleibende Bauzeit setzen
                $geb = $this->checkBuildingThing( 'gebaeude', false );
                if ( $geb == true ) {
                    $gremtime = $geb[1] - time();
                    $this->planet_info['building']['gebaeude'][1] = $gremtime;
                    $this->changed = true;
                }
                #Forschung verbleibende Bauzeit setzen
                $fors = $this->checkBuildingThing( 'forschung', false );
                if ( $fors == true ) {
                    $fremtime = $fors[1] - time();
                    $this->planet_info['building']['forschung'][1] = $fremtime;
                    $this->changed = true;
                }
                #Roboter verbleibende Bauzeit setzen
                $rob = $this->checkBuildingThing( 'roboter', false );
                if ( $rob == true ) {
                    foreach ( $rob as $i => $r ) {
                        if ( time() > $r[1] ) {
                            $rremtime = $r[3] - ( time() - $r[1] );
                        }
                        if ( time() < $r[1] ) {
                            $rremtime = time() - $r[1] - $r[3];
                            $rremtime = $rremtime * - 1;
                        }
                        $this->planet_info['building']['roboter'][$i][1] = $rremtime;
                        $this->changed = true;
                    }
                }
                #Schiffe verbleibende Bauzeit setzen
                $schi = $this->checkBuildingThing( 'schiffe', false );
                if ( $schi == true ) {
                    foreach ( $schi as $si => $sr ) {
                        if ( time() > $sr[1] ) {
                            $sremtime = $sr[3] - ( time() - $sr[1] );
                        }
                        if ( time() < $sr[1] ) {
                            $sremtime = time() - $sr[1] - $sr[3];
                            $sremtime = $sremtime * - 1;
                        }
                        $this->planet_info['building']['schiffe'][$si][1] = $sremtime;
                        $this->changed = true;
                    }
                }
                #Vereidigung verbleibende Bauzeit setzen
                $ver = $this->checkBuildingThing( 'verteidigung', false );
                if ( $ver == true ) {
                    foreach ( $ver as $vi => $vr ) {
                        if ( time() > $vr[1] ) {
                            $vremtime = $vr[3] - ( time() - $vr[1] );
                        }
                        if ( time() < $vr[1] ) {
                            $vremtime = time() - $vr[1] - $vr[3];
                            $vremtime = $vremtime * - 1;
                        }
                        $this->planet_info['building']['verteidigung'][$vi][1] = $vremtime;
                        $this->changed = true;
                    }
                }
            }
        }
        #Bei Rueckkehren aus Umod Zeiten setzen
        if ( $set == false && $this->raw['umodeback'] == 1 ) {
            $planets1 = $this->getPlanetsList();
            foreach ( $planets1 as $planet1 ) {
                $this->setActivePlanet( $planet1 );
                #Gebaeudebauzeit setzen
                $bui = $this->checkBuildingThing( 'gebaeude' );
                if ( $bui[1] < 1111118969 ) {
                    $time = time() + $bui[1];
                    $this->planet_info['building']['gebaeude'][1] = $time;
                }
                #Forschungsbauzeit setzen
                $for = $this->checkBuildingThing( 'forschung' );
                if ( $for[1] < 1111118969 ) {
                    $time = time() + $for[1];
                    $this->planet_info['building']['forschung'][1] = $time;
                }
                #Roboterbauzeit setzen
                $rob = $this->checkBuildingThing( 'roboter' );
                foreach ( $rob as $i => $r ) {
                    if ( $r[1] < 1111118969 ) {
                        $time = time() + $r[1] - $r[3];
                        $this->planet_info['building']['roboter'][$i][1] = $time;
                    }
                }
                #Flottenbauzeit setzen
                $flo = $this->checkBuildingThing( 'schiffe' );
                foreach ( $flo as $i => $r ) {
                    if ( $r[1] < 1111118969 ) {
                        $time = time() + $r[1] - $r[3];
                        $this->planet_info['building']['schiffe'][$i][1] = $time;
                    }
                }
                #Verteidigungsbauzeit setzen
                $ver = $this->checkBuildingThing( 'verteidigung' );
                foreach ( $ver as $i => $r ) {
                    if ( $r[1] < 1111118969 ) {
                        $time = time() + $r[1] - $r[3];
                        $this->planet_info['building']['verteidigung'][$i][1] = $time;
                    }
                }
            }
        }
        
        #Umode Flags setzen und wieder loeschen
        if ( $set !== - 1 ) {
            $set = (bool) $set;
            if ( $set == $this->umode() )
                return true;
            $this->raw['umode'] = $set;
            $this->raw['umode_time'] = time();
            $this->raw['umodeback'] = 1;
            $this->changed = true;
            
            $flag = ( $this->raw['umode'] ? 'U' : '' );
            $active_planet = $this->getActivePlanet();
            $planets = $this->getPlanetsList();
            foreach ( $planets as $planet ) {
                $this->setActivePlanet( $planet );
                $pos = $this->getPos();
                $galaxy_obj = Classes::Galaxy( $pos[0] );
                $galaxy_obj->setPlanetOwnerFlag( $pos[1], $pos[2], $flag );
            }
            $this->setActivePlanet( $planet );
            
            if ( isset( $this->cache['getProduction'] ) ) # Produktion wird auf 0 gefahren
                unset( $this->cache['getProduction'] );
            
            return true;
        }
        
        return ( isset( $this->raw['umode'] ) && $this->raw['umode'] );
    }

    /**
     * TODO, add tests
     *
     */       
    function permissionToUmode( )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['umode_time'] ) )
            return true;
        
        if ( $this->umode() )
            $min_days = 2; # Ist gerade im Urlaubsmodus
        else
            $min_days = 2;
        
        return ( ( time() - $this->raw['umode_time'] ) > $min_days * 86400 );
    }

    /**
     * TODO, add tests
     *
     */       
    function getUmodeReturnTime( )
    {
        if ( ! $this->status )
            return false;
        
        if ( $this->umode() )
            return $this->raw['umode_time'] + 2 * 86400;
        else
            return time() + 2 * 86400;
    }

    /**
     * TODO, add tests
     *
     */       
    function permissionToAct( )
    {
        return ! ( database_locked() || $this->userLocked() || $this->umode() );
    }

    /**
     * TODO, add tests
     *
     */       
    protected function getDataFromRaw( )
    {
        $settings = array( 'skin' => false, 'schrift' => true, 'sonden' => 1, 'ress_refresh' => 0, 'fastbuild' => false, 'shortcuts' => false, 'tooltips' => true, 'ipcheck' => true, 'noads' => false, 'show_extern' => true, 'notify' => true, 'email' => true, 'ajax' => true, 'receive' => array( 1 => array( true, true ), 2 => array( true, false ), 3 => array( true, false ), 4 => array( true, true ), 5 => array( true, false ) ), 'show_building' => array( 'gebaeude' => 1, 'forschung' => 1, 'roboter' => 0, 'schiffe' => 0, 'verteidigung' => 0 ), 'prod_show_days' => 1, 'messenger_receive' => array( 'messages' => array( 1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true, 7 => true ), 'building' => array( 'gebaeude' => 1, 'forschung' => 1, 'roboter' => 3, 'schiffe' => 3, 'verteidigung' => 3 ) ) );
        
        $this->settings = array();
        foreach ( $settings as $setting => $default ) {
            if ( isset( $this->raw[$setting] ) )
                $this->settings[$setting] = $this->raw[$setting];
            else
                $this->settings[$setting] = $default;
        }
        if ( ! isset( $this->settings['messenger_receive']['building'] ) )
            $this->settings['messenger_receive']['building'] = array( 'gebaeude' => 1, 'forschung' => 1, 'roboter' => 3, 'schiffe' => 3, 'verteidigung' => 3 );
        
        $this->items = array();
        $this->items['forschung'] = $this->raw['forschung'];
        $this->items['ids'] = array();
        foreach ( $this->items['forschung'] as $id => $level )
            $this->items['ids'][$id] = &$this->items['forschung'][$id];
        
        $this->name = $this->raw['username'];
        
        $this->realEventhandler();
    }

    /**
     * TODO, add tests
     *
     */       
    protected function getRawFromData( )
    {
        if ( $this->recalc_highscores[0] || $this->recalc_highscores[1] || $this->recalc_highscores[2] || $this->recalc_highscores[3] || $this->recalc_highscores[4] ) {
            $this->doRecalcHighscores( $this->recalc_highscores[0], $this->recalc_highscores[1], $this->recalc_highscores[2], $this->recalc_highscores[3], $this->recalc_highscores[4] );
        }
        
        foreach ( $this->settings as $setting => $value )
            $this->raw[$setting] = $value;
        $this->raw['forschung'] = $this->items['forschung'];
        
        $active_planet = $this->getActivePlanet();
        if ( $active_planet !== false ) {
            $this->planet_info['gebaeude'] = $this->items['gebaeude'];
            $this->planet_info['roboter'] = $this->items['roboter'];
            $this->planet_info['schiffe'] = $this->items['schiffe'];
            $this->planet_info['verteidigung'] = $this->items['verteidigung'];
            $this->planet_info['ress'] = $this->ress;
        }
    }

    /**
     * TODO, add tests
     *
     */       
    /**
     * checks the active planet info for currently building stuff 
     * and returns the building elements if exists
     * @param object $type - type of item to check
     * @param object $run_eventhandler [optional] - run eventhandler before returning, default = yes
     * @return 
     */
    function checkBuildingThing( $type, $run_eventhandler = true )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        if ( $run_eventhandler ) {
            switch ( $type ) {
                case 'gebaeude':
                    $this->eventhandler( false, 1, 0, 0, 0, 0 );
                    break;
                case 'forschung':
                    $this->eventhandler( false, 0, 1, 0, 0, 0 );
                    break;
                case 'roboter':
                    $this->eventhandler( false, 0, 0, 1, 0, 0 );
                    break;
                case 'schiffe':
                    $this->eventhandler( false, 0, 0, 0, 1, 0 );
                    break;
                case 'verteidigung':
                    $this->eventhandler( false, 0, 0, 0, 0, 1 );
                    break;
                default:
                    return false;
            }
        }
        
        switch ( $type ) {
            case 'gebaeude':
            case 'forschung':
                
                if ( ! isset( $this->planet_info['building'] ) || ! isset( $this->planet_info['building'][$type] ) || ! isset( $this->planet_info['building'][$type][0] ) || trim( $this->planet_info['building'][$type][0] ) == '' )
                    return false;
                else
                    return $this->planet_info['building'][$type];
            
            case 'roboter':
            case 'schiffe':
            case 'verteidigung':
                
                if ( ! isset( $this->planet_info['building'] ) || ! isset( $this->planet_info['building'][$type] ) || count( $this->planet_info['building'][$type] ) <= 0 )
                    return array();
                else
                    return $this->planet_info['building'][$type];
            
            default:
                return false;
        }
    }

    /**
     * TODO, add tests
     *
     */       
    function removeBuildingThing( $type, $cancel = true )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        switch ( $type ) {
            case 'gebaeude':
            case 'forschung':
                if ( ! isset( $this->planet_info['building'] ) || ! isset( $this->planet_info['building'][$type] ) || trim( $this->planet_info['building'][$type][0] ) == '' )
                    return false;
                
                if ( $type == 'forschung' && $this->planet_info['building'][$type][2] ) {
                    $source_planet = $this->planet_info['building'][$type][4];
                    //if(!isset($this->raw['planets'][$source_planet]['building'][$type]) || trim($this->raw['planets'][$source_planet]['building'][$type][0]) == '')
                    //    return false;
                    $active_planet = $this->getActivePlanet();
                    $planets = $this->getPlanetsList();
                    foreach ( $planets as $planet ) {
                        $this->setActivePlanet( $planet );
                        if ( $planet == $source_planet && $cancel )
                            $this->addRess( $this->planet_info['building'][$type][3] );
                        if ( isset( $this->planet_info['building'][$type] ) )
                            unset( $this->planet_info['building'][$type] );
                    }
                    $this->setActivePlanet( $active_planet );
                }
                elseif ( $cancel )
                    $this->addRess( $this->planet_info['building'][$type][3] );
                
                if ( $cancel ) {
                    $this->raw['punkte'][7] -= $this->planet_info['building'][$type][3][0];
                    $this->raw['punkte'][8] -= $this->planet_info['building'][$type][3][1];
                    $this->raw['punkte'][9] -= $this->planet_info['building'][$type][3][2];
                    $this->raw['punkte'][10] -= $this->planet_info['building'][$type][3][3];
                    $this->raw['punkte'][11] -= $this->planet_info['building'][$type][3][4];
                    if ( isset( $this->cache['getSpentRess'] ) )
                        unset( $this->cache['getSpentRess'] );
                }
                
                unset( $this->planet_info['building'][$type] );
                $this->changed = true;
                
                if ( $cancel )
                    $this->refreshMessengerBuildingNotifications( $type );
                
                return true;
            case 'roboter':
            case 'schiffe':
            case 'verteidigung':
                if ( ! isset( $this->planet_info['building'] ) || ! isset( $this->planet_info['building'][$type] ) || count( $this->planet_info['building'][$type] ) <= 0 )
                    return false;
                unset( $this->planet_info['building'][$type] );
                $this->changed = true;
                
                if ( $cancel )
                    $this->refreshMessengerBuildingNotifications( $type );
                
                return true;
        }
    }

    /**
     * TODO, add tests
     *
     */       
    function eventhandler( $check_id = false, $check_gebaeude = true, $check_forschung = true, $check_roboter = true, $check_schiffe = true, $check_verteidigung = true )
    {/* Dummy function */}

    /**
     * TODO, add tests
     *
     */       
    function realEventhandler( )
    {
        if ( ! $this->raw )
            return false;
        
        $actions = array();
        /* Array
               (
                [0] => Zeit
                [1] => ID
                [2] => Stufen hinzuzaehlen
                [3] => Rohstoffe neu berechnen?
                [4] => Planet
              )*/
        
        $active_planet = $this->getActivePlanet();
        
        foreach ( $this->getPlanetsList() as $planet ) {
            $this->setActivePlanet( $planet );
            if ( ! isset( $this->raw['umode'] ) || $this->raw['umode'] == 0 ) {
                $building = $this->checkBuildingThing( 'gebaeude', false );
                if ( $building !== false && $building[1] <= time() && $this->removeBuildingThing( 'gebaeude', false ) ) {
                    $stufen = 1;
                    if ( $building[2] )
                        $stufen = - 1;
                    $actions[] = array( $building[1], $building[0], $stufen, true, $planet );
                }
                
                $building = $this->checkBuildingThing( 'forschung', false );
                if ( $building !== false && $building[1] <= time() && $this->removeBuildingThing( 'forschung', false ) ) {
                    $actions[] = array( $building[1], $building[0], 1, true, $planet );
                }
            
            }
            
            $building = $this->checkBuildingThing( 'roboter', false );
            foreach ( $building as $j => $items ) {
                $info = $this->getItemInfo( $items[0], 'roboter', false );
                if ( ! $info || isset( $this->raw['umode'] ) && $this->raw['umode'] == 1 )
                    continue;
                
                $time = $items[1];
                for ( $i = 0; $i < $items[2]; $i ++ ) {
                    $time += $items[3];
                    if ( $time <= time() ) {
                        $actions[] = array( $time, $items[0], 1, true, $planet );
                        
                        # Roboter entfernen
                        $this->planet_info['building']['roboter'][$j][2] --;
                        if ( $this->planet_info['building']['roboter'][$j][2] <= 0 ) {
                            unset( $this->planet_info['building']['roboter'][$j] );
                            break;
                        }
                        else
                            $this->planet_info['building']['roboter'][$j][1] = $time;
                    }
                    else
                        break 2;
                }
            }
            
            $building = $this->checkBuildingThing( 'schiffe', false );
            foreach ( $building as $j => $items ) {
                $info = $this->getItemInfo( $items[0], 'schiffe', false );
                if ( ! $info || isset( $this->raw['umode'] ) && $this->raw['umode'] == 1 )
                    continue;
                $time = $items[1];
                for ( $i = 0; $i < $items[2]; $i ++ ) {
                    $time += $items[3];
                    if ( $time <= time() ) {
                        $actions[] = array( $time, $items[0], 1, true, $planet );
                        
                        # Schiff entfernen
                        $this->planet_info['building']['schiffe'][$j][2] --;
                        if ( $this->planet_info['building']['schiffe'][$j][2] <= 0 ) {
                            unset( $this->planet_info['building']['schiffe'][$j] );
                            break;
                        }
                        else
                            $this->planet_info['building']['schiffe'][$j][1] = $time;
                    }
                    else
                        break 2;
                }
            }
            
            $building = $this->checkBuildingThing( 'verteidigung', false );
            foreach ( $building as $j => $items ) {
                $info = $this->getItemInfo( $items[0], 'verteidigung', false );
                if ( ! $info || isset( $this->raw['umode'] ) && $this->raw['umode'] == 1 )
                    continue;
                
                $time = $items[1];
                for ( $i = 0; $i < $items[2]; $i ++ ) {
                    $time += $items[3];
                    if ( $time <= time() ) {
                        $actions[] = array( $time, $items[0], 1, true, $planet );
                        
                        # Schiff entfernen
                        $this->planet_info['building']['verteidigung'][$j][2] --;
                        if ( $this->planet_info['building']['verteidigung'][$j][2] <= 0 ) {
                            unset( $this->planet_info['building']['verteidigung'][$j] );
                            break;
                        }
                        else
                            $this->planet_info['building']['verteidigung'][$j][1] = $time;
                    }
                    else
                        break 2;
                }
            }
            
            if ( count( $actions ) > 0 ) {
                usort( $actions, 'sortEventhandlerActions' );
                
                while ( $action = array_shift( $actions ) ) {
                    
                    $this->setActivePlanet( $action[4] );
                    
                    if ( $action[3] )
                        $this->refreshRess( $action[0] );
                    
                    $this->changeItemLevel( $action[1], $action[2], false, $action[0], $actions );
                    
                    if ( isset( $this->cache['getProduction'] ) )
                        unset( $this->cache['getProduction'] );
                    if ( isset( $this->cache['getItemInfo'] ) )
                        unset( $this->cache['getItemInfo'] );
                }
                
                $this->changed = true;
            }
        }
        
        $this->setActivePlanet( $active_planet );
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function isVerbuendet( $user )
    {
        if ( ! $this->status )
            return false;
        
        if ( $user == $this->getName() )
            return true;
        
        if ( ! isset( $this->raw['verbuendete'] ) )
            return false;
        return in_array( $user, $this->raw['verbuendete'] );
    }

    /**
     * TODO, add tests
     *
     */       
    function existsVerbuendet( $user )
    {
        if ( ! $this->status )
            return false;
        
        return ( $user == $this->getName() || ( isset( $this->raw['verbuendete'] ) && in_array( $user, $this->raw['verbuendete'] ) ) || ( isset( $this->raw['verbuendete_bewerbungen'] ) && in_array( $user, $this->raw['verbuendete_bewerbungen'] ) ) || ( isset( $this->raw['verbuendete_anfragen'] ) && in_array( $user, $this->raw['verbuendete_anfragen'] ) ) );
    }

    /**
     * TODO, add tests
     *
     */       
    function renameVerbuendet( $old_name, $new_name )
    {
        if ( ! $this->status )
            return false;
        
        if ( $old_name == $new_name )
            return 2;
        
        $k1 = ( isset( $this->raw['verbuendete'] ) ? array_search( $old_name, $this->raw['verbuendete'] ) : false );
        $k2 = ( isset( $this->raw['verbuendete_bewerbungen'] ) ? array_search( $old_name, $this->raw['verbuendete_bewerbungen'] ) : false );
        $k3 = ( isset( $this->raw['verbuendete_anfragen'] ) ? array_search( $old_name, $this->raw['verbuendete_anfragen'] ) : false );
        
        if ( $k1 !== false )
            $this->raw['verbuendete'][$k1] = $new_name;
        if ( $k2 !== false )
            $this->raw['verbuendete_bewerbungen'][$k2] = $new_name;
        if ( $k3 !== false )
            $this->raw['verbuendete_anfragen'][$k3] = $new_name;
        
        $this->changed = ( $k1 !== false || $k2 !== false || $k3 !== false );
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function getVerbuendetList( )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['verbuendete'] ) )
            return array();
        else
            return $this->raw['verbuendete'];
    }

    /**
     * TODO, add tests
     *
     */       
    function getVerbuendetApplicationList( )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['verbuendete_bewerbungen'] ) )
            return array();
        else
            return $this->raw['verbuendete_bewerbungen'];
    }

    /**
     * TODO, add tests
     *
     */       
    function getVerbuendetRequestList( )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['verbuendete_anfragen'] ) )
            return array();
        else
            return $this->raw['verbuendete_anfragen'];
    }

    /**
     * TODO, add tests
     *
     */       
    function _addVerbuendetRequest( $user )
    {
        if ( ! $this->status )
            return false;
        if ( $this->existsVerbuendet( $user ) )
            return false;
        
        if ( ! isset( $this->raw['verbuendete_anfragen'] ) )
            $this->raw['verbuendete_anfragen'] = array();
        $this->raw['verbuendete_anfragen'][] = $user;
        
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function _removeVerbuendetRequest( $user )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['verbuendete_anfragen'] ) || ! in_array( $user, $this->raw['verbuendete_anfragen'] ) )
            return false;
        unset( $this->raw['verbuendete_anfragen'][array_search( $user, $this->raw['verbuendete_anfragen'] )] );
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function _removeVerbuendetApplication( $user )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['verbuendete_bewerbungen'] ) || ! in_array( $user, $this->raw['verbuendete_bewerbungen'] ) )
            return false;
        
        unset( $this->raw['verbuendete_bewerbungen'][array_search( $user, $this->raw['verbuendete_bewerbungen'] )] );
        $this->changed = true;
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function _addVerbuendet( $user )
    {
        if ( ! $this->status )
            return false;
        
        if ( $this->isVerbuendet( $user ) )
            return false;
        
        if ( ! isset( $this->raw['verbuendete'] ) )
            $this->raw['verbuendete'] = array();
        $this->raw['verbuendete'][] = $user;
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function _removeVerbuendet( $user )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! $this->isVerbuendet( $user ) )
            return false;
        unset( $this->raw['verbuendete'][array_search( $user, $this->raw['verbuendete'] )] );
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function applyVerbuendet( $user, $text = '' )
    {
        if ( ! $this->status )
            return false;
        
        if ( $this->existsVerbuendet( $user ) )
            return false;
        
        $that_user = Classes::User( $user );
        if ( $that_user->_addVerbuendetRequest( $this->getName() ) ) {
            if ( ! isset( $this->raw['verbuendete_bewerbungen'] ) )
                $this->raw['verbuendete_bewerbungen'] = array();
            $this->raw['verbuendete_bewerbungen'][] = $user;
            $this->changed = true;
            
            $message = Classes::Message();
            if ( $message->create() ) {
                $message->addUser( $user, 7 );
                $message->subject( "Anfrage auf ein B\xc3\xbcndnis" );
                $message->from( $this->getName() );
                if ( trim( $text ) == '' )
                    $message->text( "Der Spieler " . $this->getName() . " hat Ihnen eine mitteilungslose B\xc3\xbcndnisanfrage gestellt." );
                else
                    $message->text( $text );
            }
            
            return true;
        }
        else
            return false;
    }

    /**
     * TODO, add tests
     *
     */       
    function acceptVerbuendetApplication( $user )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['verbuendete_anfragen'] ) || ! in_array( $user, $this->raw['verbuendete_anfragen'] ) )
            return false;
        
        $user_obj = Classes::User( $user );
        if ( ! $user_obj->_removeVerbuendetApplication( $this->getName() ) )
            return false;
        
        unset( $this->raw['verbuendete_anfragen'][array_search( $user, $this->raw['verbuendete_anfragen'] )] );
        
        $user_obj->_addVerbuendet( $this->getName() );
        $this->_addVerbuendet( $user );
        
        $message = Classes::Message();
        if ( $message->create() ) {
            $message->from( $this->getName() );
            $message->subject( "B\xc3\xbcndnisanfrage angenommen" );
            $message->text( "Der Spieler " . $this->getName() . " hat Ihre B\xc3\xbcndnisanfrage angenommen." );
            $message->addUser( $user, 7 );
        }
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function rejectVerbuendetApplication( $user )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['verbuendete_anfragen'] ) || ! in_array( $user, $this->raw['verbuendete_anfragen'] ) )
            return false;
        
        $user_obj = Classes::User( $user );
        if ( ! $user_obj->_removeVerbuendetApplication( $this->getName() ) )
            return false;
        
        unset( $this->raw['verbuendete_anfragen'][array_search( $user, $this->raw['verbuendete_anfragen'] )] );
        
        $message = Classes::Message();
        if ( $message->create() ) {
            $message->from( $this->getName() );
            $message->subject( "B\xc3\xbcndnisanfrage abgelehnt" );
            $message->text( "Der Spieler " . $this->getName() . " hat Ihre B\xc3\xbcndnisanfrage abgelehnt." );
            $message->addUser( $user, 7 );
        }
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function quitVerbuendet( $user )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! $this->isVerbuendet( $user ) )
            return false;
        
        $user_obj = Classes::User( $user );
        if ( $user_obj->_removeVerbuendet( $user ) ) {
            $this->_removeVerbuendet( $user );
            
            $message = Classes::Message();
            if ( $message->create() ) {
                $message->from( $this->getName() );
                $message->subject( "B\xc3\xbcndnis gek\xc3\xbcndigt" );
                $message->text( "Der Spieler " . $this->getName() . " hat sein B\xc3\xbcndnis mit Ihnen gek\xc3\xbcndigt." );
                $message->addUser( $user, 7 );
            }
            
            $this->changed = true;
            
            return true;
        }
        else
            return false;
    }

    /**
     * TODO, add tests
     *
     */       
    function verbuendetNewsletter( $subject, $text )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['verbuendete'] ) || count( $this->raw['verbuendete'] ) <= 0 )
            return false;
        if ( trim( $text ) == '' )
            return false;
        
        $message = Classes::Message();
        if ( $message->create() ) {
            $message->from( $this->getName() );
            $message->to( 'Bündnisrundschreiben' );
            
            $message->subject( $subject );
            $message->text( $text );
            foreach ( $this->raw['verbuendete'] as $verbuendeter )
                $message->addUser( $verbuendeter, 7 );
        }
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function cancelVerbuendetApplication( $user )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['verbuendete_bewerbungen'] ) || ! in_array( $user, $this->raw['verbuendete_bewerbungen'] ) )
            return false;
        
        $user_obj = Classes::User( $user );
        if ( $user_obj->_removeVerbuendetRequest( $this->getName() ) ) {
            unset( $this->raw['verbuendete_bewerbungen'][array_search( $user, $this->raw['verbuendete_bewerbungen'] )] );
            
            $message = Classes::Message();
            if ( $message->create() ) {
                $message->from( $this->getName() );
                $message->subject( "B\xc3\xbcndnisanfrage zur\xc3\xbcckgezogen" );
                $message->text( "Der Spieler " . $this->getName() . " hat seine B\xc3\xbcndnisanfrage an Sie zur\xc3\xbcckgezogen." );
                $message->addUser( $user, 7 );
            }
            $this->changed = true;
            return true;
        }
        else
            return false;
    }

    /**
     * TODO, add tests
     *
     */       
    function allianceTag( $tag = '', $check = true )
    {
        if ( ! $this->status )
            return false;
        
        if ( $tag === '' ) {
            __autoload( 'Alliance' );
            if ( ! isset( $this->raw['alliance'] ) || trim( $this->raw['alliance'] ) == '' || ! Alliance::allianceExists( $this->raw['alliance'] ) )
                return false;
            else
                return trim( $this->raw['alliance'] );
        }
        else {
            if ( $tag && $check ) {
                $that_alliance = Classes::Alliance( $tag );
                if ( ! $that_alliance->getStatus() )
                    return false;
            }
            if ( ( isset( $this->raw['alliance'] ) && trim( $this->raw['alliance'] ) != '' ) && ( ! $tag || $tag != $this->raw['alliance'] ) ) {
                # Aus der aktuellen Allianz austreten
                if ( $check ) {
                    $my_alliance = Classes::Alliance( trim( $this->raw['alliance'] ) );
                    if ( ! $my_alliance->getStatus() )
                        return false;
                    if ( ! $my_alliance->removeUser( $this->getName() ) )
                        return false;
                }
                $this->raw['alliance'] = '';
                $this->changed = true;
            }
            
            if ( $check ) {
                if ( $tag ) {
                    $that_alliance->addUser( $this->getName(), $this->getScores() );
                    $tag = $that_alliance->getName();
                }
                else
                    $tag = '';
            }
            
            $this->raw['alliance'] = $tag;
            
            if ( $check )
                $this->cancelAllianceApplication( false );
            $this->changed = true;
            
            $highscores = Classes::Highscores();
            $highscores->updateUser( $this->getName(), $tag );
            
            $active_planet = $this->getActivePlanet();
            $planets = $this->getPlanetsList();
            foreach ( $planets as $planet ) {
                $this->setActivePlanet( $planet );
                $pos = $this->getPos();
                $galaxy = Classes::Galaxy( $pos[0] );
                $galaxy->setPlanetOwnerAlliance( $pos[1], $pos[2], $tag );
            }
            $this->setActivePlanet( $active_planet );
            
            return true;
        }
    }

    /**
     * TODO, add tests
     *
     */       
    function getAllianceTag( )
    {
        return $this->raw['alliance'];
    }

    /**
     * TODO, add tests
     *
     */       
    function cancelAllianceApplication( $message = true )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['alliance_bewerbung'] ) || ! $this->raw['alliance_bewerbung'] )
            return false;
        
        $alliance_obj = Classes::Alliance( $this->raw['alliance_bewerbung'] );
        if ( ! $alliance_obj->deleteApplication( $this->getName() ) )
            return false;
        if ( $message ) {
            $message_obj = Classes::Message();
            if ( $message_obj->create() ) {
                $message_obj->from( $this->getName() );
                $message_obj->subject( "Allianzbewerbung zur\xc3\xbcckgezogen" );
                $message_obj->text( 'Der Benutzer ' . $this->getName() . " hat seine Bewerbung bei Ihrer Allianz zur\xc3\xbcckgezogen." );
                $users = $alliance_obj->getUsersWithPermission( 4 );
                foreach ( $users as $user )
                    $message_obj->addUser( $user, 7 );
            }
        }
        unset( $alliance_obj );
        $this->raw['alliance_bewerbung'] = false;
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function allianceApplication( $alliance = false, $text = false )
    {
        if ( ! $this->status )
            return false;
        if ( $this->allianceTag() )
            return false;
        
        if ( ! $alliance ) {
            if ( ! isset( $this->raw['alliance_bewerbung'] ) )
                return false;
            return $this->raw['alliance_bewerbung'];
        }
        else {
            if ( $this->status != 1 )
                return false;
            if ( isset( $this->raw['alliance_bewerbung'] ) && $this->raw['alliance_bewerbung'] )
                return false;
            
            $alliance_obj = Classes::Alliance( $alliance );
            $alliance = $alliance_obj->getName();
            if ( ! $alliance_obj->getStatus() )
                return false;
            if ( ! $alliance_obj->newApplication( $this->getName() ) )
                return false;
            
            $message = Classes::Message();
            if ( $message->create() ) {
                $message_text = "Der Benutzer " . $this->getName() . " hat sich bei Ihrer Allianz beworben. Gehen Sie auf Ihre Allianzseite, um die Bewerbung anzunehmen oder abzulehnen.";
                if ( ! trim( $text ) )
                    $message_text .= "\n\nDer Bewerber hat keinen Bewerbungstext hinterlassen.";
                else
                    $message_text .= "\n\nDer Bewerber hat folgenden Bewerbungstext hinterlassen:\n\n" . $text;
                $message->text( $message_text );
                $message->from( $this->getName() );
                $message->subject( 'Neue Allianzbewerbung' );
                
                $users = $alliance_obj->getUsersWithPermission( 4 );
                foreach ( $users as $user )
                    $message->addUser( $user, 7 );
            }
            
            $this->raw['alliance_bewerbung'] = $alliance;
            $this->changed = true;
            return true;
        }
    }

    /**
     * TODO, add tests
     *
     */       
    function quitAlliance( )
    {
        if ( $this->status != 1 )
            return false;
        if ( ! $this->allianceTag() )
            return false;
        
        $alliance = Classes::Alliance( $this->allianceTag() );
        if ( ! $alliance->removeUser( $this->getName() ) )
            return false;
        
        $members = $alliance->getUsersList();
        if ( $members ) {
            $message = Classes::Message();
            if ( $message->create() ) {
                $message->from( $this->getName() );
                $message->subject( 'Benutzer aus Allianz ausgetreten' );
                $message->text( 'Der Benutzer ' . $this->getName() . ' hat Ihre Allianz verlassen.' );
                foreach ( $members as $member )
                    $message->addUser( $member, 7 );
            }
        
        }
        
        $this->allianceTag( false );
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function checkPlanetCount( )
    {
        if ( ! $this->status )
            return false;
        
        if ( global_setting( "MAX_PLANETS" ) > 0 && count( $this->raw['planets'] ) < global_setting( "MAX_PLANETS" ) )
            return true;
        else
            return false;
    }

    /**
     * TODO, add tests
     *
     */       
    function buildGebaeude( $id, $rueckbau = false )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        if ( $this->checkBuildingThing( 'gebaeude' ) )
            return false;
        if ( $id == 'B8' && $this->checkBuildingThing( 'forschung' ) )
            return false;
        if ( $id == 'B9' && $this->checkBuildingThing( 'roboter' ) )
            return false;
        if ( $id == 'B10' && ( $this->checkBuildingThing( 'schiffe' ) || $this->checkBuildingThing( 'verteidigung' ) ) )
            return false;
        
        $item_info = $this->getItemInfo( $id, 'gebaeude' );
        if ( $item_info && ( ( ! $rueckbau && $item_info['buildable'] ) || ( $rueckbau && $item_info['debuildable'] ) ) ) {
            # Rohstoffkosten
            $ress = $item_info['ress'];
            
            if ( $rueckbau ) {
                $ress[0] = $ress[0] >> 1;
                $ress[1] = $ress[1] >> 1;
                $ress[2] = $ress[2] >> 1;
                $ress[3] = $ress[3] >> 1;
            }
            
            # Genuegend Rohstoffe zum Ausbau
            if ( ! $this->checkRess( $ress ) )
                return false;
            
            $time = $item_info['time'];
            if ( $rueckbau )
                $time = $time >> 1;
            $time += time();
            
            if ( ! isset( $this->planet_info['building'] ) )
                $this->planet_info['building'] = array();
            $this->planet_info['building']['gebaeude'] = array( $id, $time, $rueckbau, $ress );
            
            # Rohstoffe abziehen
            $this->subtractRess( $ress );
            
            $this->refreshMessengerBuildingNotifications( 'gebaeude' );
            
            return true;
        }
        return false;
    }

    /**
     * TODO, add tests
     *
     */       
    function buildForschung( $id, $global )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) ) {
            return false;
        }
        
        if ( $this->checkBuildingThing( 'forschung' ) ) {
            return false;
        }
        
        if ( ( $gebaeude = $this->checkBuildingThing( 'gebaeude' ) ) && $gebaeude[0] == 'B8' ) {
            return false;
        }
        
        $buildable = true;
        $planets = $this->getPlanetsList();
        $active_planet = $this->getActivePlanet();
        
        foreach ( $planets as $planet ) {
            $this->setActivePlanet( $planet );
            
            /*
                 * WARNING BULLSHIT IF AHEAD
                 * 
                 * disallow to research when it is:
                 *     - going to be global
                 *   AND
                 *     - we are already researching on the planet
                 * OR
                 *     - its not global
                 *   AND
                 *     - we are already researching on the planet
                 *   AND
                 *     - the thing we are going to research is already in research on the planet
                 */
            if ( ( $global && $this->checkBuildingThing( 'forschung' ) ) || ( ! $global && ( $building = $this->checkBuildingThing( 'forschung' ) ) && $building[0] == $id ) ) {
                $buildable = false;
                break;
            }
        }
        
        $this->setActivePlanet( $active_planet );
        $item_info = $this->getItemInfo( $id, 'forschung' );
        
        // check for enough ressources to build that
        if ( !$this->checkRess( $item_info['ress'] ) )
        {
            return false;
        }
        
        if ( $item_info && $item_info['buildable'] ) {
            $build_array = array( $id, time() + $item_info['time_' . ( $global ? 'global' : 'local' )], $global, $item_info['ress'] );
            if ( $global ) {
                $build_array[] = $this->getActivePlanet();
                
                $planets = $this->getPlanetsList();
                
                foreach ( $planets as $planet ) {
                    $this->setActivePlanet( $planet );
                    $this->planet_info['building']['forschung'] = $build_array;
                }
                $this->setActivePlanet( $active_planet );
            }
            else {
                $this->planet_info['building']['forschung'] = $build_array;
            }
            
            $this->subtractRess( $item_info['ress'] );
            
            $this->refreshMessengerBuildingNotifications( 'forschung' );
            
            $this->changed = true;
            
            return true;
        }
        return false;
    }

    /**
     * TODO, add tests
     *
     */       
    function buildRoboter( $id, $anzahl )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        $anzahl = floor( $anzahl );
        if ( $anzahl < 0 )
            return false;
        
        if ( ( $gebaeude = $this->checkBuildingThing( 'gebaeude' ) ) && $gebaeude[0] == 'B9' )
            return false;
        
        $item_info = $this->getItemInfo( $id, 'roboter' );
        if ( ! $item_info || ! $item_info['buildable'] )
            return false;
        
        $ress = $item_info['ress'];
        $ress[0] *= $anzahl;
        $ress[1] *= $anzahl;
        $ress[2] *= $anzahl;
        $ress[3] *= $anzahl;
        
        if ( ! $this->checkRess( $ress ) ) {
            $planet_ress = $this->getRess();
            $ress = $item_info['ress'];
            $anzahlen = array();
            if ( $ress[0] > 0 )
                $anzahlen[] = floor( $planet_ress[0] / $ress[0] );
            if ( $ress[1] > 0 )
                $anzahlen[] = floor( $planet_ress[1] / $ress[1] );
            if ( $ress[2] > 0 )
                $anzahlen[] = floor( $planet_ress[2] / $ress[2] );
            if ( $ress[3] > 0 )
                $anzahlen[] = floor( $planet_ress[3] / $ress[3] );
            $anzahl = min( $anzahlen );
            $ress[0] *= $anzahl;
            $ress[1] *= $anzahl;
            $ress[2] *= $anzahl;
            $ress[3] *= $anzahl;
        }
        
        if ( $anzahl <= 0 )
            return false;
        
        $roboter = $this->checkBuildingThing( 'roboter' );
        $make_new = true;
        $last_time = time();
        if ( $roboter && count( $roboter ) > 0 ) {
            $roboter_keys = array_keys( $this->planet_info['building']['roboter'] );
            $last = &$this->planet_info['building']['roboter'][array_pop( $roboter_keys )];
            $last_time = $last[1] + $last[2] * $last[3];
            if ( $last[0] == $id && $last[3] == $item_info['time'] ) {
                $build_array = &$last;
                $make_new = false;
            }
        }
        if ( $make_new ) {
            if ( ! isset( $this->planet_info['building'] ) )
                $this->planet_info['building'] = array();
            if ( ! isset( $this->planet_info['building']['roboter'] ) )
                $this->planet_info['building']['roboter'] = array();
            $build_array = &$this->planet_info['building']['roboter'][];
            $build_array = array( $id, $last_time, 0, $item_info['time'] );
        }
        
        $build_array[2] += $anzahl;
        
        $this->subtractRess( $ress );
        
        $this->refreshMessengerBuildingNotifications( 'roboter' );
        
        $this->changed = true;
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function buildSchiffe( $id, $anzahl )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        $anzahl = floor( $anzahl );
        if ( $anzahl < 0 )
            return false;
        
        if ( ( $gebaeude = $this->checkBuildingThing( 'gebaeude' ) ) && $gebaeude[0] == 'B10' )
            return false;
        
        $item_info = $this->getItemInfo( $id, 'schiffe' );
        if ( ! $item_info || ! $item_info['buildable'] )
            return false;
        
        $ress = $item_info['ress'];
        $ress[0] *= $anzahl;
        $ress[1] *= $anzahl;
        $ress[2] *= $anzahl;
        $ress[3] *= $anzahl;
        
        if ( ! $this->checkRess( $ress ) ) {
            $planet_ress = $this->getRess();
            $ress = $item_info['ress'];
            $anzahlen = array();
            if ( $ress[0] > 0 )
                $anzahlen[] = floor( $planet_ress[0] / $ress[0] );
            if ( $ress[1] > 0 )
                $anzahlen[] = floor( $planet_ress[1] / $ress[1] );
            if ( $ress[2] > 0 )
                $anzahlen[] = floor( $planet_ress[2] / $ress[2] );
            if ( $ress[3] > 0 )
                $anzahlen[] = floor( $planet_ress[3] / $ress[3] );
            $anzahl = min( $anzahlen );
            $ress[0] *= $anzahl;
            $ress[1] *= $anzahl;
            $ress[2] *= $anzahl;
            $ress[3] *= $anzahl;
        }
        
        if ( $anzahl <= 0 )
            return false;
        
        $schiffe = $this->checkBuildingThing( 'schiffe' );
        $make_new = true;
        $last_time = time();
        if ( $schiffe && count( $schiffe ) > 0 ) {
            $schiffe_keys = array_keys( $this->planet_info['building']['schiffe'] );
            $last = &$this->planet_info['building']['schiffe'][array_pop( $schiffe_keys )];
            $last_time = $last[1] + $last[2] * $last[3];
            if ( $last[0] == $id && $last[3] == $item_info['time'] ) {
                $build_array = &$last;
                $make_new = false;
            }
        }
        if ( $make_new ) {
            if ( ! isset( $this->planet_info['building'] ) )
                $this->planet_info['building'] = array();
            if ( ! isset( $this->planet_info['building']['schiffe'] ) )
                $this->planet_info['building']['schiffe'] = array();
            $build_array = &$this->planet_info['building']['schiffe'][];
            $build_array = array( $id, $last_time, 0, $item_info['time'] );
        }
        
        $build_array[2] += $anzahl;
        
        $this->subtractRess( $ress );
        
        $this->refreshMessengerBuildingNotifications( 'schiffe' );
        
        $this->changed = true;
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function buildVerteidigung( $id, $anzahl )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        
        $anzahl = floor( $anzahl );
        if ( $anzahl < 0 )
            return false;
        
        if ( ( $gebaeude = $this->checkBuildingThing( 'gebaeude' ) ) && $gebaeude[0] == 'B10' )
            return false;
        
        $item_info = $this->getItemInfo( $id, 'verteidigung' );
        if ( ! $item_info || ! $item_info['buildable'] )
            return false;
        
        $ress = $item_info['ress'];
        $ress[0] *= $anzahl;
        $ress[1] *= $anzahl;
        $ress[2] *= $anzahl;
        $ress[3] *= $anzahl;
        
        if ( ! $this->checkRess( $ress ) ) {
            $planet_ress = $this->getRess();
            $ress = $item_info['ress'];
            $anzahlen = array();
            if ( $ress[0] > 0 )
                $anzahlen[] = floor( $planet_ress[0] / $ress[0] );
            if ( $ress[1] > 0 )
                $anzahlen[] = floor( $planet_ress[1] / $ress[1] );
            if ( $ress[2] > 0 )
                $anzahlen[] = floor( $planet_ress[2] / $ress[2] );
            if ( $ress[3] > 0 )
                $anzahlen[] = floor( $planet_ress[3] / $ress[3] );
            $anzahl = min( $anzahlen );
            $ress[0] *= $anzahl;
            $ress[1] *= $anzahl;
            $ress[2] *= $anzahl;
            $ress[3] *= $anzahl;
        }
        
        if ( $anzahl <= 0 )
            return false;
        
        $verteidigung = $this->checkBuildingThing( 'verteidigung' );
        $make_new = true;
        $last_time = time();
        if ( $verteidigung && count( $verteidigung ) > 0 ) {
            $verteidigung_keys = array_keys( $this->planet_info['building']['verteidigung'] );
            $last = &$this->planet_info['building']['verteidigung'][array_pop( $verteidigung_keys )];
            $last_time = $last[1] + $last[2] * $last[3];
            if ( $last[0] == $id && $last[3] == $item_info['time'] ) {
                $build_array = &$last;
                $make_new = false;
            }
        }
        if ( $make_new ) {
            if ( ! isset( $this->planet_info['building'] ) )
                $this->planet_info['building'] = array();
            if ( ! isset( $this->planet_info['building']['verteidigung'] ) )
                $this->planet_info['building']['verteidigung'] = array();
            $build_array = &$this->planet_info['building']['verteidigung'][];
            $build_array = array( $id, $last_time, 0, $item_info['time'] );
        }
        
        $build_array[2] += $anzahl;
        
        $this->subtractRess( $ress );
        
        $this->refreshMessengerBuildingNotifications( 'verteidigung' );
        
        $this->changed = true;
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function recalcHighscores( $recalc_gebaeude = false, $recalc_forschung = false, $recalc_roboter = false, $recalc_schiffe = false, $recalc_verteidigung = false )
    {
        if ( ! $this->status )
            return false;
        
        $this->recalc_highscores[0] = ( $this->recalc_highscores[0] || $recalc_gebaeude );
        $this->recalc_highscores[1] = ( $this->recalc_highscores[1] || $recalc_forschung );
        $this->recalc_highscores[2] = ( $this->recalc_highscores[2] || $recalc_roboter );
        $this->recalc_highscores[3] = ( $this->recalc_highscores[3] || $recalc_schiffe );
        $this->recalc_highscores[4] = ( $this->recalc_highscores[4] || $recalc_verteidigung );
        
        $this->changed = true;
        return 2;
    }

    /**
     * TODO, add tests
     *
     */       
    function doRecalcHighscores( $recalc_gebaeude = false, $recalc_forschung = false, $recalc_roboter = false, $recalc_schiffe = false, $recalc_verteidigung = false )
    {
        if ( $recalc_gebaeude || $recalc_forschung || $recalc_roboter || $recalc_schiffe || $recalc_verteidigung ) {
            if ( $recalc_gebaeude )
                $this->raw['punkte'][0] = 0;
            if ( $recalc_forschung )
                $this->raw['punkte'][1] = 0;
            if ( $recalc_roboter )
                $this->raw['punkte'][2] = 0;
            if ( $recalc_schiffe )
                $this->raw['punkte'][3] = 0;
            if ( $recalc_verteidigung )
                $this->raw['punkte'][4] = 0;
            
            $planets = $this->getPlanetsList();
            $active_planet = $this->getActivePlanet();
            foreach ( $planets as $planet ) {
                $this->setActivePlanet( $planet );
                
                if ( $recalc_gebaeude ) {
                    $items = $this->getItemsList( 'gebaeude' );
                    foreach ( $items as $item ) {
                        $item_info = $this->getItemInfo( $item, 'gebaeude', true, true );
                        $this->raw['punkte'][0] += $item_info['scores'];
                        //print "doRecalcHighscores() adding score ".$item_info['scores']." to id: 0 (buildings) to user: ".$this->getName()."\n";
                    }
                }
                
                if ( $recalc_roboter ) {
                    $items = $this->getItemsList( 'roboter' );
                    foreach ( $items as $item ) {
                        $item_info = $this->getItemInfo( $item, 'roboter', true, true );
                        $this->raw['punkte'][2] += $item_info['scores'];
                    }
                }
                
                if ( $recalc_schiffe ) {
                    $items = $this->getItemsList( 'schiffe' );
                    foreach ( $items as $item ) {
                        $item_info = $this->getItemInfo( $item, 'schiffe', true, true );
                        $this->raw['punkte'][3] += $item_info['scores'];
                    }
                }
                
                if ( $recalc_verteidigung ) {
                    $items = $this->getItemsList( 'verteidigung' );
                    foreach ( $items as $item ) {
                        $item_info = $this->getItemInfo( $item, 'verteidigung', true, true );
                        $this->raw['punkte'][4] += $item_info['scores'];
                    }
                }
            }
            $this->setActivePlanet( $active_planet );
            
            if ( $recalc_forschung ) {
                $items = $this->getItemsList( 'forschung' );
                foreach ( $items as $item ) {
                    $item_info = $this->getItemInfo( $item, 'forschung', true, true );
                    $this->raw['punkte'][1] += $item_info['scores'];
                }
            }
            
            if ( $recalc_schiffe || $recalc_roboter ) {
                foreach ( $this->getFleetsList() as $flotte ) {
                    $fl = Classes::Fleet( $flotte, false );
                    if ( ! $fl->getStatus() )
                        continue;
                    if ( $fl->userExists( $this->getName() ) ) {
                        if ( $recalc_schiffe ) {
                            $schiffe = $fl->getFleetList( $this->getName() );
                            if ( $schiffe ) {
                                foreach ( $schiffe as $id => $count ) {
                                    $item_info = $this->getItemInfo( $id, 'schiffe', true, true );
                                    $this->raw['punkte'][3] += $count * $item_info['simple_scores'];
                                }
                            }
                        }
                        if ( $recalc_roboter ) {
                            $transport = $fl->getTransport( $this->getName() );
                            if ( $transport ) {
                                foreach ( $transport[1] as $id => $count ) {
                                    $item_info = $this->getItemInfo( $id, 'roboter', true, true );
                                    $this->raw['punkte'][2] += $count * $item_info['simple_scores'];
                                }
                            }
                        }
                    }
                    
                    if ( $recalc_roboter ) {
                        # Handel miteinbeziehen
                        $users = $fl->getUsersList();
                        foreach ( $users as $user ) {
                            $handel = $fl->getHandel( $user );
                            if ( $handel ) {
                                foreach ( $handel[1] as $id => $count ) {
                                    $item_info = $this->getItemInfo( $id, 'roboter', true, true );
                                    $this->raw['punkte'][2] += $count * $item_info['simple_scores'];
                                }
                            }
                        }
                    }
                }
            }
            
            if ( isset( $this->cache['getScores'] ) )
                unset( $this->cache['getScores'] );
        }
        
        $new_scores = $this->getScores();
        $highscores = Classes::Highscores(); #
        //print "doRecalcHighscores() updating: ".$this->getName()." with new_scores: ".$new_scores."\n";
        $highscores->updateUser( $this->getName(), false, $new_scores );
        
        $my_alliance = $this->allianceTag();
        if ( $my_alliance ) {
            $alliance = Classes::Alliance( $my_alliance );
            $alliance->setUserScores( $this->getName(), $new_scores );
        }
        
        $this->changed = true;
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function maySeeKoords( $user )
    {
        if ( ! $this->status )
            return false;
        
        if ( $user == $this->getName() )
            return true;
        if ( $this->isVerbuendet( $user ) )
            return true;
        
        if ( $this->allianceTag() ) {
            $alliance = Classes::Alliance( $this->allianceTag() );
            if ( ! $alliance->getStatus() )
                return false;
            if ( ! $alliance->checkUserPermissions( $this->getName(), 1 ) )
                return false;
            if ( ! in_array( $user, $alliance->getUsersList() ) )
                return false;
            return true;
        }
    }

    /**
     * TODO, add tests
     *
     */       
    function rename( $new_name )
    {
        # Ueberpruefen
        $really_rename = ( strtolower( $new_name ) != strtolower( $this->name ) );
        
        if ( $really_rename ) {
            $new_fname = $this->save_dir . '/' . urlencode( strtolower( $new_name ) );
            if ( file_exists( $new_fname ) )
                return false;
        }
        
        # Planeteneigentuemer aendern
        $active_planet = $this->getActivePlanet();
        foreach ( $this->getPlanetsList() as $planet ) {
            $this->setActivePlanet( $planet );
            $pos = $this->getPos();
            $galaxy_obj = Classes::Galaxy( $pos[0] );
            $galaxy_obj->setPlanetOwner( $pos[1], $pos[2], $new_name );
        }
        $this->setActivePlanet( $active_planet );
        
        # Nachrichtenabsender aendern
        Classes::resetInstances( 'Message' );
        $dh = opendir( global_setting( "DB_MESSAGES" ) );
        while ( ( $fname = readdir( $dh ) ) !== false ) {
            if ( $fname == '.' || $fname == '..' )
                continue;
            
            $message = new Message( urldecode( $fname ) );
            $message->renameUser( $this->name, $new_name );
            unset( $message );
        }
        closedir( $dh );
        
        # Bei Buendnispartnern abaendern
        Classes::resetInstances( 'Users' );
        foreach ( array_merge( $this->getVerbuendetList(), $this->getVerbuendetRequestList(), $this->getVerbuendetApplicationList() ) as $username ) {
            $user = new User( $username );
            $user->renameVerbuendet( $this->name, $new_name );
            unset( $user );
        }
        
        # In Flottenbewegungen umbenennen
        Classes::resetInstances( 'Fleet' );
        foreach ( $this->getFleetsList() as $fleet ) {
            $fleet = new Fleet( $fleet );
            $fleet->renameUser( $this->name, $new_name );
        }
        
        # In der Allianz umbenennen
        if ( $this->allianceTag() ) {
            $alliance = Classes::Alliance( $this->allianceTag() );
            $alliance->renameUser( $this->name, $new_name );
        }
        
        # Highscores-Eintrag neu schreiben
        $highscores = Classes::Highscores();
        $highscores->renameUser( $this->name, $new_name );
        
        # IM-Benachrichtigungen aendern
        $imfile = Classes::IMFile();
        $imfile->renameUser( $this->name, $new_name );
        
        $this->raw['username'] = $new_name;
        $this->changed = true;
        
        if ( $really_rename ) {
            # Datei umbenennen
            $this->__destruct();
            rename( $this->filename, $new_fname );
            $this->__construct( $new_name );
            $this->setActivePlanet( $active_planet );
        }
        else
            $this->name = $new_name;
        
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function lastMailSent( $time = false )
    {
        if ( ! $this->status )
            return false;
        
        if ( $time !== false ) {
            $this->raw['last_mail'] = $time;
            $this->changed = true;
            return true;
        }
        
        if ( ! isset( $this->raw['last_mail'] ) )
            return false;
        return $this->raw['last_mail'];
    }

    /**
     * TODO, add tests
     *
     */       
    function addForeignFleet( $fleet )
    {
        if ( $this->status != 1 )
            return false;
        
        if ( ! isset( $this->raw['halteflotten'] ) )
            $this->raw['halteflotten'] = array();
        elseif ( in_array( $fleet, $this->raw['halteflotten'] ) )
            return 2;
        $this->raw['halteflotten'][] = $fleet;
        natcasesort( $this->raw['halteflotten'] );
        $this->changed = true;
        return true;
    
    }

    /**
     * TODO, add tests
     *
     */       
    function unsetForeignFleet( $fleet )
    {
        if ( $this->status != 1 )
            return false;
        
        if ( ! isset( $this->raw['halteflotten'] ) )
            return true;
        $key = array_search( $fleet, $this->raw['halteflotten'] );
        if ( $key === false )
            return true;
        unset( $this->raw['halteflotten'][$key] );
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function getForeignFleetsArray( )
    {
        if ( ! $this->status )
            return false;
        
        if ( isset( $this->raw['halteflotten'] ) ) {
            foreach ( $this->raw['halteflotten'] as $i => $flotte ) {
                __autoload( 'Fleet' );
                if ( ! Fleet::fleetExists( $flotte ) ) {
                    unset( $this->raw['halteflotten'][$i] );
                    $this->changed = true;
                }
            }
            return $this->raw['halteflotten'];
        }
        else
            return array();
    }

    /**
     * TODO, add tests
     *
     */       
    function getForeignFleetsWithPlanet( )
    {
        if ( ! $this->status || ! isset( $this->planet_info ) )
            return false;
        $activep = $this->getActivePlanet();
        $fleets = array();
        foreach ( $this->getForeignFleetsArray() as $flotte ) {
            $fl = Classes::Fleet( $flotte );
            #if($activep = $fl->raw[0][$pos])
            $fleets[] = $flotte;
        }
        return $fleets;
    }

    /**
     * TODO, add tests
     *
     */       
    function getForeignFleet( $flotte )
    {
        if ( ! $this->status )
            return false;
        
        return array_keys( $this->raw[1] );
    }

    /**
     * TODO, add tests
     *
     */       
    function subForeignFleet( $id, $count )
    {
        if ( $this->status != 1 )
            return false;
        
        if ( ! isset( $this->raw['halteflotten'] ) )
            return true;
        $key = array_search( $id, $this->raw['halteflotten'] );
        if ( $key === false )
            return true;
        ( $this->raw['halteflotten'][$key] );
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function getForeignUser( $fleet )
    
    {
        if ( ! $this->status )
            return false;
        
        return ( $this->raw[1] );
    }

    /**
     * TODO, add tests
     *
     */       
    function resolveFleetPasswd( $passwd )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw["flotten_passwds"] ) || ! isset( $this->raw["flotten_passwds"][$passwd] ) )
            return null;
        $fleet_id = $this->raw["flotten_passwds"][$passwd];
        
        # Ueberpruefen, ob die Flotte noch die Kriterien erfuellt, ansonsten aus der Liste loeschen
        $fleet = Classes::Fleet( $fleet_id );
        if ( $fleet->getCurrentType() != 3 || $fleet->isFlyingBack() || array_search( $this->getName(), $fleet->getUsersList() ) !== 0 ) {
            unset( $this->raw["flotten_passwds"][$passwd] );
            $this->changed = true;
            return null;
        }
        
        return $fleet_id;
    }

    /**
     * TODO, add tests
     *
     */       
    function getFleetPasswd( $fleet_id )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw["flotten_passwds"] ) || ( $idx = array_search( $fleet_id, $this->raw["flotten_passwds"] ) ) === false )
            return null;
        return $idx;
    }

    /**
     * TODO, add tests
     *
     */       
    function changeFleetPasswd( $fleet_id, $passwd )
    {
        if ( ! $this->status )
            return false;
        if ( ! isset( $this->raw["flotten_passwds"] ) )
            $this->raw["flotten_passwds"] = array();
        
        $old_passwd = $this->getFleetPasswd( $fleet_id );
        if ( ( $old_passwd === null || $old_passwd != $passwd ) && $this->resolveFleetPasswd( $passwd ) !== null )
            return false;
        
        if ( $old_passwd !== null )
            unset( $this->raw["flotten_passwds"][$old_passwd] );
        
        if ( $passwd )
            $this->raw["flotten_passwds"][$passwd] = $fleet_id;
        
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function _printRaw( )
    {
        echo "<pre>";
        print_r( $this->raw );
        echo "</pre>";
    }

    /**
     * TODO, add tests
     *
     */       
    function resolveName( $name )
    {
        $instance = Classes::User( $name );
        return $instance->getName();
    }

    /**
     * TODO, add tests
     *
     */       
    function getNotificationType( )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['im_notification'] ) )
            return false;
        return $this->raw['im_notification'];
    }

    /**
     * TODO, add tests
     *
     */       
    function checkNewNotificationType( $uin, $protocol )
    {
        if ( ! $this->status )
            return false;
        
        $this->raw['im_notification_check'] = array( $uin, $protocol, time() );
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function doSetNotificationType( $uin, $protocol )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['im_notification_check'] ) )
            return false;
        if ( $this->raw['im_notification_check'][0] != $uin || $this->raw['im_notification_check'][1] != $protocol )
            return false;
        if ( time() - $this->raw['im_notification_check'][2] > 86400 )
            return false;
        
        $this->raw['im_notification'] = array( $this->raw['im_notification_check'][0], $this->raw['im_notification_check'][1] );
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function disableNotification( )
    {
        if ( ! $this->status )
            return false;
        
        $this->raw['im_notification_check'] = false;
        $this->raw['im_notification'] = false;
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function addPosShortcut( $pos )
    { # Fuegt ein Koordinatenlesezeichen hinzu
        if ( ! $this->status )
            return false;
        
        if ( ! is_array( $this->raw['pos_shortcuts'] ) )
            $this->raw['pos_shortcuts'] = array();
        if ( in_array( $pos, $this->raw['pos_shortcuts'] ) )
            return 2;
        
        $this->raw['pos_shortcuts'][] = $pos;
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function getPosShortcutsList( )
    { # Gibt die Liste der Koordinatenlesezeichen zurueck
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['pos_shortcuts'] ) )
            return array();
        return $this->raw['pos_shortcuts'];
    }

    /**
     * TODO, add tests
     *
     */       
    function removePosShortcut( $pos )
    { # Entfernt ein Koordinatenlesezeichen wieder
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['pos_shortcuts'] ) )
            return 2;
        $idx = array_search( $pos, $this->raw['pos_shortcuts'] );
        if ( $idx === false )
            return 2;
        unset( $this->raw['pos_shortcuts'][$idx] );
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function movePosShortcutUp( $pos )
    { # Veraendert die Reihenfolge der Lesezeichen
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['pos_shortcuts'] ) )
            return false;
        
        $idx = array_search( $pos, $this->raw['pos_shortcuts'] );
        if ( $idx === false )
            return false;
        
        $keys = array_keys( $this->raw['pos_shortcuts'] );
        $keys_idx = array_search( $idx, $keys );
        
        if ( ! isset( $keys[$keys_idx - 1] ) )
            return false;
        
        list( $this->raw['pos_shortcuts'][$idx], $this->raw['pos_shortcuts'][$keys[$keys_idx - 1]] ) = array( $this->raw['pos_shortcuts'][$keys[$keys_idx - 1]], $this->raw['pos_shortcuts'][$idx] ); # Confusing, ain't it? ;-)
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function movePosShortcutDown( $pos )
    { # Veraendert die Reihenfolge der Lesezeichen
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['pos_shortcuts'] ) )
            return false;
        
        $idx = array_search( $pos, $this->raw['pos_shortcuts'] );
        if ( $idx === false )
            return false;
        
        $keys = array_keys( $this->raw['pos_shortcuts'] );
        $keys_idx = array_search( $idx, $keys );
        
        if ( ! isset( $keys[$keys_idx + 1] ) )
            return false;
        
        list( $this->raw['pos_shortcuts'][$idx], $this->raw['pos_shortcuts'][$keys[$keys_idx + 1]] ) = array( $this->raw['pos_shortcuts'][$keys[$keys_idx + 1]], $this->raw['pos_shortcuts'][$idx] ); # The same another time...
        $this->changed = true;
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function getPasswordSendID( )
    { # Liefert eine ID zurueck, die zum Senden des Passworts benutzt werden kann
        if ( $this->status != 1 )
            return false;
        
        $send_id = md5( microtime() );
        $this->raw['email_passwd'] = $send_id;
        $this->changed = true;
        return $send_id;
    }

    /**
     * TODO, add tests
     *
     */       
    function checkPasswordSendID( $id )
    { # Ueberprueft, ob eine vom Benutzer eingegebene ID der letzten durch getPasswordSendID zurueckgelieferten ID entspricht
        if ( ! $this->status )
            return false;
        
        return ( isset( $this->raw['email_passwd'] ) && $this->raw['email_passwd'] && $this->raw['email_passwd'] == $id );
    }

    /**
     * TODO, add tests
     *
     */       
    function refreshMessengerBuildingNotifications( $type = false )
    {
        if ( ! $this->status || ! $this->planet_info )
            return false;
        
        if ( $type == false ) {
            return ( $this->refreshMessengerBuildingNotifications( 'gebaeude' ) && $this->refreshMessengerBuildingNotifications( 'forschung' ) && $this->refreshMessengerBuildingNotifications( 'roboter' ) && $this->refreshMessengerBuildingNotifications( 'schiffe' ) && $this->refreshMessengerBuildingNotifications( 'verteidigung' ) );
        }
        
        if ( ! in_array( $type, array( 'gebaeude', 'forschung', 'roboter', 'schiffe', 'verteidigung' ) ) )
            return false;
        
        $special_id = $this->getActivePlanet() . '-' . $type;
        $imfile = Classes::IMFile();
        $imfile->removeMessages( $this->getName(), $special_id );
        
        $messenger_receive = $this->checkSetting( 'messenger_receive' );
        if ( ! $messenger_receive['building'][$type] )
            return 2;
        $building = $this->checkBuildingThing( $type );
        if ( ! $building )
            return 2;
        $messenger_settings = $this->getNotificationType();
        if ( ! $messenger_settings )
            return 2;
        
        switch ( $type ) {
            case 'gebaeude':
            case 'forschung':
                if ( ! $building || ( $type == 'forschung' && $building[2] && $this->getActivePlanet() != $building[4] ) )
                    break;
                
                $item_info = $this->getItemInfo( $building[0], $type );
                
                if ( $type == 'gebaeude' )
                    $message = "Gebäudebau abgeschlossen: " . $item_info['name'] . " (" . ( $item_info['level'] + ( $building[2] ? - 1 : 1 ) ) . ")";
                else
                    $message = "Forschung fertiggestellt: " . $item_info['name'] . " (" . ( $item_info['level'] + 1 ) . ")";
                $imfile->addMessage( $messenger_settings[0], $messenger_settings[1], $this->getName(), $message, $special_id, $building[1] );
                break;
            case 'roboter':
            case 'schiffe':
            case 'verteidigung':
                switch ( $type ) {
                    case 'roboter':
                        $singular = 'Roboter';
                        $plural = 'Roboter';
                        $art = 'ein';
                        break;
                    case 'schiffe':
                        $singular = 'Schiff';
                        $plural = 'Schiffe';
                        $art = 'ein';
                        break;
                    case 'verteidigung':
                        $singular = 'Verteidigungsanlage';
                        $plural = 'Verteidigungsanlagen';
                        $art = 'eine';
                        break;
                }
                
                switch ( $messenger_receive['building'][$type] ) {
                    case 1:
                        foreach ( $building as $b ) {
                            $item_info = $this->getItemInfo( $b[0], $type );
                            $time = $b[1];
                            for ( $i = 0; $i < $b[2]; $i ++ ) {
                                $time += $b[3];
                                $imfile->addMessage( $messenger_settings[0], $messenger_settings[1], $this->getName(), ucfirst( $art ) . " " . $singular . " der Sorte " . $item_info['name'] . " wurde fertiggestellt.", $special_id, $time );
                            }
                        }
                        break;
                    case 2:
                        foreach ( $building as $b ) {
                            $item_info = $this->getItemInfo( $b[0], $type );
                            $imfile->addMessage( $messenger_settings[0], $messenger_settings[1], $this->getName(), $b[2] . " " . ( $b[2] == 1 ? $singular : $plural ) . " der Sorte " . $item_info['name'] . " " . ( $b[2] == 1 ? 'wurde' : 'wurden' ) . " fertiggestellt.", $special_id, $b[1] + $b[2] * $b[3] );
                        }
                        break;
                    case 3:
                        $keys = array_keys( $building );
                        $b = $building[array_pop( $keys )];
                        $imfile->addMessage( $messenger_settings[0], $messenger_settings[1], $this->getName(), "Alle " . $plural . " wurden fertiggestellt.", $special_id, $b[1] + $b[2] * $b[3] );
                        break;
                }
                break;
        }
        return true;
    }

    /**
     * TODO, add tests
     *
     */       
    function clearCache( )
    {
        if ( isset( $user->cache ) )
            unset( $user->cache );
        
        $this->changed = true;
    }

}

    /**
     * TODO, add tests
     *
     */   
function getUsersCount( )
{
    $highscores = Classes::Highscores();
    return $highscores->getCount( 'users' );
}

    /**
     * TODO, add tests
     *
     */   
function sortEventhandlerActions( $a, $b )
{
    if ( $a[0] < $b[0] )
        return - 1;
    elseif ( $a[0] > $b[0] )
        return 1;
    else
        return 0;
}

?>
