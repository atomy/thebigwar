<?php

$root = $_SERVER['DOCUMENT_ROOT'];

require_once( $root.'/include/config_inc.php' );
require_once( TBW_ROOT.'admin/include/cadmin.php' );
require_once( TBW_ROOT.'engine/include.php' ); 

// get the relative path after root 
// e.g. http://tbw.localhost/tbw/admin/index.php gets /tbw/admin/
// needed for cookies path
$RELPATH = substr( $_SERVER['PHP_SELF'], 0, strrpos( $_SERVER['PHP_SELF'], "/" ) + 1 );
ini_set( "session.cookie_path", $RELPATH );

require_once( TBW_ROOT.'engine/include.php' );

	$actions = array(
		"0" => "%s hat sich angemeldet.",
		"1" => "%s hat sich als Geist unter dem Benutzer %s angemeldet.",
		"2" => "%s hat das Passwort des Benutzers %s geändert.",
		"2.1" => "%s hat die Werbung von %s eingeschaltet.",
		"2.2" => "%s hat die Werbung von %s ausgeschaltet.",
		"3" => "%s hat die Passwörter von %s und %s verglichen.",
		"4" => "%s hat den Benutzer %s gelöscht.",
		"5.1" => "%s hat den Benutzer %s gesperrt.",
		"5.2" => "%s hat den Benutzer %s entsperrt.",
		"6" => "%s hat den Benutzer %s nach %s umbenannt.",
		"7.1" => "%s hat den Anfängerschutz ausgeschaltet.",
		"7.2" => "%s hat den Anfängerschutz eingeschaltet.",
		"8.1" => "%s hat einen Eintrag zum Changelog hinzugefügt: %s",
		"8.2" => "%s hat einen Eintrag aus dem Changelog gelöscht: %s",
		"8.5" => "%s hat einen Eintrag zum Pranger hinzugefügt: %s",
		"8.6" => "%s hat einen Eintrag aus dem Pranger gelöscht: %s",
		"9" => "%s hat einen Eintrag eine Nachricht mit dem Betreff %s an %s versandt.",
		"10" => "%s hat den Logeintrag %s angeschaut.",
		"11.1" => "%s hat den Administrator %s hinzugefügt.",
		"11.2" => "%s hat die Rechte des Administrators %s verändert.",
		"11.3" => "%s hat den Administrator %s nach %s umbenannt.",
		"11.4" => "%s hat den Administrator %s entfernt.",
		"12.1" => "%s hat die Wartungsarbeiten eingeschaltet.",
		"12.2" => "%s hat die Wartungsarbeiten ausgeschaltet.",
		"13.1" => "%s hat das Spiel gesperrt.",
		"13.2" => "%s hat das Spiel entsperrt.",
		"14.1" => "%s hat einen Newseintrag mit dem Titel %s hinzugefügt.",
		"14.2" => "%s hat den Newseintrag mit dem Titel %s verändert.",
		"14.3" => "%s hat den Newseintrag mit dem Titel %s gelöscht.",
		"15.1" => "%s hat die Flottensperre eingeschaltet.",
		"15.2" => "%s hat die Flottensperre ausgeschaltet.",
		"18.1" => "%s verändert die Flotte von %s",
		"18.15" => "  %s addiert %s zur Flotte (%s)",
		"18.2" => "Flotte Ende",
		"18.3" => "%s verändert die Verteidigung von %s.",
		"18.35" => "  %s addiert %s zur Verteidigung (%s)",
		"18.4" => "Verteidigung Ende",
		"19.1" => "%s verändert die Gebäude von %s",
		"19.15" => "  %s addiert %s zum Gebäude (%s)",
		"19.2" => "Gebäude Ende",
		"19.3" => "%s verändert die Forschung von %s.",
		"19.35" => "  %s addiert %s zur Forschung (%s)",
		"19.4" => "Forschung Ende"
	);

