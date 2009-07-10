<?php
	$__FILE__ = str_replace("\\", "/", __FILE__);
	$include_filename = dirname($__FILE__).'/../../engine/include.php';
	$LOGIN = true;
	require_once($include_filename);

	$resume = false;
	$del_email_passwd = false;
	session_start();
	header('Cache-Control: no-cache', true);

	$databases = get_databases();
	if(isset($_SESSION['database']) && isset($databases[$_SESSION['database']]))
		define_globals($_SESSION['database']);

	if(!isset($_SESSION['username']) || !isset($_SESSION['database']) || (isset($_SESSION['database']) && (!isset($databases[$_SESSION['database']]) || !User::userExists($_SESSION['username']))))
	{
		if(isset($_REQUEST['username']) && isset($_REQUEST['password']) && isset($_REQUEST['database']))
		{
			# Anmelden

			if(!isset($databases[$_REQUEST['database']]))
				$loggedin = false;
			else
			{
				define_globals($_REQUEST['database']);
				if(!User::userExists($_REQUEST['username']))
					$loggedin = false;
				else
				{
					$me = Classes::User($_REQUEST['username']);
					if(!$me->checkPassword($_REQUEST['password']))
						$loggedin = false;
					else
						$loggedin = true;
				}
			}
		}
		else
			$loggedin = false;

		if(!$loggedin)
		{
			# Auf die Startseite zurueckleiten
			$url = explode('/', $_SERVER['PHP_SELF']);
			array_pop($url); array_pop($url);
			$url = 'http://'.get_default_hostname().implode('/', $url).'/index.php';
			if(!isset($_REQUEST['username']) || !isset($_REQUEST['password']))
			{
				header('Location: '.$url, true, 303);
				die('Not logged in. Please <a href="'.htmlentities($url).'">relogin</a>.');
			}
			else
				die('Anmeldung fehlgeschlagen. Haben Sie sich bereits registriert und Ihren Benutzernamen und Ihr Passwort korrekt in die zugehörigen Felder über dem Anmelden-Button eingetragen? Haben Sie Groß-Klein-Schreibung beim Passwort beachtet? <a href="'.htmlentities($url).'">Probieren Sie es noch einmal.</a>');
		}
		else
		{
			# Session aktualisieren
			$_SESSION['username'] = $_REQUEST['username'];
			$_SESSION['act_planet'] = 0;
			$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['database'] = $_REQUEST['database'];
			$_SESSION['use_protocol'] = global_setting("USE_PROTOCOL");
			$_SESSION['freshlogin'] = true;
			$resume = true;
			$del_email_passwd = true;
		}
	}
	# Schnellklicksperre
	$now_time = array_sum(explode(' ', microtime()));
	if(!isset($_SESSION['last_click_sleep']))
		$_SESSION['last_click_sleep'] = 0;
	#if(isset($_SESSION['last_click']) && (!isset($_SESSION['last_click_ignore']) || !$_SESSION['last_click_ignore']))
	#{
		#$last_click_diff = $now_time-$_SESSION['last_click']-pow($_SESSION['last_click_sleep'], 1.5);
		#if($last_click_diff < global_setting("MIN_CLICK_DIFF"))
		#{
			#$_SESSION['last_click_sleep']++;
			#$sleep_time = round(pow($_SESSION['last_click_sleep'], 1.5));
			#sleep($sleep_time);
		#}
		#else
			#$_SESSION['last_click_sleep'] = 0;
	#}
	#Neue Schnellklicksperre
	if($_SESSION['last_click_sleep'] > 0)
	{
		$now_time = $now_time-$_SESSION['last_click_sleep'];	
	}
	if(!isset($_SESSION['freshlogin']) && isset($_SESSION['last_click']) && !isset($_SESSION['admin_username']) && (!isset($_SESSION['last_click_ignore']) || !$_SESSION['last_click_ignore']))
	{
			
			
			$last_click_diff = $now_time-$_SESSION['last_click'];
			if($last_click_diff < 0.4)
			{
				$_SESSION['last_click_sleep'] = 1.5;
				die('Schnellklicksperre. Bitte warten Sie 1,5 Sekunden mit der n�chsten Spielaktion, Sie geraten sonst erneut in die Schnellklicksperre. Bitte benutzen Sie den Back Button Ihres Browsers, um zum Spiel zurückzukehren.');				
			}		
	}
	if(isset($_SESSION['last_click_ignore']))
		unset($_SESSION['last_click_ignore']);
		$_SESSION['last_click_sleep'] = 0;
	$_SESSION['last_click'] = $now_time;

	# Skins bekommen
	$skins = get_skins();

	# Version herausfinden
	$version = get_version();
	define('VERSION', $version);

	$me = Classes::User($_SESSION['username']);
	$_SESSION['username'] = $me->getName();

	if(!$me->getStatus())
	{
		login_gui::html_head();
?>
<p class="error">Datenbankfehler.</p>
<?php
		login_gui::html_foot();
		exit(1);
	}

	if($_SESSION['ip'] != $_SERVER['REMOTE_ADDR'] && $me->checkSetting('ipcheck'))
	{
		if(isset($_COOKIE[session_name()]))
			setcookie(session_name(), '');
		die('Diese Session wird bereits von einer anderen IP-Adresse benutzt. Bitte <a href="http://'.htmlentities(get_default_hostname().h_root).'/index.php">neu anmelden</a>.');
	}

	if(isset($_SESSION['resume']) && $_SESSION['resume'])
	{
		$resume = true;
		unset($_SESSION['resume']);
	}

	# Wiederherstellen
	if($resume && $last_request = $me->lastRequest())
	{

		$_SESSION['act_planet'] = $last_request[1];
		$url = 'http://'.$databases[$_SESSION['database']][2].$last_request[0];
		$url = explode('?', $url, 2);
		if(isset($url[1]))
			$url[1] = explode('&', $url[1]);
		else
			$url[1] = array();
		$one = false;
		foreach($url[1] as $key=>$val)
		{
			$val = explode("=", $val, 2);
			if($val[0] == session_name())
			{
				$url[1][$key] = urlencode(session_name()).'='.urlencode(session_id());
				$one = true;
			}
		}
		$url2 = $url[0];
		if(count($url[1]) > 0)
			$url2 .= '?'.implode('&', $url[1]);
		$url = $url2;
		if(!$one)
		{
			if(strpos($url, '?') === false)
				$url .= '?';
			else
				$url .= '&';
			$url .= urlencode(session_name()).'='.urlencode(session_id());
		}
		header('Location: '.$url, true, 303);
		die('HTTP redirect: <a href="'.htmlentities($url).'">'.htmlentities($url).'</a>');
	}

	if(isset($_GET['planet']) && $me->planetExists($_GET['planet'])) # Planeten wechseln
		$_SESSION['act_planet'] = $_GET['planet'];
	if(!isset($_SESSION['act_planet']) || !$me->planetExists($_SESSION['act_planet']))
	{
		$planets = $me->getPlanetsList();
		$_SESSION['act_planet'] = array_shift($planets);
	}

	$me->setActivePlanet($_SESSION['act_planet']);

	if((!isset($_SESSION['ghost']) || !$_SESSION['ghost']) && !defined('ignore_action'))
		$me->registerAction();

	class login_gui
	{
		function html_head()
		{
			global $me;
			global $skins;
			global $databases;
	if(isset($_SESSION['freshlogin']))
		unset($_SESSION['freshlogin']);

?>
<?='<?xml version="1.0" encoding="UTF-8"?>'."\n"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
	<head>
		<meta name="author" content="The Big War">
		<meta name="publisher" content="The Big War">
		<meta name="copyright" content="The Big War">
		<meta name="page-topic" content="Multiplayer Online Game">
		<meta name="page-type" content="Browsergame">
		<meta name="audience" content="Alle">
		<meta name="description" content="T-B-W &ndash; The Big War ist ein Online-Spiel, fuer das man nur einen Firefox oder Opera Browser benoetigt. Bauen Sie sich im Weltraum ein kleines Imperium auf und kaempfen und handeln Sie mit Hunderten anderer Spielern.">
	        <meta name="keywords" content="onlinegame, gaming, allianz, handel, simulation, spiel, internet, freunde, community, handelsversicherung, wirtschaft, partner, zeitschrift, science-fiction, browsergame, kurse, freizeit, sicherheit, spass, handygame, sms, payment, werbung, kostenlos">
		<meta http-equiv="content-language" content="de">
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<title xml:lang="en">T-B-W &ndash; The Big War</title>
		
		<!-------------------------------->
		<!-- AdBlockPlus Blocken	-->
		<!-------------------------------->
		
		<!-- 
		<script>
			if(document.all){ci= new Array(1,2);}
			else{ci=Components.interfaces;}
			if("nsIAdblockPlus" in ci){
			document.write('ad block detected');
			var bod = document.getElementsByTagName("html");
			bod[0].innerHTML = '<p align="center"><font face="Century Gothic"><b>This page cannot be displayed because ad blocking software has been detected.</b></font></p>';
			} 
		</script>
		// -->
		
			


			<script type="text/javascript">
			var session_cookie = '<?=str_replace('\'', '\\\'', session_name())?>';
			var session_id = '<?=str_replace('\'', '\\\'', session_id())?>';
			var database_id = '<?=str_replace('\'', '\\\'', $_SESSION['database'])?>';
		</script>
		<script type="text/javascript" src="<?=htmlentities(h_root.'/login/scripts.js.php')?>"></script>
