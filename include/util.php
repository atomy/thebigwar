<?php

if ( ! defined( TBW_ROOT ) );
{
    require_once ( 'config_inc.php' );
}

function IsGameOperator( $name )
{
    if ( strtolower( $name ) == "stoffel" || strtolower( $name ) == "atomy" )
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
?>