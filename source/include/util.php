<?php

if (! defined ( TBW_ROOT ))
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

/**
 * converts the given $fleetType to the according string
 * @param $fleetType
 */
function fleetType2String($fleetType)
{
	switch( $fleetType )
	{
		case 1:
			return "Besiedeln";
			break;
		case 2:
			return "Sammeln";
			break;
		case 3:
			return "Angriff";
			break;
		case 4:
			return "Transport";
			break;
		case 5:
			return "Spionage";
			break;
		case 6:
			return "Stationieren";
			break;															
		default:
			return "Unknown";
			break;
	}
}

/**
 * converts given fleetID to the according string
 * @param $fleetID
 */
function fleetID2String($fleetID)
{
	switch( $fleetID )
	{
		case "S0":
			 return "Kleiner Transporter";
		break;
		
		case "S1":
			 return "Grosser Transporter";
		break;
		case "S2":
			 return "Transcube";
		break;

		case "S3":
			 return "Sammler";
		break;

		case "S4":
			 return "unknown";
		break;

		case "S5":
			 return "Spionagesonde";
		break;

		case "S6":
			 return "Besiedlungsschiff";
		break;

		case "S7":
			 return "Kampfkapsel";
		break;		
		
		case "S8":
			 return "Leichter Jäger";
		break;	

		case "S9":
			 return "Schwerer Jäger";
		break;	

		case "S10":
			 return "Leichte Fregatte";
		break;	

		case "S11":
			 return "Schwere Fregatte";
		break;	

		case "S12":
			 return "Leichter Kreuzer";
		break;	

		case "S13":
			 return "Schwerer Kreuzer";
		break;	

		case "S14":
			 return "Schlachtschiff";
		break;	

		case "S15":
			 return "Zerstörer";
		break;	

		case "S16":
			 return "Warcube";
		break;			
		
		default:
			return "unknown";
		break;
	}
}

/**
 * subtracts 2nd array from 1st one and returns result
 * @param $arr1
 * @param $arr2 
 */
function array4Sub($arr1, $arr2)
{
    for($i=0; $i < 5; $i++)
    {
        $arr1[$i] -= $arr2[$i];
    }    
    return $arr1;
}

/**
 * returns true if name is restricted for use
 * @param $name
 */
function isBlacklistedName($name)
{
    if(strtolower($name) == "ticketsystem")
    {
        return true;
    }
    return false;
}

function IsValidMessageType($type)
{
	if ( is_numeric($type) && $type >= 0 && $type < MSGTYPE_MAX )
		return true;
		
	return false;
}

function GetNameOfMessageType($type)
{
	global $g_MSGTYPE_NAMES;
	
	return $g_MSGTYPE_NAMES[$type];
}