<?php

include_once "include/config_inc.php";


function phpbb2egg($text, $where)
{
	global $bb2egg;
	 
	if ( !isset( $bb2egg['botip'] ) )
	{
		return;
	}

	if ( $text == '')
	{
		return;
	}

	$text = ereg_replace(";", ":", $text);
	$text = ereg_replace("<br>", ";", $text);

	$fp = fsockopen($bb2egg['botip'], $bb2egg['botport'], $errno, $errstr, 5);

	if ($fp)
	{
		$channel = "channel_".$where;
		$line = md5($bb2egg['pass']) ." ". $bb2egg[$channel] ." ". $text;
		fputs($fp, "$line\n");

		// wait for 0.5 seconds
		usleep(500000);

		fclose($fp);
	}
}
