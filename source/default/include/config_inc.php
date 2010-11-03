<?php
	
    // this is automatically set by phing, uncomment when not using phing prepare
	//define("TBW_ROOT", "/var/www/tbw-test.jackinpoint.net/htdocs/");

	define("GLOBAL_GAMEURL", "http://testuni.thebigwar.org/");
    define("GLOBAL_SGOACCNAME", "admin");
    define("GLOBAL_DEMOACCNAME", "demo");
    define("GLOBAL_DEMOACCPASS", "demo");
    define("USERNAME_MAXLEN", 20);

	// url for news iframe used at front page
	define("TBW_EXT_NEWSURL", 'http://forum.thebigwar.org/ext/news.php');

	require_once( TBW_ROOT."include/util.php" );

	// eggdrop, announcing new user registrations into IRC
    $bb2egg['botip'] = "";
    $bb2egg['botport'] = ""; 
    $bb2egg['pass'] = "";
    $bb2egg['channel_tbw'] = "#tbw";
    $bb2egg['channel_tbwsupport'] = "#tbw-support";

    /**
     * those are defines for the supported log types, 
     * when adding more types they need to be also added to Logger::setupLog() for linkage to their filename
     */
    define( "LOG_EVENTH_GENERAL", 1 );
    define( "LOG_EVENTH_FLEET", 2 );
    define( "LOG_USER_FLEET", 3 );
    define( "LOG_USER_ITEMCHANGE", 4 );    
    
    /**
     * defines for logfiles, filenames for the above types
     */
	define( "LOGDIR", "logs/" );
	define( "LOGFILE_EVENTH_GENERAL", "eventhandler.general.log" );
	define( "LOGFILE_EVENTH_FLEET", "eventhandler.fleet.log" );
	define( "LOGFILE_USER_FLEET", "user.fleet.log" );
	define( "LOGFILE_USER_ITEMCHANGE", "user.itemchange.log" );
	
	/**
	 * keep given days of logfiles
	 * @var int    
	 */
	define( "KEEP_NUM_LOGS", 9 );	
	 
	define( "TEMPDIR", TBW_ROOT."tmp/" );
	define( "KEYFILE", "ipcKey" );
	define( "IPCPROJCHAR", "T" );
	define( "IPC_MSG_INTERVAL", 2 );
	
	define( "MYSQL_LOGDB_HOST", "localhost" );
	define( "MYSQL_LOGDB_USER", "tbw" );
	define( "MYSQL_LOGDB_PASS", "tbwpass" );
	define( "MYSQL_LOGDB_DB", "tbw" );

    date_default_timezone_set("Europe/Berlin");
    
	define( "INGAMEMESSAGES_MAX_RECIPIENTS", 2 );
	define( "INGAMEMESSAGES_MAX_SUBJECT_LENGTH", 32 );
	define( "INGAMEMESSAGES_MAX_TEXT_LENGTH", 5048 );

	define("MSGTYPE_BATTLE", 0);
	define("MSGTYPE_SPY", 1);
	define("MSGTYPE_FLEET", 2);
	define("MSGTYPE_ALLY", 3);
	define("MSGTYPE_SENT", 4);
	define("MSGTYPE_USER", 5);
	define("MSGTYPE_MAX", 6);
	
	$g_MSGTYPE_NAMES = array();
	$g_MSGTYPE_NAMES[MSGTYPE_BATTLE] = "Kämpfe";
	$g_MSGTYPE_NAMES[MSGTYPE_SPY] = "Spionage";
	$g_MSGTYPE_NAMES[MSGTYPE_FLEET] = "Flotte";
	$g_MSGTYPE_NAMES[MSGTYPE_ALLY] = "Verbündete";
	$g_MSGTYPE_NAMES[MSGTYPE_SENT] = "Postausgang";
	$g_MSGTYPE_NAMES[MSGTYPE_USER] = "Benutzernachrichten";
	$g_MSGTYPE_NAMES[MSGTYPE_MAX] = "MAX";
