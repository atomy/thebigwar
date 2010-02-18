<?php

class user_control
{

    public static function removeUser( $strUsername )
    {
        if ( ! isset( $strUsername ) || $strUsername == NULL ) {
            return false;
        }
        
        $userObj = Classes::User( $strUsername );
        
        if ( ! $userObj || ! $userObj->getStatus() ) {
            return false;
        }
        
        # Planeten zuruecksetzen
        $planets = $userObj->getPlanetsList();
        
        foreach ( $planets as $planet ) {
            $userObj->setActivePlanet( $planet );
            
            if ( ! $userObj->removePlanet() )
                return false;
        }
        
        # Buendnispartner entfernen
        $verb_list = $userObj->getVerbuendetList();
        
        foreach ( $verb_list as $verb )
            $userObj->quitVerbuendet( $verb );
        
        $verb_list = $userObj->getVerbuendetApplicationList();
        
        foreach ( $verb_list as $verb )
            $userObj->cancelVerbuendetApplication( $verb );
        
        $verb_list = $userObj->getVerbuendetRequestList();
        
        foreach ( $verb_list as $verb )
            $userObj->rejectVerbuendetApplication( $verb );
            
        # Nachrichten entfernen
        $categories = $userObj->getMessageCategoriesList();
        
        foreach ( $categories as $category ) {
            $messages = $userObj->getMessagesList( $category );
            
            foreach ( $messages as $message )
                $userObj->removeMessage( $message, $category );
        }
        
        # Aus der Allianz austreten
        $userObj->allianceTag( false );
        
        # Aus den Highscores entfernen
        $highscores = Classes::Highscores();
        $highscores->removeEntry( 'users', $userObj->getName() );
        
        # Flotten zurueckrufen
        $fleets = $userObj->getFleetsList();
        
        foreach ( $fleets as $fleet ) {
            $fleet_obj = Classes::Fleet( $fleet );
            
            foreach ( array_reverse( $fleet_obj->getUsersList() ) as $username ) {
                $fleet_obj->callBack( $username );
            }
        }
        
        # IM-Benachrichtigungen entfernen
        $imfile = Classes::IMFile();
        $imfile->removeMessages( $userObj->getName() );
        
        $fname = $userObj->getFilename();
        
        # destruct obj before removing, otherwise there will be a lock on it and php wont delete it
        $userObj->__destruct();
        
        $status = ( unlink( $fname ) );
        
        if ( $status ) {
            # it doesnt matter, since its dead
            #$userObj->setStatus( 0 );
            #$userObj->changed = false;
            

            return true;
        }
        else {
            return false;
        }
    }
}
?>