<?php
			if($me->checkSetting('ajax'))
			{
?>
		<script type="text/javascript" src="<?=htmlentities(h_root.'/sarissa.js')?>"></script>
<?php
			}
?>
		<script type="text/javascript">
			set_time_globals(<?=time()+1?>);
		</script>
<?php
			$skin_path = '';
			$my_skin = $me->checkSetting('skin');
			if($my_skin)
			{
				if(!is_array($my_skin))
				{
					if(isset($skins['default']) && isset($skins['default'][1][$my_skin]))
						$my_skin = array('default', $my_skin);
					else $my_skin = array('custom', $my_skin);
					$me->setSetting('skin', $my_skin);
				}
				if($my_skin[0] == 'custom')
					$skin_path = $my_skin[1];
				elseif(isset($skins[$my_skin[0]]))
					$skin_path = h_root.'/login/style/skin.php?skin='.urlencode($my_skin[0]).'&type='.urlencode($my_skin[1]);
			}
			elseif(isset($skins['default']))
			{
				$keys = array_keys($skins['default'][1]);
				$skin_path = h_root.'/login/style/skin.php?skin=default&type='.urlencode(array_shift($keys));
			}

			if(trim($skin_path) != '')
			{
?>
		<link rel="stylesheet" href="<?=utf8_htmlentities($skin_path)?>" type="text/css" />
<?php
			}

			if($me->checkSetting('schrift'))
			{ # Schrift ueberschreiben
?>
		<style type="text/css">
			html { font-size:9pt; font-family:Arial,Tahoma,"Adobe Helvetica",sans-serif; }
		</style>
<?php
			}

			$class = 'planet-'.$me->getPlanetClass();
			if(!$me->checkSetting('noads'))
				$class .= ' mit-werbung';
			else
				$class .= ' ohne-werbung';
