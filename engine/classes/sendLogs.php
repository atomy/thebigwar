<?

if ( !defined( "TBW_ROOT" ) )
{
	require_once( '../../include/config_inc.php' );
}

require_once( TBW_ROOT.'loghandler/LogHandler.php' );

/**
 * Logger 
 * 
 * @package 
 * @version $id$
 * @copyright 1997-2005 The PHP Group
 * @author atomy <atomy@jackinpoint.net>
 * @license GPL
 */
class SendLogs
{
    /**
     * ipc key used for the message queue
     * @var int
     */
	private $ipcKey = 0;
	
	/**
	 * message queue resource
	 * @var resource
	 */
	private $msgQue = false;

	/**
	 * setup all que stuff needed to actual push messages through it
	 * @return void
	 */
	private function setupMsgQue()
	{	
	    if ( !function_exists( "msg_get_queue" ) )
        {
            fputs( STDERR, "msg_get_queue() unavailable, logging impossible\n" );
            
            return false;
        }
        	    
	    $this->ipcKey = LogHandler::getIPCKey();
	    $pppid = getmypid();
	    //print "meh: $this->ipcKey -- $pppid \n";
	    $this->msgQue = msg_get_queue( $this->ipcKey );	

	    if ( ! $this->msgQue )
	    {
	        fputs( STDERR, "unable to create message queue with key: $this->ipcKey \n" );
	    }
	}
	
	/**
	 * log incomming messages
	 * @param $type type of the log entry, will be the file later one
	 * @param $text log entry itself, full of text
	 * @return void
	 */
	public function logIt( $type, $text )
	{
	    /*
	     * not setup yet, set it up!
	     */
		if ( $this->ipcKey == 0 || $this->msgQue == false )
		{
		    $this->setupMsgQue();
		}

		/*
		 * send the log message via queue to the log handler
		 */
		if ( !msg_send( $this->msgQue, $type, $text, false, false, $incErrCode ) ) 
		{
		    fputs( STDERR, "error sending message to msg queue: $incErrCode \n" );
		}
	}
}

?>