#	if(global_setting("PROTOCOL") != 'https')
#	{
#		$url = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
##		header('Location: '.$url, true, 307);
#		die('Please use SSL: <a href="'.htmlentities($url).'">'.htmlentities($url).'</a>');
#	}

    // check if session id is available via GET, if not, use cookies
    if ( isset($_GET["PHPSESSID"] ) )
        session_id( $_GET["PHPSESSID"] );
    else if ( isset( $_COOKIE['PHPSESSID'] ) )
    	session_id( $_COOKIE['PHPSESSID'] );
		
	session_start();

	$ipcheck = isset( $_SESSION['ip'] ) && $_SESSION['ip'] != $_SERVER['REMOTE_ADDR'];
	$logout = isset( $_GET['logout'] ) && $_GET['logout'];
	$timeout = isset( $_SESSION['last_admin_access']) && time()-$_SESSION['last_admin_access'] > 900;

	if( $ipcheck || $logout	|| $timeout	)
	{
		if(isset($_COOKIE[session_name()]))
			setcookie(session_name(), '', 0, $RELPATH);
			
		unset($_SESSION);
		$_SESSION = array();
	}
	if((isset($_GET['logout']) && $_GET['logout']) || (isset($_SESSION['last_admin_access']) && time()-$_SESSION['last_admin_access'] > 900))
	{
		session_destroy();
		
		if(isset($_COOKIE[session_name()]))
			setcookie(session_name(), '', 0, $RELPATH);
	}

	$databases = get_databases();
	
	if(isset($_SESSION['database']) && isset($databases[$_SESSION['database']]))
	{
		define_globals($_SESSION['database']);
		$admins = get_admin_list();
	}

	if(!isset($_SESSION['admin_username']) || !isset($admins) || !isset($admins[$_SESSION['admin_username']]))
	{
		$show_login = true;
		if(isset($_POST['admin_username']) && isset($_POST['admin_password']) && isset($_POST['database']) && isset($databases[$_POST['database']]))
		{
			define_globals($_POST['database']);
			$admins = get_admin_list();

			if((isset($admins[$_POST['admin_username']]) && md5($_POST['admin_password']) == $admins[$_POST['admin_username']]['password']))
			{
				$_SESSION['admin_username'] = $_POST['admin_username'];
				$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
				$_SESSION['debug'] = true;
				$_SESSION['database'] = $_POST['database'];
				$show_login = false;
				
				protocol("0", $_SESSION['database']);
			}
		}

		if($show_login)
		{
			admin_gui::html_head();

			$request_uri = $_SERVER['PHP_SELF'];
			$request_string = array();
			foreach($_GET as $key=>$val)
			{
				if($key != 'logout')
					$request_string[] = urlencode($key).'='.urlencode($val);
			}
			$request_string = implode('&', $request_string);
			if($request_string != '')
				$request_uri .= '?'.$request_string;
?>
<form action="<?php echo htmlentities($request_uri)?>" method="post">
	<dl>
		<dt><label for="admin-runde-select">Runde</label></dt>
		<dd><select name="database">
<?php
			foreach($databases as $id=>$info)
			{
?>
			<option value="<?php echo utf8_htmlentities($id)?>"><?php echo utf8_htmlentities($info[1])?></option>
<?php
			}
?>
		</select></dd>

		<dt><label for="admin-benutzername-input">Benutzername</label></dt>
		<dd><input type="text" name="admin_username" id="admin-benutzername-input" /></dd>

		<dt><label for="admin-passwort-input">Passwort</label></dt>
		<dd><input type="password" name="admin_password" id="admin-passwort-input" /></dd>
	</dl>
	<div><button type="submit">Anmelden</button></div>
</form>
<ul>
	<li><a href="http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'].h_root)?>/index.php">Zurück zum Spiel</a></li>
</ul>

<?php
			admin_gui::html_foot();
			die();
		}
	}
 
	$admin_array = &$admins[$_SESSION['admin_username']];
	$adminObj = new CAdmin($admin_array, $_SESSION['admin_username']);
	
	$_SESSION['last_admin_access'] = time();

	class admin_gui
	{
		public static function html_head()
		{
?>

<?php echo '<?php echo xml version="1.0" encoding="UTF-8"?>'."\n"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
		<head>
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<title>T-B-W &ndash; Adminbereich</title>
		<link rel="stylesheet" href="<?php echo htmlspecialchars(h_root.'/admin/style.css')?>" type="text/css" />
		<script type="text/javascript" src="<?php echo htmlspecialchars(h_root.'/login/scripts.js.php')?>"></script>
		<script type="text/javascript" src="<?php echo htmlspecialchars(h_root.'/sarissa.js')?>"></script>
		<script type="text/javascript" src="<?php echo htmlspecialchars(h_root.'/javascript/jQuery.js')?>"></script>
	</head>
	<body>
		<a name="top"></a>
		<fieldset><legend>TBW Admin-Interface (Version <?php echo get_version().".".getVersion()?>)</legend>
		<?php 
		if ( isset($_SESSION['admin_username']) )
			print "<div>Logged in as <b>".$_SESSION['admin_username']."</b></div>";
		?>
		<h1><a href="<?php echo htmlentities(h_root.'/admin/index.php')?>"><abbr title="The Big War" xml:lang="en">T-B-W</abbr> &ndash; Adminbereich</a> [&nbsp;<a href="?logout=1">Abmelden nicht vergessen!</a>&nbsp;]</h1>
<?php
		}

		public static function html_foot()
		{
?>
	</body>
</html>
<?php
		}
	}

	function protocol($type)
	{
		$fh = fopen(global_setting("DB_ADMIN_LOGFILE"), "a");
		if(!$fh) return false;
		flock($fh, LOCK_EX);
		fwrite($fh, session_id()."\t".time()."\t".$_SESSION['admin_username']);
		foreach(func_get_args() as $arg)
			fwrite($fh, "\t".preg_replace("/[\n\t]/", " ", $arg));
		fwrite($fh, "\n");
		flock($fh, LOCK_UN);
		fclose($fh);
		return true;
	}
?>
