<?

if ( !defined( "TBW_ROOT" ) )
{
    require_once( '../../include/config_inc.php' );
}

/**
 * Logfile 
 * 
 * @package 
 * @version $id$
 * @copyright 1997-2005 The PHP Group
 * @author Tobias Schlitt <toby@php.net> 
 * @license GPLPHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class Logfile
{
	/**
	 * filePath 
	 * holds relative or full path to file
	 * @var string
	 * @access private
	 */
	private $filePath;

	/**
	 * fileStream 
	 * holds stream resource to open file
	 * @var resource
	 * @access private
	 */
	private $fileStream;

	/**
	 * isOpen 
	 * true if stream has been opened
	 * @var bool
	 * @access private
	 */
	private $isOpen;

	/**
	 * __construct 
	 * initializes variables and sets the file path
	 * @param string $path relative or fullpath to target file
	 * @access protected
	 * @return void
	 */
	function __construct( $path )
	{
		$this->isOpen = false;
		$this->filePath = '';
		$this->fileStream = '';

		/*
 		* check if file is reachable, create if not already exists 
 		*/
		if ( !is_file( $path ) )
		{
			if ( !touch( $path ) ) 
			{
				throw new Exception( __METHOD__." couldnt create logfile with path: $path" );
			}
		}

		if ( !is_writeable( $path ) )
		{
			throw new Exception( __METHOD__." cant write to path: $path" );
		}
		else
		{
			$this->filePath = $path;
		}
	}

	/**
	 * __destruct 
	 * close our resource handle
	 * @access protected
	 * @return void
	 */
	function __destruct()
	{
		fclose( $this->fileStream );
	}

	/**
	 * logIt 
	 * log given text, care about resources to get setup if not already
	 * @param string $text 
	 * @access public
	 * @return bool true on success false otherwise
	 */
	public function logIt( $text )
	{	
		if ( !$this->openLogfile() )
		{
			fputs( STDERR, __METHOD__." Error opening logfile: $this->filePath\n" );

			return false;
		}
	
		// date prefix goes here
		$output = date("M j H:i:s ").$text."\n";

		if ( fputs( $this->fileStream, $output ) >= 0 )
		{
			return true;
		}
		else
		{
			fputs( STDERR, __METHOD__." Error writing to file: $this->filePath\n" );

			return false;
		}

		return false;
	}

	/**
	 * openLogfile 
	 * open our target logfile for appending
	 * @access private
	 * @return bool true on success false otherwise
	 */
	private function openLogfile()
	{
		/*
		 * if already open return, otherwise we need to open it
		 */
		if ( $this->isOpen )
		{
			return true;
		}
		else
		{
			$this->fileStream = fopen( $this->filePath, "a" );
		}

		if ( strlen( $this->filePath ) == 0 )
		{
			throw new Exception( __METHOD__." tried to open logfile but no path is set" );
		}

		if ( $this->fileStream === false )
		{
			fputs( STDERR, __METHOD__." Error opening file: ".$this->filePath."\n" );

			return false;
		}
		else
		{
			$this->isOpen = true;

			return true;
		}

		return false;
	}
}

?>