?>
	</head>
	<body class="<?=$class?>" id="body-root"><div id="content-1"><div id="content-2"><div id="content-3"><div id="content-4"><div id="content-5"><div id="content-6"><div id="content-7"><div id="content-8">
		<dl id="time">
			<dt>Serverzeit</dt>
			<dd id="time-server"><?=date('H:i:s', time()+1)?></dd>
		</dl>
		<script type="text/javascript">
			var dd_element = document.createElement('dd');
			dd_element.setAttribute('id', 'time-local');
			dd_element.appendChild(document.createTextNode(mk2(local_time_obj.getHours())+':'+mk2(local_time_obj.getMinutes())+':'+mk2(local_time_obj.getSeconds())));
			var dt_element = document.createElement('dt');
			dt_element.appendChild(document.createTextNode('Lokalzeit'));
			var time_element = document.getElementById('time');
			time_element.insertBefore(dd_element, time_element.firstChild);
			time_element.insertBefore(dt_element, dd_element);
			setInterval('time_up()', 1000);
		</script>
		<div id="navigation">
			<form action="<?=htmlentities($_SERVER['PHP_SELF'])?>" method="get" id="change-planet">
				<fieldset>
					<legend>Planet wechseln</legend>
<?php
			foreach($_GET as $key=>$val)
			{
				if($key == 'planet') continue;
?>
					<input type="hidden" name="<?=utf8_htmlentities($key)?>" value="<?=utf8_htmlentities($val)?>" />
<?php
			}
