<?php

require_once ( 'dataset.php' );

class Alliance extends Dataset
{

    protected $datatype = 'alliance';

    function __construct( $name = false, $write = true )
    {
        $this->save_dir = global_setting( "DB_ALLIANCES" );
        parent::__construct( $name, $write );
    }

    function create( )
    {
        if ( file_exists( $this->filename ) )
            return false;
        $this->raw = array( 'tag' => $this->name, 'members' => array(), 'name' => '', 'description' => '', 'description_parsed' => '', 'inner_description' => '', 'inner_description_parsed' => '' );
        
        $highscores = Classes::Highscores();
        $highscores->updateAlliance( $this->name, 0, 0, 0 );
        
        $this->write( true, false );
        $this->__construct( $this->name, ! $this->readonly );
        return true;
    }

    function destroy( $by_whom = false )
    {
        if ( $this->status != 1 )
            return false;
        
        if ( $this->getMembersCount() > 0 ) {
            $members = $this->getUsersList();
            $message = Classes::Message();
            if ( $message->create() ) {
                $message->subject( "Allianz aufgel\xc3\xb6st" );
                $message->text( 'Die Allianz ' . $this->getName() . " wurde aufgel\xc3\xb6st." );
                if ( $by_whom )
                    $message->from( $by_whom );
                foreach ( $members as $member ) {
                    if ( $member == $by_whom )
                        continue;
                    $message->addUser( $member, 7 );
                }
            }
            
            $applicants = $this->getApplicationsList();
            if ( count( $applicants ) > 0 ) {
                $message = Classes::Message();
                if ( $message->create() ) {
                    $message->subject( "Allianz aufgel\xc3\xb6st" );
                    $message->text( "Die Allianz " . $this->getName() . " wurde aufgel�st. Ihre Bewerbung wurde deshalb zur�ckgewiesen." );
                    foreach ( $applicants as $applicant )
                        $message->addUser( $applicant, 7 );
                }
            }
            
            foreach ( $applicants as $applicant ) {
                $user = Classes::User( $applicant );
                $user_obj->cancelAllianceApplication( false );
            }
            
            $i = count( $members );
            foreach ( $members as $member ) {
                $this_user = Classes::User( $member );
                if ( $i > 1 )
                    $this_user->allianceTag( false );
                else
                    return $this_user->allianceTag( false );
                $i --;
            }
        }
        
        # Aus den Allianz-Highscores entfernen
        $highscores = Classes::Highscores();
        $highscores->removeEntry( 'alliances', $this->getName() );
        
        $status = ( unlink( $this->filename ) );
        if ( $status ) {
            $this->status = 0;
            $this->changed = false;
            return true;
        }
        else
            return false;
    }

    function allianceExists( $alliance )
    {
        $filename = global_setting( "DB_ALLIANCES" ) . '/' . strtolower( urlencode( $alliance ) );
        return ( is_file( $filename ) && is_readable( $filename ) );
    }

    function getAverageScores( )
    {
        if ( ! $this->status )
            return false;
        
        return floor( $this->getTotalScores() / $this->getMembersCount() );
    }

    function getMembersCount( )
    {
        if ( ! $this->status )
            return false;
        
        return count( $this->raw['members'] );
    }

    function getTotalScores( )
    {
        if ( ! $this->status )
            return false;
        
        $overall = 0;
        foreach ( $this->raw['members'] as $member )
            $overall += $member['punkte'];
        return $overall;
    }

    function recalcHighscores( )
    {
        if ( $this->status != 1 )
            return false;
        
        $overall = 0;
        foreach ( $this->raw['members'] as $member )
            $overall += $member['punkte'];
        $members = count( $this->raw['members'] );
        $average = floor( $overall / $members );
        $highscores = Classes::Highscores();
        $highscores->updateAlliance( $this->getName(), $average, $overall, $members );
        
        return true;
    }

    function getRankAverage( )
    {
        if ( ! $this->status )
            return false;
        
        $highscores = Classes::Highscores();
        return $highscores->getPosition( 'alliances', $this->getName(), 'scores_average' );
    }

    function getRankTotal( )
    {
        if ( ! $this->status )
            return false;
        
        $highscores = Classes::Highscores();
        return $highscores->getPosition( 'alliances', $this->getName(), 'scores_total' );
    }

    function setUserPermissions( $user, $key, $permission )
    {
        if ( $this->status != 1 )
            return false;
        
        if ( ! isset( $this->raw['members'][$user] ) )
            return false;
        $this->raw['members'][$user]['permissions'][$key] = (bool) $permission;
        $this->changed = true;
        return true;
    }

