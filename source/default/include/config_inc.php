<?php
	
    // this is automatically set by phing, uncomment when not using phing prepare
    //define("TBW_ROOT", "/var/www/tbw-test.jackinpoint.net/htdocs/");

    define("GLOBAL_GAMEURL", "http://testuni.thebigwar.org/");
    define("GLOBAL_SGOACCNAME", "admin");
    define("GLOBAL_DEMOACCNAME", "demo");
    define("GLOBAL_DEMOACCPASS", "demo");    
    
    // url for news iframe used at front page
    define("TBW_EXT_NEWSURL", 'http://forum.thebigwar.org/ext/news.php');
	define("TBW_FORUM_URL", 'http://forum.thebigwar.org/');
    define("GLOBAL_MANTISURL", "https://mantis.jackinpoint.net/main_page.php");
    define("GLOBAL_CHANGELOGURL", "https://mantis.jackinpoint.net/changelog_page.php");    
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
    
    define( "TEAM_SUPPORT_MAILINGLIST", "team-support@list.thebigwar.org")

    date_default_timezone_set("Europe/Berlin");
?>
