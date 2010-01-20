<?php

require_once ( 'config_inc.php' );
require_once ( TBW_ROOT.'loghandler/logger.php' );

/**
 * class to receive log entries via ipc message queue and processing them
 * @author atomy
 *
 */
class LogHandler
{
    /**
     * ipc key for ipc queue
     * @var int
     */
    private $ipcKey;

    /**
     * ipc resource queue for retrieving log messages
     * @var resource
     */
    private $msgQue;

    /**
     * logger obj for distributing messages to the logfiles
     * @var Logger()
     */
    private $logger;

    function __construct( )
    {
        $this->ipcKey = 0;
        $this->msgQue = false;
        $this->logger = false;
    }

    /**
     * remove queue, destruct logger obj
     */
    function __destruct( )
    {    
        msg_remove_queue( $this->msgQue );
        unset( $this->logger );     
    }

    /**
     * endless run loop, retrieve incomming log messages and forward them to the logger obj
     * @return void
     */
    public function run( )
    {
        if ( !function_exists( "msg_get_queue" ) )
        {
            fputs( STDERR, "msg_get_queue() unavailable, logging unavailable\n" );
            exit( 1 );
        }     
        
        set_time_limit( 0 );
        
        $logger = &new Logger( );
        $this->logger = &$logger;
        $this->ipcKey = getIPCKey();
        $this->msgQue = msg_get_queue( $this->ipcKey );
        
        if ( !$this->msgQue )
        {
             fputs( STDERR, "error setting up queue (1), logging unavailable\n" );
             exit ( 1 );
        }

        /*
        if ( !msg_set_queue( $this->msgQue, array ( 'msg_perm.mode' => '0640' ) ) )
        {
             fputs( STDERR, "error setting up queue (2), logging unavailable\n" );
             exit ( 1 );            
        }
        */
            
        $incMsgType = 0;
        $incMessage = '';
        
        while ( true )
        {
            while ( msg_receive( $this->msgQue, 0, $msgType, 4096, $incMessage, false ) )
            {
                $logger->logIt( $msgType, $incMessage );
                //echo "message --> $incMessage <-- of type --> $msgType <-- written! i'm ".getmypid()."\n";
            }
            sleep( IPC_MSG_INTERVAL );
        }
    }
}

?>