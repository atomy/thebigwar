<?php

if (! defined ( TBW_ROOT ))
	;
{
	require_once ('config_inc.php');
}

function IsGameOperator($name) {
	$userObj = Classes::User ( $name );
	
	// all members of the "go"-alliance are gameoperators!
	if ($userObj->getStatus () && strtolower ( $userObj->allianceTag () ) == "go") {
		return true;
	}
	return false;
}

/**
 * retrieve ipc key via ftok of given file, if not created create one
 * @return int returns ipc key used for queues
 */
function getIPCKey() {
	if (! file_exists ( TEMPDIR . KEYFILE )) {
		if (! touch ( TEMPDIR . KEYFILE )) {
			throw new Exception ( __FUNCTION__ . " Unable to generate keyfile." );
		}
	}
	
	return ftok ( TEMPDIR . KEYFILE, IPCPROJCHAR );
}

function array_split($input, $callback = null) {
	$callback = isset ( $callback ) ? $callback : create_function ( '$x', 'return $x == true;' );
	
	$true = array ();
	$false = array ();
	
	foreach ( $input as $key => $value ) {
		if (call_user_func ( $callback, $value )) {
			$true [$key] = $value;
		} else {
			$false [$key] = $value;
		}
	}
	
	return array ($true, $false );
}

/**
 * transfers a given timestamp into a string in the form of e.g. 2d 3h 13m 10s with the given granularity
 * @param int $timestamp
 * @param int $granularity
 */
function timeAgo($timestamp, $granularity = 2) {
	
	$difference = time () - $timestamp;
	
	// if difference is lower than zero check server offset
	if ($difference <= 0)
		return 'vor 0s';
		
	// if difference is over 30 days show normal time form
	else {
		
		$periods = array ('d' => 86400, 'h' => 3600, 'm' => 60, 's' => 1 );
		$output = '';
		
		foreach ( $periods as $key => $value ) {
			if ($difference >= $value) {
				$time = round ( $difference / $value );
				$difference %= $value;
				
				$output .= ($output ? ' ' : '') . $time . ' ';
				$output .= (($time > 1 && $key == 'd') ? $key . 's' : $key);
				
				$granularity --;
			}
			
			if ($granularity == 0)
				break;
		}
		
		return "vor " . $output;
	}
}

/**
 * converts a given category to their string reflecting a description of it
 * @param $cat
 */
function msgCategoryToText($cat)
{
	global $message_type_names;
	
	return $message_type_names[$cat];
}
?>