    function allowApplications( $allow = -1 )
    {
        if ( ! $this->status )
            return false;
        
        if ( $allow === - 1 )
            return ( ! isset( $this->raw['allow_applications'] ) || $this->raw['allow_applications'] );
        
        $this->raw['allow_applications'] = ( $allow == true );
        $this->changed = true;
        return true;
    }

    function checkUserPermissions( $user, $key )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['members'][$user] ) )
            return false;
        if ( ! isset( $this->raw['members'][$user]['permissions'][$key] ) )
            return false;
        return $this->raw['members'][$user]['permissions'][$key];
    }

    function setUserScores( $user, $scores )
    {
        if ( $this->status != 1 )
            return false;
        
        if ( ! isset( $this->raw['members'][$user] ) )
            return false;
        $this->raw['members'][$user]['punkte'] = $scores;
        $this->changed = true;
        
        $this->recalcHighscores();
        
        return true;
    }

    function getUserScores( $user )
    {
        if ( ! $this->status )
            return false;
        if ( ! isset( $this->raw['members'][$user] ) )
            return false;
        
        return $this->raw['members'][$user]['punkte'];
    }

    function getUserJoiningTime( $user )
    {
        if ( ! $this->status )
            return false;
        if ( ! isset( $this->raw['members'][$user] ) )
            return false;
        
        return $this->raw['members'][$user]['time'];
    }

    function getUsersList( $sortby = false, $invert = false )
    {
        if ( ! $this->status )
            return false;
        
        if ( $sortby )
            $sortby = '' . $sortby;
        
        if ( $sortby && ( 'punkte' == $sortby || 'rang' == $sortby || 'time' == $sortby ) ) {
            global $sortAllianceMembersBy;
            global $sortAllianceMembersInvert;
            $sortAllianceMembersBy = $sortby;
            $sortAllianceMembersInvert = $invert;
            
            $members_raw = $this->raw['members'];
            uasort( $members_raw, 'sortAllianceMembersList' );
            $members = array_keys( $members_raw );
        }
        else {
            $members = array_keys( $this->raw['members'] );
            if ( $sortby ) {
                natcasesort( $members );
                if ( $invert )
                    $members = array_reverse( $members );
            }
        }
        
        return $members;
    }

    function getUsersWithPermission( $permission )
    {
        if ( ! $this->status )
            return false;
        
        $users = array();
        
        foreach ( $this->raw['members'] as $name => $member ) {
            if ( isset( $member['permissions'][$permission] ) && $member['permissions'][$permission] )
                $users[] = $name;
        }
        return $users;
    }

    function setUserStatus( $user, $status )
    {
        if ( $this->status != 1 )
            return false;
        
        if ( ! isset( $this->raw['members'][$user] ) )
            return false;
        $this->raw['members'][$user]['rang'] = $status;
        $this->changed = true;
        return true;
    }

    function getUserStatus( $user )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['members'][$user] ) )
            return false;
        return $this->raw['members'][$user]['rang'];
    }

    function addUser( $user, $punkte = 0 )
    {
        if ( $this->status != 1 )
            return false;
        
        if ( isset( $this->raw['members'][$user] ) )
            return false;
        
        $this->raw['members'][$user] = array( 'punkte' => $punkte, 'rang' => 'Neuling', 'time' => time(), 'permissions' => array( false, false, false, false, false, false, false, false, false ) );
        $this->changed = true;
        
        $this->recalcHighscores();
        return true;
    }

    function removeUser( $user )
    {
        if ( $this->status != 1 )
            return false;
        
        if ( ! isset( $this->raw['members'][$user] ) )
            return true;
        
        unset( $this->raw['members'][$user] );
        $this->changed = true;
        
        if ( count( $this->raw['members'] ) <= 0 ) {
            $this->destroy();
            return true;
        }
        
        $this->recalcHighscores();
        return true;
    }

    function newApplication( $user )
    {
        if ( $this->status != 1 )
            return false;
        if ( ! $this->allowApplications() )
            return false;
        
        if ( ! isset( $this->raw['bewerbungen'] ) )
            $this->raw['bewerbungen'] = array();
        if ( in_array( $user, $this->raw['bewerbungen'] ) )
            return false;
        
        $this->raw['bewerbungen'][] = $user;
        $this->changed = true;
        
        return true;
    }

    function deleteApplication( $user )
    {
        #if($this->status != 1) return false;
        if ( ! isset( $this->raw['bewerbungen'] ) )
            return true;
        
        $key = array_search( $user, $this->raw['bewerbungen'] );
        if ( $key === false )
            return true;
        
        unset( $this->raw['bewerbungen'][$key] );
        
        $this->changed = true;
        return true;
    }

    function getApplicationsList( )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['bewerbungen'] ) )
            return array();
        return $this->raw['bewerbungen'];
    }

    function name( $name = false )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! trim( $name ) ) {
            if ( ! isset( $this->raw['name'] ) )
                return '';
            return $this->raw['name'];
        }
        else {
            if ( $this->status != 1 )
                return false;
            $this->raw['name'] = $name;
            $this->changed = true;
            return true;
        }
    }

    function kickUser( $user, $by_whom = false )
    {
        if ( $this->status != 1 )
            return false;
        if ( ! isset( $this->raw['members'][$user] ) )
            return false;
        
        $user_obj = Classes::User( $user );
        if ( ! $user_obj->allianceTag( false ) )
            return false;
        
        $this->removeUser( $user );
        
        $message = Classes::Message();
        if ( $message->create() ) {
            $message->subject( "Allianzmitgliedschaft gek\xc3\xbcndigt" );
            $message->text( "Sie wurden aus der Allianz " . $this->getName() . " geworfen." );
            $message->addUser( $user, 7 );
        }
        
        $message = Classes::Message();
        if ( $message->create() ) {
            $message->subject( "Spieler aus Allianz geworfen" );
            $message->text( "Der Spieler " . $user . " wurde aus Ihrer Allianz geworfen." );
            if ( $by_whom )
                $message->from( $by_whom );
            
            $members = $this->getUsersWithPermission( 5 );
            foreach ( $members as $member ) {
                if ( $member == $by_whom )
                    continue;
                $message->addUser( $member );
            }
        }
        return true;
    }

    function getExternalDescription( $parsed = true )
    {
        if ( ! $this->status )
            return false;
        
        if ( $parsed ) {
            if ( ! isset( $this->raw['description_parsed'] ) ) {
                $this->raw['description_parsed'] = parse_html( $this->getExternalDescription( false ) );
                $this->changed = true;
            }
            return $this->raw['description_parsed'];
        }
        else {
            if ( ! isset( $this->raw['description'] ) )
                return '';
            return $this->raw['description'];
        }
    }

    function setExternalDescription( $description )
    {
        if ( $this->status != 1 )
            return false;
        
        $this->raw['description'] = $description;
        $this->raw['description_parsed'] = parse_html( $description );
        $this->changed = true;
        return true;
    }

    function setInternalDescription( $description )
    {
        if ( $this->status != 1 )
            return false;
        
        $this->raw['inner_description'] = $description;
        $this->raw['inner_description_parsed'] = parse_html( $description );
        $this->changed = true;
        return true;
    }

    function getInternalDescription( $parsed = true )
    {
        if ( ! $this->status )
            return false;
        
        if ( $parsed ) {
            if ( ! isset( $this->raw['inner_description_parsed'] ) ) {
                $this->raw['inner_description_parsed'] = parse_html( $this->getInternalDescription( false ) );
                $this->changed = true;
            }
            return $this->raw['inner_description_parsed'];
        }
        else {
            if ( ! isset( $this->raw['inner_description'] ) )
                return '';
            return $this->raw['inner_description'];
        }
    }

    function acceptApplication( $user, $by_whom = false )
    {
        if ( $this->status != 1 )
            return false;
        
        $key = array_search( $user, $this->raw['bewerbungen'] );
        if ( $key === false )
            return false;
        
        $members = $this->getUsersList();
        
        $user_obj = Classes::User( $user );
        if ( ! $user_obj->allianceTag( $this->getName() ) )
            return false;
        unset( $this->raw['bewerbungen'][$key] );
        $this->changed = true;
        
        $message = Classes::Message();
        if ( $message->create() ) {
            $message->subject( 'Neues Allianzmitglied' );
            $message->text( 'Ein neues Mitglied wurde in Ihre Allianz aufgenommen: ' . $user );
            if ( $by_whom )
                $message->from( $by_whom );
            foreach ( $members as $member ) {
                if ( $member == $by_whom )
                    continue;
                $message->addUser( $member, 7 );
            }
        }
        
        $message = Classes::Message();
        if ( $message->create() ) {
            $message->subject( 'Allianzbewerbung angenommen' );
            $message->text( 'Ihre Bewerbung bei der Allianz ' . $this->getName() . ' wurde angenommen.' );
            if ( $by_whom )
                $message->from( $by_whom );
            $message->addUser( $user, 7 );
        }
        
        return true;
    }

    function rejectApplication( $user, $by_whom = false )
    {
        if ( $this->status != 1 )
            return false;
        
        if ( ! in_array( $user, $this->raw['bewerbungen'] ) )
            return false;
        
        $user_obj = Classes::User( $user );
        if ( ! $user_obj->cancelAllianceApplication( false ) )
            return false;
        
        $message = Classes::Message();
        if ( $message->create() ) {
            $message->subject( 'Allianzbewerbung abgelehnt' );
            $message->text( 'Die Bewerbung von ' . $user . ' an Ihre Allianz wurde abgelehnt.' );
            if ( $by_whom )
                $message->from( $by_whom );
            $members = $this->getUsersWithPermission( 4 );
            foreach ( $members as $member ) {
                if ( $member == $by_whom )
                    continue;
                $message->addUser( $member, 7 );
            }
        }
        
        $message = Classes::Message();
        if ( $message->create() ) {
            $message->subject( 'Allianzbewerbung abgelehnt' );
            $message->text( 'Ihre Bewerbung bei der Allianz ' . $this->getName() . ' wurde abgelehnt.' );
            $message->addUser( $user, 7 );
        }
        
        return true;
    }

    protected function getDataFromRaw( )
    {
        $this->name = $this->raw['tag'];
    }

    protected function getRawFromData( )
    {}

    function resolveName( $name )
    {
        $instance = Classes::Alliance( $name );
        return $instance->getName();
    }

    function renameUser( $old_name, $new_name )
    {
        if ( ! $this->status )
            return false;
        if ( $old_name == $new_name )
            return 2;
        if ( ! isset( $this->raw['members'][$old_name] ) )
            return true;
        
        $this->raw['members'][$new_name] = $this->raw['members'][$old_name];
        unset( $this->raw['members'][$old_name] );
        $this->changed = true;
        return true;
    }

    function renameAllowed( )
    {
        if ( ! $this->status )
            return false;
        
        if ( ! isset( $this->raw['last_rename'] ) )
            return true;
        return ( time() - $this->raw['last_rename'] >= global_setting( "ALLIANCE_RENAME_PERIOD" ) * 86400 );
    }

    function rename( $new_name )
    {
        if ( ! $this->status )
            return false;
        
        $new_name = trim( $new_name );
        
        $really_rename = ( strtolower( $new_name ) != strtolower( $this->getName() ) );
        
        if ( $really_rename ) {
            $new_fname = $this->save_dir . '/' . urlencode( strtolower( $new_name ) );
            if ( file_exists( $new_fname ) )
                return false;
        }
        
        # Alliancetag bei den Mitgliedern aendern
        foreach ( $this->raw['members'] as $username => $info ) {
            $user = Classes::User( $username );
            $user->allianceTag( $new_name, false );
        }
        
        # Highscores-Eintrag aendern
        $hs = Classes::Highscores();
        $hs->renameAlliance( $this->getName(), $new_name );
        
        $this->raw['tag'] = $new_name;
        if ( $really_rename )
            $this->raw['last_rename'] = time();
        $this->changed = true;
        
        if ( $really_rename ) {
            # Datei umbenennen
            $this->__destruct();
            rename( $this->filename, $new_fname );
            $this->__construct( $new_name, ! $this->readonly );
        }
        else
            $this->name = $new_name;
        
        return true;
    }
}

