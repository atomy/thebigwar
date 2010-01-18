#!/usr/bin/php
<?php
require ( '../include/config_inc.php' );

###########################
### Parameter auswerten ###
###########################


{
    // goto ROOT dir
    chdir( dirname( __FILE__ ) );
    chdir( '..' );
    
    $print_usage = false;
    $error = false;
    $daemon = false;
    $verbose = false;
    $gCheckMsg = true;
    $use_jabber = $wanna_use_jabber = false;
    $getopt_exists = false;
    
    foreach ( explode( ':', get_include_path() ) as $path )
    {
        if ( is_file( $path . '/Console/Getopt.php' ) && is_readable( $path . '/Console/Getopt.php' ) )
        {
            $getopt_exists = true;
            break;
        }
    }
    
    // get command-line options
    if ( $getopt_exists )
    {
        require_once ( 'Console/Getopt.php' ); # PEAR
        $options = Console_Getopt::getopt( $_SERVER['argv'], 'hdvJ', array( 'help', 'daemon', 'verbose', 'no-jabber', 'checkmsg' ) );
        
        if ( $options instanceof PEAR_Error )
        {
            fputs( STDERR, $options->message . "\n" );
            exit( 1 );
        }
        
        foreach ( $options[0] as $o )
        {
            switch ( $o[0] )
            {
                case 'h':
                case '--help':
                    $print_usage = true;
                    break;
                case 'd':
                case '--daemon':
                    $daemon = true;
                    break;
                case 'v':
                case '--verbose':
                    $verbose = true;
                    break;
                case 'J':
                case '--no-jabber':
                    $use_jabber = $wanna_use_jabber = false;
                    break;
                case 'm':
                case 'checkmsg':
                    $gCheckMsg = true;
            }
        }
    }
    else
        fputs( STDERR, "Warning: PEAR package Console_Getopt does not exist. Switching to default options.\n\n" );
    
    if ( $print_usage || $error )
    {
        if ( $error )
            $stream = STDERR;
        else
            $stream = STDOUT;
        
        fputs( $stream, '        
Usage: ' . $_SERVER['argv'][0] . ' [Options]
Options:
  -h, --help:    Display this help and exit
  -d, --daemon:  Run in background
  -v, --verbose: Verbose output
  -m, --checkmsg: Check for old Messages and delete them (expire)
			' );
        
        if ( $error )
            exit( 1 );
        else
            exit( 0 );
    }
    
    $USE_OB = false;
    require ( 'engine/include.php' );
    
    // endless execution
    set_time_limit( 0 );
    
    // pid files are cool
    if ( ! touch( global_setting( "DB_EVENTHANDLER_PIDFILE" ) ) || ! ( $fh_pid = fopen( global_setting( "DB_EVENTHANDLER_PIDFILE" ), 'r+' ) ) )
    {
        fputs( STDERR, "Error, couldn't create pid file " . global_setting( "DB_EVENTHANDLER_PIDFILE" ) . ".\n" );
        exit( 1 );
    }
    
    // locking is gay, but lets check if there's already something running
    if ( ! flock( $fh_pid, LOCK_EX + LOCK_NB ) )
    {
        fputs( STDERR, "Error, another instance seems already to be running. The PID seems to be " . trim( file_get_contents( global_setting( "DB_EVENTHANDLER_PIDFILE" ) ) ) . ".\n" );
        exit( 1 );
    }
    
    ftruncate( $fh_pid, 0 );
    
    $databases = get_databases();
    
    
    __autoload( 'Classes' );
    __autoload( 'Fleet' );
    __autoload( 'Galaxy' );
}

################
### Routinen ###
################


{

    function time_prefix( )
    {
        global $selected_database;
        
        $prefix = date( 'Y-m-d, H:i:s' ) . "\t";
        
        // we arent using multiple databases
        #if(isset($selected_database) && $selected_database)
        #	$prefix .= $selected_database."\t";
        

        return $prefix;
    }

    // are we going to terminate? exit some stuff
    function check_termination( )
    {
        global $errlog;
        global $use_jabber;
        global $jabber;
        global $fh_pid;
        global $daemon;
        
        if ( defined( 'terminate' ) && terminate )
        {
            if ( $use_jabber && $jabber->connected )
            {
                fputs( $errlog, time_prefix() . "Disconnecting Jabber client... " );
                $jabber->Disconnect();
                fputs( $errlog, "Done.\n" );
            }
            
            fputs( $errlog, time_prefix() . "Terminated.\n\n" );
            
            ftruncate( $fh_pid, 0 );
            flock( $fh_pid, LOCK_UN );
            fclose( $fh_pid );
            if ( $daemon )
                fclose( $errlog );
            
            exit( 0 );
        }
    }

}

########################
### Prozesskontrolle ###
########################


{
    // fork into background, papa dies and child becomes the new eventhandler
    if ( $daemon )
    {
		// wth do we need this?
        declare(ticks = 1);
        
        if ( function_exists( 'pcntl_fork' ) )
            $pid = pcntl_fork();
        else
            $pid = - 1;
        
        if ( $pid == - 1 )
            fputs( STDERR, time_prefix() . "Forking failed, continuing.\n" );
        else 
            if ( $pid )
            {
                fputs( STDOUT, time_prefix() . "Eventhandler forked, PID " . $pid . ".\n" );
                exit( 0 );
            }
    }
    
    fwrite( $fh_pid, getmypid() . "\n" );

    function error_handler( $errno, $errstr, $errfile, $errline, $errcontext )
    {
        global $errlog;

        fputs( $errlog, time_prefix() );

        switch ( $errno )
        {
            case E_WARNING:
                fputs( $errlog, "Warning: " );
                break;
            case E_NOTICE:
                fputs( $errlog, "Notice: " );
                break;
            default:
                fputs( $errlog, "Error " . $errno . ": " );
                break;
        }
        
        fputs( $errlog, $errstr );
        fputs( $errlog, " in " . $errfile . " on line " . $errline . "." );
        
        global $process;

        if ( isset( $process ) && isset( $process['fleet'] ) )
            fputs( $errlog, " Last fleet was " . $process['fleet'] . "." );
        
        fputs( $errlog, "\n" );
    }

    function sig_handler( $signo )
    {
        global $errlog;
        global $databases;

        switch ( $signo )
        {
            case SIGTERM:
                fputs( $errlog, time_prefix() . "SIGTERM (" . SIGTERM . ")\n" );
                if ( ! defined( 'terminate' ) )
                    define( 'terminate', true );
                break;

            case SIGINT:
                fputs( $errlog, time_prefix() . "SIGINT (" . SIGINT . ")\n" );
                if ( ! defined( 'terminate' ) )
                    define( 'terminate', true );
                break;

            case SIGHUP:
                fputs( $errlog, time_prefix() . "SIGHUP (" . SIGHUP . ")\n" );
                break;

            case SIGUSR1:
                fputs( $errlog, time_prefix() . "SIGUSR1 (" . SIGUSR1 . ")\n" );
                fputs( $errlog, time_prefix() . "Rescanning databases... " );
                
                global $databases;
                
                $databases = get_databases();
                fputs( $errlog, "Done\n" );
                
                global $use_jabber, $wanna_use_jabber, $jabber, $jabber_messengers, $jabber_auth_info;
                
                if ( $wanna_use_jabber )
                {
                    if ( $use_jabber && $jabber->connected )
                    {
                        fputs( $errlog, time_prefix() . "Disconnecting Jabber and rescanning config... " );
                        $jabber->Disconnect();
                    }
                    else
                        fputs( $errlog, time_prefix() . "Rescanning Jabber config... " );
                    $jabber_messengers = get_messenger_info( false, true );
                    $jabber_auth_info = get_messenger_info( 'jabber' );
                    fputs( $errlog, "Done.\n" );
                    
                    if ( ! $jabber_auth_info || ! isset( $jabber_auth_info['username'] ) || ! isset( $jabber_auth_info['password'] ) )
                    {
                        fputs( $errlog, time_prefix() . "Notice: no Jabber account information. Won't use instant messaging.\n" );
                        $use_jabber = false;
                    }
                    else
                    {
                        $use_jabber = true;
                        $jabber->username = $jabber_auth_info['username'];
                        $jabber->password = $jabber_auth_info['password'];
                        fputs( $errlog, time_prefix() . "Reconnecting Jabber... " );
                        connect_jabber();
                        fputs( $errlog, "Done.\n" );
                    }
                }
                
                break;
            case SIGUSR2:
                fputs( $errlog, time_prefix() . "SIGUSR2 (" . SIGUSR2 . ")\n" );
                foreach ( $databases as $selected_database => $dbinfo )
                {
                    if ( ! is_dir( $dbinfo[0] ) )
                        continue;
                    
                    define_globals( $selected_database );
                    walkthrough_users();
                    check_termination();
                }
                break;
        }
    }
    
    if ( function_exists( 'pcntl_signal' ) )
    {
        pcntl_signal( SIGTERM, "sig_handler" );
        pcntl_signal( SIGINT, "sig_handler" );
        pcntl_signal( SIGUSR1, "sig_handler" );
        pcntl_signal( SIGUSR2, "sig_handler" );

        if ( $daemon )
        {
            pcntl_signal( SIGHUP, "sig_handler" );
            if ( function_exists( 'posix_setsid' ) )
                posix_setsid();
        }
    }
    
    set_error_handler( 'error_handler', E_WARNING );
    set_error_handler( 'error_handler', E_NOTICE );

}

##########################
### Spezielle Routinen ###
##########################

{
    function arrive( $fleet_id )
    {
        #$filename = s_root.'/logs/eventhandler.log';
        #$fo = fopen($filename, "a");
        #fwrite($fo, time_prefix(). "Eventhandler Funktion Arrive. Fleet-ID:  ".$fleet_id."\n");
        
        global $errlog;
        
        if ( function_exists( 'pcntl_fork' ) )
        {
            #fwrite($fo, time_prefix(). "Eventhandler Funktion Arrive. function_exists('pcntl_fork') Pid wird zugewiesen. Fleet-ID: ".$fleet_id."\n");
            Classes::resetInstances();
            $pid = pcntl_fork();
            #fwrite($fo, time_prefix(). "Eventhandler Funktion Arrive. function_exists('pcntl_fork') Zugewiesene Pid: ".$pid." Fleet-ID: ".$fleet_id."\n");
        

        }
        else
        {
            #fwrite($fo, time_prefix(). "Eventhandler Funktion Arrive. Function_exists('pcntl_fork') nicht vorhanden. Pid wird auf PID = -1 gesetzt. Fleet-ID:  ".$fleet_id."\n");
            $pid = - 1;
        }
        
        if ( ! $pid || $pid == - 1 )
        {
            if ( $pid != - 1 )
            {
                #fwrite($fo, time_prefix(). "Eventhandler Funktion Arrive. PID != -1, Timelimit 30 Fleet-ID:  ".$fleet_id."\n");
                set_time_limit( 30 );
            }
            
            $fleet = Classes::Fleet( $fleet_id );
            
            if ( $fleet->getStatus() > 0 && ( $fleet->getStatus() > 1 || $fleet->getArrivalTime() > time() || ! $fleet->arriveAtNextTarget() ) )
            {
                fputs( $errlog, time_prefix() . "Eventhandler Funktion Arrive. ##### F L O T T E N H � N G E R ##### Fleet-ID:  " . $fleet_id . "\n" );
                fputs( $errlog, time_prefix() . "Warning: Couldn't complete fleet " . $fleet_id . ". Gonna process it later.\n" );
                $eventfile = Classes::EventFile();
                $eventfile->addNewFleet( time() + global_setting( "EVENTHANDLER_INTERVAL" ), $fleet_id );
                unset( $fleet );
                #$eventfile->addNewFleet(time()+global_setting("EVENTHANDLER_INTERVAL"), $fleet_id);
            }
            
            unset( $fleet );
            
            Classes::resetInstances();

            if ( $pid != - 1 )
            {
                fputs( $errlog, time_prefix() . "Eventhandler Funktion Arrive. Exit Fleet-ID:  " . $fleet_id . "\n\n\n" );
                exit( 0 );
            }
        }
        else
        {
            fputs( $errlog, time_prefix() . "Eventhandler Funktion Arrive. pcntl_wait: " . $pid . "  Fleet-ID:  " . $fleet_id . "\n" );
            
            pcntl_waitpid( $pid, &$status );
        }
    }

    function checkExpiredUsers( &$user )
    {
        global $errlog;
        
        $last_activity = $user->getLastActivity();
        
        // letztes login, abgerundet in tagen
        if ( $last_activity !== false )
        {
            $days = ceil( ( time() - $last_activity ) / 86400 );
        }
        else
        {
            $days = ceil( ( time() - $user->getRegistrationTime() ) / 86400 );
        }
        
        $today = date( 'Y-m-d' );
        
        # Wenn der Spieler inaktiv ist, loeschen
        if ( $last_activity !== false )
        {
            if ( $user->umode() )
            {
                if ( $days == 21 && $user->lastMailSent() != $today ) # 3 Wochen: Nachricht
                {
                    if ( $user->checkSetting( 'email' ) )
                    {
                        mail( $user->checkSetting( 'email' ), "Accountinaktivit\xc3\xa4t in T-B-W", "Sie erhalten diese Nachricht, weil Sie sich seit geraumer Zeit nicht mehr in The Big War angemeldet haben. Sie haben zwei Wochen Zeit, sich anzumelden, danach wird Ihr Account einer automatischen Loeschung unterzogen.\n\nDas Spiel erreichen Sie unter " . GLOBAL_GAMEURL . " \xe2\x80\x93 Ihr Benutzername lautet " . $user->getName(), "Content-Type: text/plain;\r\n  charset=\"utf-8\"\r\nFrom: " . global_setting( "EMAIL_FROM" ) . "\r\nReply-To: " . global_setting( "EMAIL_FROM" ) );
                        $user->lastMailSent( $today );
                    }
                }
                else 
                    if ( $days >= 35 ) # 5 Wochen: Loeschung
                    {
                        if ( $user->destroy() )
                        {
                            fputs( $errlog, "Deleted user `" . $user->getName() . "' because of inactivity.\n" );
                        }
                        else
                        {
                            fputs( $errlog, "Error: Couldn't delete user `" . $user->getName() . "'.\n" );
                        }
                        
                        continue;
                    }
            }
            else
            {
                if ( ( $days == 21 || $days == 34 ) && $user->lastMailSent() != $today )
                {
                    if ( $user->checkSetting( 'email' ) )
                    {
                        mail( $user->checkSetting( 'email' ), "Accountinaktivitaet in T-B-W", "Sie erhalten diese Nachricht, weil Sie sich seit geraumer Zeit nicht mehr in The Big War angemeldet haben. Sie haben " . ( ( $days == 34 ) ? 'einen Tag' : 'zwei Wochen' ) . " Zeit, sich anzumelden, danach wird Ihr Account einer automatischen Loeschung unterzogen.\n\nDas Spiel erreichen Sie unter " . GLOBAL_GAMEURL . " \xe2\x80\x93 Ihr Benutzername lautet " . $user->getName(), "Content-Type: text/plain;\r\n  charset=\"utf-8\"\r\nFrom: " . global_setting( "EMAIL_FROM" ) . "\r\nReply-To: " . global_setting( "EMAIL_FROM" ) );
                        $user->lastMailSent( $today );
                    }
                }
                elseif ( $days >= 35 )
                {
                    if ( $user->destroy() )
                    {
                        fputs( $errlog, "Deleted user `" . $user->getName() . "' because of inactivity.\n" );
                    }
                    else
                    {
                        fputs( $errlog, "Error: Couldn't delete user `" . $user->getName() . "'.\n" );
                    }
                    continue;
                }
            }
        }
        elseif ( $days == 7 && $user->lastMailSent() != $today )
        {
            if ( $user->checkSetting( 'email' ) )
            {
                mail( $user->checkSetting( 'email' ), "Accountinaktivit\xc3\xa4t in T-B-W", "Sie erhalten diese Nachricht, weil Sie sich seit geraumer Zeit nicht mehr in The Big War angemeldet haben. Sie haben eine Woche Zeit, sich anzumelden, danach wird Ihr Account einer automatischen L\xc3\xb6schung unterzogen.\n\nDas Spiel erreichen Sie unter " . GLOBAL_GAMEURL . " \xe2\x80\x93 Ihr Benutzername lautet " . $user->getName(), "Content-Type: text/plain;\r\n  charset=\"utf-8\"\r\nFrom: " . global_setting( "EMAIL_FROM" ) . "\r\nReply-To: " . global_setting( "EMAIL_FROM" ) );
            }
        }
        elseif ( $days >= 14 )
        {
            if ( $user->destroy() )
            {
                fputs( $errlog, "Deleted user `" . $user->getName() . "' because of inactivity.\n" );
            }
            else
            {
                fputs( $errlog, "Error: Couldn't delete user `" . $user->getName() . "'.\n" );
            }
            continue;
        }
    }

    /**
     * check for old messages stored in given user object
     * @return unknown_type
     */
    function checkExpiredMessages( &$user )
    {
        global $message_type_times;
        
        # Alte Nachrichten loeschen        
        $deleted_messages = 0;
        $processed_messages = array();
        $max_ages = $message_type_times;
        
        foreach ( $max_ages as $k => $v )
        {
            $max_ages[$k] *= 86400; // *24h
        }
        
        $message_categories = $user->getMessageCategoriesList();
        
        foreach ( $message_categories as $category )
        {
            $max_diff = $max_ages[$category];
            $messages_list = $user->getMessagesList( $category );
            
            foreach ( $messages_list as $message_id )
            {
                $processed_messages[$message_id] = true;
                
                if ( $user->checkMessageStatus( $message_id, $category ) )
                {
                    continue; # Ungelesen / Archiviert
                }
                
                $message_obj = Classes::Message( $message_id );
                
                if ( ! $message_obj->getStatus() || ( time() - $message_obj->getTime() ) > $max_diff )
                {
                    $user->removeMessage( $message_id, $category );
                    $deleted_messages ++;
                }
                else
                {
                    if ( ! $message_obj->getStatus() )
                    {
                        fputs( $errlog, time_prefix() . "checkForOldMessages() invalid Status returned while trying to check message id: " . $message_id . "\n" );
                    }
                }
            }
        }
        
        echo "deleted " . $deleted_messages . " on user " . $user->getName() . " \n";
        
        // Nachrichten, die niemandem gehoeren, loeschen
        $dh = opendir( global_setting( "DB_MESSAGES" ) );
        
        while ( ( $fname = readdir( $dh ) ) !== false )
        {
            if ( $fname[0] == '.' )
            {
                continue;
            }
            
            $fname = urldecode( $fname );
            
            if ( ! isset( $processed_messages[$fname] ) )
            {
                $message = Classes::Message( $fname );
                $message->destroy();
            }
        }
        closedir( $dh );
        
        $processed_messages = count( $processed_messages );
        fputs( $errlog, "Checked " . $processed_messages . " messages.\n" );
        fputs( $errlog, "Deleted " . $deleted_messages . " messages.\n" );
    }

    function walkthrough_users( )
    {
        global $errlog;
        global $last_walked;
        $last_walked = date( 'Y-m-d' );
        
        fputs( $errlog, "\n" . time_prefix() . "Walking through users for database " . global_setting( "DB" ) . "...\n" );
        
        # Rohstoffe aller Planeten aller Benutzer zusammenzaehlen
        $ges_ress = array( 0, 0, 0, 0, 0 );
        
        $dh = opendir( global_setting( "DB_PLAYERS" ) );
        while ( ( $filename = readdir( $dh ) ) !== false )
        {
            if ( ! is_file( global_setting( "DB_PLAYERS" ) . '/' . $filename ) )
                continue;
            
            $user = Classes::User( urldecode( $filename ) );
            if ( ! $user->getStatus() )
                continue;
            
            checkExpiredUsers( $user );
            
            $planets = $user->getPlanetsList();
            foreach ( $planets as $planet )
            {
                $user->setActivePlanet( $planet );
                $ress = $user->getRess();
                unset( $ress[5] ); # Energie soll nicht miteinberechnet werden
                

                if ( min( $ress ) < 0 )
                {
                    fputs( $errlog, time_prefix() . "Warning: Planet " . $user->getPosString() . " (" . $user->getName() . ") has negative resources.\n" );
                    continue;
                }
                
                $min = max( $ress );
                if ( $min != 0 )
                {
                    foreach ( $ress as $val )
                    {
                        if ( $val < $min && $val != 0 )
                            $min = $val;
                    }
                    
                    $ress[0] /= $min;
                    $ress[1] /= $min;
                    $ress[2] /= $min;
                    $ress[3] /= $min;
                    $ress[4] /= $min;
                }
                
                $ress[0] = pow( $ress[0], 1 / ( $days + 1 ) );
                $ress[1] = pow( $ress[1], 1 / ( $days + 1 ) );
                $ress[2] = pow( $ress[2], 1 / ( $days + 1 ) );
                $ress[3] = pow( $ress[3], 1 / ( $days + 1 ) );
                $ress[4] = pow( $ress[4], 1 / ( $days + 1 ) );
                
                $ges_ress[0] += $ress[0];
                $ges_ress[1] += $ress[1];
                $ges_ress[2] += $ress[2];
                $ges_ress[3] += $ress[3];
                $ges_ress[4] += $ress[4];
            }
            
            checkExpiredMessages( $user );
            
            unset( $user );
            Classes::resetInstances();
        }
        closedir( $dh );
        
        # Kurs berechnen
        $min = max( $ges_ress );
        if ( $min != 0 )
        {
            foreach ( $ges_ress as $val )
            {
                if ( $val < $min && $val != 0 )
                    $min = $val;
            }
        }
        
        if ( $min == 0 )
            $kurs = array( 10, 5, 3.75, 2.5, 1 );
        else
        {
            $kurs = array();
            $kurs[0] = $ges_ress[0] / $min;
            $kurs[1] = $ges_ress[1] / $min;
            $kurs[2] = $ges_ress[2] / $min;
            $kurs[3] = $ges_ress[3] / $min;
            $kurs[4] = $ges_ress[4] / $min;
            
            foreach ( $kurs as $key => $val )
            {
                if ( $val == 0 )
                    $kurs[$key] = 1;
            }
        }
        
        unset( $ges_ress );
        
        # Kurs schreiben
        $handelskurs = preg_split( "/\r\n|\r|\n/", file_get_contents( global_setting( "DB_HANDELSKURS" ) ) );
        $handelskurs[0] = $kurs[0];
        $handelskurs[1] = $kurs[1];
        $handelskurs[2] = $kurs[2];
        $handelskurs[3] = $kurs[3];
        $handelskurs[4] = $kurs[4];
        
        $fh = fopen( global_setting( "DB_HANDELSKURS" ), 'w' );
        flock( $fh, LOCK_EX );
        
        fwrite( $fh, implode( "\n", $handelskurs ) );
        
        flock( $fh, LOCK_UN );
        fclose( $fh );
        
        fputs( $errlog, "Handelskurs recalculated.\n" );
        
        Classes::resetInstances();
        
        # Oeffentliche Nachrichten loeschen
        

        #$processed_messages_public = 0;
        #$deleted_messages_public = 0;
        

        #global $public_messages_time;
        #$max_age = $public_messages_time*86400;
        #$dh = opendir(global_setting("DB_MESSAGES_PUBLIC"));
        #while(($fname = readdir($dh)) !== false)
        #{
        

        #if($fname[0] == '.') continue;
        

        #$fname = urldecode($fname);
        #fputs($errlog, "Vorchecked ".$fname." public messages.\n");
        

        #$message = Classes::PublicMessage($fname);
        #fputs($errlog, "Vorchecked ".$fname." public messages.\n");
        #fputs($errlog, "Checked ".$processed_messages_public." public messages.\n");
        #if(!$message->getStatus())continue;
        #else $processed_messages_public++;
        #if(time()-$message->getLastViewTime() > $max_age)
        #{
        

        #$message->destroy();
        #$deleted_messages_public++;
        #}
        #}
        

        #closedir($dh);
        

        #Classes::resetInstances();
        

        #fputs($errlog, "Checked ".$processed_messages_public." public messages.\n");
        #fputs($errlog, "Deleted ".$deleted_messages_public." public messages.\n");
        

        fputs( $errlog, time_prefix() . "Finished.\n\n" );
    }

    function connect_jabber( )
    {
        global $jabber;
        global $jabber_messengers;
        global $errlog;
        
        if ( ! $jabber->Connect() || ! $jabber->SendAuth() )
        {
            fputs( $errlog, time_prefix() . "Warning: Could not connect to Jabber server.\n" );
            return false;
        }
        
        $jabber->SendPresence( NULL, NULL, "online" );
        
        # Transports
        foreach ( $jabber_messengers as $m => $ai )
        {
            if ( $m == 'jabber' )
                continue;
            
            $m = strtolower( $m );
            
            $jabber->TransportRegistration( $ai['server'], $ai );
        }
        
        return true;
    }
}

#####################
### Jabber-Client ###
#####################


if ( $wanna_use_jabber )
{
    $jabber_messengers = get_messenger_info();
    $jabber_auth_info = get_messenger_info( 'jabber' );
    $jabber = new Jabber( );
    $jabber->server = $jabber_auth_info['server'];
    $jabber->port = 5222;
    $jabber->resource = false;
    $imfile = Classes::IMFile();
    
    if ( ! $jabber_auth_info || ! isset( $jabber_auth_info['username'] ) || ! isset( $jabber_auth_info['password'] ) )
    {
        fputs( $errlog, time_prefix() . "Notice: no Jabber account information. Won't use instant messaging.\n" );
        $use_jabber = false;
    }
    else
    {
        $jabber->username = $jabber_auth_info['username'];
        $jabber->password = $jabber_auth_info['password'];
        fputs( $errlog, time_prefix() . "Connecting Jabber client... " );
        connect_jabber();
        fputs( $errlog, "Done.\n" );
    }
}

#################
### Durchlauf ###
#################


{
    #$filename = s_root.'/logs/eventhandler.log';
    #$fo = fopen($filename, "a");
    

    global $gCheckMsg;
    
    if ( $gCheckMsg )
    {// TODO
}
    
    $fposition = 0;
    
    if ( date( 'H' ) * 3600 + date( 'i' ) * 60 + 60 < 14400 )
        $last_walked = false;
    else
        $last_walked = date( 'Y-m-d' );
    
    fputs( $errlog, time_prefix() . "Eventhandler started.\n" );
    
    while ( true )
    {
        check_termination();
        
        # Flotten ankommen lassen
        foreach ( $databases as $selected_database => $dbinfo )
        {
            if ( ! is_dir( $dbinfo[0] ) )
                continue;
            
            define_globals( $selected_database );
            
            $event_obj = Classes::EventFile();
            while ( $process = $event_obj->removeNextFleet() )
            {
                fputs( $errlog, time_prefix() . "Eventhandler Eventobjekt remove next Fleet\n" );
                
                arrive( $process['fleet'] );
                check_termination();
            }
        }
        
        check_termination();
        
        # Handelskurs neu berechnen und Inaktive loeschen
        if ( date( 'H' ) * 3600 + date( 'i' ) * 60 + 60 > 14400 && $last_walked != date( 'Y-m-d' ) ) // 4:30 Uhr
        {
            foreach ( $databases as $selected_database => $dbinfo )
            {
                if ( ! is_dir( $dbinfo[0] ) )
                    continue;
                
                define_globals( $selected_database );
                walkthrough_users();
                check_termination();
            }
        }
        
        # Jabber-Nachrichten ueberpruefen
        if ( $use_jabber )
        {
            if ( ! $jabber->connected )
            {
                fputs( $errlog, time_prefix() . "Disconnected. Trying to reconnect.\n" );
                connect_jabber();
                
                if ( $jabber->connected )
                    fputs( $errlog, time_prefix() . "Reconnected!\n" );
                else
                    fputs( $errlog, time_prefix() . "Couldn't reconnect.\n" );
            }
            
            if ( $jabber->connected )
            {
                while ( $next_notification = $imfile->shiftNextMessage() )
                {
                    $to = $next_notification['uin'];
                    if ( $next_notification['protocol'] != 'jabber' )
                    {
                        if ( ! isset( $jabber_messengers[$next_notification['protocol']] ) )
                            continue;
                        $to .= '@' . $jabber_messengers[$next_notification['protocol']]['server'];
                    }
                    
                    $message = "Automatische Benachrichtigung von " . GLOBAL_GAMEURL . " " . $databases[$next_notification['database']][1] . ", " . $next_notification['username'] . ":\n";
                    $message .= $next_notification['message'];
                    
                    $jabber->SendMessage( $to, 'normal', NULL, array( 'body' => htmlspecialchars( $message ) ) );
                }
                
                $jabber->Listen();
                while ( $p = array_shift( $jabber->packet_queue ) )
                {
                    if ( ! isset( $p['message'] ) || ! isset( $p['message']['#']['body'] ) )
                        continue;
                    
                    $message = $p['message']['#']['body'][0]['#'];
                    list( $from ) = explode( '/', $p['message']['@']['from'], 2 );
                    $transport = 'jabber';
                    foreach ( $jabber_messengers as $tn => $t )
                    {
                        if ( substr( $from, - strlen( $t['server'] ) - 1 ) == '@' . $t['server'] )
                        {
                            $transport = $tn;
                            $from = substr( $from, 0, - strlen( $t['server'] ) - 1 );
                            break;
                        }
                    }
                    
                    $username = $imfile->checkCheckID( $from, $transport, trim( $message ) );
                    if ( $username && isset( $databases[$username[1]] ) )
                    {
                        define_globals( $username[1] );
                        $user = Classes::User( $username[0] );
                        $old_settings = $user->getNotificationType();
                        if ( $user->getStatus() == 1 && $user->doSetNotificationType( $from, $transport ) )
                        {
                            $imfile->removeChecks( $username[0] );
                            
                            if ( $old_settings )
                                $imfile->changeUIN( $username[0], $from, $transport );
                            else
                                $user->refreshMessengerBuildingNotifications();
                            unset( $user );
                            Classes::resetInstances();
                            
                            $jabber->sendMessage( $p['message']['@']['from'], 'normal', NULL, array( 'body' => 'Accepted' ) );
                        }
                    }
                    else
                        $jabber->sendMessage( $p['message']['@']['from'], 'normal', NULL, array( 'body' => 'Unrecognised command' ) );
                }
            }
        }
        
        sleep( global_setting( "EVENTHANDLER_INTERVAL" ) );
    }
}
?>
