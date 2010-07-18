<?php

function phpbb2egg($text, $where)
{
	global $bb2egg;
	 
	if ( !isset( $bb2egg['botip'] ) || $text == '')
		return;

	$text = ereg_replace(";", ":", $text);
	$text = ereg_replace("<br>", ";", $text);
	$channel = "channel_".$where;
	
	if(!isset($bb2egg[$channel]))
	    return;

	$line = md5($bb2egg['pass']) ." ". $bb2egg[$channel] ." ". $text;
	$fp = fsockopen($bb2egg['botip'], $bb2egg['botport'], $errno, $errstr, 5);

	if ($fp)
	{
    	fputs($fp, "$line\n");
    	
		// wait for 0.5 seconds
		usleep(500000);
		fclose($fp);
	}
}
