<?php

/**
 * this example demonstrates howto use the class Logger()
 *
 * the main porpose of this class is to handle multiple log types and write them to the type specific file
 * additional log-entries are prefixed with timestamps
 */

if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/loghandler/logger.php');

/*
 * create logger object and send log entry of type LOG_USER to it
 */
$meh = new Logger();
$meh->logIt( LOG_USER_FLEET, "sample user.fleet log-entry" );

?>