?>
					<select name="planet" onchange="if(this.value != <?=$_SESSION['act_planet']?>) this.form.submit();" onkeyup="if(this.value != <?=$_SESSION['act_planet']?>) this.form.submit();" accesskey="p" title="Ihre Planeten [P]">
<?php
			$planets = $me->getPlanetsList();
			foreach($planets as $planet)
			{
				$me->setActivePlanet($planet);
?>
						<option value="<?=utf8_htmlentities($planet)?>"<?=($planet == $_SESSION['act_planet']) ? ' selected="selected"' : ''?>><?=utf8_htmlentities($me->planetName())?> (<?=utf8_htmlentities($me->getPosString())?>)</option>
<?php
			}
			$me->setActivePlanet($_SESSION['act_planet']);
?>
					</select>
					<noscript><div><button type="submit">Wechseln</button></div></noscript>
				</fieldset>
			</form>
			<ul id="main-navigation">
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/index.php') ? ' class="active"' : ''?> id="navigation-index"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/index.php?<?=htmlentities(session_name().'='.urlencode(session_id()))?>" accesskey="ü"><kbd>Ü</kbd>bersicht</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/rohstoffe.php') ? ' class="active"' : ''?> id="navigation-rohstoffe"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/rohstoffe.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="r"><kbd>R</kbd>ohstoffe</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/gebaeude.php') ? ' class="active"' : ''?> id="navigation-gebaeude"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/gebaeude.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="g"><kbd>G</kbd>ebäude</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/forschung.php') ? ' class="active"' : ''?> id="navigation-forschung"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/forschung.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="f"><kbd>F</kbd>orschung</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/roboter.php') ? ' class="active"' : ''?> id="navigation-roboter"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/roboter.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="b">Ro<kbd>b</kbd>oter</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/flotten.php') ? ' class="active"' : ''?> id="navigation-flotten"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/flotten.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="l">F<kbd>l</kbd>otten</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/schiffswerft.php') ? ' class="active"' : ''?> id="navigation-schiffswerft"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/schiffswerft.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="s"><kbd>S</kbd>chiffswerft</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/verteidigung.php') ? ' class="active"' : ''?> id="navigation-verteidigung"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/verteidigung.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="v"><kbd>V</kbd>erteidigung</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/imperium.php') ? ' class="active"' : ''?> id="navigation-imperium"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/imperium.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="m">I<kbd>m</kbd>perium</a></li>
			</ul>
			<ul id="action-navigation">
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/karte.php') ? ' class="active"' : ''?> id="navigation-karte"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/karte.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="k"><kbd>K</kbd>arte</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/handelsrechner.php') ? ' class="active"' : ''?> id="navigation-handel"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/handelsrechner.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="d">Han<kbd>d</kbd>elsrechner</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/allianz.php') ? ' class="active"' : ''?> id="navigation-allianz"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="i">All<kbd>i</kbd>anz</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/verbuendete.php') ? ' class="active"' : ''?> id="navigation-verbuendete"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/verbuendete.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="e">V<kbd>e</kbd>rbündete</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/highscores.php') ? ' class="active"' : ''?> id="navigation-highscores"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/highscores.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" xml:lang="en" accesskey="o">Highsc<kbd>o</kbd>res</a></li>
               		<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/search.php') ? ' class="active"' : ''?> id="navigation-search"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/search.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="h">Suc<kbd>h</kbd>en</a></li>
               		<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/pranger.php') ? ' class="active"' : ''?> id="navigation-pranger"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/pranger.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="x">Pranger</a></li>
