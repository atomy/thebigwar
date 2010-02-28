<?php

if ( ! defined( TBW_ROOT ) );
{
    require_once ( 'config_inc.php' );
}

function IsGameOperator( $name )
{       
    $userObj = Classes::User($name);
    
    // all members of the "go"-alliance are gameoperators!
    if ( $userObj->getStatus() && strtolower( $userObj->allianceTag() ) == "go" )
    {
        return true;
    }
    return false;
}

/**
 * retrieve ipc key via ftok of given file, if not created create one
 * @return int returns ipc key used for queues
 */
function getIPCKey( )
{
    if ( ! file_exists( TEMPDIR . KEYFILE ) )
    {
        if ( ! touch( TEMPDIR . KEYFILE ) )
        {
            throw new Exception( __FUNCTION__ . " Unable to generate keyfile." );
        }
    }
    
    return ftok( TEMPDIR . KEYFILE, IPCPROJCHAR );
}

function array_split($input, $callback=null) {
	    $callback = isset($callback) ? $callback : create_function('$x', 'return $x == true;');
	   
	    $true = array();
	    $false = array();
	    
	    foreach ($input as $key => $value) {
	        if (call_user_func($callback, $value)) {
	            $true[$key] = $value;
	        }
	        else {
	            $false[$key] = $value;
	        }
	    }
	   
	    return array($true, $false);
}
?>