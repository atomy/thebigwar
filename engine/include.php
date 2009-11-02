<?php
	if ( !defined( "TBW_ROOT" ) )
		define( "TBW_ROOT", "" );
		
	require_once( TBW_ROOT.'include/config_inc.php' );
	
	define('start_mtime', microtime(true));
	ini_set( "session.gc_maxlifetime", 3600 );
	error_reporting(2047);
	ignore_user_abort(false);
	#set_time_limit(300); # cant set timelimit in safe mode, why set a time limit?!

	$this_filename = '/engine/include.php';
	$__FILE__ = str_replace('\\', '/', __FILE__);
	if(substr($__FILE__, -strlen($this_filename)) !== $this_filename)
	{
		echo 'Der absolute Pfad der Datenbank konnte nicht ermittelt werden. Bitte gib ihn in der Datei /engine/include.php an.';
		exit(1);
	}
	define('s_root', substr($__FILE__, 0, -strlen($this_filename)));
	if(isset($_SERVER['SCRIPT_FILENAME']) && isset($_SERVER['PHP_SELF']) && substr($_SERVER['SCRIPT_FILENAME'], -strlen($_SERVER['PHP_SELF'])) == $_SERVER['PHP_SELF'])
		$document_root = substr(realpath($_SERVER['SCRIPT_FILENAME']), 0, -strlen($_SERVER['PHP_SELF']));
	elseif(isset($_SERVER['DOCUMENT_ROOT']))
		$document_root = $_SERVER['DOCUMENT_ROOT'];
	else $document_root = '/';

	if(substr($document_root, -1) == '/')
		$document_root = substr($document_root, 0, -1);
	define('h_root', substr(s_root, strlen($document_root)));

	if(isset($_GET['nossl']))
	{
#		if($_GET['nossl'] && (!isset($_COOKIE['use_ssl']) || $_COOKIE['use_ssl']))
#		{
			setcookie('use_ssl', '0', time()+4838400, h_root);
			$_COOKIE['use_ssl'] = '0';
#		}
#		elseif(!$_GET['nossl'] && isset($_COOKIE['use_ssl']) && !$_COOKIE['use_ssl'])
#		{
#			setcookie('use_ssl', '1', time()+4838400, h_root);
#			$_COOKIE['use_ssl'] = '1';
#		}
	}

	function global_setting( $key, $value=NULL )
	{
		static $settings;

		if( $value == NULL )
		{
			if( !isset( $settings[ $key ] ) ) 
				return null;
			else 
				return $settings[ $key ];
		}
		else
		{
			$settings[ $key ] = $value;
			
			return $settings[ $key ];
		}
	}


	$GDB_DIR = s_root.'/database.global';
	global_setting('GDB_DIR', $GDB_DIR);
	global_setting('DB_NEWS', $GDB_DIR.'/news');
	global_setting('DB_CHANGELOG', $GDB_DIR.'/changelog');
	global_setting('DB_PRANGER', $GDB_DIR.'/pranger');
	global_setting('DB_VERSION', $GDB_DIR.'/version');
	global_setting('DB_REVISION', $GDB_DIR.'/revision');
	global_setting('DB_MESSENGERS', $GDB_DIR.'/messengers');
	global_setting('DB_NOTIFICATIONS', $GDB_DIR.'/notifications');
	global_setting('DB_EVENTHANDLER_LOG', s_root.'/logs/eventhandler.log');
	global_setting('DB_EVENTHANDLER_PIDFILE', $GDB_DIR.'/eventhandler.pid');
	global_setting('EVENTHANDLER_INTERVAL', 2);
	global_setting('THS_HTML', '&nbsp;');
	global_setting('THS_UTF8', "\xc2\xa0");
	global_setting('MIN_CLICK_DIFF', 0.3); # Sekunden, die zwischen zwei Klicks mindestens vergehen muessen, sonst Bremsung
	global_setting('EMAIL_FROM', 'atomy@jackinpoint.net');
	global_setting('MAX_PLANETS', 15);
	global_setting('LIST_MIN_CHARS', 1); # Fuer Ajax-Auswahllisten
	global_setting('ALLIANCE_RENAME_PERIOD', 3); # Minimalabstand fuers Umbenennen von Allianzen in Tagen
	global_setting('PROTOCOL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'http' : 'http');
	global_setting('USE_PROTOCOL', (isset($_SESSION['use_protocol']) ? $_SESSION['use_protocol'] : (((!isset($_COOKIE['use_ssl']) || $_COOKIE['use_ssl'])) ? 'http' : 'http')));

	function define_globals( $DB )
	{
		global $databases;
		if(!isset($databases))
			$databases = get_databases();

		if(!isset($databases[$DB])) return false;

		global_setting('DB', $DB);

		$DB_DIR = $databases[$DB][0];
		if(substr($DB_DIR, 0, 1) != '/')
			$DB_DIR = s_root.'/'.$DB_DIR;

		global_setting('DB_DIR', $DB_DIR);

		global_setting('EVENT_FILE', $DB_DIR.'/events');
		global_setting('DB_LOCKED', $DB_DIR.'/locked');
		global_setting('DB_ALLIANCES', $DB_DIR.'/alliances');
		global_setting('DB_FLEETS', $DB_DIR.'/fleets');
		global_setting('DB_PLAYERS', $DB_DIR.'/players');
		global_setting('DB_UNIVERSE', $DB_DIR.'/universe');
		global_setting('DB_ITEMS', $DB_DIR.'/items');
		global_setting('DB_ITEM_DB', $DB_DIR.'/items.db');
		global_setting('DB_MESSAGES', $DB_DIR.'/messages');
		global_setting('DB_MESSAGES_PUBLIC', $DB_DIR.'/messages_public');
		global_setting('DB_HIGHSCORES', $DB_DIR.'/highscores');
		global_setting('DB_TRUEMMERFELDER', $DB_DIR.'/truemmerfelder');
		global_setting('DB_HANDEL', $DB_DIR.'/handel');
		global_setting('DB_HANDELSKURS', $DB_DIR.'/handelskurs');
		global_setting('DB_ADMINS', $DB_DIR.'/admins');
		global_setting('DB_NONOOBS', $DB_DIR.'/nonoobs');
		global_setting('DB_NO_ATTS', $DB_DIR.'/no_atts');
		global_setting('DB_ADMIN_LOGFILE', $DB_DIR.'/admin_logfile');
		global_setting('DB_NO_STRICT_ROB_LIMITS', $DB_DIR.'/no_strict_rob_limits');
		global_setting('DB_GLOBAL_TIME_FACTOR', $DB_DIR.'/global_time_factor');
		global_setting('DB_GLOBAL_PROD_FACTOR', $DB_DIR.'/global_prod_factor');
		global_setting('DB_GLOBAL_COST_FACTOR', $DB_DIR.'/global_cost_factor');

		return true;
	}

	function __autoload($class)
	{
		if(strtolower($class) == 'items') $class = 'Item';
		$filename = s_root.'/engine/classes/'.strtolower($class).'.php';
		if(is_file($filename) && is_readable($filename)) require_once($filename);
	}

	__autoload('Classes');

	//if(isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml') !== false)
	//	define('CONTENT_TYPE', 'application/xhtml+xml; charset=UTF-8');
	//else
		define('CONTENT_TYPE', 'text/html; charset=UTF-8');
	header('Content-type: '.CONTENT_TYPE);

	if(!isset($USE_OB) || $USE_OB)
		ob_start('ob_gzhandler');
	$tabindex = 1;

	if(!isset($LOGIN) || !$LOGIN)
		check_hostname();

	if(!isset($_SESSION))
		$GLOBALS['_SESSION'] = array();

	# Namen der Nachrichtensorten
	$message_type_names = array (
		1 => 'Kämpfe',
		2 => 'Spionage',
		3 => 'Transport',
		4 => 'Sammeln',
		5 => 'Besiedelung',
		6 => 'Benutzernachrichten',
		7 => 'Verbündete',
		8 => 'Postausgang'
	);

	# Namen der Flottenauftragsarten
	$type_names = array (
		1 => 'Besiedeln',
		2 => 'Sammeln',
		3 => 'Angriff',
		4 => 'Transport',
		5 => 'Spionieren',
		6 => 'Stationieren'
	);

	# Maximales Alter in Tagen der Nachrichtensorten
	$message_type_times = array (
		1 => 3,
		2 => 3,
		3 => 2,
		4 => 2,
		5 => 1,
		6 => 5,
		7 => 4,
		8 => 2
	);
	# Fuer veroeffentlichte Nachrichten
	$public_messages_time = 365;

	# Zu jeder Flottenauftragsart die zugehoerige Nachrichtensorte
	$types_message_types = array(1=>5, 2=>4, 3=>1, 4=>3, 5=>2, 6=>3);

	function &stripslashes_r(&$var)
	{ # Macht rekursiv in einem Array addslashes() rueckgaengig
		if(is_array($var))
        {
            foreach($var as $key=>$val)
            stripslashes_r($var[$key]);
        }
        else
			$var = stripslashes($var);
		return $var;
	}
	# magic_quotes_gpc abschalten
	if(get_magic_quotes_gpc())
	{
		stripslashes_r($_POST);
		stripslashes_r($_GET);
		stripslashes_r($_COOKIE);
	}


	########################################
	### Hier beginnen die Klassen
	########################################

	class truemmerfeld
	{ # Bearbeitet Truemmerfelder
		function get($galaxy, $system, $planet)
		{
			# Bekommt die Groesse eines Truemmerfelds

			if(!is_file(global_setting("DB_TRUEMMERFELDER").'/'.$galaxy.'_'.$system.'_'.$planet))
				return array(0, 0, 0, 0);
			elseif(!is_readable(global_setting("DB_TRUEMMERFELDER").'/'.$galaxy.'_'.$system.'_'.$planet))
				return false;
			else
			{
				$string = file_get_contents(global_setting("DB_TRUEMMERFELDER").'/'.$galaxy.'_'.$system.'_'.$planet);

				$rohstoffe = array('', '', '', '');

				$index = 0;
				for($i = 0; $i < strlen($string); $i++)
				{
					$bin = add_nulls(decbin(ord($string[$i])), 8);
					$rohstoffe[$index] .= substr($bin, 0, -1);
					if(!substr($bin, -1)) # Naechste Zahl
						$index++;
				}
				for($rohstoff = 0; $rohstoff < 4; $rohstoff++)
				{
					if($rohstoffe[$rohstoff] == '')
						$rohstoffe[$rohstoff] = 0;
					else
						$rohstoffe[$rohstoff] = base_convert($rohstoffe[$rohstoff], 2, 10);
				}

				return array($rohstoffe[0], $rohstoffe[1], $rohstoffe[2], $rohstoffe[3]);
			}
		}

		function add($galaxy, $system, $planet, $carbon=0, $aluminium=0, $wolfram=0, $radium=0)
		{
			# Fuegt einem Truemmerfeld Rohstoffe hinzu
			$old = truemmerfeld::get($galaxy, $system, $planet);
			if($old === false)
				return false;
			$old[0] += $carbon;
			$old[1] += $aluminium;
			$old[2] += $wolfram;
			$old[3] += $radium;

			return truemmerfeld::set($galaxy, $system, $planet, $old[0], $old[1], $old[2], $old[3]);
		}

		function sub($galaxy, $system, $planet, $carbon=0, $aluminium=0, $wolfram=0, $radium=0)
		{
			# Zieht einem Truemmerfeld Rohstoffe ab
			$old = truemmerfeld::get($galaxy, $system, $planet);
			if($old === false)
				return false;
			$old[0] -= $carbon;
			$old[1] -= $aluminium;
			$old[2] -= $wolfram;
			$old[3] -= $radium;

			if($old[0] < 0)
				$old[0] = 0;
			if($old[1] < 0)
				$old[1] = 0;
			if($old[2] < 0)
				$old[2] = 0;
			if($old[3] < 0)
				$old[3] = 0;

			return truemmerfeld::set($galaxy, $system, $planet, $old[0], $old[1], $old[2], $old[3]);
		}

		function set($galaxy, $system, $planet, $carbon=0, $aluminium=0, $wolfram=0, $radium=0)
		{
			if($carbon <= 0 && $aluminium <= 0 && $wolfram <= 0 && $radium <= 0)
			{
				if(is_file(global_setting("DB_TRUEMMERFELDER").'/'.$galaxy.'_'.$system.'_'.$planet))
					return unlink(global_setting("DB_TRUEMMERFELDER").'/'.$galaxy.'_'.$system.'_'.$planet);
				else
					return true;
			}

			$new = array(
				base_convert($carbon, 10, 2),
				base_convert($aluminium, 10, 2),
				base_convert($wolfram, 10, 2),
				base_convert($radium, 10, 2)
			);

			$string = '';

			for($i = 0; $i < 4; $i++)
			{
				if(strlen($new[$i])%7)
					$new[$i] = str_repeat('0', 7-strlen($new[$i])%7).$new[$i];

				$strlen = strlen($new[$i]);
				for($j = 0; $j < $strlen; $j+=7)
				{
					if($j == $strlen-7)
						$suf = '0';
					else
						$suf = '1';
					$string .= chr(bindec(substr($new[$i], $j, 7).$suf));
				}
			}

			unset($new);

			# Schreiben
			$fh = fopen(global_setting("DB_TRUEMMERFELDER").'/'.$galaxy.'_'.$system.'_'.$planet, 'w');
			if(!$fh)
				return false;
			flock($fh, LOCK_EX);
			fwrite($fh, $string);
			flock($fh, LOCK_UN);
			fclose($fh);

			return true;
		}
	}

	########################################
	### Hier beginnen die Funktionen
	########################################

	function get_skins()
	{
		# Vorgegebene Skins-Liste bekommen
		$skins = array();
		if(is_dir(s_root.'/login/style') && is_readable(s_root.'/login/style'))
		{
			$dh = opendir(s_root.'/login/style');
			while(($fname = readdir($dh)) !== false)
			{
				if($fname[0] == '.') continue;
				$path = s_root.'/login/style/'.$fname;
				if(!is_dir($path) || !is_readable($path)) continue;
				if(!is_file($path.'/types') || !is_readable($path.'/types')) continue;
				$skins_file = preg_split("/\r\n|\r|\n/", file_get_contents($path.'/types'));
				$new_skin = &$skins[$fname];
				$new_skin = array(array_shift($skins_file), array());
				foreach($skins_file as $skins_line)
				{
					$skins_line = explode("\t", $skins_line, 3);
					if(count($skins_line) < 3)
						continue;
					$new_skin[1][$skins_line[0]] = array($skins_line[1], explode(' ', $skins_line[2]));
				}
				unset($new_skin);
			}
			closedir($dh);
		}
		return $skins;
	}

	function get_version()
	{
		# Aktuell installierte Version herausfinden
		$version = '';
		if(is_file(global_setting("DB_VERSION")) && is_readable(global_setting("DB_VERSION")))
			$version = trim(file_get_contents(global_setting("DB_VERSION")));
		return $version;
	}

	function get_revision()
	{
		# Aktuell laufende Revision herausfinden

		if(!is_dir(s_root.'/.svn')) return false;

		$revision_file = global_setting("DB_REVISION");
		$entries_file = s_root.'/.svn/entries';

		if(!is_file($revision_file) && !is_file($entries_file)) return false;

		if(is_file($entries_file))
		{
			if(!is_file($revision_file) || filemtime($entries_file) > filemtime($revision_file))
			{
				# Update revision file
				if(!function_exists('simplexml_load_file')) return false;
				$entries_xml = new DomDocument;
				@$entries_xml->loadXML(file_get_contents($entries_file), LIBXML_NSCLEAN);
				if(!$entries_xml) return false;

				$new_revision = false;
				foreach($entries_xml->getElementsByTagName('entry') as $e)
				{
					if($e->hasAttribute('name') && $e->getAttribute('name') == '' && $e->hasAttribute('revision'))
					{
						$new_revision = $e->getAttribute('revision');
						break;
					}
				}
				if($new_revision === false) return false;

				file_put_contents($revision_file, $new_revision, LOCK_EX);
			}
		}

		return floor(file_get_contents($revision_file));
	}

	function get_databases()
	{
		# Liste der Runden/Universen herausfinden
		if(!is_file(global_setting("GDB_DIR").'/databases') || !is_readable(global_setting("GDB_DIR").'/databases'))
			return false;

		$databases = preg_split("/\r\n|\r|\n/", file_get_contents(global_setting("GDB_DIR").'/databases'));
		array_shift($databases);

		$return = array();
		foreach($databases as $database)
		{
			$database = explode("\t", $database, 4);
			if(count($database) < 4)
				continue;
			$return[array_shift($database)] = $database;
		}

		return $return;
	}

	function get_default_hostname()
	{
		# Den Hostnamen herausfinden, der fuer die Startseite verwendet werden soll

		# Die folgende Zeile auskommentieren, um diese Funktion zu deaktivieren
		#return (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : false);

		if(!is_file(global_setting("GDB_DIR").'/databases') || !is_readable(global_setting("GDB_DIR").'/databases'))
			return false;

		$fh = fopen(global_setting("GDB_DIR").'/databases', 'r');
		flock($fh, LOCK_SH);

		$hostname = trim(fgets($fh, 1024));

		flock($fh, LOCK_UN);
		fclose($fh);

		return $hostname;
	}

	function check_hostname()
	{
		# Leitet weiter, wenn der Hostname nicht dem Hostnamen entspricht, der verwendet werden soll
		if(isset($_SERVER['HTTP_HOST']))
		{
			$hostname = $_SERVER['HTTP_HOST'];
			$real_hostname = get_default_hostname();
			if(isset($_SESSION['database']))
			{
				$databases = get_databases();
				if(isset($databases[$_SESSION['database']]))
					$real_hostname = $databases[$_SESSION['database']][2];
			}

			if($real_hostname)
			{
				$request_uri = $_SERVER['REQUEST_URI'];
				if(strpos($request_uri, '?') !== false)
					$request_uri = substr($request_uri, 0, strpos($request_uri, '?'));

				if(strtolower($hostname) == strtolower($real_hostname) && substr($request_uri, -1) != '/')
					return true;

				$url = global_setting("PROTOCOL").'://'.$real_hostname.$_SERVER['PHP_SELF'];
			
				if($_SERVER['QUERY_STRING'] != '')
					$url .= '?'.$_SERVER['QUERY_STRING'];
				header('Location: '.$url, true, 307);

				if(count($_POST) > 0)
				{
					echo '<form action="'.htmlentities($url).'" method="post">';
					foreach($_POST as $key=>$val)
						echo '<input type="hidden" name="'.htmlentities($key).'" value="'.htmlentities($val).'" />';
					echo '<button type="submit">'.htmlentities($url).'</button>';
					echo '</form>';
				}
				else
					echo 'HTTP redirect: <a href="'.htmlentities($url).'">'.htmlentities($url).'</a>';
				die();
			}
		}
	}

	function get_admin_list()
	{
		# Gibt eine Liste aller Administratoren zurueck
		$admins = array();
		if(!is_file(global_setting("DB_ADMINS")) || !is_readable(global_setting("DB_ADMINS")))
			return false;
		$admin_file = preg_split("/\r\n|\r|\n/", file_get_contents(global_setting("DB_ADMINS")));
		foreach($admin_file as $line)
		{
			$line = explode("\t", $line);
			if(count($line) < 2)
				continue;

			$this_admin = &$admins[urldecode(array_shift($line))];
			$this_admin = array();
			$this_admin['password'] = array_shift($line);
			$this_admin['permissions'] = $line;

			unset($this);
		}

		return $admins;
	}

	function write_admin_list($admins)
	{
		# Speichert eine mit get_admin_list() geholte Liste wieder ab
		$admin_file = array();
		foreach($admins as $name=>$settings)
		{
			$this_admin = &$admin_file[];
			$this_admin = $name;
			$this_admin .= "\t".$settings['password'];
			if(count($settings['permissions']) > 0)
				$this_admin .= "\t".implode("\t", $settings['permissions']);
			unset($this_admin);
		}

		$fh = fopen(global_setting("DB_ADMINS"), 'w');
		if(!$fh)
			return false;
		flock($fh, LOCK_EX);

		fwrite($fh, implode("\n", $admin_file));

		flock($fh, LOCK_UN);
		fclose($fh);

		return true;
	}

	########################################

	function format_btime($time2)
	{
		# Formatiert eine in Punkten angegebene Bauzeitangabe,
		# sodass diese auf den Seiten angezeigt werden kann
		# (zum Beispiel 2 Stunden, 5 Minuten und 30 Sekunden)

		$time = round($time2);
		$days = $hours = $minutes = $seconds = 0;

		if($time >= 86400)
		{
			$mod = $time%86400;
			$days = ($time-$mod)/86400;
			$time = $mod;
		}
		if($time >= 3600)
		{
			$mod = $time%3600;
			$hours = ($time-$mod)/3600;
			$time = $mod;
		}
		if($time >= 60)
		{
			$mod = $time%60;
			$minutes = ($time-$mod)/60;
			$time = $mod;
		}
		$seconds = $time;

		$return = array();
		if($time2 >= 86400)
		{
			if($days == 1)
				$days .= '&nbsp;Tag';
			else
				$days .= '&nbsp;Tage';
			$return[] = $days;
		}
		if($time2 >= 3600)
		{
			if($hours == 1)
				$hours .= '&nbsp;Stunde';
			else
				$hours .= '&nbsp;Stunden';
			$return[] = $hours;
		}
		if($time2 >= 60)
		{
			if($minutes == 1)
				$minutes .= '&nbsp;Minute';
			else
				$minutes .= '&nbsp;Minuten';
			$return[] = $minutes;
		}

		if($seconds == 1)
			$seconds .= '&nbsp;Sekunde';
		else
			$seconds .= '&nbsp;Sekunden';
		$return[] = $seconds;

		$return = implode(' ', $return);
		return $return;
	}

	function format_ress($ress, $tabs_count=0, $tritium=false)
	{
		# Erstellt eine Definitionsliste aus der uebergebenen
		# Rohstoffanzahl, beispielsweise fuer die Rohstoffkosten
		# der Gebaeude verwendbar

		$tabs = '';
		if($tabs_count >= 1)
			$tabs = str_repeat("\t", $tabs_count);

		$return = "<dl class=\"ress\">\n";
		$return .= $tabs."\t<dt class=\"ress-carbon\">Carbon</dt>\n";
		$return .= $tabs."\t<dd class=\"ress-carbon\">".ths($ress[0])."</dd>\n";
		$return .= $tabs."\t<dt class=\"ress-aluminium\">Aluminium</dt>\n";
		$return .= $tabs."\t<dd class=\"ress-aluminium\">".ths($ress[1])."</dd>\n";
		$return .= $tabs."\t<dt class=\"ress-wolfram\">Wolfram</dt>\n";
		$return .= $tabs."\t<dd class=\"ress-wolfram\">".ths($ress[2])."</dd>\n";
		$return .= $tabs."\t<dt class=\"ress-radium\">Radium</dt>\n";
		$return .= $tabs."\t<dd class=\"ress-radium\">".ths($ress[3])."</dd>\n";
		if($tritium)
		{
			$return .= $tabs."\t<dt class=\"ress-tritium\">Tritium</dt>\n";
			$return .= $tabs."\t<dd class=\"ress-tritium\">".ths($ress[4])."</dd>\n";
		}
		$return .= $tabs."</dl>\n";
		return $return;
	}

	function ths($count, $utf8=false, $round=0)
	{
		# Fuegt Tausendertrennzeichen ein
		# (Oben als THS_UTF8 und THS_HTML definiert)
		# Wenn $utf8 gesetzt ist, wird ein UTF-8-String
		# zurueckgeliefert, ansonsten ein HTML-String
		# $round gibt die Anzahl der Stellen an, auf die
		# gerundet werden soll

		if(!isset($count))
			$count = 0;
		if($round == 0)
			$count = floor($count);
		else
			$count = round($count, $round);

		$neg = false;
		if($count < 0)
		{
			$neg = true;
			if($round == 0)
				$count = (int) substr($count, 1);
			else
				$count = (double) substr($count, 1);
		}

		$ths = global_setting("THS_HTML");
		if($utf8)
			$ths = global_setting("THS_UTF8");
		$count = str_replace('.', $ths, number_format($count, $round, ',', '.'));

		if($neg)
			$count = '&minus;'.$count;

		return $count;
	}

	function utf8_htmlentities($string, $nospecialchars=false, $js=false)
	{
		# Das gleiche wie htmlentities(), nur fuer einen
		# UTF-8-String.
		# Ist $js gesetzt, wird ein JavaScript-String zurueckgeliefert
		# (mit \uXXXX)

		if($js)
			$rep = array("'\\\\u'.add_nulls(dechex(", "), 4)");
		else
			$rep = array("'&#'.(", ").';'");

		if(!$nospecialchars)
			$string = htmlspecialchars($string);

		$string = preg_replace("/([\\xc0-\\xdf])([\\x80-\\xbf])/e", $rep[0]."64*ord('$1')+ord('$2')-12416".$rep[1], $string);
		$string = preg_replace("/([\\xe0-\\xef])([\\x80-\\xbf])([\\x80-\\xbf])/e", $rep[0]."4096*ord('$1')+64*ord('$2')+ord('$3')-925824".$rep[1], $string);
		$string = preg_replace("/([\\xf0-\\xf7])([\\x80-\\xbf])([\\x80-\\xbf])([\\x80-\\xbf])/e", $rep[0]."262144*ord('$1')+2048*ord('$2')+64*ord('$3')+ord('$4')-63185024)".$rep[1], $string);

		return $string;
	}

	function utf8_jsentities($string)
	{
		return utf8_htmlentities($string, true, true);
	}

	function ob_utf8($string)
	{
		$now_mtime = microtime(true);
		$start_mtime = start_mtime;

		#$string .= '<!-- '.($now_mtime-$start_mtime).' -->'."\n";

		return utf8_encode($string);
	}

	if(!function_exists('array_product'))
	{
		function array_product($array)
		{
			$return = 1;
			foreach($array as $val)
				$return *= $val;
			return $return;
		}
	}

	function add_nulls($count, $len)
	{
		while(strlen($count) < $len)
			$count = '0'.$count;

		return $count;
	}

	function string2bin($string)
	{
		$return = '';

		$len = strlen($string);
		for($i = 0; $i < $len; $i++)
			$return .= add_nulls(decbin(ord($string[$i])), 8);

		return $return;
	}

	function bin2string($bin)
	{
		$return = '';

		$len = strlen($bin);
		for($i=0; $i < $len; $i+=8)
		{
			$substr = substr($bin, $i, 8);
			$return .= chr(bindec($substr));
		}

		return $return;
	}

	function diff($ao, $bo)
	{
		$diff = max($ao, $bo)-min($ao, $bo);

		return $diff;
	}

	function sort_koords($a, $b)
	{
		$a_expl = explode(':', $a);
		$b_expl = explode(':', $b);

		if($a_expl[0] > $b_expl[0])
			return 1;
		elseif($a_expl[0] < $b_expl[0])
			return -1;
		else
		{
			if($a_expl[1] > $b_expl[1])
				return 1;
			elseif($a_expl[1] < $b_expl[1])
				return -1;
			else
			{
				if($a_expl[2] > $b_expl[2])
					return 1;
				elseif($a_expl[2] < $b_expl[2])
					return -1;
				else
					return 0;
			}
		}
	}

	function parse_html($string)
	{
		$root = parse_html_get_element_information('div');

		$remaining_string = str_replace("\t", " ", preg_replace("/\r\n|\r|\n/", "\n", $string));
		$string = '';
		$open_elements = array();
		while(($next_bracket = strpos($remaining_string, '<')) !== false)
		{
			if($next_bracket != 0)
			{
				$string .= htmlspecialchars(substr($remaining_string, 0, $next_bracket));
				$remaining_string = substr($remaining_string, $next_bracket);
			}

			if(substr($remaining_string, 1, 1) == '/')
			{
				if(!preg_match('/^<\\/([a-z]+) *>/', $remaining_string, $match) || count($open_elements) <= 0 || $open_elements[count($open_elements)-1] != strtolower($match[1]))
				{
					$string .= '&lt;';
					$remaining_string = substr($remaining_string, 1);
				}
				else
				{
					$string .= '</'.strtolower($match[1]).'>';
					$remaining_string = substr($remaining_string, strlen($match[0]));
					array_pop($open_elements);
				}
				continue;
			}

			if(!preg_match('/^<([a-z]+)( |>)/i', $remaining_string, $match) || ($close_bracket = strpos($remaining_string, '>')) === false)
			{
				$string .= '&lt;';
				$remaining_string = substr($remaining_string, 1);
				continue;
			}

			$element_name = strtolower($match[1]);
			$info = parse_html_get_element_information($element_name);
			if(!$info)
			{
				$string .= '&lt;';
				$remaining_string = substr($remaining_string, 1);
				continue;
			}
			if(count($open_elements))
				$parent_info = parse_html_get_element_information($open_elements[count($open_elements)-1]);
			else
				$parent_info = $root;

			if(!in_array($element_name, $parent_info[0]))
			{
				$string .= '&lt;';
				$remaining_string = substr($remaining_string, 1);
				continue;
			}

			$part = substr($remaining_string, 0, $close_bracket);
			$part = ' '.substr($part, strlen($element_name)+2);

			if($part != ' ' && !preg_match('/^( +(xml:)?[a-z]+="[^"]*")*( *\\/)?$/i', $part))
			{
				$string .= '&lt;';
				$remaining_string = substr($remaining_string, 1);
				continue;
			}

			$closed = (substr($part, -1) == '/');
			if($closed)
				$part = substr($part, 0, -1);
			else
				$open_elements[] = $element_name;

			preg_match_all('/ +([a-z:]+)="([^"]*)"/i', $part, $attrs, PREG_SET_ORDER);
			$attrs2 = array();
			foreach($attrs as $attr)
			{
				if(!isset($info[1][strtolower($attr[1])]))
					continue;
				$attrs2[] = strtolower($attr[1]).'="'.$attr[2].'"';
				unset($info[1][strtolower($attr[1])]);
			}

			if(in_array(true, $info[1]))
			{
				$string .= '&lt;';
				$remaining_string = substr($remaining_string, 1);
				continue;
			}

			array_unshift($attrs2, '<'.$element_name);
			$string .= implode(' ', $attrs2);
			if($closed)
				$string .= ' />';
			else
				$string .= '>';

			$remaining_string = substr($remaining_string, $close_bracket+1);
		}

		$string .= htmlspecialchars($remaining_string);

		$open_elements = array_reverse($open_elements);
		foreach($open_elements as $el)
			$string .= '</'.$el.'>';

		# Zeilenumbruchstruktur aufbauen
		$string = preg_replace("/> *(\r\n|\r|\n) *</", "><", $string);

		$remaining_string = $string;
		$string = '';
		$open_elements = array();
		$p_open = false;
		$span = parse_html_get_element_information('span');
		while(($next_bracket = strpos($remaining_string, '<')) !== false)
		{
			if($next_bracket != 0)
			{
				$part = substr($remaining_string, 0, $next_bracket);
				if(count($open_elements))
					$parent_info = parse_html_get_element_information($open_elements[count($open_elements)-1]);
				else
					$parent_info = $root;
				if(parse_html_trim($part) != '' && in_array('span', $parent_info[0]))
				{
					if(!$p_open && count($open_elements) <= 0)
					{
						$string .= '<p>';
						$p_open = true;
					}
					if(in_array('br', $parent_info[0]))
					{
						if(count($open_elements) <= 0)
						{
							if(substr($part, -1) == "\n")
								$string .= preg_replace('/[\n]+/e', 'parse_html_repl_nl(strlen(\'$0\'))', substr($part, 0, -1));
							else
								$string .= preg_replace('/[\n]+/e', 'parse_html_repl_nl(strlen(\'$0\'))', $part);
						}
						else
						{
							if(substr($part, -1) == "\n")
								$string .= str_replace("\n", "<br />", substr($part, 0, -1));
							else
								$string .= str_replace("\n", "<br />", $part);
						}
					}
					else
						$string .= str_replace("\n", '', $part);
				}
				$remaining_string = substr($remaining_string, $next_bracket);
			}
			$close_bracket = strpos($remaining_string, '>');
			if(substr($remaining_string, 1, 1) == '/')
			{
				preg_match('/^<\\/([a-z]+) *>/', $remaining_string, $match);
				if(count($open_elements) > 0 && $open_elements[count($open_elements)-1] == $match[1])
					array_pop($open_elements);
			}
			elseif(preg_match('/^<([a-z]+)( |>)/', $remaining_string, $match))
			{
				if($p_open && !in_array($match[1], $span[0]))
				{
					$string .= "</p>\n";
					$p_open = false;
				}
				if(substr($remaining_string, $close_bracket-1, 1) != '/')
					$open_elements[] = $match[1];
			}

			$string .= substr($remaining_string, 0, $close_bracket+1);
			$remaining_string = substr($remaining_string, $close_bracket+1);
		}

		if(strlen($remaining_string) > 0 && trim($remaining_string) != '')
		{
			if(!$p_open)
			{
				$string .= '<p>';
				$p_open = true;
			}
			$string .= preg_replace('/[\n]+/e', 'parse_html_repl_nl(strlen(\'$0\'))', $remaining_string);
		}
		if($p_open)
			$string .= '</p>';

		$string = preg_replace('/&amp;(#[0-9]{1,6};)/', '&$1', $string);
		$string = preg_replace('/&amp;(#x[0-9a-fA-F]{1,4};)/', '&$1', $string);
		$string = preg_replace('/&amp;([a-zA-Z0-9]{2,8};)/', '&$1', $string);

		$string = str_replace("\n<p></p>", '<br /><br />', $string);

		$string = utf8_htmlentities($string, true);

		return $string;
	}

	function parse_html_get_element_information($element)
	{
		$elements = array(
			'div' => array('br div span table h4 h5 h6 a img em strong var code abbr acronym address blockquote cite dl dfn hr bdo ins kbd ul ol q samp var p', 'class title xml:lang dir datafld datasrc dataformates'),
			'span' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir datafld datasrc dataformates'),
			'table' => array('thead tbody tfoot', 'class title xml:lang dir summary'),
			'thead' => array('tr', 'class title xml:lang dir'),
			'tbody' => array('tr', 'class title xml:lang dir'),
			'tfoot' => array('tr', 'class title xml:lang dir'),
			'tr' => array('th td', 'class title xml:lang dir'),
			'td' => array('br span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir abbr colspan rowspan'),
			'th' => array('br span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir abbr colspan rowspan'),
			'caption' => array('br span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'h4' => array('br span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'h5' => array('br span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'h6' => array('br span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'a' => array('span img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir !href hreflang rel rev'),
			'img' => array('', 'class title xml:lang dir !src !alt longdesc'),
			'em' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'strong' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'var' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'code' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'abbr' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'acronym' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'address' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'blockquote' => array('div span table h4 h5 h6 a img em strong var code abbr acronym address blockquote cite dl dfn hr bdo ins kbd ul ol q samp var p', 'class title xml:lang dir cite'),
			'cite' => array('br span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'dl' => array('dt dd', 'class title xml:lang dir'),
			'dt' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'dd' => array('br span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'dfn' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'hr' => array('', 'class title xml:lang'),
			'bdo' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'ins' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir cite datetime'),
			'kbd' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'ul' => array('li', 'class title xml:lang dir'),
			'ol' => array('li', 'class title xml:lang dir'),
			'li' => array('br span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'q' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir cite'),
			'samp' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'var' => array('span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir'),
			'p' => array('br span a img em strong var abbr acronym cite dfn bdo ins kbd q samp var', 'class title xml:lang dir datafld datasrc dataformates')
		);

		if(!isset($elements[$element]))
			return false;

		$return = array(explode(' ', $elements[$element][0]), array());
		$el_attrs = explode(' ', $elements[$element][1]);
		foreach($el_attrs as $el_attr)
		{
			if(substr($el_attr, 0, 1) == '!')
				$return[1][substr($el_attr, 1)] = true;
			else
				$return[1][$el_attr] = false;
		}
		return $return;
	}

	function parse_html_nls($string, $minus1)
	{
		$string2 = $string;
		$string = preg_replace('/[\n]+/e', 'repl_nl(strlen(\'$0\')-$minus1);', utf8_htmlentities($player_info['description']));
		return $string;
	}

	function parse_html_repl_nl($len)
	{
		if($len == 1)
			return "<br />";
		elseif($len == 2)
			return "</p>\n<p>";
		elseif($len > 2)
			return "</p>\n".str_repeat('<br />', $len-2)."\n<p>";
	}

	function parse_html_trim($string)
	{
		while(strlen($string) > 0 && $string[0] == ' ')
			$string = substr($string, 1);
		while(strlen($string) > 0 && substr($string, -1) == ' ')
			$string = substr($string, 0, -1);
		return $string;
	}

	function report_error($error_number)
	{
		return mail('yourmail', 'Fehlermeldung auf T-B-W', 'Fehlernummer: '.$error_number);
	}

	function message_repl_nl($nls)
	{
		$len = strlen($nls);
		if($len == 1)
			return "<br />\n\t";
		elseif($len == 2)
			return "\n</p>\n<p>\n\t";
		elseif($len > 2)
			return "\n</p>\n".str_repeat('<br />', $len-2)."\n<p>\n\t";
	}

	function message_repl_links($a, $b, $c)
	{
		if(!session_id())
			return $a.$b.$c;

		$url2 = html_entity_decode($b);
		if(substr($url2, 0, 7) != 'http://')
		{
			$url3 = explode('#', $url2);
			$url3[0] = explode('?', $url3[0]);
			$url = array($url3[0][0]);
			if(isset($url3[0][1]))
				$url[1] = $url3[0][1];
			else
				$url[1] = '';
			if(isset($url3[1]))
				$url[2] = $url[1];
			else
				$url[2] = '';

			if($url[1] != '')
				$url[1] .= '&';
			$url[1] .= session_name().'='.urlencode(session_id());

			$url2 = $url[0].'?'.$url[1];
			if($url[2] != '')
				$url2 .= '#'.$url[2];
		}

		return $a.htmlentities($url2).$c;
	}

	function stdround(&$a, $d=0)
	{
		$f = pow(10, $d);
		$a *= $f;
		$i = floor($a+.5);
		$a = $i/$f;
		return $a;
	}

	function fancy_flock($file, $lock_flag)
	{
		if($lock_flag == LOCK_SH) $timeout = 1;
		else $timeout = 5;

		$flag = $lock_flag+LOCK_NB;

		$steps = $timeout*10000;
		for($i=0; $i<100; $i++)
		{
			if(flock($file, $flag)) return true;
			usleep($steps);
		}
		return false;
	}

	function fit_to_max($array, $max)
	{
		if(!is_array($array) || $max < 0) return false;

		$sum = 0;
		foreach($array as $k=>$v)
		{
			if($v<0) $array[$k] = 0;
			else $sum += $v;
		}

		if($sum > $max)
		{
			$f = $max/$sum;
			$sum = 0;
			global $_fit_to_max_usort;
			$_fit_to_max_usort = array();
			foreach($array as $k=>$v)
			{
				$new_c = $v*$f;
				$fl = ceil($new_c)-$new_c;
				if($fl > 0) $_fit_to_max_usort[$k] = $fl;
				$array[$k] = floor($new_c);
				$sum += $array[$k];
			}

			$remaining = $max-$sum;
			uksort($_fit_to_max_usort, "_fit_to_max_usort");
			while($remaining > 0 && count($_fit_to_max_usort) > 0)
			{
				foreach($_fit_to_max_usort as $k=>$v)
				{
					if($v <= 0) continue;
					$array[$k]++;
					if(--$remaining <= 0) break 2;
				}
			}
		}
		return $array;
	}

	function _fit_to_max_usort($a, $b)
	{
		global $_fit_to_max_usort;

		if($_fit_to_max_usort[$a] > $_fit_to_max_usort[$b]) return -1;
		elseif($_fit_to_max_usort[$a] < $_fit_to_max_usort[$b]) return 1;
		elseif($a > $b) return 1;
		elseif($a < $b) return -1;
		else return 0;
	}

	function get_messenger_info($type=false, $force_reload=false)
	{
		global $messengers_parsed_file;

		if(!isset($messenger_parsed_file) || $force_reload)
		{
			if(!is_file(global_setting("DB_MESSENGERS")) || !is_readable(global_setting("DB_MESSENGERS"))) $messenger_parsed_file = false;
			else
			{
				$messenger_parsed_file = parse_ini_file(global_setting("DB_MESSENGERS"), true);
				foreach($messenger_parsed_file as $k=>$v)
				{
					if(!is_array($v) || !isset($v['server']) || !isset($v['username']) || !isset($v['server']))
						unset($messenger_parsed_file[$k]);
				}
			}
		}

		if(!$messenger_parsed_file) return false;

		if($type)
		{
			if(!isset($messenger_parsed_file[$type])) return false;
			return $messenger_parsed_file[$type];
		}
		else return $messenger_parsed_file;
	}

	function get_global_factors($force_reload=false)
	{
		static $factors;

		if(!isset($factors) || $force_reload)
		{
			$factors = array('time' => 1, 'prod' => 1, 'cost' => 1);
			if(is_file(global_setting('DB_GLOBAL_TIME_FACTOR')) && is_readable(global_setting('DB_GLOBAL_TIME_FACTOR')))
			{
				$content = str_replace(',', '.', trim(file_get_contents(global_setting('DB_GLOBAL_TIME_FACTOR'))));
				if(strlen($content) > 0 && preg_match("/^[0-9]*(\.[0-9]+)?$/", $content))
					$factors['time'] = $content;
			}
			if(is_file(global_setting('DB_GLOBAL_PROD_FACTOR')) && is_readable(global_setting('DB_GLOBAL_PROD_FACTOR')))
			{
				$content = str_replace(',', '.', trim(file_get_contents(global_setting('DB_GLOBAL_PROD_FACTOR'))));
				if(strlen($content) > 0 && preg_match("/^[0-9]*(\.[0-9]+)?$/", $content))
					$factors['prod'] = $content;
			}
			if(is_file(global_setting('DB_GLOBAL_COST_FACTOR')) && is_readable(global_setting('DB_GLOBAL_COST_FACTOR')))
			{
				$content = str_replace(',', '.', trim(file_get_contents(global_setting('DB_GLOBAL_COST_FACTOR'))));
				if(strlen($content) > 0 && preg_match("/^[0-9]*(\.[0-9]+)?$/", $content))
					$factors['cost'] = $content;
			}
		}

		return $factors;
	}

	function database_locked()
  	{
  		        if(!file_exists(global_setting("DB_LOCKED"))) return false;

  		        if(!is_readable(global_setting("DB_LOCKED"))) return true;

  		        $until = trim(file_get_contents(global_setting("DB_LOCKED")));
  		        if($until && time() > $until)
  		        {
  		            unlink(global_setting("DB_LOCKED"));
  		            return false;
  		        }
  		        return ($until ? $until : true);
  	 }

	 function fleets_locked()
  	 {
  		        if(!file_exists(global_setting("DB_NO_ATTS"))) return false;

  		        if(!is_readable(global_setting("DB_NO_ATTS"))) return true;

  		        $until = trim(file_get_contents(global_setting("DB_NO_ATTS")));
  		        if($until && time() > $until)
  		        {
  		            unlink(global_setting("DB_NO_ATTS"));
  		            return false;
  		        }
  		        return ($until ? $until : true);
	 }

?>
