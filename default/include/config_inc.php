<?php
    define("GLOBAL_GAMEURL", "http://testuni.thebigwar.org/");
    define("GLOBAL_SGOACCNAME", "admin");
    define("GLOBAL_DEMOACCNAME", "demo");
    define("GLOBAL_DEMOACCPASS", "demo");

	define("TBW_ROOT", "/var/www/tbw-test.jackinpoint.net/htdocs/");
	
	// url for news iframe used at front page
	define("TBW_EXT_NEWSURL", 'http://forum.thebigwar.org/ext/news.php');

	require_once( TBW_ROOT."include/util.php" );

	// eggdrop, announcing new user registrations into IRC
    $bb2egg['botip'] = "";
    $bb2egg['botport'] = ""; 
    $bb2egg['pass'] = "";
    $bb2egg['channel_tbw'] = "#tbw";

    date_default_timezone_set("Europe/Berlin");
?>
