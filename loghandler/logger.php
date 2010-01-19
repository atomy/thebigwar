<?

if ( !defined( "TBW_ROOT" ) )
{
	require_once( '../../include/config_inc.php' );
}

require_once( TBW_ROOT.'loghandler/logfile.php' );

/**
 * Logger 
 * 
 * @package 
 * @version $id$
 * @copyright 1997-2005 The PHP Group
 * @author atomy <atomy@jackinpoint.net>
 * @license GPL
 */
class Logger
{
	/**
	 * logFiles 
	 * holds all log files, array( 'type', 'path' ) 
	 * @var array
	 * @access private
	 */
	private $logFiles = array();

	/**
	 * __destruct 
	 * destruct our logfile objects 
	 * @access protected
	 * @return void
	 */
	function __destruct()
	{
		foreach( $this->logFiles as $logFile )
		{
			unset( $logFile );
		}
	}

	/**
	 * setLogFile 
	 * set a log file for the given $type
	 * @param int $type 
	 * @param string $path 
	 * @access private
	 * @return void
	 */
	private function setLogFile( $type, $path )
	{
		if ( strlen( $type ) == 0 || strlen( $path ) == 0 )
		{
			throw new Exception( __METHOD__." tried to set with empty type: $type or path: $path" );
		}

		if ( isset( $this->logFiles[$type] ) )
		{
			throw new Exception( __METHOD__." there is already a file set for type: $type" );
		}

		$logObj = new Logfile( $path );

		$this->logFiles[$type] = &$logObj;
	}

	/**
	 * logIt 
	 * log some $text to a given $type
	 * @param int $type 
	 * @param string $text 
	 * @access public
	 * @return void
	 */
	public function logIt( $type, $text )
	{
		// check if we have a log for this type
		if ( !isset( $this->logFiles[$type] ) )
		{
			if ( ! $this->setupLog( $type ) )
			{
				throw new Exception( __METHOD__." we dont have any logfile registered for type: $type" );
			}
		}

		$logF = &$this->logFiles[$type];

		// empty log entry? done!
		if ( strlen( $text ) == 0 )
		{
			return true;
		}
		else
		{
			$logF->logIt( $text );
		}
	}

	/**
	 * setupLog 
	 * setup given log type 
	 * @param int $type 
	 * @access public
	 * @return bool true on success, false otherwise
	 */
	public function setupLog( $type = 0 )
	{
		if ( $type == 0 )
		{
			throw new Exception( __METHOD__." no type given" );
		}

		/*
		 * catch the types and link them to their file,
		 * add new types here
		 */
		switch( $type )
		{
			case LOG_EVENTH_GENERAL:
				$this->setLogFile( $type, LOGDIR . LOGFILE_EVENTH_GENERAL );
				return true;
			break;
			
			case LOG_EVENTH_FLEET:
				$this->setLogFile( $type, LOGDIR . LOGFILE_EVENTH_FLEET );
				return true;
			break;			

			default:
				throw new Exception( __METHOD__." logtype $type not defined" );
			break;
		}

		return false;
	}
}

?>