<!--				<li id="navigation-handel" xml:lang="en"><a href="/tbwforum/viewforum.php?f=5" target="_blank"><abbr title="Handel im Forum">Handel</abbr></a></li> -->

				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/nachrichten.php') ? ' class="active"' : ''?> id="navigation-nachrichten"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/nachrichten.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="c">Na<kbd>c</kbd>hrichten</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/help/dependencies.php') ? ' class="active"' : ''?> id="navigation-abhaengigkeiten"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/help/dependencies.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="a">Forschungsb<kbd>a</kbd>um</a></li>
				<li<?=($_SERVER['PHP_SELF'] == h_root.'/login/einstellungen.php') ? ' class="active"' : ''?> id="navigation-einstellungen"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/einstellungen.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" accesskey="t">Eins<kbd>t</kbd>ellungen</a></li>
<?php
			if(isset($_SESSION['admin_username']))
			{
?>
				<li id="navigation-abmelden"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/admin/index.php">Adminbereich</a></li>
<?php
			}
			else
			{
?>
				<li id="navigation-abmelden"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/scripts/logout.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>">Abmelden</a></li>
<?php
			}
?>
			</ul>
<?php
			if($me->checkSetting('show_extern'))
			{
?>
			<ul id="external-navigation">
				<li id="navigation-board" xml:lang="en"><a href="http://www.stephanlinden.net/forum/" target="_blank"><abbr title="Board / Forum">Forum</abbr></a></li>
<!--				<li id="navigation-rules" xml:lang="en"><a href="http://<?=htmlentities(get_default_hostname().h_root)?>/tbwforum/viewtopic.php?t=80" target="blank"><abbr title="Regeln">Regeln</abbr></a></li> -->
				<li id="navigation-faq" xml:lang="en"><a href="http://<?=htmlentities(get_default_hostname().h_root)?>/faq.php" target="_blank"><abbr title="Frequently Asked Questions">FAQ</abbr></a></li>
<!--				<li id="navigation-wiki" xml:lang="en"><a href="/mediawiki/" target="_blank"><abbr title="TBW-Wiki">Wiki</abbr></a></li> -->
				<li id="navigation-chat" xml:lang="en"><a href="http://<?=htmlentities(get_default_hostname().h_root)?>/chat.php" target="blank"><abbr title="Support / Chat (IRC)">Support / Chat</abbr></a></li>
				<li id="navigation-bug" xml:lang="en"><a href="https://mantis.jackinpoint.net/main_page.php" target="blank"><abbr title="Fehler melden">Fehler melden</abbr></a></li>

<!--				<li id="navigation-vote" xml:lang="en"><a href="http://bgs.gdynamite.de/charts_vote_1066.html" target="_blank"><img src="http://voting.gdynamite.de/images/gd_animbutton.gif" border="0"><br><abbr title="Please vote for TBW">Please vote for TBW</abbr></a></li> -->
			</ul>
<?php
			}
?>
<div class="donate" style="position:absolute; top:428px; left:8px;">
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_donations">
<input type="hidden" name="business" value="spenden@thebigwar.org">
<input type="hidden" name="lc" value="DE">
<input type="hidden" name="item_name" value="thebigwar.org">
<input type="hidden" name="currency_code" value="EUR">
<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_LG.gif:NonHostedGuest">
<input type="image" src="https://www.paypal.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen � mit PayPal.">
<img alt="" border="0" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1">
</form>
</div>

<div class="gdynamite"  style="position:absolute; top:459px; left:15px;">
<a href="http://bgs.gdynamite.de/charts_vote_1066.html" target="_blank"><img src="http://voting.gdynamite.de/images/gd_animbutton.gif" border="0"></a>
</div>

		</div>
		<ul id="gameinfo">
			<li class="username"><?=utf8_htmlentities($_SESSION['username'])?></li>
			<li class="database"><?=utf8_htmlentities($databases[$_SESSION['database']][1])?></li>
			<li class="version"><a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root)?>/login/changelog.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Changelog anzeigen">Version <?=VERSION?></a></li>