function getAlliancesCount( )
{
    $highscores = Classes::Highscores();
    return $highscores->getCount( 'alliances' );
}

function sortAllianceMembersList( $a, $b )
{
    global $sortAllianceMembersInvert;
    global $sortAllianceMembersBy;
    
    if ( isset( $sortAllianceMembersInvert ) && $sortAllianceMembersInvert )
        $invert = - 1;
    else
        $invert = 1;
    if ( isset( $sortAllianceMembersBy ) && ( $sortAllianceMembersBy == 'punkte' || $sortAllianceMembersBy == 'time' ) ) {
        if ( $a[$sortAllianceMembersBy] > $b[$sortAllianceMembersBy] )
            return $invert;
        elseif ( $a[$sortAllianceMembersBy] < $b[$sortAllianceMembersBy] )
            return - $invert;
        else
            return 0;
    }
    else {
        $cmp = strnatcasecmp( $a[$sortAllianceMembersBy], $b[$sortAllianceMembersBy] );
        if ( $cmp < 0 )
            return - $invert;
        elseif ( $cmp > 0 )
            return $invert;
        else
            return 0;
    }
}

function findAlliance( $search_string )
{
    $preg = '/^' . str_replace( array( '\\*', '\\?' ), array( '.*', '.?' ), preg_quote( $search_string, '/' ) ) . '$/i';
    $alliances = array();
    $dh = opendir( global_setting( "DB_ALLIANCES" ) );
    while ( ( $fname = readdir( $dh ) ) !== false ) {
        if ( ! is_file( global_setting( "DB_ALLIANCES" ) . '/' . $fname ) || ! is_readable( global_setting( "DB_ALLIANCES" ) . '/' . $fname ) )
            continue;
        $alliance = urldecode( $fname );
        if ( preg_match( $preg, $alliance ) )
            $alliances[] = $alliance;
    }
    closedir( $dh );
    natcasesort( $alliances );
    return $alliances;
}
?>
