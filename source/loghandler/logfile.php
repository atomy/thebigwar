<?php

if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');

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
	 * holds relative or full path to file
	 * @var string
	 * @access private
	 */
	private $filePath;

	/**
	 * holds stream resource to open file
	 * @var resource
	 * @access private
	 */
	private $fileStream;

	/**
	 * true if stream has been opened
	 * @var bool
	 * @access private
	 */
	private $isOpen;
	
	/**
	 * day on which the file got opened, used for daily logrotating
	 * @var unknown_type
	 */
	private $openDate;

	/**
	 * initializes variables and sets the file path
	 * @param string $path relative or fullpath to target file
	 * @access protected
	 * @return void
	 */
	function __construct( $relpath )
	{
		$this->isOpen = false;
		$this->filePath = '';
		$this->fileStream = '';
		$path = TBW_ROOT . $relpath;
		//echo "path is: ".$path."\n";

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
	 * close our resource handle
	 * @access protected
	 * @return void
	 */
	function __destruct()
	{
		$this->logClose();
	}

	/**
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
	 * responsible for closing the stream
	 * @return void
	 */
	private function logClose( )
	{
	    $this->isOpen = false;
	    fflush( $this->fileStream );
	    fclose( $this->fileStream );
	}
	
	/**
	 * rotates logs when the day changed
	 * @return unknown_type
	 */
	private function logRotate( )
	{	   
	    $this->logIt( "Rotating logfile..." );
		$this->logClose();
			
		/*
		 * start at the end and shift suffix of logfiles +1
		 * ( logfile.log.8 => logfile.log.7, ... )
		 */
		for( $i = KEEP_NUM_LOGS; $i > 0; $i-- )
		{
		    $prev = $i - 1;
		    $actFname = $this->filePath.".".$i;
		    
		    if ( $prev == 0 )
		    {
		        $prevFname = $this->filePath;
		    }
		    else
		    {
		        $prevFname = $this->filePath.".".$prev;
		    }
		    
		    /*
		     * previous name doesnt exists, skip this one
		     */
		    if ( !file_exists( $prevFname ) )
		    {
		        continue;
		    }
		    
			/*
		     * oldest logfile over here, remove it
		     * and move the previous one over there
		     */		    
		    if ( file_exists( $this->filePath.".".$i ) )
		    {
                unlink( $actFname );
		    }
		    		   
		    rename( $prevFname, $actFname );
		}
		
		$this->logIt( "Logfile rotated" );	    
	}
	
	/**
	 * open our target logfile for appending
	 * @access private
	 * @return bool true on success false otherwise
	 */
	private function openLogfile()
	{
	    $curDay = date( "j" );
	    
		/*
		 * if already open return, otherwise we need to open it
		 */
		if ( $this->isOpen )		
		{
		    /*
		     * we are still on that day, no need to logrotate
		     */		    		    
		    if ( $this->openDate == $curDay )
		    {
		        return true;
		    }
		    else		    
		    {
		        $this->openDate = $curDay;
		        $this->logRotate();
		    }
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
			$this->openDate = $curDay;

			return true;
		}

		return false;
	}
}

?>