<?php
			if(($rev = get_revision()) !== false)
			{
?>
			<li class="revision">Revision <?=htmlspecialchars($rev)?></li>
<?php
			}
?>
		</ul>
<?php
			$cur_ress = $me->getRess();
?>
		<div id="content-9">
	            <dl id="ress" class="ress">
       	         <dt class="ress-carbon">Carbon</dt>
              	  <dd class="ress-carbon<?=($cur_ress[0]<0) ? " negativ" : ""?>" id="ress-carbon"><?=ths($cur_ress[0])?></dd>

	                <dt class="ress-aluminium">Aluminium</dt>
	                <dd class="ress-aluminium<?=($cur_ress[1]<0) ? " negativ" : ""?>" id="ress-aluminium"><?=ths($cur_ress[1])?></dd>

       	         <dt class="ress-wolfram">Wolfram</dt>
              	  <dd class="ress-wolfram<?=($cur_ress[2]<0) ? " negativ" : ""?>" id="ress-wolfram"><?=ths($cur_ress[2])?></dd>

	                <dt class="ress-radium">Radium</dt>
       	         <dd class="ress-radium<?=($cur_ress[3]<0) ? " negativ" : ""?>" id="ress-radium"><?=ths($cur_ress[3])?></dd>

       	         <dt class="ress-tritium">Tritium</dt>
              	  <dd class="ress-tritium<?=($cur_ress[4]<0) ? " negativ" : ""?>" id="ress-tritium"><?=ths($cur_ress[4])?></dd>

	                <dt class="ress-energie">Energie</dt>
       	         <dd class="ress-energie<?=($cur_ress[5]<0) ? " negativ" : ""?>" id="ress-energie"><?=ths($cur_ress[5])?></dd>
	            </dl>
		<div id="content-10"><div id="content-11"><div id="content-12"><div id="content-13">


<!-- Werbecode Beginn -->
<?php
			global $me;
			global $DISABLE_ADS;
			$aus = $me->checkSetting('noads');
			if($aus == 1)

			{
?>

<?php
			}
			else
            {
?>
<p>

                       <script type="text/javascript"><!--
                       google_ad_client = "pub-1723997077347194";
                       google_ad_width = 468;
                       google_ad_height = 60;
                       google_ad_format = "468x60_as";
                       google_ad_type = "text";
                       google_ad_channel = "";
                       google_color_border = "000000";
                       google_color_bg = "FFFFFF";
                       google_color_link = "000000";
                       google_color_text = "000000";
                       google_color_url = "008000";
                       //-->
                       </script>
                       <!-- <script type="text/javascript"                      src="http://pagead2.googlesyndication.com/pagead/show_ads.js">                        </script> -->

</P>

<?php
			}
?>

<!-- Werbecode Ende -->


<?php
			$locked_until = false;
			if($l = database_locked())
			{
			if($l !== true) $locked_until = $l;
?>
			<p id="gesperrt-hinweis" class="spiel"><strong>Das Spiel ist derzeit gesperrt.</strong><?php if($locked_until){?> <span id="restbauzeit-sperre">bis <?=date('Y-m-d, H:i:s', $locked_until)?>, Serverzeit</span><?php }?></p>
<?php
			}
			elseif($me->userLocked())
			{
 	                $l = $me->lockedUntil();
	                if($l) $locked_until = $l;
?>
			<p id="gesperrt-hinweis" class="account"><strong>Ihr Benutzeraccount ist gesperrt.</strong><?php if($locked_until){?> <span id="restbauzeit-sperre">bis <?=date('Y-m-d, H:i:s', $locked_until)?>, Serverzeit</span><?php }?></p>
<?php
			}
			elseif($me->umode())
			{
?>
				<p id="gesperrt-hinweis" class="urlaub"><strong>Ihr Benutzeraccount befindet sich im Urlaubsmodus.</strong></p>
<?php
  		            }
			     elseif($l = fleets_locked())
  		            {
			     if($l !== true) $locked_until = $l;
 ?>
  		            <p id="gesperrt-hinweis" class="flotten"><strong>Es herrscht eine Flottensperre f&uuml;r feindliche Fl&uuml;ge:</strong><?php if($locked_until){?> <span id="restbauzeit-sperre">bis <?=date('Y-m-d, H:i:s', $locked_until)?>, Serverzeit</span><?php }?></p>
<?php
  		            }
  		            if($locked_until)
  		            {
?>
  		                <script type="text/javascript">
  		                    init_countdown("sperre", <?=$locked_until?>, false);
  		                </script>

<?php
  		            }
