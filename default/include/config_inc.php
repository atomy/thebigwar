<?php
	
    // this is automatically set by phing, uncomment when not using phing prepare
	//define("TBW_ROOT", "/var/www/tbw-test.jackinpoint.net/htdocs/");

	define("GLOBAL_GAMEURL", "http://testuni.thebigwar.org/");
    define("GLOBAL_SGOACCNAME", "admin");
    define("GLOBAL_DEMOACCNAME", "demo");
    define("GLOBAL_DEMOACCPASS", "demo");

	// url for news iframe used at front page
	define("TBW_EXT_NEWSURL", 'http://forum.thebigwar.org/ext/news.php');

	require_once( TBW_ROOT."include/util.php" );

	// eggdrop, announcing new user registrations into IRC
    $bb2egg['botip'] = "";
    $bb2egg['botport'] = ""; 
    $bb2egg['pass'] = "";
    $bb2egg['channel_tbw'] = "#tbw";

	// stuff for logging
	define( "LOG_USER", 1 );
	define( "LOGDIR", "./logs/" );
	define( "LOGFILE_USER", "user.log" );

    date_default_timezone_set("Europe/Berlin");
?>