?>
				<h1>Planet <em><?=utf8_htmlentities($me->planetName())?></em> <span class="koords">(<?=utf8_htmlentities($me->getPosString())?>)</span></h1>
<?php
			if($me->checkSetting('notify'))
			{
				global $message_type_names;

				$ncount = array(
					1 => 0,
					2 => 0,
					3 => 0,
					4 => 0,
					5 => 0,
					6 => 0,
					7 => 0
				);
				$ges_ncount = 0;

				$cats = $me->getMessageCategoriesList();
				foreach($cats as $cat)
				{
					$message_ids = $me->getMessagesList($cat);
					foreach($message_ids as $message)
					{
						$status = $me->checkMessageStatus($message, $cat);
						if($status == 1 && $cat != 8)
						{
							$ncount[$cat]++;
							$ges_ncount++;
						}
					}
				}

				if($ges_ncount > 0)
				{
					$title = array();
					$link = 'nachrichten.php';
					foreach($ncount as $type=>$count)
					{
						if($count > 0)
							$title[] = utf8_htmlentities($message_type_names[$type]).':&nbsp;'.htmlentities($count);
						if($count == $ges_ncount)
							$link .= '?type='.urlencode($type);
					}
					$title = implode('; ', $title);
					if(strpos($link, '?') === false)
						$link .= '?';
					else
						$link .= '&';
					$link .= urlencode(session_name()).'='.urlencode(session_id());
?>
<p class="neue-nachrichten">
	<a href="<?=htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root.'/login/'.$link)?>" title="<?=$title?>">Sie haben <?=htmlentities($ges_ncount)?> neue <kbd>N</kbd>achricht<?=($ges_ncount != 1) ? 'en' : ''?>.</a>
</p>
<?php
				}
			}
		}

		function html_foot()
		{
			global $me;
?>
			</div></div>

		<div id="css-1"></div>
		</div></div>
		<div id="css-2"></div>
		</div>
		<div id="css-3"></div>
		</div></div></div></div></div></div></div></div>
		<div id="css-4"></div>
<?php
			if($me->checkSetting('tooltips') || $me->checkSetting('shortcuts') || $me->checkSetting('ress_refresh') > 0)
			{
?>
		<script type="text/javascript">
<?php
				if($me->checkSetting('shortcuts'))
				{
?>
			get_key_elements();
<?php
				}
				if($me->checkSetting('tooltips'))
				{
?>
			load_titles();
<?php
				}
				if($me->checkSetting('ress_refresh') > 0)
				{
					$ress = $me->getRess();
					$prod = $me->getProduction();
?>
			refresh_ress(<?=$me->checkSetting('ress_refresh')*1000?>, <?=$ress[0]?>, <?=$ress[1]?>, <?=$ress[2]?>, <?=$ress[3]?>, <?=$ress[4]?>, <?=$prod[0]?>, <?=$prod[1]?>, <?=$prod[2]?>, <?=$prod[3]?>, <?=$prod[4]?>);
<?php
				}
?>
		</script>
<?php
			}
?>
	</body>
</html>
<?php
		}
	}

	function delete_request()
	{
		$_SESSION['last_click_ignore'] = true;
		$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.urlencode(session_name()).'='.urlencode(session_id());
		header('Location: '.$url, true, 303);
		die('HTTP redirect: <a href="'.htmlentities($url).'">'.htmlentities($url).'</a>');
	}
?>
